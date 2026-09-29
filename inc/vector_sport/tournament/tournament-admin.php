<?php
defined('ABSPATH') || exit;

/* =========================================================================
 * ADMIN: menu, danh sách, chi tiết, cài đặt, xuất Excel
 * ========================================================================= */

function vs_tournament_register_admin_menu() {
    add_menu_page(
        'Đăng ký giải đấu',
        'Đăng ký giải đấu',
        'manage_options',
        'vs-tournament-registers',
        'vs_render_tournament_register_admin_page',
        'dashicons-awards',
        27
    );

    add_submenu_page(
        'vs-tournament-registers',
        'Danh sách đơn vị đăng ký',
        'Danh sách',
        'manage_options',
        'vs-tournament-registers',
        'vs_render_tournament_register_admin_page'
    );

    add_submenu_page(
        'vs-tournament-registers',
        'Cấu hình giải đấu',
        'Cấu hình giải đấu',
        'manage_options',
        'vs-tournament-events-settings',
        'vs_render_tournament_events_settings_page'
    );

    add_submenu_page(
        'vs-tournament-registers',
        'Email nhận thông báo',
        'Email nhận thông báo',
        'manage_options',
        'vs-tournament-email-settings',
        'vs_render_tournament_email_settings_page'
    );
}
add_action('admin_menu', 'vs_tournament_register_admin_menu');


/**
 * Xuất Excel + Xoá toàn bộ dữ liệu PHẢI xử lý ở admin_init — hook này
 * chạy TRƯỚC KHI WordPress in bất kỳ HTML nào của trang quản trị (menu,
 * khung trang...). Đặt trong hàm hiển thị trang (vs_render_tournament_
 * register_admin_page, chạy MUỘN hơn nhiều, SAU KHI khung trang đã in
 * ra) sẽ khiến header()/wp_safe_redirect() không còn tác dụng — đây
 * chính là lý do file Excel xuất ra bị hiện thẳng dạng chữ thô trên
 * trình duyệt thay vì tải về, thay vì lỗi ở nội dung file.
 */
function vs_maybe_handle_tournament_admin_page_actions() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'vs-tournament-registers') {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['vs_export']) && $_GET['vs_export'] === 'full_xlsx') {
        check_admin_referer('vs_tournament_export_full_xlsx');
        vs_export_tournament_full_xlsx();
        exit;
    }

    if (isset($_GET['vs_export']) && $_GET['vs_export'] === 'xlsx') {
        check_admin_referer('vs_tournament_export_xlsx');
        vs_export_tournament_registrations_xlsx();
        exit;
    }

    if (isset($_GET['vs_export']) && $_GET['vs_export'] === 'xlsx_staff') {
        check_admin_referer('vs_tournament_export_xlsx_staff');
        vs_export_tournament_staff_xlsx();
        exit;
    }

    // Xoá TOÀN BỘ dữ liệu đăng ký (cả 2 bảng) — dùng lúc còn test, trước
    // khi giải thật bắt đầu nhận đăng ký. Không thể hoàn tác, nên bắt
    // xác nhận ở JS (onclick) TRƯỚC khi link này được bấm.
    if (isset($_GET['vs_action']) && $_GET['vs_action'] === 'reset_all_data') {
        check_admin_referer('vs_tournament_reset_all_data');
        vs_reset_all_tournament_register_data();
        wp_safe_redirect(admin_url('admin.php?page=vs-tournament-registers&reset=1'));
        exit;
    }
}
add_action('admin_init', 'vs_maybe_handle_tournament_admin_page_actions');


function vs_render_tournament_register_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập trang này.');
    }

    $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
    $id = isset($_GET['id']) ? absint($_GET['id']) : 0;

    echo '<div class="wrap">';

    if ($action === 'view' && $id > 0) {
        vs_render_tournament_register_detail($id);
    } else {
        vs_render_tournament_register_list();
    }

    echo '</div>';
}


function vs_render_tournament_register_list() {
    global $wpdb;

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;

    $per_page = 20;
    $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
    $offset = ($paged - 1) * $per_page;

    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$reg_table}");
    $total_athletes = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$ath_table}");
    $total_staff = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$staff_table}");

    $items = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT r.*,
                    (SELECT COUNT(*) FROM {$ath_table} a WHERE a.registration_id = r.id) AS so_vdv,
                    (SELECT COUNT(*) FROM {$staff_table} s WHERE s.registration_id = r.id) AS so_nhan_su
             FROM {$reg_table} r
             ORDER BY r.created_at DESC
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        )
    );

    $export_full_url = wp_nonce_url(
        add_query_arg(
            ['page' => 'vs-tournament-registers', 'vs_export' => 'full_xlsx'],
            admin_url('admin.php')
        ),
        'vs_tournament_export_full_xlsx'
    );

    $reset_url = wp_nonce_url(
        add_query_arg(
            ['page' => 'vs-tournament-registers', 'vs_action' => 'reset_all_data'],
            admin_url('admin.php')
        ),
        'vs_tournament_reset_all_data'
    );
    $reset_confirm = sprintf(
        'Xoá HẲN toàn bộ %d đơn vị, %d VĐV và %d Trưởng đoàn/HLV đã đăng ký? Không thể hoàn tác — chỉ dùng lúc còn test, KHÔNG bấm khi giải đã nhận đăng ký thật.',
        $total,
        $total_athletes,
        $total_staff
    );

    ?>
<?php if (isset($_GET['reset']) && $_GET['reset'] === '1') : ?>
<div class="notice notice-success is-dismissible">
    <p>Đã xoá toàn bộ dữ liệu đăng ký.</p>
</div>
<?php endif; ?>

<h1 class="wp-heading-inline">Đăng ký giải đấu</h1>

<a href="<?php echo esc_url($export_full_url); ?>" class="page-title-action">
    Xuất toàn bộ Excel (3 sheet)
</a>

<a href="<?php echo esc_url($reset_url); ?>" class="page-title-action"
    style="color: #b32d2e; border-color: #b32d2e; margin-left: 6px;"
    onclick="return confirm('<?php echo esc_js($reset_confirm); ?>');">
    Xoá toàn bộ dữ liệu (test)
</a>

<p class="description">
    Một file Excel duy nhất gồm 3 sheet: <strong>Đơn vị</strong>, <strong>VĐV</strong> và
    <strong>Trưởng đoàn - HLV</strong>. Các sheet dùng chung <strong>Mã đơn vị</strong> để phần mềm
    điều hành có thể liên kết đúng dữ liệu giữa đoàn, VĐV và ban huấn luyện.
</p>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Đơn vị</th>
            <th>Người liên hệ</th>
            <th>Điện thoại</th>
            <th>Số VĐV</th>
            <th>Ban huấn luyện</th>
            <th>Thời gian gửi</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($items) : ?>
        <?php foreach ($items as $item) : ?>
        <tr>
            <td>
                <a href="<?php echo esc_url(add_query_arg([
                    'page'   => 'vs-tournament-registers',
                    'action' => 'view',
                    'id'     => absint($item->id),
                ], admin_url('admin.php'))); ?>" style="text-decoration:none;">
                    <strong><?php echo esc_html($item->ten_don_vi); ?></strong>
                </a>
            </td>
            <td><?php echo esc_html($item->nguoi_lien_he); ?></td>
            <td>
                <a href="<?php echo esc_attr('tel:' . $item->phone); ?>">
                    <?php echo esc_html($item->phone); ?>
                </a>
            </td>
            <td><?php echo (int) $item->so_vdv; ?></td>
            <td><?php echo (int) $item->so_nhan_su; ?></td>
            <td><?php echo esc_html(mysql2date('d/m/Y H:i', $item->created_at)); ?></td>
            <td>
                <a class="button button-small" href="<?php echo esc_url(add_query_arg([
                            'page'   => 'vs-tournament-registers',
                            'action' => 'view',
                            'id'     => absint($item->id),
                        ], admin_url('admin.php'))); ?>">
                    Xem chi tiết
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else : ?>
        <tr>
            <td colspan="7">Chưa có đơn vị nào đăng ký.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
    $total_pages = (int) ceil($total / $per_page);

    if ($total_pages > 1) {
        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo paginate_links([
            'base'      => add_query_arg('paged', '%#%'),
            'format'    => '',
            'prev_text' => '‹',
            'next_text' => '›',
            'total'     => $total_pages,
            'current'   => $paged,
        ]);
        echo '</div></div>';
    }
}


function vs_render_tournament_register_detail($id) {
    global $wpdb;

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;

    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$reg_table} WHERE id = %d", $id));

    if (!$item) {
        echo '<h1>Không tìm thấy dữ liệu</h1>';
        echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=vs-tournament-registers')) . '">Quay lại danh sách</a></p>';
        return;
    }

    $athletes = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$ath_table} WHERE registration_id = %d ORDER BY id ASC", $id)
    );

    $staff_list = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$staff_table} WHERE registration_id = %d ORDER BY CASE WHEN vai_tro = 'truong_doan' THEN 0 ELSE 1 END, id ASC",
            $id
        )
    );

    $logo_url = !empty($item->logo_id) ? wp_get_attachment_url((int) $item->logo_id) : false;
    $back_url = admin_url('admin.php?page=vs-tournament-registers');

    ?>
<div style="display:flex;align-items:center;gap:18px;margin:18px 0 14px;">
    <div style="width:84px;height:84px;border:1px solid #dcdcde;border-radius:14px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex:0 0 84px;">
        <?php if ($logo_url) : ?>
        <img src="<?php echo esc_url($logo_url); ?>"
            alt="<?php echo esc_attr($item->ten_don_vi); ?>"
            style="max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;display:block;">
        <?php else : ?>
        <span class="dashicons dashicons-format-image" style="font-size:30px;width:30px;height:30px;color:#a7aaad;"></span>
        <?php endif; ?>
    </div>

    <div style="min-width:0;flex:1;">
        <p style="margin:0 0 4px;color:#646970;">Chi tiết đơn vị đăng ký</p>
        <h1 style="margin:0;line-height:1.25;"><?php echo esc_html($item->ten_don_vi); ?></h1>
        <p style="margin:8px 0 0;color:#646970;">
            <?php echo count($athletes); ?> VĐV &nbsp;•&nbsp; <?php echo count($staff_list); ?> Trưởng đoàn/HLV
        </p>
    </div>
</div>

<p style="margin-bottom:20px;">
    <a class="button" href="<?php echo esc_url($back_url); ?>">← Quay lại danh sách</a>
</p>

<h2>Thông tin đội / đơn vị</h2>
<table class="widefat striped" style="max-width:1000px;margin-bottom:28px;">
    <tbody>
        <tr>
            <th style="width:220px;">Logo</th>
            <td>
                <?php if ($logo_url) : ?>
                <img src="<?php echo esc_url($logo_url); ?>"
                    alt="<?php echo esc_attr($item->ten_don_vi); ?>"
                    style="width:88px;height:88px;object-fit:contain;border:1px solid #dcdcde;border-radius:10px;background:#fff;padding:6px;box-sizing:border-box;">
                <?php else : ?>
                <span style="color:#646970;">Chưa có logo</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Tên đơn vị</th>
            <td><strong><?php echo esc_html($item->ten_don_vi); ?></strong></td>
        </tr>
        <tr>
            <th>Người liên hệ</th>
            <td><?php echo esc_html($item->nguoi_lien_he); ?></td>
        </tr>
        <tr>
            <th>Số điện thoại</th>
            <td>
                <a href="<?php echo esc_attr('tel:' . $item->phone); ?>">
                    <?php echo esc_html($item->phone); ?>
                </a>
            </td>
        </tr>
        <tr>
            <th>Email</th>
            <td>
                <?php if ($item->email) : ?>
                <a href="<?php echo esc_attr('mailto:' . $item->email); ?>">
                    <?php echo esc_html($item->email); ?>
                </a>
                <?php else : ?>
                Không nhập
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Ghi chú</th>
            <td><?php echo nl2br(esc_html($item->ghi_chu ?: 'Không có')); ?></td>
        </tr>
        <tr>
            <th>Thời gian gửi</th>
            <td><?php echo esc_html(mysql2date('d/m/Y H:i', $item->created_at)); ?></td>
        </tr>
    </tbody>
</table>

<h2>Trưởng đoàn &amp; Huấn luyện viên (<?php echo count($staff_list); ?>)</h2>
<table class="wp-list-table widefat fixed striped" style="margin-bottom:28px;">
    <thead>
        <tr>
            <th style="width:70px;">Ảnh</th>
            <th>Họ tên</th>
            <th style="width:220px;">Vai trò</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($staff_list) : ?>
        <?php foreach ($staff_list as $staff) : ?>
        <tr>
            <td>
                <?php $staff_avatar_url = !empty($staff->anh_dai_dien_id) ? wp_get_attachment_image_url($staff->anh_dai_dien_id, 'thumbnail') : false; ?>
                <?php if ($staff_avatar_url) : ?>
                <img src="<?php echo esc_url($staff_avatar_url); ?>" alt=""
                    style="width:46px;height:46px;border-radius:50%;object-fit:cover;display:block;">
                <?php else : ?>
                <span style="width:46px;height:46px;border-radius:50%;background:#f0f0f1;color:#8c8f94;display:flex;align-items:center;justify-content:center;">—</span>
                <?php endif; ?>
            </td>
            <td><strong><?php echo esc_html($staff->ho_ten); ?></strong></td>
            <td><?php echo esc_html(vs_staff_role_label($staff->vai_tro)); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else : ?>
        <tr>
            <td colspan="3">Chưa có Trưởng đoàn/Huấn luyện viên.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<h2>Danh sách VĐV (<?php echo count($athletes); ?>)</h2>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th style="width:70px;">Ảnh</th>
            <th>Họ tên</th>
            <th style="width:110px;">Năm sinh</th>
            <th style="width:100px;">Giới tính</th>
            <th style="width:160px;">Nhóm tuổi</th>
            <th>Nội dung đăng ký</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($athletes) : ?>
        <?php foreach ($athletes as $a) : ?>
        <tr>
            <td>
                <?php $avatar_url = !empty($a->anh_dai_dien_id) ? wp_get_attachment_image_url($a->anh_dai_dien_id, 'thumbnail') : false; ?>
                <?php if ($avatar_url) : ?>
                <img src="<?php echo esc_url($avatar_url); ?>" alt=""
                    style="width:46px;height:46px;border-radius:50%;object-fit:cover;display:block;">
                <?php else : ?>
                <span style="width:46px;height:46px;border-radius:50%;background:#f0f0f1;color:#8c8f94;display:flex;align-items:center;justify-content:center;">—</span>
                <?php endif; ?>
            </td>
            <td><strong><?php echo esc_html($a->ho_ten); ?></strong></td>
            <td><?php echo esc_html($a->nam_sinh); ?></td>
            <td><?php echo esc_html($a->gioi_tinh); ?></td>
            <td><?php echo esc_html(vs_get_tournament_age_group_label($a->nhom_tuoi)); ?></td>
            <td><?php echo esc_html($a->noi_dung ?: '—'); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else : ?>
        <tr>
            <td colspan="6">Chưa có VĐV nào.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
}


/**
 * Xoá TOÀN BỘ dữ liệu đăng ký giải đấu (cả đơn vị lẫn VĐV) — dùng lúc
 * còn test, trước khi giải thật mở đăng ký. Không hoàn tác được.
 */
function vs_reset_all_tournament_register_data() {
    global $wpdb;

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;

    // Xoá 2 bảng con (VĐV, Trưởng đoàn/HLV) trước, rồi tới bảng đơn vị
    // (bảng cha) — không có ràng buộc khoá ngoại thật ở DB, nhưng giữ
    // đúng thứ tự cho rõ ràng, tránh còn sót dữ liệu trỏ tới đơn vị đã
    // mất nếu có lỗi giữa chừng.
    $wpdb->query("TRUNCATE TABLE {$ath_table}");
    $wpdb->query("TRUNCATE TABLE {$staff_table}");
    $wpdb->query("TRUNCATE TABLE {$reg_table}");
}
/**
 * Xuất TOÀN BỘ dữ liệu đăng ký vào MỘT workbook gồm 3 sheet:
 * - Đơn vị
 * - VĐV
 * - Trưởng đoàn - HLV
 *
 * Cả 3 sheet đều dùng registration_id làm "Mã đơn vị". Đây là khoá liên
 * kết ổn định để phần mềm điều hành import 1 file rồi gắn đúng VĐV/HLV
 * về đúng đoàn, không phải dựa vào tên đơn vị có thể bị trùng/đổi tên.
 */
function vs_export_tournament_full_xlsx() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền thực hiện thao tác này.');
    }

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;

    $registrations = $wpdb->get_results(
        "SELECT * FROM {$reg_table} ORDER BY ten_don_vi ASC, id ASC"
    );

    $athletes = $wpdb->get_results(
        "SELECT a.*, r.ten_don_vi
         FROM {$ath_table} a
         INNER JOIN {$reg_table} r ON r.id = a.registration_id
         ORDER BY r.ten_don_vi ASC, a.id ASC"
    );

    $staff_list = $wpdb->get_results(
        "SELECT s.*, r.ten_don_vi
         FROM {$staff_table} s
         INNER JOIN {$reg_table} r ON r.id = s.registration_id
         ORDER BY r.ten_don_vi ASC,
                  CASE WHEN s.vai_tro = 'truong_doan' THEN 0 ELSE 1 END,
                  s.id ASC"
    );

    $unit_rows = [
        [
            'Tên đơn vị',
            'Logo',
        ],
    ];

    foreach ($registrations as $r) {
        $logo_url = !empty($r->logo_id) ? wp_get_attachment_url((int) $r->logo_id) : '';
        $logo_file = $logo_url ? wp_basename(parse_url($logo_url, PHP_URL_PATH)) : '';

        $unit_rows[] = [
            $r->ten_don_vi,
            $logo_url ?: '',
        ];
    }

    $athlete_rows = [
        [
            'Đơn vị',
            'Họ tên',
            'Năm sinh',
            'Giới tính',
            'Nhóm tuổi',
            'Nội dung',
            'Link ảnh',
        ],
    ];

    foreach ($athletes as $a) {
        $avatar_url = !empty($a->anh_dai_dien_id)
            ? wp_get_attachment_url((int) $a->anh_dai_dien_id)
            : '';

        $athlete_rows[] = [
            $a->ten_don_vi,
            $a->ho_ten,
            (int) $a->nam_sinh,
            $a->gioi_tinh,
            vs_get_tournament_age_group_label($a->nhom_tuoi),
            $a->noi_dung ?: '',
            $avatar_url ?: '',
        ];
    }

    $staff_rows = [
        [
            'Đơn vị',
            'Họ tên',
            'Vai trò',
            'Link ảnh',
        ],
    ];

    foreach ($staff_list as $staff) {
        $avatar_url = !empty($staff->anh_dai_dien_id)
            ? wp_get_attachment_url((int) $staff->anh_dai_dien_id)
            : '';

        $staff_rows[] = [
            $staff->ten_don_vi,
            $staff->ho_ten,
            vs_staff_role_label($staff->vai_tro),
            $avatar_url ?: '',
        ];
    }

    $binary = vs_build_xlsx_workbook([
        [
            'name' => 'Đơn vị',
            'rows' => $unit_rows,
        ],
        [
            'name' => 'VĐV',
            'rows' => $athlete_rows,
        ],
        [
            'name' => 'Trưởng đoàn - HLV',
            'rows' => $staff_rows,
        ],
    ]);

    $filename = 'dang-ky-giai-vovinam-toan-bo-' . gmdate('Y-m-d-His') . '.xlsx';

    nocache_headers();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($binary));
    echo $binary;
}

/**
 * Xuất TOÀN BỘ VĐV (mọi đơn vị) ra đúng định dạng 6 cột phần mềm điều
 * hành cần: Họ tên, Năm sinh, Giới tính, Nhóm tuổi, Đơn vị, Nội dung.
 */
function vs_export_tournament_registrations_xlsx() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền thực hiện thao tác này.');
    }

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;

    $results = $wpdb->get_results(
        "SELECT a.ho_ten, a.nam_sinh, a.gioi_tinh, a.nhom_tuoi, a.noi_dung, a.anh_dai_dien_id, r.ten_don_vi
         FROM {$ath_table} a
         INNER JOIN {$reg_table} r ON r.id = a.registration_id
         ORDER BY r.ten_don_vi ASC, a.id ASC"
    );

    // "Link ảnh" đặt Ở CUỐI, sau 6 cột gốc — phần mềm điều hành hiện tại
    // đọc đúng 6 cột đầu theo vị trí, thêm cột mới ở cuối không làm lệch
    // các cột đó. Bên phần mềm cần được cập nhật riêng để ĐỌC thêm cột
    // này khi import (chưa làm ở đây).
    $rows = [
        ['Đơn vị', 'Họ tên', 'Năm sinh', 'Giới tính', 'Nhóm tuổi', 'Nội dung', 'Link ảnh'],
    ];

    foreach ($results as $r) {
        $anh_url = !empty($r->anh_dai_dien_id) ? wp_get_attachment_url($r->anh_dai_dien_id) : '';
        $rows[] = [
            $r->ten_don_vi,
            $r->ho_ten,
            (int) $r->nam_sinh,
            $r->gioi_tinh,
            vs_get_tournament_age_group_label($r->nhom_tuoi),
            $r->noi_dung ?: '',
            $anh_url ?: '',
        ];
    }

    $binary = vs_build_xlsx($rows, 'VĐV đăng ký');

    $filename = 'dang-ky-giai-vovinam-' . gmdate('Y-m-d-His') . '.xlsx';

    nocache_headers();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($binary));
    echo $binary;
}

/**
 * Y hệt vs_export_tournament_registrations_xlsx() ở trên, nhưng cho
 * Trưởng đoàn/HLV — xuất RIÊNG 1 file (không gộp chung với VĐV) vì bên
 * phần mềm điều hành sẽ nhập 2 loại này vào 2 chỗ khác nhau.
 */
function vs_export_tournament_staff_xlsx() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền thực hiện thao tác này.');
    }

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;

    $results = $wpdb->get_results(
        "SELECT s.ho_ten, s.vai_tro, s.anh_dai_dien_id, r.ten_don_vi
         FROM {$staff_table} s
         INNER JOIN {$reg_table} r ON r.id = s.registration_id
         ORDER BY r.ten_don_vi ASC, s.id ASC"
    );

    $rows = [
        ['Đơn vị', 'Họ tên', 'Vai trò', 'Link ảnh'],
    ];

    foreach ($results as $r) {
        $anh_url = !empty($r->anh_dai_dien_id) ? wp_get_attachment_url($r->anh_dai_dien_id) : '';
        $rows[] = [
            $r->ten_don_vi,
            $r->ho_ten,
            vs_staff_role_label($r->vai_tro),
            $anh_url ?: '',
        ];
    }

    $binary = vs_build_xlsx($rows, 'Trưởng đoàn - HLV');

    $filename = 'truong-doan-hlv-' . gmdate('Y-m-d-His') . '.xlsx';

    nocache_headers();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($binary));
    echo $binary;
}