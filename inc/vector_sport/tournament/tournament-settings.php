<?php
defined('ABSPATH') || exit;

/* =========================================================================
 * CÀI ĐẶT: nhóm tuổi, danh sách nội dung thi đấu và email nhận thông báo.
 *
 * Nhóm tuổi được lưu bằng option để BTC có thể thay đổi trực tiếp trong
 * wp-admin mà không phải sửa source. Mã nhóm là giá trị thật lưu trong DB
 * / xuất Excel; tên hiển thị chỉ dùng cho giao diện.
 * ========================================================================= */

/**
 * Nhóm tuổi mặc định cho site chưa từng lưu cấu hình.
 * Giữ đúng 1-4 để tương thích hoàn toàn với dữ liệu/cấu hình cũ.
 */
function vs_get_default_tournament_age_groups() {
    return [
        '1' => 'Nhóm tuổi 1',
        '2' => 'Nhóm tuổi 2',
        '3' => 'Nhóm tuổi 3',
        '4' => 'Nhóm tuổi 4',
    ];
}

/**
 * Lấy cấu hình nhóm tuổi dạng:
 * [
 *   '1' => 'Nhóm tuổi 1',
 *   '2' => 'Nhóm tuổi 2',
 * ]
 *
 * Key là "mã nhóm" dùng để lưu DB / xuất Excel / khớp phần mềm điều hành.
 * Value là nhãn hiển thị cho trưởng đoàn và wp-admin.
 */
function vs_get_tournament_age_groups() {
    $saved = get_option('vs_tournament_age_groups', null);

    if (!is_array($saved) || empty($saved)) {
        return vs_get_default_tournament_age_groups();
    }

    $groups = [];

    foreach ($saved as $key => $label) {
        $key = sanitize_key((string) $key);
        $label = sanitize_text_field((string) $label);

        if ($key === '' || $key === 'hon_hop') {
            continue;
        }

        $groups[$key] = $label !== '' ? $label : ('Nhóm tuổi ' . $key);
    }

    return !empty($groups) ? $groups : vs_get_default_tournament_age_groups();
}

function vs_get_tournament_age_group_keys() {
    // PHP tự chuyển array key dạng chuỗi số ('1', '2', ...) thành int.
    // Trong khi dữ liệu từ form/Excel luôn là chuỗi và các chỗ validate
    // đang dùng in_array(..., true), nên nếu trả thẳng array_keys() thì
    // '1' !== 1 và toàn bộ VĐV thuộc nhóm số sẽ bị coi là không hợp lệ.
    // Chuẩn hoá tất cả mã nhóm về string để cùng kiểu với DB/form/Excel.
    return array_map('strval', array_keys(vs_get_tournament_age_groups()));
}

function vs_get_tournament_age_group_label($group_key) {
    $group_key = (string) $group_key;
    $groups = vs_get_tournament_age_groups();

    return $groups[$group_key] ?? $group_key;
}

/**
 * Nhóm dùng để cấu hình nội dung thi đấu = toàn bộ nhóm tuổi đang bật
 * + "hon_hop" (nội dung mở cho mọi nhóm tuổi).
 */
function vs_get_tournament_event_groups() {
    return array_merge(vs_get_tournament_age_group_keys(), ['hon_hop']);
}

/**
 * Chuẩn hoá nhóm tuổi từ dữ liệu chữ tự do (đặc biệt là file Excel).
 * Chấp nhận:
 * - mã nhóm: 1, 2, u12...
 * - tên hiển thị: "Nhóm tuổi 1", "U12"...
 * - kiểu cũ "Nhóm tuổi {mã}" để không phá file mẫu đang dùng.
 *
 * Trả về đúng MÃ NHÓM đã cấu hình, hoặc '' nếu không khớp.
 */
function vs_match_tournament_age_group($raw_value) {
    $raw_value = sanitize_text_field(trim((string) $raw_value));

    if ($raw_value === '') {
        return '';
    }

    $needle = function_exists('mb_strtolower')
        ? mb_strtolower($raw_value, 'UTF-8')
        : strtolower($raw_value);

    foreach (vs_get_tournament_age_groups() as $key => $label) {
        $candidates = [
            (string) $key,
            (string) $label,
            'Nhóm tuổi ' . $key,
        ];

        foreach ($candidates as $candidate) {
            $candidate_normalized = function_exists('mb_strtolower')
                ? mb_strtolower(trim($candidate), 'UTF-8')
                : strtolower(trim($candidate));

            if ($needle === $candidate_normalized) {
                return (string) $key;
            }
        }
    }

    return '';
}

function vs_parse_event_lines($raw) {
    if (!$raw) {
        return [];
    }

    $lines = preg_split('/\r\n|\r|\n/', $raw);

    if (!is_array($lines)) {
        return [];
    }

    $events = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line !== '') {
            $events[] = $line;
        }
    }

    return $events;
}

/**
 * Trả về mảng nội dung theo đúng các nhóm tuổi hiện đang cấu hình
 * + nhóm "hon_hop".
 */
function vs_get_tournament_events_by_nhom_tuoi() {
    $result = [];

    foreach (vs_get_tournament_event_groups() as $group) {
        $raw = get_option('vs_tournament_events_nt_' . $group, '');
        $result[$group] = vs_parse_event_lines($raw);
    }

    return $result;
}

/**
 * Danh sách nội dung HỢP LỆ cho đúng 1 nhóm tuổi cụ thể — gồm nội dung
 * riêng của nhóm đó CỘNG nội dung hỗn hợp (mở cho mọi nhóm tuổi).
 */
function vs_get_valid_events_for_nhom_tuoi($nhom_tuoi) {
    $by_group = vs_get_tournament_events_by_nhom_tuoi();
    $nhom_tuoi_key = (string) $nhom_tuoi;

    $specific = $by_group[$nhom_tuoi_key] ?? [];
    $hon_hop = $by_group['hon_hop'] ?? [];

    return array_values(array_unique(array_merge($specific, $hon_hop)));
}


function vs_get_tournament_register_recipient_emails() {
    $emails = get_option('vs_tournament_register_recipient_emails', []);

    if (is_array($emails) && !empty($emails)) {
        return $emails;
    }

    $admin_email = get_option('admin_email');

    if ($admin_email && is_email($admin_email)) {
        return [$admin_email];
    }

    return [];
}


function vs_render_tournament_events_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập trang này.');
    }

    $age_group_notice = '';
    $age_group_notice_type = 'success';

    // LƯU NHÓM TUỔI — tách form riêng để khi thay đổi số nhóm, trang reload
    // rồi mới render đúng số ô "Nội dung thi đấu" tương ứng.
    if (
        isset($_POST['vs_tournament_age_groups_nonce']) &&
        wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['vs_tournament_age_groups_nonce'])),
            'vs_save_tournament_age_groups'
        )
    ) {
        $keys = isset($_POST['vs_tournament_age_group_key']) && is_array($_POST['vs_tournament_age_group_key'])
            ? wp_unslash($_POST['vs_tournament_age_group_key'])
            : [];
        $labels = isset($_POST['vs_tournament_age_group_label']) && is_array($_POST['vs_tournament_age_group_label'])
            ? wp_unslash($_POST['vs_tournament_age_group_label'])
            : [];

        $new_groups = [];
        $reserved_used = false;

        $row_count = max(count($keys), count($labels));

        for ($i = 0; $i < $row_count; $i++) {
            $key = sanitize_key((string) ($keys[$i] ?? ''));
            $label = sanitize_text_field((string) ($labels[$i] ?? ''));

            if ($key === '') {
                continue;
            }

            if ($key === 'hon_hop') {
                $reserved_used = true;
                continue;
            }

            // Mã nhóm phải duy nhất. Nếu nhập trùng, giữ dòng đầu tiên.
            if (isset($new_groups[$key])) {
                continue;
            }

            $new_groups[$key] = $label !== '' ? $label : ('Nhóm tuổi ' . $key);
        }

        if (empty($new_groups)) {
            $age_group_notice = 'Phải có ít nhất 1 nhóm tuổi.';
            $age_group_notice_type = 'error';
        } else {
            update_option('vs_tournament_age_groups', $new_groups);
            $age_group_notice = 'Đã lưu cấu hình ' . count($new_groups) . ' nhóm tuổi.';
            if ($reserved_used) {
                $age_group_notice .= ' Mã "hon_hop" được dành riêng cho nội dung Hỗn hợp nên đã được bỏ qua.';
                $age_group_notice_type = 'warning';
            }
        }
    }

    // LƯU NỘI DUNG THI ĐẤU cho các nhóm tuổi hiện đang cấu hình.
    if (
        isset($_POST['vs_tournament_events_nonce']) &&
        wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['vs_tournament_events_nonce'])),
            'vs_save_tournament_events'
        )
    ) {
        foreach (vs_get_tournament_event_groups() as $group) {
            $field = 'vs_tournament_events_nt_' . $group;
            $raw = isset($_POST[$field]) ? sanitize_textarea_field(wp_unslash($_POST[$field])) : '';
            update_option($field, $raw);
        }

        $template_url = isset($_POST['vs_tournament_athlete_template_url'])
            ? esc_url_raw(trim(wp_unslash($_POST['vs_tournament_athlete_template_url'])))
            : '';
        update_option('vs_tournament_athlete_template_url', $template_url);

        echo '<div class="notice notice-success is-dismissible"><p>Đã lưu danh sách nội dung thi đấu.</p></div>';
    }

    if ($age_group_notice !== '') {
        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr($age_group_notice_type),
            esc_html($age_group_notice)
        );
    }

    $age_groups = vs_get_tournament_age_groups();

    ?>
    <div class="wrap">
        <h1>Cấu hình đăng ký giải đấu</h1>

        <p>
            BTC có thể tự cấu hình số lượng nhóm tuổi, mã nhóm và tên hiển thị.
            Mã nhóm là giá trị được lưu trong dữ liệu VĐV và xuất Excel, vì vậy
            nên đặt khớp với mã nhóm tuổi trong phần mềm điều hành giải.
        </p>

        <hr style="margin: 24px 0;">

        <h2>Cấu hình nhóm tuổi</h2>
        <p>
            Thêm/bớt dòng để thay đổi số lượng nhóm tuổi. Ví dụ:
            <code>1 → Nhóm tuổi 1</code>, <code>u12 → U12 (10–12 tuổi)</code>.
            Mã <code>hon_hop</code> được dành riêng cho nội dung Hỗn hợp và không dùng làm nhóm tuổi.
        </p>
        <p>
            <strong>Lưu ý:</strong> nếu giải đã có VĐV đăng ký, không nên đổi
            <em>mã nhóm</em> đang dùng vì mã này đã được lưu trong dữ liệu cũ.
            Có thể đổi <em>tên hiển thị</em> mà không ảnh hưởng dữ liệu.
        </p>

        <form method="post" id="vs-tournament-age-groups-form">
            <?php wp_nonce_field('vs_save_tournament_age_groups', 'vs_tournament_age_groups_nonce'); ?>

            <table class="widefat striped" id="vs-tournament-age-groups-table" style="max-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 180px;">Mã nhóm</th>
                        <th>Tên hiển thị</th>
                        <th style="width: 90px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody data-age-group-rows>
                    <?php foreach ($age_groups as $key => $label) : ?>
                    <tr data-age-group-row>
                        <td>
                            <input type="text"
                                name="vs_tournament_age_group_key[]"
                                class="regular-text code"
                                value="<?php echo esc_attr($key); ?>"
                                placeholder="VD: 1 hoặc u12"
                                required>
                        </td>
                        <td>
                            <input type="text"
                                name="vs_tournament_age_group_label[]"
                                class="regular-text"
                                value="<?php echo esc_attr($label); ?>"
                                placeholder="VD: Nhóm tuổi 1"
                                required>
                        </td>
                        <td>
                            <button type="button" class="button-link-delete" data-remove-age-group>Xoá</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="display:flex; gap:10px; align-items:center;">
                <button type="button" class="button" id="vs-add-age-group">+ Thêm nhóm tuổi</button>
                <span>
                    Hiện có <strong data-age-group-count><?php echo count($age_groups); ?></strong> nhóm tuổi.
                </span>
            </p>

            <?php submit_button('Lưu nhóm tuổi', 'primary', 'submit', false); ?>
        </form>

        <script type="text/html" id="vs-age-group-row-template">
            <tr data-age-group-row>
                <td>
                    <input type="text"
                        name="vs_tournament_age_group_key[]"
                        class="regular-text code"
                        value=""
                        placeholder="VD: 5 hoặc u15"
                        required>
                </td>
                <td>
                    <input type="text"
                        name="vs_tournament_age_group_label[]"
                        class="regular-text"
                        value=""
                        placeholder="VD: Nhóm tuổi 5"
                        required>
                </td>
                <td>
                    <button type="button" class="button-link-delete" data-remove-age-group>Xoá</button>
                </td>
            </tr>
        </script>

        <script>
        (function() {
            var rows = document.querySelector('[data-age-group-rows]');
            var addButton = document.getElementById('vs-add-age-group');
            var template = document.getElementById('vs-age-group-row-template');
            var count = document.querySelector('[data-age-group-count]');

            if (!rows || !addButton || !template) {
                return;
            }

            function updateCount() {
                if (count) {
                    count.textContent = rows.querySelectorAll('[data-age-group-row]').length;
                }
            }

            addButton.addEventListener('click', function() {
                rows.insertAdjacentHTML('beforeend', template.innerHTML);
                updateCount();

                var newRows = rows.querySelectorAll('[data-age-group-row]');
                var lastRow = newRows[newRows.length - 1];
                var firstInput = lastRow ? lastRow.querySelector('input') : null;
                if (firstInput) {
                    firstInput.focus();
                }
            });

            rows.addEventListener('click', function(event) {
                var removeButton = event.target.closest('[data-remove-age-group]');
                if (!removeButton) {
                    return;
                }

                var allRows = rows.querySelectorAll('[data-age-group-row]');
                if (allRows.length <= 1) {
                    window.alert('Phải giữ lại ít nhất 1 nhóm tuổi.');
                    return;
                }

                var row = removeButton.closest('[data-age-group-row]');
                if (row) {
                    row.remove();
                    updateCount();
                }
            });

            updateCount();
        })();
        </script>

        <hr style="margin: 32px 0;">

        <form method="post">
            <?php wp_nonce_field('vs_save_tournament_events', 'vs_tournament_events_nonce'); ?>

            <h2 style="margin-top: 0;">File mẫu Excel cho trưởng đoàn</h2>
            <p>
                Trưởng đoàn tải file này ở trang Quản lý đội để điền và nhập
                nhanh danh sách VĐV. Tự tạo file bằng Excel/Google Sheets với
                ĐÚNG 5 cột theo thứ tự sau (dòng đầu là tiêu đề):
            </p>
            <p>
                <code>Họ tên | Năm sinh | Giới tính | Nhóm tuổi | Nội dung</code>
            </p>
            <p>
                Cột "Nhóm tuổi" có thể nhập <strong>mã nhóm</strong> hoặc
                <strong>đúng tên hiển thị</strong> đã cấu hình ở trên.
                Cột "Nội dung" nhiều nội dung cách nhau bằng dấu chấm phẩy (;).
            </p>
            <p>
                Sau khi tạo file, tải lên <strong>Thư viện Media</strong>
                (Media → Thêm mới) rồi dán link file đó vào đây.
            </p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="vs_tournament_athlete_template_url">Link file mẫu</label>
                        </th>
                        <td>
                            <input type="text" id="vs_tournament_athlete_template_url"
                                name="vs_tournament_athlete_template_url" class="large-text code"
                                placeholder="/wp-content/uploads/2026/08/File-mau.xlsx"
                                value="<?php echo esc_attr(get_option('vs_tournament_athlete_template_url', '')); ?>">
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr style="margin: 24px 0;">
            <h2>Danh sách nội dung thi đấu</h2>

            <p>
                Mỗi nhóm tuổi đang bật có một danh sách nội dung riêng. Nội dung
                trong ô <strong>Hỗn hợp</strong> luôn được phép cho mọi nhóm tuổi.
            </p>

            <table class="form-table" role="presentation">
                <tbody>
                    <?php foreach (vs_get_tournament_event_groups() as $group) : ?>
                        <?php
                        $field = 'vs_tournament_events_nt_' . $group;
                        $group_label = $group === 'hon_hop'
                            ? 'Hỗn hợp (mở cho mọi nhóm tuổi)'
                            : vs_get_tournament_age_group_label($group);
                        ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($field); ?>">
                                    <?php echo esc_html($group_label); ?>
                                    <?php if ($group !== 'hon_hop') : ?>
                                        <br><code><?php echo esc_html($group); ?></code>
                                    <?php endif; ?>
                                </label>
                            </th>
                            <td>
                                <textarea id="<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>"
                                    rows="8" class="large-text code"
                                    placeholder="<?php echo $group === 'hon_hop' ? esc_attr("Võ nhạc số 1\nVõ nhạc số 2") : esc_attr("Đối kháng Nam - 23kg\nĐối kháng Nam - 26kg"); ?>"><?php echo esc_textarea(get_option($field, '')); ?></textarea>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php submit_button('Lưu danh sách'); ?>
        </form>
    </div>
    <?php
}


function vs_render_tournament_email_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập trang này.');
    }

    if (
        isset($_POST['vs_tournament_email_settings_nonce']) &&
        wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['vs_tournament_email_settings_nonce'])),
            'vs_save_tournament_email_settings'
        )
    ) {
        $raw_emails = isset($_POST['vs_tournament_recipient_emails'])
            ? sanitize_textarea_field(wp_unslash($_POST['vs_tournament_recipient_emails']))
            : '';

        $emails = vs_parse_contact_recipient_emails($raw_emails);

        update_option('vs_tournament_register_recipient_emails', $emails);

        echo '<div class="notice notice-success is-dismissible"><p>Đã lưu danh sách email nhận thông báo.</p></div>';
    }

    $saved_emails = get_option('vs_tournament_register_recipient_emails', []);
    $email_text = is_array($saved_emails) && !empty($saved_emails) ? implode("\n", $saved_emails) : '';

    ?>
    <div class="wrap">
        <h1>Email nhận thông báo đăng ký giải đấu</h1>

        <p>Mỗi email 1 dòng. Để trống thì gửi về email quản trị website.</p>

        <form method="post">
            <?php wp_nonce_field('vs_save_tournament_email_settings', 'vs_tournament_email_settings_nonce'); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="vs_tournament_recipient_emails">Email nhận thông báo</label>
                        </th>
                        <td>
                            <textarea id="vs_tournament_recipient_emails" name="vs_tournament_recipient_emails" rows="8"
                                class="large-text code"
                                placeholder="btc1@gmail.com&#10;btc2@gmail.com"><?php echo esc_textarea($email_text); ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button('Lưu cài đặt'); ?>
        </form>
    </div>
    <?php
}
