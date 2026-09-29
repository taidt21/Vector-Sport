<?php
defined('ABSPATH') || exit;

/* =========================================================================
 * ĐĂNG NHẬP RIÊNG cho trang Quản lý đội — CỐ Ý KHÔNG dùng chung shortcode
 * đăng nhập của khách mua hàng ([vectorsport_auth]): đăng nhập thành công
 * ở đó LUÔN chuyển hướng cứng về /account/ (trang tài khoản mua hàng),
 * không có cách nào quay lại đúng /quan-ly-doi/ — mượn tạm sẽ đưa nhầm
 * trưởng đoàn sang giao diện mua hàng. Dùng thẳng wp_signon() (cùng cơ
 * chế lõi của WordPress, không phải viết lại từ đầu), chỉ khác là TỰ
 * VIẾT form + luồng điều hướng cho đúng ngữ cảnh giải đấu.
 */
function vs_render_team_portal_login_form() {
    $error = isset($_GET['vs_login_error']) ? sanitize_key(wp_unslash($_GET['vs_login_error'])) : '';
    ?>
<div class="tournament_register_page__card" style="max-width: 440px; margin: 40px auto;">
    <h2 style="margin-top: 0;">Đăng nhập Quản lý đội</h2>

    <?php if ($error === '1') : ?>
    <div class="tournament_register_page__notice tournament_register_page__notice--error">
        Sai tên đăng nhập/email hoặc mật khẩu. Vui lòng thử lại.
    </div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field('vs_team_portal_login', 'vs_team_portal_login_nonce'); ?>
        <input type="hidden" name="vs_team_portal_login_action" value="1">

        <div class="tournament_register_page__field" style="margin-bottom: 14px;">
            <label for="vs_login">Email hoặc tên đăng nhập</label>
            <input type="text" id="vs_login" name="log" required aria-required="true" autocomplete="username">
        </div>

        <div class="tournament_register_page__field" style="margin-bottom: 18px;">
            <label for="vs_password">Mật khẩu</label>
            <input type="password" id="vs_password" name="pwd" required aria-required="true"
                autocomplete="current-password">
        </div>

        <div class="tournament_register_page__form_actions">
            <button type="submit" class="tournament_register_page__btn tournament_register_page__btn--primary"
                style="width: 100%;">
                Đăng nhập
            </button>
        </div>
    </form>

    <p style="margin-top: 16px; font-size: 14px;">
        <a href="<?php echo esc_url(wp_lostpassword_url(home_url('/quan-ly-doi/'))); ?>">Quên mật khẩu?</a>
    </p>
    <p style="font-size: 14px;">
        Chưa có đội? <a href="<?php echo esc_url(home_url('/dang-ky-giai-vovinam/')); ?>">Đăng ký đội tại đây</a>.
    </p>
</div>
<?php
}

function vs_handle_team_portal_login() {
    if (!isset($_POST['vs_team_portal_login_action']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $portal_url = home_url('/quan-ly-doi/');

    if (
        !isset($_POST['vs_team_portal_login_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vs_team_portal_login_nonce'])), 'vs_team_portal_login')
    ) {
        wp_safe_redirect(add_query_arg('vs_login_error', '1', $portal_url));
        exit;
    }

    // KHÔNG sanitize_text_field() mật khẩu — mật khẩu có thể chứa ký tự
    // bị hàm đó cắt bỏ, chỉ cần wp_unslash() để bỏ dấu \ WordPress tự
    // thêm vào $_POST, giống đúng cách wp-login.php gốc xử lý.
    $creds = [
        'user_login'    => isset($_POST['log']) ? sanitize_text_field(wp_unslash($_POST['log'])) : '',
        'user_password' => isset($_POST['pwd']) ? wp_unslash($_POST['pwd']) : '',
        'remember'      => true,
    ];

    if ($creds['user_login'] === '' || $creds['user_password'] === '') {
        wp_safe_redirect(add_query_arg('vs_login_error', '1', $portal_url));
        exit;
    }

    $result = wp_signon($creds, is_ssl());

    if (is_wp_error($result)) {
        wp_safe_redirect(add_query_arg('vs_login_error', '1', $portal_url));
        exit;
    }

    wp_safe_redirect($portal_url);
    exit;
}
add_action('init', 'vs_handle_team_portal_login');


/**
 * Đăng ký của CHÍNH người đang đăng nhập — nguồn DUY NHẤT xác định "đội
 * của tôi" trong toàn trang Quản lý đội. Không bao giờ đọc registration_id
 * từ $_GET/$_POST cho mục đích xác định quyền, chỉ dùng để HIỂN THỊ.
 */
function vs_get_my_tournament_registration() {
    global $wpdb;

    if (!is_user_logged_in()) {
        return null;
    }

    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;

    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$reg_table} WHERE user_id = %d", get_current_user_id())
    );
}

/**
 * Sau khi đăng nhập THÀNH CÔNG — kể cả qua trang đăng nhập MẶC ĐỊNH của
 * WordPress (VD bấm "Log in" trên trang "đặt lại mật khẩu thành công"
 * mà email tự sinh ra, trỏ thẳng tới wp-login.php, không đi qua form
 * riêng của mình) — nếu người vừa đăng nhập LÀ trưởng đoàn (có đăng ký
 * giải đấu), luôn đưa họ về ĐÚNG /quan-ly-doi/, không để WordPress tự
 * đưa vào /wp-admin/ (nơi họ không có quyền làm gì, gây bối rối).
 */
function vs_redirect_team_leader_after_login($redirect_to, $requested_redirect_to, $user) {
    if (!($user instanceof WP_User)) {
        return $redirect_to;
    }

    global $wpdb;
    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $has_registration = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM {$reg_table} WHERE user_id = %d", $user->ID)
    );

    if ($has_registration) {
        return home_url('/quan-ly-doi/');
    }

    return $redirect_to;
}
add_filter('login_redirect', 'vs_redirect_team_leader_after_login', 10, 3);

/**
 * vs_redirect_team_leader_after_login() ở trên chỉ xử lý lúc ĐĂNG NHẬP
 * thật (filter login_redirect) — còn bước SỚM HƠN, ngay sau khi ĐẶT MẬT
 * KHẨU MỚI từ link trong email (wp-login.php?action=resetpass), WordPress
 * mặc định KHÔNG tự đăng nhập/chuyển hướng đi đâu cả, chỉ hiện "Đã đổi
 * mật khẩu — bấm vào đây để đăng nhập" rồi DỪNG LẠI ở đúng trang đó. Đây
 * là hook RIÊNG cho đúng bước này (after_password_reset, chạy bên trong
 * reset_password() mà wp-login.php gọi).
 *
 * Người vừa đặt mật khẩu LÀ trưởng đoàn thì tự đăng nhập luôn (đã xác
 * thực qua đúng link email — kênh an toàn tương đương gõ mật khẩu, nên
 * không bắt gõ lại mật khẩu vừa đặt lần nữa cho phiền) rồi đưa thẳng về
 * /quan-ly-doi/, không dừng ở trang xác nhận mặc định của WordPress
 * (càng không để lỡ bấm "Log in" ở đó rồi WordPress tự đưa vào /wp-admin/).
 */
function vs_redirect_after_password_reset($user, $new_pass) {
    if (!($user instanceof WP_User)) {
        return;
    }

    global $wpdb;
    $reg_table = $wpdb->prefix . VS_TOURNAMENT_REG_TABLE;
    $has_registration = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM {$reg_table} WHERE user_id = %d", $user->ID)
    );

    if (!$has_registration) {
        return;
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);

    wp_safe_redirect(home_url('/quan-ly-doi/'));
    exit;
}
add_action('after_password_reset', 'vs_redirect_after_password_reset', 10, 2);