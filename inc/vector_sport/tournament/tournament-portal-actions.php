<?php
defined('ABSPATH') || exit;

/**
 * Xử lý mọi thao tác GHI của trang Quản lý đội — sửa thông tin đội, thêm/
 * sửa/xóa VĐV. MỌI câu lệnh đều ràng buộc registration_id = đúng đội của
 * $user_id đang đăng nhập, kể cả khi athlete_id trong request bị sửa tay
 * để trỏ sang VĐV của đội khác — điều kiện WHERE không khớp thì không
 * xóa/sửa được gì, không phải dựa vào việc "tin" dữ liệu gửi lên.
 */
function vs_handle_team_portal_actions() {
    global $wpdb;

    if (!is_user_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $portal_action = isset($_POST['vs_team_portal_action'])
        ? sanitize_key(wp_unslash($_POST['vs_team_portal_action']))
        : '';

    if (!in_array($portal_action, ['update_team', 'save_athlete', 'delete_athlete', 'add_athletes_bulk', 'update_athlete_avatar', 'import_athletes_excel', 'delete_athletes_bulk', 'save_staff', 'add_staff_bulk', 'delete_staff', 'update_staff_avatar'], true)) {
        return;
    }

    $registration = vs_get_my_tournament_registration();
    if (!$registration) {
        return;
    }

    $my_registration_id = (int) $registration->id;
    $portal_url = home_url('/quan-ly-doi/');

    if (
        !isset($_POST['vs_team_portal_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['vs_team_portal_nonce'])),
            'vs_team_portal_' . $my_registration_id
        )
    ) {
        wp_safe_redirect(add_query_arg('vs_msg', 'invalid', $portal_url));
        exit;
    }

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;

    if ($portal_action === 'update_team') {

        $ten_don_vi = isset($_POST['ten_don_vi']) ? sanitize_text_field(wp_unslash($_POST['ten_don_vi'])) : '';
        $nguoi_lien_he = isset($_POST['nguoi_lien_he']) ? sanitize_text_field(wp_unslash($_POST['nguoi_lien_he'])) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $ghi_chu = isset($_POST['ghi_chu']) ? sanitize_textarea_field(wp_unslash($_POST['ghi_chu'])) : '';
        $phone_clean = preg_replace('/[\s\.\-]/', '', $phone);

        if (!$ten_don_vi || !$nguoi_lien_he || !preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone_clean)) {
            wp_safe_redirect(add_query_arg('vs_msg', 'invalid', $portal_url));
            exit;
        }
$logo_id = null;

if (!empty($_FILES['logo_don_vi']['name'])) {

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';


    $upload_id = media_handle_upload(
        'logo_don_vi',
        0
    );


    if (!is_wp_error($upload_id)) {
        $logo_id = $upload_id;
    }
}
$data = [
    'ten_don_vi'    => $ten_don_vi,
    'nguoi_lien_he' => $nguoi_lien_he,
    'phone'         => $phone_clean,
    'ghi_chu'       => $ghi_chu,
];


$formats = [
    '%s',
    '%s',
    '%s',
    '%s',
];


if ($logo_id) {

    $data['logo_id'] = $logo_id;
    $formats[] = '%d';

}


$wpdb->update(
    $reg_table,
    $data,
    [
        'id'=>$my_registration_id
    ],
    $formats,
    [
        '%d'
    ]
);

        wp_safe_redirect(add_query_arg('vs_msg', 'team_saved', $portal_url));
        exit;
    }

    // Đổi NHANH ảnh 1 VĐV ngay từ hàng trong bảng — không đụng tới các
    // trường khác (họ tên, nội dung...), chỉ riêng ảnh.
    if ($portal_action === 'update_athlete_avatar') {
        $athlete_id = isset($_POST['athlete_id']) ? absint($_POST['athlete_id']) : 0;
        $avatar_result = vs_handle_athlete_avatar_upload($_FILES['anh_dai_dien'] ?? null);

        if ($avatar_result === false) {
            wp_safe_redirect(add_query_arg('vs_msg', 'avatar_invalid', $portal_url));
            exit;
        }

        if ($avatar_result === null) {
            // Bấm nhầm/chưa chọn file gì — không có gì để lưu, quay lại
            // bình thường, không báo lỗi (không phải người dùng làm sai).
            wp_safe_redirect($portal_url);
            exit;
        }

        // Cùng chốt an toàn — WHERE có cả id lẫn registration_id.
        $wpdb->update(
            $ath_table,
            ['anh_dai_dien_id' => $avatar_result],
            ['id' => $athlete_id, 'registration_id' => $my_registration_id],
            ['%d'],
            ['%d', '%d']
        );

        wp_safe_redirect(add_query_arg('vs_msg', 'athlete_saved', $portal_url));
        exit;
    }

    // 3 nhánh dưới đây cho Trưởng đoàn/HLV — CÙNG NGUYÊN TẮC AN TOÀN với
    // VĐV ở trên: mọi UPDATE/DELETE đều ràng buộc registration_id = đúng
    // đội đang đăng nhập trong điều kiện WHERE, không dựa vào "tin"
    // staff_id gửi lên.
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;

    if ($portal_action === 'update_staff_avatar') {
        $staff_id = isset($_POST['staff_id']) ? absint($_POST['staff_id']) : 0;
        $avatar_result = vs_handle_athlete_avatar_upload($_FILES['anh_dai_dien'] ?? null);

        if ($avatar_result === false) {
            wp_safe_redirect(add_query_arg('vs_msg', 'avatar_invalid', $portal_url));
            exit;
        }
        if ($avatar_result === null) {
            wp_safe_redirect($portal_url);
            exit;
        }

        $wpdb->update(
            $staff_table,
            ['anh_dai_dien_id' => $avatar_result],
            ['id' => $staff_id, 'registration_id' => $my_registration_id],
            ['%d'],
            ['%d', '%d']
        );

        wp_safe_redirect(add_query_arg('vs_msg', 'staff_saved', $portal_url));
        exit;
    }

    if ($portal_action === 'delete_staff') {
        $staff_id = isset($_POST['staff_id']) ? absint($_POST['staff_id']) : 0;

        $wpdb->delete(
            $staff_table,
            ['id' => $staff_id, 'registration_id' => $my_registration_id],
            ['%d', '%d']
        );

        wp_safe_redirect(add_query_arg('vs_msg', 'staff_deleted', $portal_url));
        exit;
    }

    // Thêm NHIỀU thành viên Ban huấn luyện cùng lúc. Trưởng đoàn chỉ
    // được tồn tại tối đa 1 người trong mỗi đội, kể cả khi request bị sửa tay.
    if ($portal_action === 'add_staff_bulk') {
        $staff_rows = isset($_POST['staff']) && is_array($_POST['staff'])
            ? wp_unslash($_POST['staff'])
            : [];

        if (!$staff_rows) {
            wp_safe_redirect(add_query_arg('vs_msg', 'invalid', $portal_url));
            exit;
        }

        $existing_leader = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$staff_table} WHERE registration_id = %d AND vai_tro = %s",
                $my_registration_id,
                'truong_doan'
            )
        );

        $clean_rows = [];
        $new_leader_count = 0;

        foreach ($staff_rows as $row_index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $ho_ten = isset($row['ho_ten']) ? sanitize_text_field($row['ho_ten']) : '';
            $vai_tro = isset($row['vai_tro']) ? sanitize_key($row['vai_tro']) : '';

            // Dòng rỗng hoàn toàn có thể xuất hiện nếu người dùng thêm rồi bỏ trống;
            // bỏ qua để không làm hỏng những dòng đã nhập hợp lệ khác.
            if ($ho_ten === '' && $vai_tro === '') {
                continue;
            }

            if ($ho_ten === '' || !in_array($vai_tro, VS_TOURNAMENT_STAFF_ROLES, true)) {
                wp_safe_redirect(add_query_arg('vs_msg', 'invalid', add_query_arg('view', 'add_staff', $portal_url)));
                exit;
            }

            if ($vai_tro === 'truong_doan') {
                $new_leader_count++;
            }

            $clean_rows[(string) $row_index] = [
                'ho_ten' => $ho_ten,
                'vai_tro' => $vai_tro,
            ];
        }

        if (!$clean_rows) {
            wp_safe_redirect(add_query_arg('vs_msg', 'invalid', add_query_arg('view', 'add_staff', $portal_url)));
            exit;
        }

        if ($new_leader_count > 1 || ($existing_leader > 0 && $new_leader_count > 0)) {
            wp_safe_redirect(add_query_arg('vs_msg', 'staff_leader_exists', add_query_arg('view', 'add_staff', $portal_url)));
            exit;
        }

        $inserted = 0;

        foreach ($clean_rows as $row_index => $row) {
            $data = [
                'registration_id' => $my_registration_id,
                'ho_ten' => $row['ho_ten'],
                'vai_tro' => $row['vai_tro'],
            ];
            $format = ['%d', '%s', '%s'];

            // File input dùng staff_avatar[INDEX] để mỗi khối có 1 ảnh riêng.
            if (
                isset($_FILES['staff_avatar']['name'][$row_index]) &&
                $_FILES['staff_avatar']['name'][$row_index] !== ''
            ) {
                $avatar_file = [
                    'name' => $_FILES['staff_avatar']['name'][$row_index],
                    'type' => $_FILES['staff_avatar']['type'][$row_index] ?? '',
                    'tmp_name' => $_FILES['staff_avatar']['tmp_name'][$row_index] ?? '',
                    'error' => $_FILES['staff_avatar']['error'][$row_index] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $_FILES['staff_avatar']['size'][$row_index] ?? 0,
                ];

                $avatar_result = vs_handle_athlete_avatar_upload($avatar_file);
                if ($avatar_result === false) {
                    wp_safe_redirect(add_query_arg('vs_msg', 'avatar_invalid', add_query_arg('view', 'add_staff', $portal_url)));
                    exit;
                }
                if ($avatar_result !== null) {
                    $data['anh_dai_dien_id'] = $avatar_result;
                    $format[] = '%d';
                }
            }

            $ok = $wpdb->insert($staff_table, $data, $format);
            if ($ok !== false) {
                $inserted++;
            }
        }

        wp_safe_redirect(
            add_query_arg(
                ['vs_msg' => 'staff_bulk_saved', 'count' => $inserted],
                $portal_url
            )
        );
        exit;
    }

    // Dùng chung 1 nhánh cho cả THÊM MỚI (staff_id=0) lẫn SỬA (staff_id>0)
    // — khác VĐV (tách riêng add_athletes_bulk/save_athlete) vì Trưởng
    // đoàn/HLV thường chỉ 1-2 người mỗi đội, không cần nhập nhiều dòng
    // cùng lúc như VĐV.
    if ($portal_action === 'save_staff') {
        $staff_id = isset($_POST['staff_id']) ? absint($_POST['staff_id']) : 0;
        $ho_ten = isset($_POST['ho_ten']) ? sanitize_text_field(wp_unslash($_POST['ho_ten'])) : '';
        $vai_tro = isset($_POST['vai_tro']) ? sanitize_key(wp_unslash($_POST['vai_tro'])) : '';

        if ($ho_ten === '' || !in_array($vai_tro, VS_TOURNAMENT_STAFF_ROLES, true)) {
            wp_safe_redirect(add_query_arg('vs_msg', 'invalid', $portal_url));
            exit;
        }

        if ($vai_tro === 'truong_doan') {
            $other_leader = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$staff_table} WHERE registration_id = %d AND vai_tro = %s AND id <> %d",
                    $my_registration_id,
                    'truong_doan',
                    $staff_id
                )
            );

            if ($other_leader > 0) {
                wp_safe_redirect(add_query_arg('vs_msg', 'staff_leader_exists', $portal_url));
                exit;
            }
        }

        $data = ['ho_ten' => $ho_ten, 'vai_tro' => $vai_tro];
        $format = ['%s', '%s'];

        $avatar_file = $_FILES['anh_dai_dien'] ?? null;
        $avatar_result = $avatar_file ? vs_handle_athlete_avatar_upload($avatar_file) : null;
        if ($avatar_result === false) {
            wp_safe_redirect(add_query_arg('vs_msg', 'avatar_invalid', $portal_url));
            exit;
        }
        if ($avatar_result !== null) {
            $data['anh_dai_dien_id'] = $avatar_result;
            $format[] = '%d';
        }

        if ($staff_id > 0) {
            // Sửa người đã có — CÙNG chốt an toàn registration_id trong WHERE.
            $wpdb->update(
                $staff_table,
                $data,
                ['id' => $staff_id, 'registration_id' => $my_registration_id],
                $format,
                ['%d', '%d']
            );
        } else {
            $data['registration_id'] = $my_registration_id;
            $format[] = '%d';
            $wpdb->insert($staff_table, $data, $format);
        }

        wp_safe_redirect(add_query_arg('vs_msg', 'staff_saved', $portal_url));
        exit;
    }

    if ($portal_action === 'delete_athlete') {
        $athlete_id = isset($_POST['athlete_id']) ? absint($_POST['athlete_id']) : 0;

        // registration_id = $my_registration_id trong điều kiện xóa —
        // chốt an toàn thật sự, không phải chỉ kiểm tra athlete_id tồn tại.
        $wpdb->delete(
            $ath_table,
            ['id' => $athlete_id, 'registration_id' => $my_registration_id],
            ['%d', '%d']
        );

        wp_safe_redirect(add_query_arg('vs_msg', 'athlete_deleted', $portal_url));
        exit;
    }

    // Xoá NHIỀU VĐV cùng lúc (tích chọn hàng loạt) — LẶP LẠI đúng chốt an
    // toàn của xoá từng người (registration_id = đúng đội của tôi) cho
    // TỪNG ID một, không tin tưởng danh sách ID gửi lên là đã lọc đúng
    // quyền — dù athlete_ids có bị chèn ID của đội khác vào, điều kiện
    // WHERE vẫn không khớp, không xoá được gì ngoài đúng đội mình.
    if ($portal_action === 'delete_athletes_bulk') {
        $athlete_ids = isset($_POST['athlete_ids']) && is_array($_POST['athlete_ids'])
            ? array_map('absint', $_POST['athlete_ids'])
            : [];

        $deleted_count = 0;
        foreach ($athlete_ids as $athlete_id) {
            if ($athlete_id <= 0) {
                continue;
            }
            $affected = $wpdb->delete(
                $ath_table,
                ['id' => $athlete_id, 'registration_id' => $my_registration_id],
                ['%d', '%d']
            );
            $deleted_count += (int) $affected;
        }

        wp_safe_redirect(add_query_arg(
            ['vs_msg' => 'athletes_bulk_deleted', 'count' => $deleted_count],
            $portal_url
        ));
        exit;
    }

    // Sửa ĐÚNG 1 VĐV đã có — thêm mới giờ luôn đi qua add_athletes_bulk
    // bên dưới (cho phép nhiều dòng), nên ở đây chỉ còn nhánh sửa.
    if ($portal_action === 'save_athlete') {
        $athlete_id = isset($_POST['athlete_id']) ? absint($_POST['athlete_id']) : 0;

        $ho_ten = isset($_POST['ho_ten']) ? sanitize_text_field(wp_unslash($_POST['ho_ten'])) : '';
        $nam_sinh = isset($_POST['nam_sinh']) ? (int) $_POST['nam_sinh'] : 0;
        $gioi_tinh = isset($_POST['gioi_tinh']) ? sanitize_text_field(wp_unslash($_POST['gioi_tinh'])) : '';
        $nhom_tuoi = isset($_POST['nhom_tuoi']) ? sanitize_text_field(wp_unslash($_POST['nhom_tuoi'])) : '';
        $current_year = (int) gmdate('Y');

        $valid = $athlete_id > 0
            && $ho_ten !== ''
            && $nam_sinh >= 1970 && $nam_sinh <= $current_year
            && in_array($gioi_tinh, ['Nam', 'Nữ'], true)
            && in_array($nhom_tuoi, vs_get_tournament_age_group_keys(), true);

        if (!$valid) {
            wp_safe_redirect(add_query_arg('vs_msg', 'invalid', $portal_url));
            exit;
        }

        $valid_events_for_row = vs_get_valid_events_for_nhom_tuoi($nhom_tuoi);
        $noi_dung_raw = isset($_POST['noi_dung']) && is_array($_POST['noi_dung']) ? wp_unslash($_POST['noi_dung']) : [];
        $noi_dung_clean = [];
        foreach ($noi_dung_raw as $nd) {
            $nd = sanitize_text_field($nd);
            if (in_array($nd, $valid_events_for_row, true)) {
                $noi_dung_clean[] = $nd;
            }
        }

        // Có chọn ảnh mới thì thay — không chọn gì thì GIỮ NGUYÊN ảnh cũ
        // (null nghĩa là "không đổi", không phải "xoá ảnh").
        $data = [
            'ho_ten'    => $ho_ten,
            'nam_sinh'  => $nam_sinh,
            'gioi_tinh' => $gioi_tinh,
            'nhom_tuoi' => $nhom_tuoi,
            'noi_dung'  => implode('; ', $noi_dung_clean),
        ];
        $format = ['%s', '%d', '%s', '%s', '%s'];

        $avatar_file = $_FILES['anh_dai_dien'] ?? null;
        $avatar_result = $avatar_file ? vs_handle_athlete_avatar_upload($avatar_file) : null;

        if ($avatar_result === false) {
            wp_safe_redirect(add_query_arg('vs_msg', 'avatar_invalid', $portal_url));
            exit;
        }

        if ($avatar_result !== null) {
            $data['anh_dai_dien_id'] = $avatar_result;
            $format[] = '%d';
        }

        // Cùng chốt an toàn — WHERE có cả id lẫn registration_id, dù
        // athlete_id trong request có bị sửa để trỏ sang VĐV đội khác
        // cũng không khớp điều kiện, không sửa được gì.
        $wpdb->update(
            $ath_table,
            $data,
            ['id' => $athlete_id, 'registration_id' => $my_registration_id],
            $format,
            ['%d', '%d']
        );

        wp_safe_redirect(add_query_arg('vs_msg', 'athlete_saved', $portal_url));
        exit;
    }

    // Thêm NHIỀU VĐV cùng lúc — $_POST['athlete'] là mảng nhiều dòng,
    // giống hệt cách form đăng ký cũ từng xử lý. Bỏ qua dòng thiếu họ tên
    // (VD dòng trống còn sót lại lúc bấm thêm dòng rồi không điền) thay
    // vì chặn cả form — chỉ cần TỐI THIỂU 1 dòng hợp lệ là được.
    if ($portal_action === 'add_athletes_bulk') {
        $raw_athletes = isset($_POST['athlete']) && is_array($_POST['athlete']) ? wp_unslash($_POST['athlete']) : [];
        $current_year = (int) gmdate('Y');
        $avatar_files = vs_normalize_bulk_athlete_files();
        $to_insert = [];

        // Giữ nguyên $original_index (khác với thứ tự trong $to_insert
        // sau khi bỏ qua dòng trống) — cần đúng index này để khớp lại
        // với file ảnh đã upload cho ĐÚNG dòng đó.
        foreach ($raw_athletes as $original_index => $row) {
            $ho_ten = isset($row['ho_ten']) ? sanitize_text_field($row['ho_ten']) : '';
            if ($ho_ten === '') {
                continue;
            }

            $nam_sinh = isset($row['nam_sinh']) ? (int) $row['nam_sinh'] : 0;
            $gioi_tinh = isset($row['gioi_tinh']) ? sanitize_text_field($row['gioi_tinh']) : '';
            $nhom_tuoi = isset($row['nhom_tuoi']) ? sanitize_text_field((string) $row['nhom_tuoi']) : '';

            $row_valid = $nam_sinh >= 1970 && $nam_sinh <= $current_year
                && in_array($gioi_tinh, ['Nam', 'Nữ'], true)
                && in_array($nhom_tuoi, vs_get_tournament_age_group_keys(), true);

            if (!$row_valid) {
                wp_safe_redirect(add_query_arg('vs_msg', 'invalid', $portal_url));
                exit;
            }

            $valid_events_for_row = vs_get_valid_events_for_nhom_tuoi($nhom_tuoi);
            $noi_dung_raw = isset($row['noi_dung']) && is_array($row['noi_dung']) ? $row['noi_dung'] : [];
            $noi_dung_clean = [];
            foreach ($noi_dung_raw as $nd) {
                $nd = sanitize_text_field($nd);
                if (in_array($nd, $valid_events_for_row, true)) {
                    $noi_dung_clean[] = $nd;
                }
            }

            $row_data = [
                'ho_ten'    => $ho_ten,
                'nam_sinh'  => $nam_sinh,
                'gioi_tinh' => $gioi_tinh,
                'nhom_tuoi' => $nhom_tuoi,
                'noi_dung'  => implode('; ', $noi_dung_clean),
            ];

            if (isset($avatar_files[$original_index])) {
                $avatar_result = vs_handle_athlete_avatar_upload($avatar_files[$original_index]);

                if ($avatar_result === false) {
                    wp_safe_redirect(add_query_arg('vs_msg', 'avatar_invalid', $portal_url));
                    exit;
                }

                if ($avatar_result !== null) {
                    $row_data['anh_dai_dien_id'] = $avatar_result;
                }
            }

            $to_insert[] = $row_data;
        }

        if (empty($to_insert)) {
            wp_safe_redirect(add_query_arg('vs_msg', 'invalid', $portal_url));
            exit;
        }

        // Map theo TÊN khoá, không đoán vị trí — tránh lệch thứ tự nếu
        // anh_dai_dien_id có mặt (được thêm TRƯỚC registration_id ở bước
        // xây $row_data, không phải luôn ở cuối).
        $field_formats = [
            'ho_ten'          => '%s',
            'nam_sinh'        => '%d',
            'gioi_tinh'       => '%s',
            'nhom_tuoi'       => '%s',
            'noi_dung'        => '%s',
            'anh_dai_dien_id' => '%d',
            'registration_id' => '%d',
        ];

        foreach ($to_insert as $data) {
            $data['registration_id'] = $my_registration_id;
            $format = array_map(
                fn($key) => $field_formats[$key] ?? '%s',
                array_keys($data)
            );
            $wpdb->insert($ath_table, $data, $format);
        }

        wp_safe_redirect(add_query_arg('vs_msg', 'athlete_saved', $portal_url));
        exit;
    }

    // Nhập nhanh nhiều VĐV từ file Excel — chỉ CHỮ (họ tên/năm sinh/giới
    // tính/nhóm tuổi/nội dung), không có ảnh (ảnh vẫn thêm riêng từng
    // người ở form hoặc bấm nhanh trên hàng). Dòng nào không hợp lệ thì
    // BỎ QUA (không chặn cả file) — báo lại tổng số thêm được/bỏ qua để
    // trưởng đoàn tự biết cần sửa gì.
    if ($portal_action === 'import_athletes_excel') {
        $file = $_FILES['excel_file'] ?? null;

        if (empty($file['tmp_name']) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            wp_safe_redirect(add_query_arg('vs_msg', 'import_error', $portal_url));
            exit;
        }

        try {
            $rows = vs_read_xlsx($file['tmp_name']);
        } catch (\Throwable $e) {
            error_log('VS Import Excel Error: ' . $e->getMessage());
            wp_safe_redirect(add_query_arg(
                ['vs_msg' => 'import_error', 'chi_tiet' => rawurlencode($e->getMessage())],
                $portal_url
            ));
            exit;
        }

        // Bỏ dòng đầu (header "Họ tên"/"Năm sinh"/...) — nếu lỡ dòng đầu
        // KHÔNG PHẢI header thì dòng đó vẫn được validate như bình
        // thường ở dưới và bị loại nếu không hợp lệ, không mất an toàn,
        // chỉ mất đúng 1 dòng dữ liệu thật (hiếm khi xảy ra).
        array_shift($rows);

        $current_year = (int) gmdate('Y');
        $added = 0;
        $updated = 0;
        $skipped = 0;
        $unmatched_events = []; // dùng làm KHOÁ mảng để tự loại trùng lặp

        // VĐV đã có sẵn trong đội — dùng để phát hiện đã tồn tại. Khoá
        // theo họ tên (đã chuẩn hoá hoa/thường, khoảng trắng) + năm sinh,
        // vì 2 người trùng tên nhưng khác năm sinh vẫn là 2 người khác
        // nhau. Trỏ tới ID thật để CẬP NHẬT đúng dòng đó khi trùng — cho
        // phép trưởng đoàn sửa file (VD gõ đúng lại tên nội dung) rồi
        // nhập lại để CẬP NHẬT, thay vì bị chặn hẳn không sửa được.
        $existing_rows = $wpdb->get_results(
            $wpdb->prepare("SELECT id, ho_ten, nam_sinh FROM {$ath_table} WHERE registration_id = %d", $my_registration_id)
        );
        $existing_keys = [];
        foreach ($existing_rows as $er) {
            $existing_keys[mb_strtolower(trim($er->ho_ten), 'UTF-8') . '|' . (int) $er->nam_sinh] = (int) $er->id;
        }

        foreach ($rows as $row) {
            $ho_ten = isset($row[0]) ? sanitize_text_field(trim((string) $row[0])) : '';
            if ($ho_ten === '') {
                continue; // dòng trống hoàn toàn (VD dòng cuối file) — không tính là lỗi.
            }

            $nam_sinh = isset($row[1]) ? (int) $row[1] : 0;

            // Excel là chữ tự do, không phải dropdown — chấp nhận "nam",
            // "NAM", "Nam " đều hiểu là "Nam", đỡ phải gõ đúng tuyệt đối.
            $gioi_tinh_raw = isset($row[2]) ? mb_strtolower(trim((string) $row[2]), 'UTF-8') : '';
            $gioi_tinh = $gioi_tinh_raw === 'nam' ? 'Nam' : ($gioi_tinh_raw === 'nữ' ? 'Nữ' : '');

            $nhom_tuoi_raw = isset($row[3]) ? (string) $row[3] : '';
            $nhom_tuoi = vs_match_tournament_age_group($nhom_tuoi_raw);

            $noi_dung_text = isset($row[4]) ? (string) $row[4] : '';

            $row_valid = $nam_sinh >= 1970 && $nam_sinh <= $current_year
                && $gioi_tinh !== ''
                && in_array($nhom_tuoi, vs_get_tournament_age_group_keys(), true);

            if (!$row_valid) {
                $skipped++;
                continue;
            }

            // So khớp KHÔNG phân biệt hoa/thường (Excel là chữ tự do —
            // gõ "đối kháng nam - 42kg" hay "ĐỐI KHÁNG NAM - 42KG" đều
            // nên chấp nhận, không riêng gì đúng y hệt hoa/thường lúc
            // cấu hình). Lệch cấu trúc khác (thiếu dấu gạch ngang, sai
            // số...) VẪN bị loại như cũ — CỐ Ý không tự "đoán sửa" vì
            // rủi ro gán nhầm nội dung (VD "Đối kháng Nam - 42kg" và
            // "Đối kháng Nam - 45kg" chỉ khác 1 số). Khi khớp, LƯU LẠI
            // đúng tên đã cấu hình (không lưu nguyên văn người dùng gõ)
            // để dữ liệu nhất quán, không lẫn lộn hoa/thường tuỳ người.
            $valid_events_for_row = vs_get_valid_events_for_nhom_tuoi($nhom_tuoi);
            $valid_events_lower_map = [];
            foreach ($valid_events_for_row as $ve) {
                $valid_events_lower_map[mb_strtolower($ve, 'UTF-8')] = $ve;
            }

            $noi_dung_parts = preg_split('/[,;]/', $noi_dung_text);
            $noi_dung_clean = [];
            foreach ($noi_dung_parts as $nd) {
                $nd = sanitize_text_field(trim($nd));
                if ($nd === '') {
                    continue;
                }
                $nd_lower = mb_strtolower($nd, 'UTF-8');
                if (isset($valid_events_lower_map[$nd_lower])) {
                    $noi_dung_clean[] = $valid_events_lower_map[$nd_lower];
                } else {
                    $unmatched_events[$nd] = true;
                }
            }

            $dedup_key = mb_strtolower($ho_ten, 'UTF-8') . '|' . $nam_sinh;

            if (isset($existing_keys[$dedup_key])) {
                // ĐÃ CÓ người này (hoặc đã thêm ở 1 dòng trước đó TRONG
                // CHÍNH FILE ĐANG NHẬP) — CẬP NHẬT lại đúng dòng đó, CỐ
                // Ý không đụng tới anh_dai_dien_id (ảnh) vì Excel không
                // mang theo ảnh, không được phép xoá mất ảnh đã có sẵn.
                $wpdb->update(
                    $ath_table,
                    [
                        'gioi_tinh' => $gioi_tinh,
                        'nhom_tuoi' => $nhom_tuoi,
                        'noi_dung'  => implode('; ', $noi_dung_clean),
                    ],
                    ['id' => $existing_keys[$dedup_key], 'registration_id' => $my_registration_id],
                    ['%s', '%s', '%s'],
                    ['%d', '%d']
                );
                $updated++;
                continue;
            }

            $wpdb->insert(
                $ath_table,
                [
                    'registration_id' => $my_registration_id,
                    'ho_ten'          => $ho_ten,
                    'nam_sinh'        => $nam_sinh,
                    'gioi_tinh'       => $gioi_tinh,
                    'nhom_tuoi'       => $nhom_tuoi,
                    'noi_dung'        => implode('; ', $noi_dung_clean),
                ],
                ['%d', '%s', '%d', '%s', '%s', '%s']
            );
            $existing_keys[$dedup_key] = (int) $wpdb->insert_id;
            $added++;
        }

        // Danh sách tên không khớp có thể khá dài — lưu tạm 60 giây theo
        // đúng user thay vì nhét vào query string (dễ vượt giới hạn độ
        // dài URL), đọc ra đúng 1 lần lúc hiện thông báo rồi xoá.
        if (!empty($unmatched_events)) {
            set_transient(
                'vs_import_unmatched_' . get_current_user_id(),
                array_keys($unmatched_events),
                60
            );
        }

        wp_safe_redirect(add_query_arg(
            ['vs_msg' => 'import_done', 'added' => $added, 'updated' => $updated, 'skipped' => $skipped],
            $portal_url
        ));
        exit;
    }
}
add_action('init', 'vs_handle_team_portal_actions');


/**
 * Đổi ảnh VĐV qua AJAX — không load lại trang, dùng đúng admin-ajax.php
 * chuẩn của WordPress. Tái sử dụng CHÍNH XÁC cùng hàm upload + cùng chốt
 * an toàn (registration_id = đúng đội đang đăng nhập) như đường POST
 * thường (vs_handle_team_portal_actions, action=update_athlete_avatar) —
 * đường đó vẫn giữ lại làm phương án dự phòng nếu JS không chạy được.
 */
function vs_ajax_update_athlete_avatar() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Cần đăng nhập.'], 403);
    }

    $registration = vs_get_my_tournament_registration();
    if (!$registration) {
        wp_send_json_error(['message' => 'Không tìm thấy đội của bạn.'], 403);
    }

    check_ajax_referer('vs_team_portal_' . $registration->id, 'nonce');

    $athlete_id = isset($_POST['athlete_id']) ? absint($_POST['athlete_id']) : 0;
    $avatar_result = vs_handle_athlete_avatar_upload($_FILES['anh_dai_dien'] ?? null);

    if ($avatar_result === false) {
        wp_send_json_error(['message' => 'Ảnh không hợp lệ — chỉ nhận JPG/PNG/WEBP, tối đa 5MB.']);
    }

    if ($avatar_result === null) {
        wp_send_json_error(['message' => 'Chưa chọn file nào.']);
    }

    global $wpdb;
    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;

    // Cùng chốt an toàn — WHERE có cả id lẫn registration_id, dù
    // athlete_id trong request có bị sửa để trỏ sang VĐV đội khác cũng
    // không khớp điều kiện, không sửa được gì.
    $affected = $wpdb->update(
        $ath_table,
        ['anh_dai_dien_id' => $avatar_result],
        ['id' => $athlete_id, 'registration_id' => $registration->id],
        ['%d'],
        ['%d', '%d']
    );

    if (!$affected) {
        wp_send_json_error(['message' => 'Không tìm thấy VĐV này trong đội của bạn.']);
    }

    wp_send_json_success([
        'avatar_url' => wp_get_attachment_image_url($avatar_result, 'thumbnail'),
    ]);
}
add_action('wp_ajax_vs_update_athlete_avatar', 'vs_ajax_update_athlete_avatar');

/**
 * Y hệt vs_ajax_update_athlete_avatar() ở trên, cho Trưởng đoàn/HLV.
 */
function vs_ajax_update_staff_avatar() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Cần đăng nhập.'], 403);
    }

    $registration = vs_get_my_tournament_registration();
    if (!$registration) {
        wp_send_json_error(['message' => 'Không tìm thấy đội của bạn.'], 403);
    }

    check_ajax_referer('vs_team_portal_' . $registration->id, 'nonce');

    $staff_id = isset($_POST['staff_id']) ? absint($_POST['staff_id']) : 0;
    $avatar_result = vs_handle_athlete_avatar_upload($_FILES['anh_dai_dien'] ?? null);

    if ($avatar_result === false) {
        wp_send_json_error(['message' => 'Ảnh không hợp lệ — chỉ nhận JPG/PNG/WEBP, tối đa 5MB.']);
    }
    if ($avatar_result === null) {
        wp_send_json_error(['message' => 'Chưa chọn file nào.']);
    }

    global $wpdb;
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;

    $affected = $wpdb->update(
        $staff_table,
        ['anh_dai_dien_id' => $avatar_result],
        ['id' => $staff_id, 'registration_id' => $registration->id],
        ['%d'],
        ['%d', '%d']
    );

    if (!$affected) {
        wp_send_json_error(['message' => 'Không tìm thấy người này trong đội của bạn.']);
    }

    wp_send_json_success([
        'avatar_url' => wp_get_attachment_image_url($avatar_result, 'thumbnail'),
    ]);
}
add_action('wp_ajax_vs_update_staff_avatar', 'vs_ajax_update_staff_avatar');