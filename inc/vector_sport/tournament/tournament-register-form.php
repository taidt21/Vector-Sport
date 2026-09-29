<?php
defined('ABSPATH') || exit;

/* =========================================================================
 * XỬ LÝ SUBMIT FORM ĐĂNG KÝ
 * ========================================================================= */

function vs_handle_tournament_register_submit() {
    global $wpdb;

    $redirect_url = wp_get_referer() ?: home_url('/');

    if (
        empty($_POST['vs_tournament_register_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['vs_tournament_register_nonce'])),
            'vs_tournament_register_submit'
        )
    ) {
        wp_safe_redirect(add_query_arg('dk_status', 'invalid', $redirect_url));
        exit;
    }

    $ten_don_vi    = isset($_POST['ten_don_vi']) ? sanitize_text_field(wp_unslash($_POST['ten_don_vi'])) : '';
    $nguoi_lien_he = isset($_POST['nguoi_lien_he']) ? sanitize_text_field(wp_unslash($_POST['nguoi_lien_he'])) : '';
    $phone         = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $email         = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $ghi_chu       = isset($_POST['ghi_chu']) ? sanitize_textarea_field(wp_unslash($_POST['ghi_chu'])) : '';

    $phone_clean = preg_replace('/[\s\.\-]/', '', $phone);

    if (!$ten_don_vi || !$nguoi_lien_he || !$phone_clean) {
        wp_safe_redirect(add_query_arg('dk_status', 'invalid', $redirect_url));
        exit;
    }

    if (!preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone_clean)) {
        wp_safe_redirect(add_query_arg('dk_status', 'invalid', $redirect_url));
        exit;
    }

    // Email giờ BẮT BUỘC — cần để tạo/liên kết tài khoản quản lý sau này,
    // không còn tùy chọn như trước.
    if (!$email || !is_email($email)) {
        wp_safe_redirect(add_query_arg('dk_status', 'invalid', $redirect_url));
        exit;
    }

    // Xác định TÀI KHOẢN sở hữu đăng ký này — đây là bước quyết định cho
    // việc trưởng đoàn quản lý được đội của mình sau này.
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();

        // Đã có đội rồi thì không tạo thêm đội thứ 2 — chuyển sang trang
        // quản lý đội có sẵn của họ.
        $existing_reg_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}" . VS_TOURNAMENT_REG_TABLE . " WHERE user_id = %d",
                $user_id
            )
        );
        if ($existing_reg_id) {
            wp_safe_redirect(home_url('/quan-ly-doi/'));
            exit;
        }
    } else {
        $existing_user = get_user_by('email', $email);

        if ($existing_user) {
            // Email này đã có tài khoản — không tự ý tạo đăng ký hộ (tránh
            // 1 người lạ biết email của ai đó lại tạo được đội gắn vào tài
            // khoản người ta) — bắt đăng nhập trước rồi đăng ký lại.
            wp_safe_redirect(add_query_arg('dk_status', 'need_login', $redirect_url));
            exit;
        }

        // Tạo tài khoản mới — ĐÚNG cách vectorsport-auth-shortcode.php đang
        // làm cho đăng ký thường (chỉ cần email, mật khẩu ngẫu nhiên, gửi
        // mail để tự đặt mật khẩu) — để 1 trải nghiệm nhất quán trên site.
        $base_username = sanitize_user(current(explode('@', $email)), true);
        if ($base_username === '') {
            $base_username = 'doi';
        }
        $username = $base_username;
        $suffix = 1;
        while (username_exists($username)) {
            $username = $base_username . $suffix;
            $suffix++;
        }

        $password = wp_generate_password(20, true, true);
        $new_user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($new_user_id)) {
            error_log('VS Tournament Register - tạo tài khoản thất bại: ' . $new_user_id->get_error_message());
            wp_safe_redirect(add_query_arg('dk_status', 'error', $redirect_url));
            exit;
        }

        $user_id = $new_user_id;
        wp_new_user_notification($user_id, null, 'user');
    }

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;

    $inserted = $wpdb->insert(
        $reg_table,
        [
            'user_id'       => $user_id,
            'ten_don_vi'    => $ten_don_vi,
            'nguoi_lien_he' => $nguoi_lien_he,
            'phone'         => $phone_clean,
            'email'         => $email,
            'ghi_chu'       => $ghi_chu,
            'status'        => 'new',
            'created_at'    => current_time('mysql'),
        ],
        ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
    );

    if (!$inserted) {
        error_log('VS Tournament Register Insert Error: ' . $wpdb->last_error);
        wp_safe_redirect(add_query_arg('dk_status', 'error', $redirect_url));
        exit;
    }

    $registration_id = (int) $wpdb->insert_id;

    vs_send_tournament_register_email($registration_id);

    wp_safe_redirect(add_query_arg('dk_status', 'success', $redirect_url));
    exit;
}
add_action('admin_post_vs_tournament_register_submit', 'vs_handle_tournament_register_submit');
add_action('admin_post_nopriv_vs_tournament_register_submit', 'vs_handle_tournament_register_submit');


function vs_send_tournament_register_email($registration_id) {
    global $wpdb;

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;

    $item = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$reg_table} WHERE id = %d", $registration_id)
    );

    if (!$item) {
        return;
    }

    $to = vs_get_tournament_register_recipient_emails();
    if (empty($to)) {
        return;
    }

    $subject = '[Vector Sport] Đơn vị mới đăng ký giải đấu - ' . $item->ten_don_vi;

    $admin_link = admin_url('admin.php?page=vs-tournament-registers&action=view&id=' . absint($item->id));

    $body  = "Có đơn vị vừa đăng ký giải đấu trên website (chưa có danh sách VĐV — trưởng đoàn sẽ thêm sau khi đăng nhập).\n\n";
    $body .= "THÔNG TIN ĐƠN VỊ\n";
    $body .= "Tên đơn vị: {$item->ten_don_vi}\n";
    $body .= "Người liên hệ: {$item->nguoi_lien_he}\n";
    $body .= "Số điện thoại: {$item->phone}\n";
    $body .= "Email: " . ($item->email ?: 'Không nhập') . "\n";
    $body .= "Thời gian gửi: {$item->created_at}\n";
    $body .= "Xem chi tiết trong admin: {$admin_link}\n";

    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    wp_mail($to, $subject, $body, $headers);
}
