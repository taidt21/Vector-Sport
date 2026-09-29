<?php
/**
 * Vector Sport - Đăng ký giải đấu Vovinam
 * Đơn vị đăng ký công khai trên website (đội + danh sách VĐV), admin xem
 * và xuất Excel để nhập vào phần mềm điều hành giải (đúng định dạng cột:
 * Họ tên | Năm sinh | Giới tính | Nhóm tuổi | Đơn vị | Nội dung).
 */

defined('ABSPATH') || exit;

const VS_TOURNAMENT_REG_TABLE = 'vs_tournament_registrations';
const VS_TOURNAMENT_ATH_TABLE = 'vs_tournament_athletes';
const VS_TOURNAMENT_STAFF_TABLE = 'vs_tournament_staff';

// Đổi đúng theo số nhóm tuổi thật của giải (khớp NHOM_TUOI_OPTIONS bên
// phần mềm điều hành) — mặc định 4 nhóm, hay gặp nhất.
const VS_TOURNAMENT_NHOM_TUOI_OPTIONS = [1, 2, 3, 4];

// Trưởng đoàn / Huấn luyện viên — không thi đấu nên không có năm sinh/
// giới tính/nhóm tuổi/nội dung như VĐV, chỉ cần họ tên + đúng 1 trong 2
// vai trò này + ảnh.
const VS_TOURNAMENT_STAFF_ROLES = ['truong_doan', 'huan_luyen_vien'];

// Đổi CẤU TRÚC bảng (VD kiểu cột) thì phải tăng số này — vs_maybe_create_
// tournament_register_tables() so số đã lưu với số này, khác thì tự chạy
// lại dbDelta() để nâng cấp cả site ĐÃ CÀI từ trước, không chỉ cài mới.
const VS_TOURNAMENT_REG_SCHEMA_VERSION = '6';

/**
 * Tạo 2 bảng khi active theme: 1 bảng đơn vị đăng ký (đội), 1 bảng VĐV
 * (mỗi dòng 1 VĐV, gắn với đúng 1 đơn vị qua registration_id).
 */
function vs_create_tournament_register_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    // user_id: tài khoản WordPress (trưởng đoàn) sở hữu đăng ký này — NULL
    // cho các đăng ký cũ gửi trước khi có tính năng tài khoản. Đây là cách
    // DUY NHẤT xác định "đây có phải đội của tôi không" ở trang quản lý —
    // không bao giờ nhận registration_id trực tiếp từ URL/form ở phía đó.
    $sql_reg = "CREATE TABLE {$reg_table} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NULL,
        logo_id BIGINT(20) UNSIGNED NULL,
        ten_don_vi VARCHAR(190) NOT NULL,
        nguoi_lien_he VARCHAR(190) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(190) NULL,
        ghi_chu TEXT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'new',
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY status (status),
        KEY created_at (created_at),
        KEY user_id (user_id)
    ) {$charset_collate};";
    dbDelta($sql_reg);

    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;
    // nhom_tuoi là VARCHAR dù chỉ lưu số (1-4) — để tương thích ngược với
    // dữ liệu cũ, không có tác dụng đặc biệt gì khác hiện tại.
    // anh_dai_dien_id: ID media WordPress (không lưu URL trực tiếp — ID
    // luôn đúng dù đổi domain/CDN, dùng wp_get_attachment_url() khi cần
    // hiện hoặc xuất Excel).
    $sql_ath = "CREATE TABLE {$ath_table} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        registration_id BIGINT(20) UNSIGNED NOT NULL,
        ho_ten VARCHAR(190) NOT NULL,
        nam_sinh SMALLINT NOT NULL,
        gioi_tinh VARCHAR(10) NOT NULL,
        nhom_tuoi VARCHAR(20) NOT NULL,
        noi_dung TEXT NULL,
        anh_dai_dien_id BIGINT(20) UNSIGNED NULL,
        PRIMARY KEY  (id),
        KEY registration_id (registration_id)
    ) {$charset_collate};";
    dbDelta($sql_ath);

    // Trưởng đoàn / HLV — bảng riêng với VĐV (không dùng chung 1 bảng có
    // cột thừa NULL cho nhau) vì 2 nhóm này khác hẳn về dữ liệu cần lưu.
    // Không có cột "đơn vị" riêng — lấy qua registration_id JOIN sang
    // bảng đăng ký, y hệt cách VĐV đang làm.
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;
    $sql_staff = "CREATE TABLE {$staff_table} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        registration_id BIGINT(20) UNSIGNED NOT NULL,
        vai_tro VARCHAR(30) NOT NULL,
        ho_ten VARCHAR(190) NOT NULL,
        anh_dai_dien_id BIGINT(20) UNSIGNED NULL,
        PRIMARY KEY  (id),
        KEY registration_id (registration_id)
    ) {$charset_collate};";
    dbDelta($sql_staff);
}
add_action('after_switch_theme', 'vs_create_tournament_register_tables');

/**
 * Nhãn tiếng Việt hiển thị cho từng vai trò — dùng chung ở cả trang Quản
 * lý đội, trang admin, và file Excel xuất ra, để chỉ cần đổi ở ĐÚNG 1
 * chỗ này nếu sau này cần đổi cách gọi.
 */
function vs_staff_role_label($vai_tro) {
    $labels = [
        'truong_doan'     => 'Trưởng đoàn',
        'huan_luyen_vien' => 'Huấn luyện viên',
    ];
    return $labels[$vai_tro] ?? $vai_tro;
}

/**
 * Đảm bảo bảng tồn tại VÀ đúng cấu trúc mới nhất — kể cả khi theme đã
 * active và đã có dữ liệu từ trước (after_switch_theme không chạy lại).
 * dbDelta() an toàn để chạy lại nhiều lần — chỉ ALTER đúng phần khác biệt,
 * không đụng tới dữ liệu đã có.
 */
function vs_maybe_create_tournament_register_tables() {
    $installed_version = get_option('vs_tournament_register_tables_version');

    if ($installed_version === VS_TOURNAMENT_REG_SCHEMA_VERSION) {
        return;
    }

    vs_create_tournament_register_tables();
    update_option('vs_tournament_register_tables_version', VS_TOURNAMENT_REG_SCHEMA_VERSION);
}
add_action('init', 'vs_maybe_create_tournament_register_tables');