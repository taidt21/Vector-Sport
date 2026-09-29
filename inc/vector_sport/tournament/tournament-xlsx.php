<?php
defined('ABSPATH') || exit;

/* =========================================================================
 * XUẤT EXCEL — ghi trực tiếp .xlsx bằng ZipArchive, không cần thư viện
 * ngoài (PhpSpreadsheet đòi Composer, nhiều hosting WordPress không có).
 * ========================================================================= */

/**
 * Tạo workbook .xlsx có NHIỀU sheet, không cần thư viện ngoài.
 *
 * $sheets có dạng:
 * [
 *     ['name' => 'Đơn vị', 'rows' => [[...], [...]]],
 *     ['name' => 'VĐV', 'rows' => [[...], [...]]],
 * ]
 *
 * Chỉ ghi cell text/số bằng inline string. Cách này đủ cho dữ liệu đăng
 * ký giải đấu, đồng thời giữ theme không phụ thuộc Composer/PhpSpreadsheet.
 */
function vs_build_xlsx_workbook(array $sheets): string {
    if (empty($sheets)) {
        $sheets = [
            ['name' => 'Sheet1', 'rows' => []],
        ];
    }

    $col_letter = function (int $n): string {
        $letters = '';
        $n++;
        while ($n > 0) {
            $rem = ($n - 1) % 26;
            $letters = chr(65 + $rem) . $letters;
            $n = intdiv($n - 1, 26);
        }
        return $letters;
    };

    $xml_escape = function ($v): string {
        return htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    };

    // Excel giới hạn tên sheet 31 ký tự và không cho các ký tự này.
    // Đồng thời bảo đảm không có 2 sheet trùng tên sau khi rút gọn.
    $used_names = [];
    $normalised_sheets = [];

    foreach (array_values($sheets) as $sheet_index => $sheet) {
        $raw_name = isset($sheet['name']) ? (string) $sheet['name'] : ('Sheet' . ($sheet_index + 1));
        $name = preg_replace('~[\\/\?\*\[\]:]+~u', '-', trim($raw_name));
        $name = $name !== '' ? $name : ('Sheet' . ($sheet_index + 1));
        $name = function_exists('mb_substr') ? mb_substr($name, 0, 31, 'UTF-8') : substr($name, 0, 31);

        $base_name = $name;
        $suffix = 2;
        while (isset($used_names[$name])) {
            $suffix_text = ' (' . $suffix . ')';
            $max_base_len = 31 - strlen($suffix_text);
            $short_base = function_exists('mb_substr')
                ? mb_substr($base_name, 0, $max_base_len, 'UTF-8')
                : substr($base_name, 0, $max_base_len);
            $name = $short_base . $suffix_text;
            $suffix++;
        }
        $used_names[$name] = true;

        $normalised_sheets[] = [
            'name' => $name,
            'rows' => (isset($sheet['rows']) && is_array($sheet['rows'])) ? $sheet['rows'] : [],
        ];
    }

    $content_types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';

    $workbook_sheets_xml = '';
    $workbook_rels_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    $worksheet_files = [];

    foreach ($normalised_sheets as $sheet_index => $sheet) {
        $sheet_no = $sheet_index + 1;
        $sheet_rows_xml = '';

        foreach ($sheet['rows'] as $r_idx => $row) {
            $row_num = $r_idx + 1;
            $cells_xml = '';

            foreach (array_values((array) $row) as $c_idx => $val) {
                $ref = $col_letter($c_idx) . $row_num;

                if (is_int($val) || is_float($val)) {
                    $cells_xml .= '<c r="' . $ref . '"><v>' . $xml_escape($val) . '</v></c>';
                } else {
                    $cells_xml .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">'
                        . $xml_escape($val)
                        . '</t></is></c>';
                }
            }

            $sheet_rows_xml .= '<row r="' . $row_num . '">' . $cells_xml . '</row>';
        }

        $worksheet_files['xl/worksheets/sheet' . $sheet_no . '.xml'] =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $sheet_rows_xml . '</sheetData>'
            . '</worksheet>';

        $content_types .= '<Override PartName="/xl/worksheets/sheet' . $sheet_no . '.xml" '
            . 'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';

        $workbook_sheets_xml .= '<sheet name="' . $xml_escape($sheet['name']) . '" sheetId="' . $sheet_no
            . '" r:id="rId' . $sheet_no . '"/>';

        $workbook_rels_xml .= '<Relationship Id="rId' . $sheet_no
            . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
            . 'Target="worksheets/sheet' . $sheet_no . '.xml"/>';
    }

    $content_types .= '</Types>';
    $workbook_rels_xml .= '</Relationships>';

    $root_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';

    $workbook_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
        . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets>' . $workbook_sheets_xml . '</sheets>'
        . '</workbook>';

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $tmp_path = wp_tempnam('vs-xlsx');
    $zip = new ZipArchive();

    if ($zip->open($tmp_path, ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Không tạo được file Excel tạm.');
    }

    $zip->addEmptyDir('_rels');
    $zip->addEmptyDir('xl');
    $zip->addEmptyDir('xl/_rels');
    $zip->addEmptyDir('xl/worksheets');
    $zip->addFromString('[Content_Types].xml', $content_types);
    $zip->addFromString('_rels/.rels', $root_rels);
    $zip->addFromString('xl/workbook.xml', $workbook_xml);
    $zip->addFromString('xl/_rels/workbook.xml.rels', $workbook_rels_xml);

    foreach ($worksheet_files as $path => $xml) {
        $zip->addFromString($path, $xml);
    }

    $zip->close();

    $binary = file_get_contents($tmp_path);
    wp_delete_file($tmp_path);

    if ($binary === false) {
        throw new RuntimeException('Không đọc được file Excel vừa tạo.');
    }

    return $binary;
}

/**
 * Giữ API cũ cho các chỗ đang tạo file 1 sheet (ví dụ file mẫu VĐV).
 */
function vs_build_xlsx(array $rows, string $sheet_name = 'Sheet1'): string {
    return vs_build_xlsx_workbook([
        [
            'name' => $sheet_name,
            'rows' => $rows,
        ],
    ]);
}

/**
 * Đọc file .xlsx do TRƯỞNG ĐOÀN tải lên — không cần thư viện ngoài.
 * Xử lý cả 2 kiểu Excel thật sự dùng:
 *  - "shared strings" (xl/sharedStrings.xml + cell t="s" trỏ theo index)
 *    — cách Excel/LibreOffice/Google Sheets THẬT SỰ xuất ra.
 *  - inline string (t="inlineStr") — cách bộ ghi ở trên (vs_build_xlsx)
 *    và cả openpyxl (Python) dùng.
 * Trả về mảng 2 chiều theo đúng vị trí dòng/cột — ô nào Excel bỏ trống
 * (không xuất hiện trong XML, rất hay gặp) vẫn được điền rỗng ĐÚNG VỊ
 * TRÍ, không làm lệch các cột phía sau.
 *
 * @throws RuntimeException Nếu file không mở được hoặc sai cấu trúc.
 */
function vs_read_xlsx(string $file_path): array {
    $zip = new ZipArchive();
    if ($zip->open($file_path) !== true) {
        throw new RuntimeException('Không mở được file — có thể không phải file .xlsx hợp lệ.');
    }

    $shared_strings = [];
    $shared_xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($shared_xml !== false) {
        $shared_dom = simplexml_load_string($shared_xml);
        if ($shared_dom !== false) {
            foreach ($shared_dom->si as $si) {
                // Thẻ si trong sharedStrings.xml có thể chứa 1 thẻ t đơn
                // giản, hoặc nhiều cặp thẻ r/t (rich
                // text nhiều định dạng trong cùng 1 ô) — nối hết lại.
                if (isset($si->t)) {
                    $shared_strings[] = (string) $si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                    $shared_strings[] = $text;
                }
            }
        }
    }

    // Tên file vật lý của sheet ĐẦU TIÊN (theo đúng thứ tự hiển thị
    // trong Excel) KHÔNG LUÔN LÀ "sheet1.xml" — nếu người dùng đã từng
    // xoá/thêm/sắp xếp lại sheet, Excel có thể đặt tên file khác. Đọc
    // đúng qua workbook.xml (thứ tự thẻ sheet = thứ tự hiển thị) +
    // workbook.xml.rels (map r:id -> tên file thật) — rơi về "sheet1.xml"
    // nếu vì lý do gì đó không đọc được cấu trúc này (file thiếu chuẩn).
    $sheet_target = 'worksheets/sheet1.xml';
    $workbook_xml = $zip->getFromName('xl/workbook.xml');
    $rels_xml = $zip->getFromName('xl/_rels/workbook.xml.rels');

    if ($workbook_xml !== false && $rels_xml !== false) {
        $workbook_dom = simplexml_load_string($workbook_xml);
        $rels_dom = simplexml_load_string($rels_xml);

        if ($workbook_dom !== false && $rels_dom !== false && isset($workbook_dom->sheets->sheet[0])) {
            $r_namespace = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
            $first_sheet_rid = (string) $workbook_dom->sheets->sheet[0]->attributes($r_namespace)['id'];

            if ($first_sheet_rid !== '') {
                foreach ($rels_dom->Relationship as $rel) {
                    if ((string) $rel['Id'] === $first_sheet_rid) {
                        $sheet_target = (string) $rel['Target'];
                        break;
                    }
                }
            }
        }
    }

    $sheet_xml = $zip->getFromName('xl/' . ltrim($sheet_target, '/'));
    $zip->close();

    if ($sheet_xml === false) {
        throw new RuntimeException('File không đúng cấu trúc .xlsx (không tìm thấy sheet đầu tiên).');
    }

    $dom = simplexml_load_string($sheet_xml);
    if ($dom === false) {
        throw new RuntimeException('Không đọc được nội dung sheet — file có thể bị hỏng.');
    }

    // Sheet ĐẦU TIÊN hoàn toàn trống (chưa từng nhập gì) đôi khi không
    // xuất ra thẻ sheetData nào cả (khác với thẻ sheetData tự đóng vẫn
    // đọc được bình thường) — lúc đó ->row trả về null thật, không phải
    // 1 danh sách rỗng, foreach thẳng vào sẽ vỡ. Coi đây là "không có
    // dữ liệu" và báo lỗi RÕ RÀNG, thay vì để PHP tự cảnh báo khó hiểu.
    if (!isset($dom->sheetData) || $dom->sheetData->row === null) {
        throw new RuntimeException('Sheet đầu tiên trong file không có dữ liệu — kiểm tra lại đã điền đúng vào sheet đầu tiên (không phải sheet khác) chưa.');
    }

    // "A1" -> cột 0 (A=0, B=1... Z=25, AA=26...) — dùng để biết ô đang ở
    // ĐÚNG cột nào, không dựa vào thứ tự xuất hiện trong XML (Excel có
    // thể bỏ qua ô trống, không xuất thẻ cell cho ô đó).
    $col_to_index = function (string $ref): int {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $letters = $m[1] ?? 'A';
        $index = 0;
        foreach (str_split($letters) as $ch) {
            $index = $index * 26 + (ord($ch) - 64);
        }
        return $index - 1;
    };

    $rows = [];
    foreach ($dom->sheetData->row as $row_xml) {
        $row_data = [];
        foreach ($row_xml->c as $cell) {
            $ref = (string) $cell['r'];
            $type = (string) $cell['t'];
            $col_index = $col_to_index($ref);

            if ($type === 'inlineStr') {
                $value = isset($cell->is->t) ? (string) $cell->is->t : '';
            } elseif ($type === 's') {
                $idx = (int) $cell->v;
                $value = $shared_strings[$idx] ?? '';
            } else {
                // Không có t (số thường) hoặc t="n"/"str" — lấy thẳng giá trị thẻ v.
                $value = isset($cell->v) ? (string) $cell->v : '';
            }

            $row_data[$col_index] = $value;
        }

        if (!empty($row_data)) {
            ksort($row_data);
            $max_col = max(array_keys($row_data));
            $full_row = [];
            for ($i = 0; $i <= $max_col; $i++) {
                $full_row[] = $row_data[$i] ?? '';
            }
            $rows[] = $full_row;
        }
    }

    return $rows;
}
