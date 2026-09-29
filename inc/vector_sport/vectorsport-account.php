<?php
defined('ABSPATH') || exit;

final class VectorSport_Account
{
    public static function init(): void
    {
        add_shortcode('vectorsport_account', [__CLASS__, 'render_shortcode']);
        add_action('init', [__CLASS__, 'handle_requests']);
    }

    private static function base_url(): string
    {
        return home_url('/account/');
    }

    private static function get_tabs(): array
    {
        return [
            'dashboard' => 'Trang tài khoản',
            'orders'    => 'Đơn hàng',
            'downloads' => 'Tệp tải xuống',
            'address'   => 'Địa chỉ',
            'account'   => 'Tài khoản',
            'logout'    => 'Đăng xuất',
        ];
    }

    private static function get_current_tab(): string
    {
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'dashboard';
        $tabs = self::get_tabs();

        return array_key_exists($tab, $tabs) ? $tab : 'dashboard';
    }

    private static function tab_url(string $tab, array $extra = []): string
    {
        $args = array_merge(['tab' => $tab], $extra);
        return add_query_arg($args, self::base_url());
    }

    public static function render_shortcode(): string
    {
        ob_start();
        ?>
        <section class="vs-account-page">
            <h1 class="vs-account-page__title">Đăng nhập/Đăng ký</h1>

            <?php if (!is_user_logged_in()) : ?>
                <div class="vs-account-page__auth">
                    <?php
                    if (shortcode_exists('vectorsport_auth')) {
                        echo do_shortcode('[vectorsport_auth]');
                    } else {
                        wp_login_form([
                            'echo'           => true,
                            'remember'       => true,
                            'redirect'       => self::base_url(),
                            'label_username' => 'Tên tài khoản hoặc email',
                            'label_password' => 'Mật khẩu',
                            'label_remember' => 'Ghi nhớ mật khẩu',
                            'label_log_in'   => 'Đăng nhập',
                        ]);
                    }
                    ?>
                </div>
            <?php else : ?>
                <?php self::render_logged_in_layout(); ?>
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }

    private static function render_logged_in_layout(): void
    {
        $tabs        = self::get_tabs();
        $current_tab = self::get_current_tab();
        $user        = wp_get_current_user();
        ?>
        <div class="vs-account">
            <?php self::render_notices(); ?>

            <aside class="vs-account__sidebar">
                <ul class="vs-account__menu">
                    <?php foreach ($tabs as $key => $label) : ?>
                        <?php
                        $is_active = $current_tab === $key;

                        if ($key === 'logout') {
                            $url = wp_logout_url(self::base_url());
                        } else {
                            $url = self::tab_url($key);
                        }
                        ?>
                        <li class="vs-account__menu-item<?php echo $is_active ? ' is-active' : ''; ?>">
                            <a href="<?php echo esc_url($url); ?>" class="vs-account__menu-link">
                                <?php echo esc_html($label); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="vs-account__content">
                <?php
                switch ($current_tab) {
                    case 'orders':
                        self::render_orders_tab($user);
                        break;

                    case 'downloads':
                        self::render_downloads_tab();
                        break;

                    case 'address':
                        self::render_address_tab($user);
                        break;

                    case 'account':
                        self::render_account_tab($user);
                        break;

                    default:
                        self::render_dashboard_tab($user);
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    private static function render_notices(): void
    {
        $notice = isset($_GET['vs_notice']) ? sanitize_key(wp_unslash($_GET['vs_notice'])) : '';
        $error  = isset($_GET['vs_error']) ? sanitize_key(wp_unslash($_GET['vs_error'])) : '';

        $notice_messages = [
            'account_updated' => 'Thông tin tài khoản đã được cập nhật.',
            'address_updated' => 'Địa chỉ đã được cập nhật.',
        ];

        $error_messages = [
            'invalid_email'        => 'Email không hợp lệ.',
            'email_exists'         => 'Email này đã được sử dụng.',
            'empty_display_name'   => 'Vui lòng nhập tên hiển thị.',
            'current_password'     => 'Mật khẩu hiện tại không đúng.',
            'password_mismatch'    => 'Mật khẩu mới và xác nhận mật khẩu không khớp.',
            'password_too_short'   => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'update_failed'        => 'Không thể cập nhật dữ liệu. Vui lòng thử lại.',
        ];

        if ($notice && isset($notice_messages[$notice])) {
            echo '<div class="vs-account__notice is-success">' . esc_html($notice_messages[$notice]) . '</div>';
        }

        if ($error && isset($error_messages[$error])) {
            echo '<div class="vs-account__notice is-error">' . esc_html($error_messages[$error]) . '</div>';
        }
    }

    private static function render_dashboard_tab(WP_User $user): void
    {
        $display_name = $user->display_name ?: $user->user_login;
        ?>
        <div class="vs-account-box">
            <p>
                Xin chào <strong><?php echo esc_html($display_name); ?></strong>
                (không phải <strong><?php echo esc_html($display_name); ?></strong>?
                <a href="<?php echo esc_url(wp_logout_url(self::base_url())); ?>">Đăng xuất</a>)
            </p>

            <p>
                Từ bảng điều khiển tài khoản của bạn, bạn có thể xem các đơn hàng gần đây,
                quản lý địa chỉ giao hàng, và chỉnh sửa mật khẩu cũng như chi tiết tài khoản.
            </p>
        </div>
        <?php
    }

    private static function render_orders_tab(WP_User $user): void
    {
        ?>
        <div class="vs-account-box">
            <h2 class="vs-account-box__title">Đơn hàng</h2>
            <p>Hiện tại bạn chưa kết nối module đơn hàng custom.</p>
            <p>Sau này phần này có thể nối với bảng đơn hàng riêng hoặc custom post type như <code>vs_order</code>.</p>
        </div>
        <?php
    }

    private static function render_downloads_tab(): void
    {
        ?>
        <div class="vs-account-box">
            <h2 class="vs-account-box__title">Tệp tải xuống</h2>
            <p>Hiện chưa có tệp nào để tải xuống.</p>
        </div>
        <?php
    }

    private static function render_address_tab(WP_User $user): void
    {
        $phone      = get_user_meta($user->ID, 'vs_phone', true);
        $address_1  = get_user_meta($user->ID, 'vs_address_1', true);
        $ward       = get_user_meta($user->ID, 'vs_ward', true);
        $district   = get_user_meta($user->ID, 'vs_district', true);
        $province   = get_user_meta($user->ID, 'vs_province', true);
        $note       = get_user_meta($user->ID, 'vs_address_note', true);
        ?>
        <div class="vs-account-box">
            <h2 class="vs-account-box__title">Địa chỉ</h2>

            <form method="post" class="vs-account-form">
                <?php wp_nonce_field('vs_update_address', 'vs_update_address_nonce'); ?>
                <input type="hidden" name="vs_account_action" value="update_address">

                <div class="vs-account-form__row">
                    <label for="vs_phone">Số điện thoại</label>
                    <input type="text" id="vs_phone" name="vs_phone" value="<?php echo esc_attr($phone); ?>">
                </div>

                <div class="vs-account-form__row">
                    <label for="vs_address_1">Địa chỉ cụ thể</label>
                    <input type="text" id="vs_address_1" name="vs_address_1" value="<?php echo esc_attr($address_1); ?>">
                </div>

                <div class="vs-account-form__grid">
                    <div class="vs-account-form__row">
                        <label for="vs_ward">Phường/Xã</label>
                        <input type="text" id="vs_ward" name="vs_ward" value="<?php echo esc_attr($ward); ?>">
                    </div>

                    <div class="vs-account-form__row">
                        <label for="vs_district">Quận/Huyện</label>
                        <input type="text" id="vs_district" name="vs_district" value="<?php echo esc_attr($district); ?>">
                    </div>

                    <div class="vs-account-form__row">
                        <label for="vs_province">Tỉnh/Thành phố</label>
                        <input type="text" id="vs_province" name="vs_province" value="<?php echo esc_attr($province); ?>">
                    </div>
                </div>

                <div class="vs-account-form__row">
                    <label for="vs_address_note">Ghi chú</label>
                    <textarea id="vs_address_note" name="vs_address_note" rows="4"><?php echo esc_textarea($note); ?></textarea>
                </div>

                <button type="submit" class="vs-account-btn">Lưu địa chỉ</button>
            </form>
        </div>
        <?php
    }

    private static function render_account_tab(WP_User $user): void
    {
        $first_name   = get_user_meta($user->ID, 'first_name', true);
        $last_name    = get_user_meta($user->ID, 'last_name', true);
        $display_name = $user->display_name;
        $email        = $user->user_email;
        ?>
        <div class="vs-account-box">
            <h2 class="vs-account-box__title">Tài khoản</h2>

            <form method="post" class="vs-account-form">
                <?php wp_nonce_field('vs_update_account', 'vs_update_account_nonce'); ?>
                <input type="hidden" name="vs_account_action" value="update_account">

                <div class="vs-account-form__grid">
                    <div class="vs-account-form__row">
                        <label for="vs_first_name">Tên</label>
                        <input type="text" id="vs_first_name" name="vs_first_name" value="<?php echo esc_attr($first_name); ?>">
                    </div>

                    <div class="vs-account-form__row">
                        <label for="vs_last_name">Họ</label>
                        <input type="text" id="vs_last_name" name="vs_last_name" value="<?php echo esc_attr($last_name); ?>">
                    </div>
                </div>

                <div class="vs-account-form__row">
                    <label for="vs_display_name">Tên hiển thị</label>
                    <input type="text" id="vs_display_name" name="vs_display_name" value="<?php echo esc_attr($display_name); ?>" required>
                </div>

                <div class="vs-account-form__row">
                    <label for="vs_email">Địa chỉ email</label>
                    <input type="email" id="vs_email" name="vs_email" value="<?php echo esc_attr($email); ?>" required>
                </div>

                <hr class="vs-account-form__divider">

                <div class="vs-account-form__row">
                    <label for="vs_current_password">Mật khẩu hiện tại</label>
                    <input type="password" id="vs_current_password" name="vs_current_password">
                </div>

                <div class="vs-account-form__grid">
                    <div class="vs-account-form__row">
                        <label for="vs_new_password">Mật khẩu mới</label>
                        <input type="password" id="vs_new_password" name="vs_new_password">
                    </div>

                    <div class="vs-account-form__row">
                        <label for="vs_confirm_password">Xác nhận mật khẩu mới</label>
                        <input type="password" id="vs_confirm_password" name="vs_confirm_password">
                    </div>
                </div>

                <button type="submit" class="vs-account-btn">Lưu thay đổi</button>
            </form>
        </div>
        <?php
    }

    public static function handle_requests(): void
    {
        if (!is_user_logged_in() || empty($_POST['vs_account_action'])) {
            return;
        }

        $action = sanitize_key(wp_unslash($_POST['vs_account_action']));

        if ($action === 'update_account') {
            self::handle_account_update();
        }

        if ($action === 'update_address') {
            self::handle_address_update();
        }
    }

    private static function handle_account_update(): void
    {
        if (
            empty($_POST['vs_update_account_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vs_update_account_nonce'])), 'vs_update_account')
        ) {
            return;
        }

        $user = wp_get_current_user();

        $first_name      = isset($_POST['vs_first_name']) ? sanitize_text_field(wp_unslash($_POST['vs_first_name'])) : '';
        $last_name       = isset($_POST['vs_last_name']) ? sanitize_text_field(wp_unslash($_POST['vs_last_name'])) : '';
        $display_name    = isset($_POST['vs_display_name']) ? sanitize_text_field(wp_unslash($_POST['vs_display_name'])) : '';
        $email           = isset($_POST['vs_email']) ? sanitize_email(wp_unslash($_POST['vs_email'])) : '';
        $current_pass    = isset($_POST['vs_current_password']) ? (string) wp_unslash($_POST['vs_current_password']) : '';
        $new_pass        = isset($_POST['vs_new_password']) ? (string) wp_unslash($_POST['vs_new_password']) : '';
        $confirm_pass    = isset($_POST['vs_confirm_password']) ? (string) wp_unslash($_POST['vs_confirm_password']) : '';

        if ($display_name === '') {
            wp_safe_redirect(self::tab_url('account', ['vs_error' => 'empty_display_name']));
            exit;
        }

        if (!is_email($email)) {
            wp_safe_redirect(self::tab_url('account', ['vs_error' => 'invalid_email']));
            exit;
        }

        $email_owner = email_exists($email);
        if ($email_owner && (int) $email_owner !== (int) $user->ID) {
            wp_safe_redirect(self::tab_url('account', ['vs_error' => 'email_exists']));
            exit;
        }

        $userdata = [
            'ID'           => $user->ID,
            'display_name' => $display_name,
            'user_email'   => $email,
        ];

        if ($new_pass !== '' || $confirm_pass !== '' || $current_pass !== '') {
            if (!wp_check_password($current_pass, $user->user_pass, $user->ID)) {
                wp_safe_redirect(self::tab_url('account', ['vs_error' => 'current_password']));
                exit;
            }

            if ($new_pass !== $confirm_pass) {
                wp_safe_redirect(self::tab_url('account', ['vs_error' => 'password_mismatch']));
                exit;
            }

            if (strlen($new_pass) < 6) {
                wp_safe_redirect(self::tab_url('account', ['vs_error' => 'password_too_short']));
                exit;
            }

            $userdata['user_pass'] = $new_pass;
        }

        $updated = wp_update_user($userdata);

        if (is_wp_error($updated)) {
            wp_safe_redirect(self::tab_url('account', ['vs_error' => 'update_failed']));
            exit;
        }

        update_user_meta($user->ID, 'first_name', $first_name);
        update_user_meta($user->ID, 'last_name', $last_name);

        if (!empty($userdata['user_pass'])) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, true);
        }

        wp_safe_redirect(self::tab_url('account', ['vs_notice' => 'account_updated']));
        exit;
    }

    private static function handle_address_update(): void
    {
        if (
            empty($_POST['vs_update_address_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vs_update_address_nonce'])), 'vs_update_address')
        ) {
            return;
        }

        $user = wp_get_current_user();

        $phone     = isset($_POST['vs_phone']) ? sanitize_text_field(wp_unslash($_POST['vs_phone'])) : '';
        $address_1 = isset($_POST['vs_address_1']) ? sanitize_text_field(wp_unslash($_POST['vs_address_1'])) : '';
        $ward      = isset($_POST['vs_ward']) ? sanitize_text_field(wp_unslash($_POST['vs_ward'])) : '';
        $district  = isset($_POST['vs_district']) ? sanitize_text_field(wp_unslash($_POST['vs_district'])) : '';
        $province  = isset($_POST['vs_province']) ? sanitize_text_field(wp_unslash($_POST['vs_province'])) : '';
        $note      = isset($_POST['vs_address_note']) ? sanitize_textarea_field(wp_unslash($_POST['vs_address_note'])) : '';

        update_user_meta($user->ID, 'vs_phone', $phone);
        update_user_meta($user->ID, 'vs_address_1', $address_1);
        update_user_meta($user->ID, 'vs_ward', $ward);
        update_user_meta($user->ID, 'vs_district', $district);
        update_user_meta($user->ID, 'vs_province', $province);
        update_user_meta($user->ID, 'vs_address_note', $note);

        wp_safe_redirect(self::tab_url('address', ['vs_notice' => 'address_updated']));
        exit;
    }
}

VectorSport_Account::init();