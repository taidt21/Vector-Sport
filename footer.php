<?php
if (!defined('ABSPATH')) {
    exit;
}

$theme_uri = get_template_directory_uri();

$company_info = [
    'Địa chỉ' => [
        'icon'  => $theme_uri . '/assets/icon/location.svg',
        'value' => 'GH3, CT17, Nguyễn Cao Luyện, Việt Hưng, Hà Nội, Việt Nam',
        'link'  => 'https://maps.app.goo.gl/37Pm3RnHgGdLZ3hd9',
        'external' => true,
    ],
    'Số điện thoại' => [
        'icon'  => $theme_uri . '/assets/icon/call.svg',
        'value' => '0931729304',
        'link'  => 'tel:0931729304',
        'external' => false,
    ],
    'Email' => [
        'icon'  => $theme_uri . '/assets/icon/mail.svg',
        'value' => 'tungnhh.tutor@gmail.com',
        'link'  => 'mailto:tungnhh.tutor@gmail.com',
        'external' => false,
    ],
];

$policy_links = [
    [
        'title' => 'Chính sách 60 ngày đổi trả',
        'url'   => home_url('/chinh-sach-doi-tra/'),
    ],
    [
        'title' => 'Chính sách khách hàng thân thiết',
        'url'   => home_url('/chinh-sach-khach-hang-than-thiet/'),
    ],
    [
        'title' => 'Chính sách giao hàng',
        'url'   => home_url('/chinh-sach-giao-hang/'),
    ],
    [
        'title' => 'Chính sách bảo mật',
        'url'   => home_url('/chinh-sach-bao-mat/'),
    ],
];

$about_links = [
    [
        'title' => 'Giới thiệu về Vector Sport',
        'url'   => home_url('/gioi-thieu/'),
    ],
    [
        'title' => 'Lớp Vovinam',
        'url'   => home_url('/vovinam/'),
    ],
    [
        'title' => 'Hệ thống CLB',
        'url'   => home_url('/he-thong-cau-lac-bo/'),
    ],
    [
        'title' => 'Liên hệ / Đăng ký tư vấn',
        'url'   => home_url('/lien-he/'),
    ],
    [
        'title' => 'Sitemap',
        'url'   => home_url('/sitemap/'),
    ],
];

$social_icons = [
    'Facebook' => [
        'icon' => $theme_uri . '/assets/icon/socials/facebook.svg',
        'link' => 'https://www.facebook.com/profile.php?id=61571221390960&sk=about',
    ],
    'Instagram' => [
        'icon' => $theme_uri . '/assets/icon/socials/instagram.svg',
        'link' => 'https://www.instagram.com/vectorsport90/',
    ],
    'Zalo' => [
        'icon' => $theme_uri . '/assets/icon/socials/zalo.svg',
        'link' => 'https://zalo.me/0931729304',
    ],
    'TikTok' => [
        'icon' => $theme_uri . '/assets/icon/socials/tiktok.svg',
        'link' => 'https://www.tiktok.com/@tung.vector_sports',
    ],
];

$custom_logo_id = get_theme_mod('custom_logo');
$logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : $theme_uri . '/assets/icon/logo-main.svg';
$site_name = get_bloginfo('name');
?>

<footer class="footer" aria-labelledby="footer-title">
    <div class="container">
        <h2 id="footer-title" class="screen-reader-text">Thông tin chân trang Vector Sport</h2>

        <div class="row">
            <div class="col">
                <div class="logo-wrapper">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo"
                        aria-label="<?php echo esc_attr($site_name); ?>">
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>"
                            width="160" height="48" loading="lazy" decoding="async">
                    </a>

                    <a href="https://www.facebook.com/nguyenvananhtdtt" target="_blank" rel="noopener noreferrer"
                        aria-label="Bộ môn Vovinam Cụm 5 Hà Nội trên Facebook">
                        <img class="logo-vovinam"
                            src="<?php echo esc_url($theme_uri . '/assets/icon/logo-vovinam.svg'); ?>"
                            alt="Bộ môn Vovinam Cụm 5 Hà Nội" width="64" height="64" loading="lazy" decoding="async">
                    </a>
                </div>

                <ul class="footer-list">
                    <?php foreach ($company_info as $label => $item): ?>
                    <li class="list-item">
                        <?php if (!empty($item['icon'])): ?>
                        <img src="<?php echo esc_url($item['icon']); ?>" alt="" class="footer-icon" aria-hidden="true"
                            loading="lazy" decoding="async">
                        <?php endif; ?>

                        <?php if (!empty($item['link'])): ?>
                        <a href="<?php echo esc_url($item['link']); ?>"
                            <?php echo !empty($item['external']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            <span class="screen-reader-text"><?php echo esc_html($label); ?>:
                            </span><?php echo esc_html($item['value']); ?>
                        </a>
                        <?php else: ?>
                        <span>
                            <span class="screen-reader-text"><?php echo esc_html($label); ?>:
                            </span><?php echo esc_html($item['value']); ?>
                        </span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <nav class="col" aria-labelledby="footer-policy-title">
                <h3 id="footer-policy-title">Chính sách</h3>
                <ul class="footer-list">
                    <?php foreach ($policy_links as $link): ?>
                    <li class="list-item">
                        <a href="<?php echo esc_url($link['url']); ?>">
                            <?php echo esc_html($link['title']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <nav class="col" aria-labelledby="footer-about-title">
                <h3 id="footer-about-title">Về Vector Sport</h3>
                <ul class="footer-list">
                    <?php foreach ($about_links as $link): ?>
                    <li class="list-item">
                        <a href="<?php echo esc_url($link['url']); ?>">
                            <?php echo esc_html($link['title']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="col">
                <h3>Đăng ký nhận tin</h3>

                <form class="subscribe-form js-newsletter-form" method="post"
                    aria-label="Đăng ký nhận thông báo từ Vector Sport">
                    <input type="hidden" name="action" value="vs_newsletter_subscribe">
                    <?php wp_nonce_field('vs_newsletter_subscribe', 'vs_newsletter_nonce'); ?>

                    <label class="screen-reader-text" for="footer-subscribe-email">
                        Email của bạn
                    </label>

                    <input id="footer-subscribe-email" type="email" name="email" placeholder="Nhập email của bạn"
                        autocomplete="email" required>

                    <button type="submit" aria-label="Đăng ký nhận thông báo">
                        <img src="<?php echo esc_url($theme_uri . '/assets/icon/fly-send.svg'); ?>" alt=""
                            aria-hidden="true" loading="lazy" decoding="async">
                    </button>

                </form>
                <p class="subscribe-message js-newsletter-message" aria-live="polite"></p>
                <?php
                    $newsletter_status = isset($_GET['newsletter'])
                        ? sanitize_key(wp_unslash($_GET['newsletter']))
                        : '';

                    $newsletter_messages = [
                        'success' => 'Đăng ký nhận thông báo thành công.',
                        'exists' => 'Email này đã được đăng ký nhận thông báo.',
                        'invalid_email' => 'Email chưa đúng định dạng.',
                        'invalid' => 'Yêu cầu không hợp lệ. Vui lòng thử lại.',
                        'unsubscribed' => 'Bạn đã hủy đăng ký nhận thông báo.',
                    ];
                    ?>

                <?php if (isset($newsletter_messages[$newsletter_status])) : ?>
                <p class="subscribe-message">
                    <?php echo esc_html($newsletter_messages[$newsletter_status]); ?>
                </p>
                <?php endif; ?>

                <ul class="list-socials" aria-label="Kênh mạng xã hội của Vector Sport">
                    <?php foreach ($social_icons as $name => $item): ?>
                    <li class="list-socials-item">
                        <a href="<?php echo esc_url($item['link']); ?>" target="_blank" rel="noopener noreferrer"
                            aria-label="Theo dõi Vector Sport trên <?php echo esc_attr($name); ?>">
                            <img src="<?php echo esc_url($item['icon']); ?>" alt="" aria-hidden="true" loading="lazy"
                                decoding="async">
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <hr aria-hidden="true" />
            <div class="row-bottom">
                <p>©<?php echo esc_html(date_i18n('Y')); ?> Công ty TNHH Vector Sport. All rights reserved.</p>

                <a href="#" class="bo-cong-thuong-link" aria-label="Thông tin đăng ký Bộ Công Thương">
                    <img class="bo-cong-thuong-icon"
                        src="<?php echo esc_url($theme_uri . '/assets/icon/bo-cong-thuong.svg'); ?>"
                        alt="Đã đăng ký Bộ Công Thương" width="120" height="45" loading="lazy" decoding="async">
                </a>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>