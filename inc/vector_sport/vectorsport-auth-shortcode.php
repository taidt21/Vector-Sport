<?php
if (!defined('ABSPATH')) {
    exit;
}

final class VectorSport_Auth_Shortcode
{
    public static function init(): void
    {
        add_shortcode('vectorsport_auth', [self::class, 'render_shortcode']);
        add_action('init', [self::class, 'handle_actions']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

public static function enqueue_assets(): void
{
    // $css_rel_path = '/assets/css/vectorsport-auth.css';
    $js_rel_path  = '/assets/js/vectorsport-auth.js';

    // $css_abs_path = get_template_directory() . $css_rel_path;
    $js_abs_path  = get_template_directory() . $js_rel_path;

    // wp_register_style(
    //     'vectorsport-auth',
    //     get_template_directory_uri() . $css_rel_path,
    //     [],
    //     file_exists($css_abs_path) ? (string) filemtime($css_abs_path) : '1.0.0'
    // );

    wp_register_script(
        'vectorsport-auth',
        get_template_directory_uri() . $js_rel_path,
        ['jquery'],
        file_exists($js_abs_path) ? (string) filemtime($js_abs_path) : '1.0.0',
        true
    );
}

    public static function handle_actions(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = isset($_POST['vectorsport_auth_action'])
            ? sanitize_text_field(wp_unslash($_POST['vectorsport_auth_action']))
            : '';

        if ($action === 'login') {
            self::handle_login();
            return;
        }

        if ($action === 'register') {
            self::handle_register();
        }
    }

    private static function handle_login(): void
    {
        if (!isset($_POST['vectorsport_login_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vectorsport_login_nonce'])), 'vectorsport_login')) {
            self::redirect_with_notice('login', 'error', 'Phiên đăng nhập không hợp lệ. Vui lòng thử lại.');
        }

        $login_input = isset($_POST['log']) ? sanitize_text_field(wp_unslash($_POST['log'])) : '';
        $password = isset($_POST['pwd']) ? (string) wp_unslash($_POST['pwd']) : '';
        $remember = !empty($_POST['rememberme']);
        $redirect_to = self::sanitize_redirect_to();

        if ($login_input === '' || $password === '') {
            self::redirect_with_notice('login', 'error', 'Vui lòng nhập đầy đủ tên tài khoản/email và mật khẩu.');
        }

        $user = get_user_by('email', $login_input);
        if ($user instanceof WP_User) {
            $login_input = $user->user_login;
        }

        $credentials = [
            'user_login'    => $login_input,
            'user_password' => $password,
            'remember'      => $remember,
        ];

        $signon = wp_signon($credentials, is_ssl());

        if (is_wp_error($signon)) {
            self::redirect_with_notice('login', 'error', 'Thông tin đăng nhập chưa đúng. Vui lòng kiểm tra lại.');
        }

        wp_safe_redirect(home_url('/account/'));
        exit;
    }

    private static function handle_register(): void
    {
        if (!get_option('users_can_register')) {
            self::redirect_with_notice('register', 'error', 'Website hiện đang tắt chức năng đăng ký tài khoản.');
        }

        if (!isset($_POST['vectorsport_register_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vectorsport_register_nonce'])), 'vectorsport_register')) {
            self::redirect_with_notice('register', 'error', 'Phiên đăng ký không hợp lệ. Vui lòng thử lại.');
        }

        $email = isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '';
        $newsletter = !empty($_POST['newsletter_optin']) ? '1' : '0';
        $redirect_to = self::sanitize_redirect_to();

        if ($email === '' || !is_email($email)) {
            self::redirect_with_notice('register', 'error', 'Vui lòng nhập địa chỉ email hợp lệ.');
        }

        if (email_exists($email)) {
            self::redirect_with_notice('register', 'error', 'Email này đã tồn tại trong hệ thống. Bạn hãy đăng nhập hoặc dùng chức năng quên mật khẩu.');
        }

        $base_username = sanitize_user(current(explode('@', $email)), true);
        if ($base_username === '') {
            $base_username = 'user';
        }

        $username = $base_username;
        $index = 1;
        while (username_exists($username)) {
            $username = $base_username . $index;
            $index++;
        }

        $password = wp_generate_password(20, true, true);
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            self::redirect_with_notice('register', 'error', 'Không thể tạo tài khoản lúc này. Vui lòng thử lại sau.');
        }

        update_user_meta($user_id, 'vectorsport_newsletter_optin', $newsletter);

        $user = get_user_by('id', $user_id);
        if ($user instanceof WP_User) {
            wp_new_user_notification($user_id, null, 'user');
        }

        $message = 'Đăng ký thành công. Hệ thống đã gửi email để bạn thiết lập mật khẩu mới.';
        if ($redirect_to) {
            $message .= ' Sau khi đăng nhập, bạn sẽ được chuyển tới trang mong muốn.';
        }

        self::redirect_with_notice('register', 'success', $message);
    }

    public static function render_shortcode(array $atts = []): string
    {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $account_url = self::get_account_url();
            $logout_url = wp_logout_url(home_url('/'));

            ob_start();
            ?>
            <div class="vs-auth vs-auth--logged-in container">
                <div class="vs-auth__notice vs-auth__notice--success">
                    Bạn đang đăng nhập với tài khoản <strong><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></strong>.
                </div>
                <div class="vs-auth__logged-actions">
                    <a class="vs-auth__button" href="<?php echo esc_url($account_url); ?>">Vào trang tài khoản</a>
                    <a class="vs-auth__button vs-auth__button--secondary" href="<?php echo esc_url($logout_url); ?>">Đăng xuất</a>
                </div>
            </div>
            <?php
            return (string) ob_get_clean();
        }

        wp_enqueue_style('vectorsport-auth');
        wp_enqueue_script('vectorsport-auth');

        $notice = self::get_notice();
        $active_tab = isset($notice['tab']) ? $notice['tab'] : 'login';
        $redirect_to = self::sanitize_redirect_to();
        $account_url = self::get_account_url();
        $lost_password_url = wp_lostpassword_url($account_url);

        ob_start();
        ?>
        <div class="vs-auth container" data-active-tab="<?php echo esc_attr($active_tab); ?>">
            <?php if (!empty($notice['message'])) : ?>
                <div class="vs-auth__notice vs-auth__notice--<?php echo esc_attr($notice['type']); ?>">
                    <?php echo esc_html($notice['message']); ?>
                </div>
            <?php endif; ?>

            <div class="vs-auth__section">
                <h2 class="vs-auth__title">Đăng nhập/Đăng ký</h2>

                <div class="vs-auth__socials">
                    <a href="#" class="vs-auth__social vs-auth__social--facebook" rel="nofollow">
                        <span class="vs-auth__social-icon vs-auth__social-icon--facebook">f</span>
                        <span>Đăng nhập bằng <strong>Facebook</strong></span>
                    </a>

                    <a href="#" class="vs-auth__social vs-auth__social--google" rel="nofollow">
                        <span class="vs-auth__social-icon vs-auth__social-icon--google">G</span>
                        <span>Đăng nhập bằng <strong>Google</strong></span>
                    </a>
                </div>
            </div>

            <div class="vs-auth__grid">
                <section class="vs-auth__section vs-auth__section--form <?php echo $active_tab === 'login' ? 'is-active' : ''; ?>" id="vs-login-panel">
                    <h2 class="vs-auth__title">Đăng nhập</h2>

                    <form class="vs-auth__card" method="post" action="<?php echo esc_url(get_permalink()); ?>">
                        <input type="hidden" name="vectorsport_auth_action" value="login">
                        <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
                        <?php wp_nonce_field('vectorsport_login', 'vectorsport_login_nonce'); ?>

                        <div class="vs-auth__field">
                            <label class="vs-auth__label" for="vs-log">Tên tài khoản hoặc địa chỉ email <span class="vs-auth__required">*</span></label>
                            <input class="vs-auth__input" id="vs-log" type="text" name="log" autocomplete="username" required>
                        </div>

                        <div class="vs-auth__field">
                            <label class="vs-auth__label" for="vs-pwd">Mật khẩu <span class="vs-auth__required">*</span></label>
                            <div class="vs-auth__password-wrap">
                                <input class="vs-auth__input vs-auth__input--password" id="vs-pwd" type="password" name="pwd" autocomplete="current-password" required>
                                <button type="button" class="vs-auth__toggle-password" data-toggle-password="#vs-pwd" aria-label="Hiện hoặc ẩn mật khẩu">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2 12C4.8 7.5 8.1 5.25 12 5.25C15.9 5.25 19.2 7.5 22 12C19.2 16.5 15.9 18.75 12 18.75C8.1 18.75 4.8 16.5 2 12Z" stroke="currentColor" stroke-width="1.7"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                                        <path d="M4 20L20 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="vs-auth__captcha">
                            <div class="vs-auth__captcha-box">
                                <span class="vs-auth__captcha-check"></span>
                                <span class="vs-auth__captcha-text">Tôi không phải là người máy</span>
                                <span class="vs-auth__captcha-badge">reCAPTCHA</span>
                            </div>
                        </div>

                        <label class="vs-auth__checkbox">
                            <input type="checkbox" name="rememberme" value="forever">
                            <span>Ghi nhớ mật khẩu</span>
                        </label>

                        <div class="vs-auth__actions">
                            <button class="vs-auth__button" type="submit">Đăng nhập</button>
                        </div>

                        <a class="vs-auth__lost-password" href="<?php echo esc_url($lost_password_url); ?>">Quên mật khẩu?</a>
                    </form>
                </section>

                <section class="vs-auth__section vs-auth__section--form <?php echo $active_tab === 'register' ? 'is-active' : ''; ?>" id="vs-register-panel">
                    <h2 class="vs-auth__title">Đăng ký</h2>

                    <form class="vs-auth__card" method="post" action="<?php echo esc_url(get_permalink()); ?>">
                        <input type="hidden" name="vectorsport_auth_action" value="register">
                        <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
                        <?php wp_nonce_field('vectorsport_register', 'vectorsport_register_nonce'); ?>

                        <div class="vs-auth__field">
                            <label class="vs-auth__label" for="vs-register-email">Địa chỉ email <span class="vs-auth__required">*</span></label>
                            <input class="vs-auth__input" id="vs-register-email" type="email" name="user_email" autocomplete="email" required>
                        </div>

                        <p class="vs-auth__desc">Một liên kết để đặt mật khẩu mới sẽ được gửi đến địa chỉ email của bạn.</p>

                        <label class="vs-auth__checkbox vs-auth__checkbox--newsletter">
                            <input type="checkbox" name="newsletter_optin" value="1" checked>
                            <span>Nhận thông tin từ GUY’s Closet</span>
                        </label>

                        <p class="vs-auth__privacy">Dữ liệu cá nhân bạn cung cấp sẽ được bảo mật tuyệt đối. Chi tiết vui lòng xem thêm tại: <a href="<?php echo esc_url(home_url('/chinh-sach-bao-mat/')); ?>">chính sách riêng tư</a>.</p>

                        <div class="vs-auth__actions">
                            <button class="vs-auth__button" type="submit">Đăng ký</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private static function get_notice(): array
    {
        $message = isset($_GET['vs_notice']) ? sanitize_text_field(wp_unslash($_GET['vs_notice'])) : '';
        $type = isset($_GET['vs_notice_type']) ? sanitize_key(wp_unslash($_GET['vs_notice_type'])) : 'success';
        $tab = isset($_GET['vs_tab']) ? sanitize_key(wp_unslash($_GET['vs_tab'])) : 'login';

        if ($message === '') {
            return [];
        }

        return [
            'message' => rawurldecode($message),
            'type'    => in_array($type, ['success', 'error'], true) ? $type : 'success',
            'tab'     => in_array($tab, ['login', 'register'], true) ? $tab : 'login',
        ];
    }

    private static function redirect_with_notice(string $tab, string $type, string $message): void
    {
        $target = wp_get_referer();
        if (!$target) {
            $target = home_url('/');
        }

        $target = add_query_arg(
            [
                'vs_tab'         => $tab,
                'vs_notice_type' => $type,
                'vs_notice'      => rawurlencode($message),
            ],
            $target
        );

        wp_safe_redirect($target);
        exit;
    }

    private static function sanitize_redirect_to(): string
    {
        $redirect_to = isset($_REQUEST['redirect_to']) ? wp_unslash($_REQUEST['redirect_to']) : '';
        if ($redirect_to === '') {
            return '';
        }

        $redirect_to = wp_validate_redirect($redirect_to, '');
        return $redirect_to ?: '';
    }

    private static function get_account_url(): string
    {
        if (function_exists('wc_get_page_permalink')) {
            $my_account_url = wc_get_page_permalink('myaccount');
            if ($my_account_url) {
                return $my_account_url;
            }
        }

        return home_url('/account/');
    }
}

VectorSport_Auth_Shortcode::init();
