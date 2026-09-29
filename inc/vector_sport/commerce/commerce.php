<?php
/**
 * Vector Sport Commerce Loader
 *
 * File này chỉ dùng để load các file con của chức năng commerce.
 * Không viết trực tiếp logic cart / checkout / order / payment tại đây.
 */

if (!defined('ABSPATH')) {
    exit;
}

$commerce_dir = __DIR__;

$commerce_files = [
    '/config.php',
    '/helpers.php',
    '/assets.php',
    '/cart.php',
    '/order-2-7.php',
    '/email.php',
    '/payment-2-7.php',
    '/checkout-cart.php',
];

foreach ($commerce_files as $file) {
    $path = $commerce_dir . $file;

    if (file_exists($path)) {
        require_once $path;
    } else {
        error_log('[VS_COMMERCE] Missing required file: ' . $path);
    }
}

if (is_admin()) {
    $admin_orders_path = $commerce_dir . '/admin-orders-2-7.php';

    if (file_exists($admin_orders_path)) {
        require_once $admin_orders_path;
    } else {
        error_log('[VS_COMMERCE] Missing admin orders file: ' . $admin_orders_path);
    }
}