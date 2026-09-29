<?php
function allow_svg_upload($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');
/**
 * Enqueue scripts and styles.
 */
function vietvodao_scripts() {
    $theme_version = wp_get_theme()->get('Version');

    $css_path = get_template_directory() . '/assets/css/main.css';
    $css_ver  = file_exists($css_path) ? filemtime($css_path) : $theme_version;

    wp_enqueue_style(
        'vietvodao-main',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        $css_ver
    );

    $js_path = get_template_directory() . '/assets/js/main.js';
    $js_ver  = file_exists($js_path) ? filemtime($js_path) : $theme_version;

    wp_enqueue_script(
        'vietvodao-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array('jquery'),
        $js_ver,
        true
    );

    wp_localize_script('vietvodao-main', 'vsNewsletter', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ]);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

function vovo_enqueue_home_swiper()
{
    $is_homepage = is_front_page() || is_home() || is_page_template('page-home.php');

    if (!$is_homepage) return;

    wp_enqueue_style(
        'swiper',
        'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css',
        [],
        '12'
    );

    wp_enqueue_script(
        'swiper',
        'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js',
        [],
        '12',
        true
    );

    wp_enqueue_script(
        'home-hero',
        get_template_directory_uri() . '/assets/js/home.js',
        array('swiper', 'jquery'),
        '1.0.0',
        true
    );

    if (function_exists('wp_script_add_data')) {
        wp_script_add_data('swiper', 'strategy', 'defer');
        wp_script_add_data('home-hero', 'strategy', 'defer');
    }
}
add_action('wp_enqueue_scripts', 'vovo_enqueue_home_swiper');

function vovo_enqueue_product_archive_assets()
{
    $post_type = 'product';
    $category_taxonomy = function_exists('vs_pa_get_category_taxonomy')
        ? vs_pa_get_category_taxonomy($post_type)
        : '';

    if (is_post_type_archive($post_type) || ($category_taxonomy && is_tax($category_taxonomy))) {
        $js_path = get_template_directory() . '/assets/js/product-archive.js';
        $js_ver  = file_exists($js_path) ? filemtime($js_path) : wp_get_theme()->get('Version');

        wp_enqueue_script(
            'product-archive',
            get_template_directory_uri() . '/assets/js/product-archive.js',
            array('jquery'),
            $js_ver,
            true
        );
    }
}
function vovo_enqueue_product_single_assets()
{
    if (is_singular('product')) {
        $js_path = get_template_directory() . '/assets/js/product-single.js';
        $js_ver  = file_exists($js_path) ? filemtime($js_path) : wp_get_theme()->get('Version');

        wp_enqueue_style(
            'swiper-bundle',
            'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css',
            [],
            '12.1.3'
        );

        wp_enqueue_script(
            'swiper-bundle',
            'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js',
            [],
            '12.1.3',
            true
        );

        wp_enqueue_script(
            'vovo-product-single',
            get_template_directory_uri() . '/assets/js/product-single.js',
            ['jquery', 'swiper-bundle'],
            $js_ver,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'vovo_enqueue_product_single_assets');


function vovo_enqueue_team_portal_assets()
{
    if (!is_page_template('page-quan-ly-doi.php')) return;

    $js_path = get_template_directory() . '/assets/js/tournament-register.js';
    $js_ver  = file_exists($js_path) ? filemtime($js_path) : wp_get_theme()->get('Version');

    wp_enqueue_script(
        'vs-tournament-register',
        get_template_directory_uri() . '/assets/js/tournament-register.js',
        ['jquery'],
        $js_ver,
        true
    );

    // ajaxurl có sẵn tự động trong wp-admin, nhưng KHÔNG tự có ở
    // front-end (trang Quản lý đội) — phải truyền tay qua đây.
    wp_localize_script('vs-tournament-register', 'vsTeamPortal', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'vovo_enqueue_team_portal_assets');

add_action('wp_enqueue_scripts', 'vovo_enqueue_product_archive_assets');
add_action( 'wp_enqueue_scripts', 'vietvodao_scripts' );

/**
 * Allow SVG upload
 */
function vs_allow_svg_upload($mimes)
{
    $mimes['svg'] = 'image/svg+xml';

    return $mimes;
}

add_filter(
    'upload_mimes',
    'vs_allow_svg_upload'
);

require_once get_template_directory() . '/inc/vector_sport/seo.php';
require_once get_template_directory() . '/inc/vector_sport/sitemap.php';
require_once get_template_directory() . '/inc/vector_sport/smtp.php';
require_once get_template_directory() . '/inc/vector_sport/newsletter.php';

// 2 dòng dưới đây BỊ THIẾU từ trước (có sẵn trong theme gốc, không phải
// do session này thêm mới) — khiến shortcode [vectorsport_auth] (trang
// đăng nhập/đăng ký, dùng ở trang Quản lý đội) và [vectorsport_account]
// (trang tài khoản khách mua hàng) KHÔNG BAO GIỜ được đăng ký, hiện ra
// dạng chữ thô y hệt gõ tay thay vì render thành form thật.
require_once get_template_directory() . '/inc/vector_sport/vectorsport-auth-shortcode.php';
require_once get_template_directory() . '/inc/vector_sport/vectorsport-account.php';

require_once get_template_directory() . '/inc/vector_sport/product/product-archive.php';
require_once get_template_directory() . '/inc/vector_sport/contact/contact-register.php';
require_once get_template_directory() . '/inc/vector_sport/tournament-register.php';
require_once get_template_directory() . '/inc/vector_sport/commerce/commerce.php';