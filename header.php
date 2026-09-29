<?php
if (!defined('ABSPATH')) {
    exit;
}

$logo_url = '';
$custom_logo_id = get_theme_mod('custom_logo');

if ($custom_logo_id) {
    $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
} else {
    $logo_url = get_template_directory_uri() . '/assets/icon/logo-main.svg';
}

$site_icon_url = function_exists('get_site_icon_url') ? get_site_icon_url(512) : '';
$favicon_url = $site_icon_url ?: $logo_url;

$logo_width  = 160;
$logo_height = 48;

$menu_left = [
    [
        'title' => 'Trang chủ',
        'url'   => home_url('/'),
    ],
    [
        'title' => 'Tuyển sinh',
        'url'   => home_url('/vovinam/'),
        'children' => [
            ['title' => 'Vovinam', 'url' => home_url('/vovinam/')],
            ['title' => 'Kèm riêng', 'url' => home_url('/kem-rieng/')],
            ['title' => 'Hệ thống CLB', 'url' => home_url('/he-thong-cau-lac-bo/')],
        ],
    ],
    [
        'title' => 'Phụ kiện / Đồ tập',
        'url'   => home_url('/product/'),
    ],
];

$menu_right = [
    [
        'title' => 'Hoạt động',
        'url'   => home_url('/hoat-dong/'),
    ],
    [
        'title' => 'Chương trình Trải nghiệm',
        'url'   => home_url('/chuong-trinh-trai-nghiem/'),
    ],
    [
        'title' => 'Liên hệ / Đăng ký',
        'url'   => home_url('/lien-he/'),
    ],
];

$desktop_menu = array_merge($menu_left, $menu_right);
$mobile_menu  = $desktop_menu;

$account_url = home_url('/account/');
if (function_exists('wc_get_page_permalink')) {
    $wc_account_url = wc_get_page_permalink('myaccount');
    if (!empty($wc_account_url) && $wc_account_url !== home_url('/my-account/')) {
        $account_url = $wc_account_url;
    }
}

$cart_count = 0;

if (function_exists('vs_cart_get_totals')) {
    $cart_totals = vs_cart_get_totals();
    $cart_count  = (int) ($cart_totals['count'] ?? 0);
}

$cart_aria_label = sprintf('Giỏ hàng, hiện có %d sản phẩm', $cart_count);

$current_url = home_url('/');
if (is_front_page() || is_home()) {
    $current_url = home_url('/');
} else {
    global $wp;
    $request_path = isset($wp->request) ? $wp->request : '';
    $current_url = home_url($request_path ? '/' . ltrim($request_path, '/') . '/' : '/');
}

$normalize_url = function ($url) {
    $url = strtok((string) $url, '?');
    $url = strtok((string) $url, '#');
    return trailingslashit(untrailingslashit($url));
};

$is_active_url = function ($item_url) use ($current_url, $normalize_url) {
    $current = $normalize_url($current_url);
    $target  = $normalize_url($item_url);
    $home    = trailingslashit(home_url('/'));

    if ($current === $target) {
        return true;
    }

    if ($target !== $home && strpos($current, $target) === 0) {
        return true;
    }

    return false;
};

$render_desktop_menu = function ($items, $is_active_url) {
    foreach ($items as $item) {
        $has_child = !empty($item['children']) && is_array($item['children']);
        $active = $is_active_url($item['url']);

        if (!$active && $has_child) {
            foreach ($item['children'] as $child) {
                if ($is_active_url($child['url'])) {
                    $active = true;
                    break;
                }
            }
        }

        echo '<li class="site-header__nav-item' . ($has_child ? ' has-child' : '') . ($active ? ' is-active' : '') . '">';
        echo '<a href="' . esc_url($item['url']) . '" class="site-header__nav-link' . ($active ? ' is-active' : '') . '"' . ($active ? ' aria-current="page"' : '') . ($has_child ? ' aria-haspopup="true"' : '') . '>';
        echo '<span>' . esc_html($item['title']) . '</span>';
        echo '</a>';

        if ($has_child) {
            echo '<ul class="site-header__sub-menu">';
            foreach ($item['children'] as $child) {
                $child_active = $is_active_url($child['url']);
                echo '<li class="site-header__sub-item' . ($child_active ? ' is-active' : '') . '">';
                echo '<a href="' . esc_url($child['url']) . '" class="site-header__sub-link' . ($child_active ? ' is-active' : '') . '"' . ($child_active ? ' aria-current="page"' : '') . '>' . esc_html($child['title']) . '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }

        echo '</li>';
    }
};

$render_mobile_menu = function ($items, $is_active_url) {
    foreach ($items as $item) {
        $has_child = !empty($item['children']) && is_array($item['children']);
        $active = $is_active_url($item['url']);

        if (!$active && $has_child) {
            foreach ($item['children'] as $child) {
                if ($is_active_url($child['url'])) {
                    $active = true;
                    break;
                }
            }
        }

        if ($has_child) {
            echo '<li class="mobile-menu__item has-child' . ($active ? ' is-active' : '') . '">';
            echo '<details' . ($active ? ' open' : '') . '>';
            echo '<summary><span>' . esc_html($item['title']) . '</span></summary>';
            echo '<ul class="mobile-sub-menu">';
            echo '<li class="mobile-sub-menu__first">';
            echo '<a href="' . esc_url($item['url']) . '" class="mobile-sub-link mobile-sub-link--parent' . ($active ? ' is-active' : '') . '"' . ($active ? ' aria-current="page"' : '') . '>Xem tất cả</a>';
            echo '</li>';

            foreach ($item['children'] as $child) {
                $child_active = $is_active_url($child['url']);
                echo '<li class="mobile-sub-item' . ($child_active ? ' is-active' : '') . '">';
                echo '<a href="' . esc_url($child['url']) . '" class="mobile-sub-link' . ($child_active ? ' is-active' : '') . '"' . ($child_active ? ' aria-current="page"' : '') . '>' . esc_html($child['title']) . '</a>';
                echo '</li>';
            }

            echo '</ul>';
            echo '</details>';
            echo '</li>';
        } else {
            echo '<li class="mobile-menu__item' . ($active ? ' is-active' : '') . '">';
            echo '<a href="' . esc_url($item['url']) . '" class="mobile-menu__link' . ($active ? ' is-active' : '') . '"' . ($active ? ' aria-current="page"' : '') . '>' . esc_html($item['title']) . '</a>';
            echo '</li>';
        }
    }
};
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php if (!empty($favicon_url)) : ?>
        <link rel="icon" href="<?php echo esc_url($favicon_url); ?>">
        <link rel="apple-touch-icon" href="<?php echo esc_url($favicon_url); ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <a class="skip-link screen-reader-text" href="#main-content">
        Bỏ qua nội dung đầu trang
    </a>

    <header class="header site-header js-site-header">
        <div class="site-header__promo js-header-promo" id="siteHeaderPromo">
            <div class="container site-header__promo-inner">
                <p class="site-header__promo-text">FREESHIP CHO ĐƠN 200K - ĐỔI TRẢ MIỄN PHÍ 60 NGÀY</p>
                <button type="button" class="site-header__promo-close js-header-promo-close"
                    aria-label="Đóng thông báo">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                        <path d="M6 6L18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        <path d="M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="site-header__main">
            <div class="container">
                <div class="site-header__mobile">
                    <button type="button" class="site-header__menu-trigger js-mobile-menu-trigger" aria-label="Mở menu"
                        aria-controls="mobileDrawer" aria-expanded="false">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo site-header__logo--mobile"
                        aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                        <img
                            src="<?php echo esc_url($logo_url); ?>"
                            alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                            width="<?php echo esc_attr($logo_width); ?>"
                            height="<?php echo esc_attr($logo_height); ?>">
                    </a>

                    <div class="site-header__actions">
                        <button type="button" class="site-header__icon-btn js-search-toggle" aria-label="Mở tìm kiếm"
                            aria-expanded="false" aria-controls="siteHeaderSearch">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <circle cx="11" cy="11" r="6.75" stroke="currentColor" stroke-width="1.8" />
                                <path d="M16.2 16.2L20 20" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" />
                            </svg>
                        </button>

                        <a href="<?php echo esc_url($account_url); ?>" class="site-header__icon-btn"
                            aria-label="Tài khoản">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <circle cx="12" cy="8" r="3.8" stroke="currentColor" stroke-width="1.8" />
                                <path d="M4.5 20C5.6 16.8 8.5 15.2 12 15.2C15.5 15.2 18.4 16.8 19.5 20"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </a>

                        <button type="button" class="site-header__icon-btn site-header__icon-btn--cart js-vs-cart-open"
                            aria-label="<?php echo esc_attr($cart_aria_label); ?>">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path
                                    d="M3.5 5H5.2L7 13.1C7.25 14.2 8.2 15 9.3 15H17C18.1 15 19.05 14.25 19.3 13.15L20.55 8.2H6.4"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <circle cx="9.4" cy="19" r="1.3" fill="currentColor" />
                                <circle cx="17.1" cy="19" r="1.3" fill="currentColor" />
                            </svg>
                            <span
                                class="site-header__cart-count<?php echo $cart_count > 0 ? ' has-items' : ''; ?> js-cart-count"><?php echo esc_html($cart_count); ?></span>
                        </button>
                    </div>
                </div>

                <div class="site-header__desktop">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo site-header__logo--desktop"
                        aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                        <img
                            src="<?php echo esc_url($logo_url); ?>"
                            alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                            width="<?php echo esc_attr($logo_width); ?>"
                            height="<?php echo esc_attr($logo_height); ?>">
                    </a>

                    <nav class="site-header__nav" aria-label="Điều hướng chính">
                        <ul class="site-header__nav-list">
                            <?php $render_desktop_menu($desktop_menu, $is_active_url); ?>
                        </ul>
                    </nav>

                    <div class="site-header__actions site-header__actions--desktop">
                        <button type="button" class="site-header__icon-btn js-search-toggle" aria-label="Mở tìm kiếm"
                            aria-expanded="false" aria-controls="siteHeaderSearch">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <circle cx="11" cy="11" r="6.75" stroke="currentColor" stroke-width="1.8" />
                                <path d="M16.2 16.2L20 20" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" />
                            </svg>
                        </button>

                        <a href="<?php echo esc_url($account_url); ?>" class="site-header__icon-btn"
                            aria-label="Tài khoản">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <circle cx="12" cy="8" r="3.8" stroke="currentColor" stroke-width="1.8" />
                                <path d="M4.5 20C5.6 16.8 8.5 15.2 12 15.2C15.5 15.2 18.4 16.8 19.5 20"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </a>

                        <button type="button" class="site-header__icon-btn site-header__icon-btn--cart js-vs-cart-open"
                            aria-label="<?php echo esc_attr($cart_aria_label); ?>">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path
                                    d="M3.5 5H5.2L7 13.1C7.25 14.2 8.2 15 9.3 15H17C18.1 15 19.05 14.25 19.3 13.15L20.55 8.2H6.4"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <circle cx="9.4" cy="19" r="1.3" fill="currentColor" />
                                <circle cx="17.1" cy="19" r="1.3" fill="currentColor" />
                            </svg>
                            <span
                                class="site-header__cart-count<?php echo $cart_count > 0 ? ' has-items' : ''; ?> js-cart-count"><?php echo esc_html($cart_count); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-header__search-panel js-header-search" id="siteHeaderSearch" aria-hidden="true">
            <div class="container">
                <form class="site-header__search-form" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
                    <label class="screen-reader-text" for="headerSearchInput">Tìm kiếm</label>
                    <input id="headerSearchInput" type="search" name="s"
                        value="<?php echo esc_attr(get_search_query()); ?>"
                        placeholder="Tìm sản phẩm, bài viết, lớp học...">
                    <button type="submit">Tìm kiếm</button>
                </form>
            </div>
        </div>

        <div class="mobile-drawer" id="mobileDrawer" aria-hidden="true" role="dialog" aria-label="Menu di động">
            <button type="button" class="mobile-drawer__overlay js-mobile-close" aria-label="Đóng menu"></button>

            <div class="mobile-drawer__panel">
                <div class="mobile-drawer__header">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="mobile-drawer__logo"
                        aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                        <img
                            src="<?php echo esc_url($logo_url); ?>"
                            alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                            width="<?php echo esc_attr($logo_width); ?>"
                            height="<?php echo esc_attr($logo_height); ?>">
                    </a>

                    <button type="button" class="mobile-drawer__close js-mobile-close" aria-label="Đóng menu">
                        <span></span>
                        <span></span>
                    </button>
                </div>

                <div class="mobile-drawer__body">
                    <form class="mobile-drawer__search" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
                        <label class="screen-reader-text" for="mobileDrawerSearchInput">Tìm kiếm</label>
                        <input id="mobileDrawerSearchInput" type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>"
                            placeholder="Tìm kiếm">
                        <button type="submit" aria-label="Tìm kiếm">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <circle cx="11" cy="11" r="6.75" stroke="currentColor" stroke-width="1.8" />
                                <path d="M16.2 16.2L20 20" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" />
                            </svg>
                        </button>
                    </form>

                    <ul class="mobile-menu">
                        <?php $render_mobile_menu($mobile_menu, $is_active_url); ?>
                    </ul>

                    <div class="mobile-drawer__cta">
                        <a href="<?php echo esc_url($account_url); ?>"
                            class="mobile-drawer__btn mobile-drawer__btn--ghost">Tài khoản</a>
                        <button type="button" class="mobile-drawer__btn mobile-drawer__btn--dark js-vs-cart-open"
                            aria-label="<?php echo esc_attr($cart_aria_label); ?>">
                            Giỏ hàng (<span class="js-cart-count"><?php echo esc_html($cart_count); ?></span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
