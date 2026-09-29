<?php
/**
 * Vector Sport - Contact/Register Form
 * Lưu dữ liệu vào custom table, hiển thị trong admin, gửi email.
 */

defined('ABSPATH') || exit;

const VS_CONTACT_TABLE = 'vs_contact_registers';

/**
 * Tạo bảng khi active theme.
 */
function vs_create_contact_register_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . VS_CONTACT_TABLE;
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        full_name VARCHAR(190) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(190) NULL,
        student_age VARCHAR(100) NULL,
        interest VARCHAR(190) NULL,
        time_preference VARCHAR(190) NULL,
        message TEXT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'new',
        source_url TEXT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY phone (phone),
        KEY status (status),
        KEY created_at (created_at)
    ) {$charset_collate};";

    dbDelta($sql);
}
add_action('after_switch_theme', 'vs_create_contact_register_table');

/**
 * Đảm bảo bảng tồn tại.
 * Dùng thêm init để tránh trường hợp copy code vào theme đang active mà after_switch_theme không chạy.
 */
function vs_maybe_create_contact_register_table() {
    $installed = get_option('vs_contact_register_table_installed');

    if ($installed === 'yes') {
        return;
    }

    vs_create_contact_register_table();
    update_option('vs_contact_register_table_installed', 'yes');
}
add_action('init', 'vs_maybe_create_contact_register_table');


/**
 * Xử lý submit form.
 */
function vs_handle_contact_register_submit() {
    global $wpdb;

    $redirect_url = wp_get_referer() ?: home_url('/');

    if (
        empty($_POST['vs_contact_register_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['vs_contact_register_nonce'])),
            'vs_contact_register_submit'
        )
    ) {
        wp_safe_redirect(add_query_arg('status', 'invalid', $redirect_url));
        exit;
    }

    $full_name       = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
    $phone           = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $email           = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $student_age     = isset($_POST['student_age']) ? sanitize_text_field(wp_unslash($_POST['student_age'])) : '';
    $interest        = isset($_POST['interest']) ? sanitize_text_field(wp_unslash($_POST['interest'])) : '';
    $time_preference = isset($_POST['time_preference']) ? sanitize_text_field(wp_unslash($_POST['time_preference'])) : '';
    $message         = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

    $phone_clean = preg_replace('/[\s\.\-]/', '', $phone);

    if (!$full_name || !$phone_clean) {
        wp_safe_redirect(add_query_arg('status', 'invalid', $redirect_url));
        exit;
    }

    if (!preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone_clean)) {
        wp_safe_redirect(add_query_arg('status', 'invalid', $redirect_url));
        exit;
    }

    if ($email && !is_email($email)) {
        wp_safe_redirect(add_query_arg('status', 'invalid', $redirect_url));
        exit;
    }

    $table_name = $wpdb->prefix . VS_CONTACT_TABLE;

    $inserted = $wpdb->insert(
        $table_name,
        [
            'full_name'       => $full_name,
            'phone'           => $phone_clean,
            'email'           => $email,
            'student_age'     => $student_age,
            'interest'        => $interest,
            'time_preference' => $time_preference,
            'message'         => $message,
            'status'          => 'new',
            'source_url'      => $redirect_url,
            'created_at'      => current_time('mysql'),
        ],
        [
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
        ]
    );

    if (!$inserted) {
        error_log('VS Contact Register Insert Error: ' . $wpdb->last_error);

        wp_safe_redirect(add_query_arg('status', 'error', $redirect_url));
        exit;
    }

    $register_id = (int) $wpdb->insert_id;

    vs_send_contact_register_email($register_id);

    wp_safe_redirect(add_query_arg('status', 'success', $redirect_url));
    exit;
}
add_action('admin_post_vs_contact_register_submit', 'vs_handle_contact_register_submit');
add_action('admin_post_nopriv_vs_contact_register_submit', 'vs_handle_contact_register_submit');


/**
 * Gửi email thông báo.
 */
function vs_send_contact_register_email($register_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . VS_CONTACT_TABLE;

    $item = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $register_id)
    );

    if (!$item) {
        return;
    }

    $to = vs_get_contact_register_recipient_emails();
        if (empty($to)) {
        return false;
    }
    $subject = '[Vector Sport] Có khách gửi form đăng ký - ' . $item->full_name;

    $admin_link = admin_url('admin.php?page=vs-contact-registers&action=view&id=' . absint($item->id));

    $body  = "Có khách vừa gửi form liên hệ / đăng ký trên website Vector Sport.\n\n";
    $body .= "THÔNG TIN KHÁCH HÀNG\n";
    $body .= "Họ và tên: {$item->full_name}\n";
    $body .= "Số điện thoại: {$item->phone}\n";
    $body .= "Email: " . ($item->email ?: 'Không nhập') . "\n";
    $body .= "Độ tuổi học viên: " . ($item->student_age ?: 'Không nhập') . "\n";
    $body .= "Nội dung quan tâm: " . ($item->interest ?: 'Không chọn') . "\n";
    $body .= "Khung giờ mong muốn: " . ($item->time_preference ?: 'Không chọn') . "\n\n";

    $body .= "NỘI DUNG CẦN TƯ VẤN\n";
    $body .= ($item->message ?: 'Không nhập') . "\n\n";

    $body .= "THÔNG TIN HỆ THỐNG\n";
    $body .= "Thời gian gửi: {$item->created_at}\n";
    $body .= "Nguồn gửi: " . ($item->source_url ?: home_url('/')) . "\n";
    $body .= "Xem trong admin: {$admin_link}\n";

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
    ];

    wp_mail($to, $subject, $body, $headers);
}

/**
 * Thêm menu admin.
 */
function vs_contact_register_admin_menu() {
    add_menu_page(
        'Khách đăng ký',
        'Khách đăng ký',
        'manage_options',
        'vs-contact-registers',
        'vs_render_contact_register_admin_page',
        'dashicons-email-alt2',
        26
    );

    add_submenu_page(
        'vs-contact-registers',
        'Danh sách khách đăng ký',
        'Danh sách',
        'manage_options',
        'vs-contact-registers',
        'vs_render_contact_register_admin_page'
    );

    add_submenu_page(
        'vs-contact-registers',
        'Cài đặt email nhận thông báo',
        'Email nhận thông báo',
        'manage_options',
        'vs-contact-email-settings',
        'vs_render_contact_email_settings_page'
    );
}
add_action('admin_menu', 'vs_contact_register_admin_menu');


/**
 * Render admin page.
 */
function vs_render_contact_register_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập trang này.');
    }

    $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
    $id = isset($_GET['id']) ? absint($_GET['id']) : 0;

    echo '<div class="wrap">';

    if ($action === 'view' && $id > 0) {
        vs_render_contact_register_detail($id);
    } else {
        vs_render_contact_register_list();
    }

    echo '</div>';
}


/**
 * Render danh sách khách đăng ký.
 */
function vs_render_contact_register_list() {
    global $wpdb;

    $table_name = $wpdb->prefix . VS_CONTACT_TABLE;

    $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
    $per_page = 20;
    $offset = ($paged - 1) * $per_page;

    $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

    $where = 'WHERE 1=1';
    $params = [];

    if ($search) {
        $where .= ' AND (full_name LIKE %s OR phone LIKE %s OR email LIKE %s OR interest LIKE %s)';
        $like = '%' . $wpdb->esc_like($search) . '%';

        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if ($params) {
        $total = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} {$where}", $params)
        );

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} {$where} ORDER BY id DESC LIMIT %d OFFSET %d",
                array_merge($params, [$per_page, $offset])
            )
        );
    } else {
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY id DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
    }

    $base_url = admin_url('admin.php?page=vs-contact-registers');

    ?>
<h1 class="wp-heading-inline">Khách đăng ký</h1>

<p>Danh sách khách gửi form liên hệ / đăng ký từ website Vector Sport.</p>

<form method="get" style="margin: 16px 0;">
    <input type="hidden" name="page" value="vs-contact-registers">

    <p class="search-box" style="float:none;">
        <label class="screen-reader-text" for="vs-contact-search-input">Tìm khách đăng ký</label>
        <input type="search" id="vs-contact-search-input" name="s" value="<?php echo esc_attr($search); ?>"
            placeholder="Tìm theo tên, SĐT, email...">
        <input type="submit" class="button" value="Tìm kiếm">
    </p>
</form>

<table class="widefat fixed striped">
    <thead>
        <tr>
            <th style="width:70px;">ID</th>
            <th>Khách hàng</th>
            <th>Số điện thoại</th>
            <th>Email</th>
            <th>Nội dung quan tâm</th>
            <th>Khung giờ</th>
            <!-- <th>Trạng thái</th> -->
            <th>Ngày gửi</th>
            <th style="width:100px;">Chi tiết</th>
        </tr>
    </thead>

    <tbody>
        <?php if ($items) : ?>
        <?php foreach ($items as $item) : ?>
        <tr>
            <td><?php echo esc_html($item->id); ?></td>

            <td>
                <strong><?php echo esc_html($item->full_name); ?></strong>
            </td>

            <td>
                <a href="tel:<?php echo esc_attr($item->phone); ?>">
                    <?php echo esc_html($item->phone); ?>
                </a>
            </td>

            <td>
                <?php if ($item->email) : ?>
                <a href="mailto:<?php echo esc_attr($item->email); ?>">
                    <?php echo esc_html($item->email); ?>
                </a>
                <?php else : ?>
                —
                <?php endif; ?>
            </td>

            <td><?php echo esc_html($item->interest ?: '—'); ?></td>

            <td><?php echo esc_html($item->time_preference ?: '—'); ?></td>

            <!-- <td>
                <?php if ($item->status === 'new') : ?>
                <span
                    style="display:inline-block;padding:4px 9px;border-radius:999px;background:#fff0f0;color:#EC2024;font-weight:700;">
                    Mới
                </span>
                <?php else : ?>
                <?php echo esc_html($item->status); ?>
                <?php endif; ?>
            </td> -->

            <td><?php echo esc_html(mysql2date('d/m/Y H:i', $item->created_at)); ?></td>

            <td>
                <a class="button button-small" href="<?php echo esc_url(add_query_arg([
                                'page'   => 'vs-contact-registers',
                                'action' => 'view',
                                'id'     => absint($item->id),
                            ], admin_url('admin.php'))); ?>">
                    Xem
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else : ?>
        <tr>
            <td colspan="9">Chưa có dữ liệu đăng ký.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
    $total_pages = (int) ceil($total / $per_page);

    if ($total_pages > 1) {
        echo '<div class="tablenav"><div class="tablenav-pages">';

        echo paginate_links([
            'base'      => add_query_arg('paged', '%#%', $base_url),
            'format'    => '',
            'prev_text' => '‹',
            'next_text' => '›',
            'total'     => $total_pages,
            'current'   => $paged,
        ]);

        echo '</div></div>';
    }
}


/**
 * Render chi tiết khách đăng ký.
 */
function vs_render_contact_register_detail($id) {
    global $wpdb;

    $table_name = $wpdb->prefix . VS_CONTACT_TABLE;

    $item = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id)
    );

    if (!$item) {
        echo '<h1>Không tìm thấy dữ liệu</h1>';
        echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=vs-contact-registers')) . '">Quay lại danh sách</a></p>';
        return;
    }

    ?>
<h1>Chi tiết khách đăng ký</h1>

<p>
    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=vs-contact-registers')); ?>">
        ← Quay lại danh sách
    </a>
</p>

<table class="widefat striped" style="max-width: 900px;">
    <tbody>
        <tr>
            <th style="width: 220px;">ID</th>
            <td><?php echo esc_html($item->id); ?></td>
        </tr>

        <tr>
            <th>Họ và tên</th>
            <td><?php echo esc_html($item->full_name); ?></td>
        </tr>

        <tr>
            <th>Số điện thoại</th>
            <td>
                <a href="tel:<?php echo esc_attr($item->phone); ?>">
                    <?php echo esc_html($item->phone); ?>
                </a>
            </td>
        </tr>

        <tr>
            <th>Email</th>
            <td>
                <?php if ($item->email) : ?>
                <a href="mailto:<?php echo esc_attr($item->email); ?>">
                    <?php echo esc_html($item->email); ?>
                </a>
                <?php else : ?>
                Không nhập
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <th>Độ tuổi học viên</th>
            <td><?php echo esc_html($item->student_age ?: 'Không nhập'); ?></td>
        </tr>

        <tr>
            <th>Nội dung quan tâm</th>
            <td><?php echo esc_html($item->interest ?: 'Không chọn'); ?></td>
        </tr>

        <tr>
            <th>Khung giờ mong muốn</th>
            <td><?php echo esc_html($item->time_preference ?: 'Không chọn'); ?></td>
        </tr>

        <tr>
            <th>Nội dung cần tư vấn</th>
            <td><?php echo nl2br(esc_html($item->message ?: 'Không nhập')); ?></td>
        </tr>

        <tr>
            <th>Trạng thái</th>
            <td><?php echo esc_html($item->status === 'new' ? 'Mới' : $item->status); ?></td>
        </tr>

        <tr>
            <th>Nguồn gửi</th>
            <td>
                <?php if ($item->source_url) : ?>
                <a href="<?php echo esc_url($item->source_url); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html($item->source_url); ?>
                </a>
                <?php else : ?>
                —
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <th>Thời gian gửi</th>
            <td><?php echo esc_html(mysql2date('d/m/Y H:i', $item->created_at)); ?></td>
        </tr>
    </tbody>
</table>
<?php
}
function vs_render_contact_email_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập trang này.');
    }

    if (
        isset($_POST['vs_contact_email_settings_nonce']) &&
        wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['vs_contact_email_settings_nonce'])),
            'vs_save_contact_email_settings'
        )
    ) {
        $raw_emails = isset($_POST['vs_contact_recipient_emails'])
            ? sanitize_textarea_field(wp_unslash($_POST['vs_contact_recipient_emails']))
            : '';

        $emails = vs_parse_contact_recipient_emails($raw_emails);

        update_option('vs_contact_recipient_emails', $emails);

        echo '<div class="notice notice-success is-dismissible"><p>Đã lưu danh sách email nhận thông báo.</p></div>';
    }

    $saved_emails = get_option('vs_contact_recipient_emails', []);
    $email_text = '';

    if (is_array($saved_emails) && !empty($saved_emails)) {
        $email_text = implode("\n", $saved_emails);
    }

    ?>
<div class="wrap">
    <h1>Cài đặt email nhận thông báo</h1>

    <p>
        Nhập danh sách email sẽ nhận thông báo khi có khách gửi form liên hệ / đăng ký.
        Mỗi email đặt trên một dòng.
    </p>

    <form method="post">
        <?php wp_nonce_field('vs_save_contact_email_settings', 'vs_contact_email_settings_nonce'); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="vs_contact_recipient_emails">
                            Email nhận thông báo
                        </label>
                    </th>

                    <td>
                        <textarea id="vs_contact_recipient_emails" name="vs_contact_recipient_emails" rows="8"
                            class="large-text code"
                            placeholder="email1@gmail.com&#10;email2@gmail.com&#10;email3@gmail.com"><?php echo esc_textarea($email_text); ?></textarea>

                        <p class="description">
                            Ví dụ: sale@vectorsport.vn, admin@vectorsport.vn. Nếu để trống, hệ thống sẽ gửi về email
                            quản trị website.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button('Lưu cài đặt'); ?>
    </form>
</div>
<?php
}

function vs_parse_contact_recipient_emails($raw_emails) {
    if (!$raw_emails) {
        return [];
    }

    $raw_emails = str_replace([',', ';'], "\n", $raw_emails);

    $lines = preg_split('/\r\n|\r|\n/', $raw_emails);

    if (!is_array($lines)) {
        return [];
    }

    $emails = [];

    foreach ($lines as $line) {
        $email = sanitize_email(trim($line));

        if ($email && is_email($email)) {
            $emails[] = $email;
        }
    }

    return array_values(array_unique($emails));
}

function vs_get_contact_register_recipient_emails() {
    $emails = get_option('vs_contact_recipient_emails', []);

    if (is_array($emails) && !empty($emails)) {
        return $emails;
    }

    $admin_email = get_option('admin_email');

    if ($admin_email && is_email($admin_email)) {
        return [$admin_email];
    }

    return [];
}