<?php
if (!defined('ABSPATH')) {
    exit;
}

// ASSETS + DRAWER
// =========================================================
function vs_commerce_enqueue_assets()
{
    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();
    $js_rel = '/assets/js/shop.js';
    $js_abs = $theme_dir . $js_rel;

    if (!file_exists($js_abs)) {
        return;
    }

    wp_enqueue_script(
        'vs-shop',
        $theme_uri . $js_rel,
        ['jquery'],
        file_exists($js_abs) ? filemtime($js_abs) : wp_get_theme()->get('Version'),
        true
    );

    wp_localize_script('vs-shop', 'vsShop', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vs_commerce_nonce'),
        'checkoutUrl' => home_url('/checkout/'),
        'addressApi' => [
            'newBase' => 'https://provinces.open-api.vn/api/v2/',
            'oldBase' => 'https://provinces.open-api.vn/api/v1/',
        ],
    ]);
}
add_action('wp_enqueue_scripts', 'vs_commerce_enqueue_assets');

function vs_commerce_render_drawer_shell()
{
    echo '<div class="vs-cart-backdrop js-vs-cart-backdrop"></div>';
    echo '<aside class="vs-cart-drawer js-vs-cart-drawer" aria-hidden="true"><div class="vs-cart-drawer__inner js-vs-cart-content">' . vs_commerce_get_drawer_html() . '</div></aside>';
}
add_action('wp_footer', 'vs_commerce_render_drawer_shell');
