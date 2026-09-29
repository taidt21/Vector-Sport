<?php
/**
 * Template Name: Page Dang Ky Giai Dau
 */

defined('ABSPATH') || exit;

// Đã đăng nhập VÀ đã có đội rồi — không cần thấy lại form trắng, chuyển
// thẳng sang trang quản lý đội của họ.
if (is_user_logged_in() && vs_get_my_tournament_registration()) {
    wp_safe_redirect(home_url('/quan-ly-doi/'));
    exit;
}

get_header();

$dk_status = isset($_GET['dk_status']) ? sanitize_key(wp_unslash($_GET['dk_status'])) : '';

$status_messages = [
    'success' => [
        'class' => 'tournament_register_page__notice--success',
        'text'  => 'Đăng ký đơn vị thành công. Hệ thống đã gửi email để bạn đặt mật khẩu — sau khi đăng nhập, vào trang Quản lý đội để thêm danh sách vận động viên.',
    ],
    'invalid' => [
        'class' => 'tournament_register_page__notice--error',
        'text'  => 'Vui lòng kiểm tra lại thông tin đơn vị (tên đơn vị, người liên hệ, số điện thoại, email).',
    ],
    'need_login' => [
        'class' => 'tournament_register_page__notice--error',
        'text'  => 'Email này đã có tài khoản trên hệ thống. Vui lòng đăng nhập trước rồi đăng ký lại.',
    ],
    'error' => [
        'class' => 'tournament_register_page__notice--error',
        'text'  => 'Có lỗi xảy ra khi gửi đăng ký. Vui lòng thử lại hoặc liên hệ trực tiếp Ban tổ chức.',
    ],
];
?>

<main class="tournament_register_page" id="main-content">
    <section class="tournament_register_page__hero">
        <div class="container">
            <p class="tournament_register_page__eyebrow">Đăng ký giải đấu</p>
            <h1 class="tournament_register_page__title">Đăng ký đơn vị tham dự</h1>
            <p class="tournament_register_page__desc">
                Điền thông tin đơn vị để tạo tài khoản quản lý. Sau khi đăng ký,
                bạn đăng nhập và thêm danh sách vận động viên tại trang Quản lý đội.
            </p>
        </div>
    </section>

    <section class="tournament_register_page__main">
        <div class="container">
            <?php if (isset($status_messages[$dk_status])) : ?>
                <div class="tournament_register_page__notice <?php echo esc_attr($status_messages[$dk_status]['class']); ?>"
                    role="status" aria-live="polite">
                    <?php echo esc_html($status_messages[$dk_status]['text']); ?>
                </div>
            <?php endif; ?>

            <form class="tournament_register_page__form" method="post"
                action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="vs_tournament_register_submit">
                <?php wp_nonce_field('vs_tournament_register_submit', 'vs_tournament_register_nonce'); ?>

                <div class="tournament_register_page__card">
                    <h2>Thông tin đơn vị</h2>

                    <div class="tournament_register_page__grid">
                        <div class="tournament_register_page__field">
                            <label for="ten_don_vi">Tên đơn vị / đoàn <span aria-hidden="true">*</span></label>
                            <input type="text" id="ten_don_vi" name="ten_don_vi" required aria-required="true">
                        </div>

                        <div class="tournament_register_page__field">
                            <label for="nguoi_lien_he">Người liên hệ <span aria-hidden="true">*</span></label>
                            <input type="text" id="nguoi_lien_he" name="nguoi_lien_he" required aria-required="true">
                        </div>

                        <div class="tournament_register_page__field">
                            <label for="phone">Số điện thoại <span aria-hidden="true">*</span></label>
                            <input type="tel" id="phone" name="phone" inputmode="tel" required aria-required="true">
                        </div>

                        <div class="tournament_register_page__field">
                            <label for="email">Email <span aria-hidden="true">*</span></label>
                            <input type="email" id="email" name="email" autocomplete="email" required aria-required="true">
                            <span class="tournament_register_page__field_hint">
                                Dùng để tạo tài khoản quản lý đội — kiểm tra hộp thư sau khi gửi.
                            </span>
                        </div>

                        <div class="tournament_register_page__field tournament_register_page__field--full">
                            <label for="ghi_chu">Ghi chú</label>
                            <textarea id="ghi_chu" name="ghi_chu" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="tournament_register_page__form_actions">
                    <button type="submit" class="tournament_register_page__btn tournament_register_page__btn--primary">
                        Đăng ký đơn vị
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php
get_footer();
