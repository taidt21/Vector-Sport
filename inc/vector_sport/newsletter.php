<?php
/**
 * Vector Sport Newsletter
 *
 * Chức năng:
 * - Đăng ký email nhận thông báo bằng AJAX
 * - Lưu email vào bảng riêng
 * - Gửi email khi có bài viết mới hoặc sản phẩm mới được publish
 * - Hỗ trợ hủy đăng ký
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database table.
 */
function vs_newsletter_table()
{
    global $wpdb;

    return $wpdb->prefix . 'vs_newsletter_subscribers';
}

function vs_newsletter_db_version()
{
    return '1.0.0';
}

/**
 * Install / update newsletter table.
 */
function vs_newsletter_install()
{
    global $wpdb;

    $installed_version = get_option('vs_newsletter_db_version');

    if ($installed_version === vs_newsletter_db_version()) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table = vs_newsletter_table();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(191) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        token VARCHAR(64) NOT NULL DEFAULT '',
        subscribed_at DATETIME NOT NULL,
        unsubscribed_at DATETIME NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email),
        KEY status (status)
    ) {$charset_collate};";

    dbDelta($sql);

    update_option('vs_newsletter_db_version', vs_newsletter_db_version());
}
add_action('after_setup_theme', 'vs_newsletter_install');

/**
 * AJAX subscribe handler.
 */
function vs_newsletter_subscribe_ajax_handler()
{
    if (
        !isset($_POST['vs_newsletter_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['vs_newsletter_nonce'])),
            'vs_newsletter_subscribe'
        )
    ) {
        wp_send_json_error([
            'message' => 'Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.',
        ]);
    }

    $email = isset($_POST['email'])
        ? sanitize_email(wp_unslash($_POST['email']))
        : '';

    if (!$email || !is_email($email)) {
        wp_send_json_error([
            'message' => 'Email chưa đúng định dạng.',
        ]);
    }

    global $wpdb;

    $table = vs_newsletter_table();
    $now = current_time('mysql');
    $token = wp_generate_password(32, false, false);

    $existing = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE email = %s LIMIT 1",
            $email
        ),
        ARRAY_A
    );

    if ($existing) {
        $updated = $wpdb->update(
            $table,
            [
                'status' => 'active',
                'token' => !empty($existing['token']) ? $existing['token'] : $token,
                'unsubscribed_at' => null,
            ],
            [
                'id' => (int) $existing['id'],
            ],
            [
                '%s',
                '%s',
                '%s',
            ],
            [
                '%d',
            ]
        );

        if ($updated === false) {
            wp_send_json_error([
                'message' => 'Không thể cập nhật email lúc này. Vui lòng thử lại sau.',
            ]);
        }

        wp_send_json_success([
            'message' => 'Email này đã được đăng ký nhận thông báo.',
        ]);
    }

    $inserted = $wpdb->insert(
        $table,
        [
            'email' => $email,
            'status' => 'active',
            'token' => $token,
            'subscribed_at' => $now,
        ],
        [
            '%s',
            '%s',
            '%s',
            '%s',
        ]
    );

    if (!$inserted) {
        wp_send_json_error([
            'message' => 'Không thể lưu email lúc này. Vui lòng thử lại sau.',
        ]);
    }

    wp_send_json_success([
        'message' => 'Đăng ký nhận thông báo thành công.',
    ]);
}
add_action('wp_ajax_vs_newsletter_subscribe', 'vs_newsletter_subscribe_ajax_handler');
add_action('wp_ajax_nopriv_vs_newsletter_subscribe', 'vs_newsletter_subscribe_ajax_handler');

/**
 * Get active subscribers.
 */
function vs_newsletter_get_active_emails()
{
    global $wpdb;

    $table = vs_newsletter_table();

    $emails = $wpdb->get_col(
        "SELECT email FROM {$table} WHERE status = 'active' ORDER BY id DESC"
    );

    $emails = array_filter(array_map('sanitize_email', (array) $emails), 'is_email');

    return array_values(array_unique($emails));
}

/**
 * Get unsubscribe URL.
 */
function vs_newsletter_get_unsubscribe_url($email)
{
    global $wpdb;

    $table = vs_newsletter_table();

    $subscriber = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT token FROM {$table} WHERE email = %s LIMIT 1",
            $email
        ),
        ARRAY_A
    );

    if (!$subscriber || empty($subscriber['token'])) {
        return home_url('/');
    }

    return add_query_arg(
        [
            'vs_newsletter_unsubscribe' => rawurlencode($email),
            'token' => rawurlencode($subscriber['token']),
        ],
        home_url('/')
    );
}

/**
 * Unsubscribe handler.
 */
function vs_newsletter_unsubscribe_handler()
{
    if (empty($_GET['vs_newsletter_unsubscribe']) || empty($_GET['token'])) {
        return;
    }

    $email = sanitize_email(wp_unslash($_GET['vs_newsletter_unsubscribe']));
    $token = sanitize_text_field(wp_unslash($_GET['token']));

    if (!$email || !$token) {
        return;
    }

    global $wpdb;

    $table = vs_newsletter_table();

    $wpdb->update(
        $table,
        [
            'status' => 'unsubscribed',
            'unsubscribed_at' => current_time('mysql'),
        ],
        [
            'email' => $email,
            'token' => $token,
        ],
        [
            '%s',
            '%s',
        ],
        [
            '%s',
            '%s',
        ]
    );

    wp_safe_redirect(add_query_arg('newsletter', 'unsubscribed', home_url('/')));
    exit;
}
add_action('template_redirect', 'vs_newsletter_unsubscribe_handler');

/**
 * Check if notification should be sent.
 */
function vs_newsletter_should_notify($new_status, $old_status, $post)
{
    if ($new_status !== 'publish' || $old_status === 'publish') {
        return false;
    }

    if (!$post || empty($post->ID)) {
        return false;
    }

    if (!in_array($post->post_type, ['post', 'product'], true)) {
        return false;
    }

    return true;
}

/**
 * Build email body.
 */
function vs_newsletter_build_email_body($post_id, $email)
{
    $post_type = get_post_type($post_id);
    $title = get_the_title($post_id);
    $url = get_permalink($post_id);

    $excerpt = has_excerpt($post_id)
        ? get_the_excerpt($post_id)
        : wp_trim_words(
            wp_strip_all_tags(get_post_field('post_content', $post_id)),
            35,
            '...'
        );

    $type_label = $post_type === 'product' ? 'sản phẩm mới' : 'bài viết mới';
    $unsubscribe_url = vs_newsletter_get_unsubscribe_url($email);

    ob_start();
    ?>
    <div style="font-family:Arial,sans-serif;color:#222;font-size:14px;">
        <h2 style="margin:0 0 12px;color:#EC2024;">
            Vector Sport vừa đăng <?php echo esc_html($type_label); ?>
        </h2>

        <h3 style="margin:0 0 12px;">
            <?php echo esc_html($title); ?>
        </h3>

        <p style="margin:0 0 18px;color:#555;">
            <?php echo esc_html($excerpt); ?>
        </p>

        <p style="margin:0 0 24px;">
            <a
                href="<?php echo esc_url($url); ?>"
                style="display:inline-block;background:#EC2024;color:#fff;text-decoration:none;padding:10px 18px;border-radius:999px;font-weight:bold;"
            >
                Xem chi tiết
            </a>
        </p>

        <p style="margin-top:24px;color:#777;font-size:12px;">
            Bạn nhận email này vì đã đăng ký nhận thông báo từ website Vector Sport.
            Nếu không muốn nhận nữa, bạn có thể
            <a href="<?php echo esc_url($unsubscribe_url); ?>">hủy đăng ký tại đây</a>.
        </p>
    </div>
    <?php

    return ob_get_clean();
}

/**
 * Send notification email to subscribers.
 */
function vs_newsletter_send_notification($post_id)
{
    $emails = vs_newsletter_get_active_emails();

    if (empty($emails)) {
        return;
    }

    $post_type = get_post_type($post_id);
    $title = get_the_title($post_id);

    $subject = $post_type === 'product'
        ? '[Vector Sport] Sản phẩm mới: ' . $title
        : '[Vector Sport] Bài viết mới: ' . $title;

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
    ];

    foreach ($emails as $email) {
        $body = vs_newsletter_build_email_body($post_id, $email);
        wp_mail($email, $subject, $body, $headers);
    }
}

/**
 * Notify subscribers when a new post/product is published.
 */
function vs_newsletter_notify_on_publish($new_status, $old_status, $post)
{
    if (!vs_newsletter_should_notify($new_status, $old_status, $post)) {
        return;
    }

    $sent_key = '_vs_newsletter_notified';

    if (get_post_meta($post->ID, $sent_key, true)) {
        return;
    }

    vs_newsletter_send_notification($post->ID);

    update_post_meta($post->ID, $sent_key, current_time('mysql'));
}
add_action('transition_post_status', 'vs_newsletter_notify_on_publish', 10, 3);
