<?php
if (!defined('ABSPATH')) {
    exit;
}

// ORDER
// =========================================================
function vs_commerce_order_table()
{
    global $wpdb;
    return $wpdb->prefix . 'vs_orders';
}

function vs_commerce_order_item_table()
{
    global $wpdb;
    return $wpdb->prefix . 'vs_order_items';
}

function vs_commerce_install_tables()
{
    global $wpdb;
    $version = get_option('vs_commerce_db_version');
    if ($version === '1.1.0') {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset_collate = $wpdb->get_charset_collate();

    $orders = vs_commerce_order_table();
    $items = vs_commerce_order_item_table();

    $sql1 = "CREATE TABLE {$orders} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_code VARCHAR(50) NOT NULL,
        cart_token VARCHAR(100) DEFAULT '' NOT NULL,
        status VARCHAR(30) DEFAULT 'pending' NOT NULL,
        payment_method VARCHAR(30) DEFAULT 'cod' NOT NULL,
        payment_status VARCHAR(30) DEFAULT 'pending' NOT NULL,
        customer_name VARCHAR(191) DEFAULT '' NOT NULL,
        customer_phone VARCHAR(50) DEFAULT '' NOT NULL,
        customer_email VARCHAR(191) DEFAULT '' NOT NULL,
        address_mode VARCHAR(20) DEFAULT 'new' NOT NULL,
        province_code VARCHAR(20) DEFAULT '' NOT NULL,
        district_code VARCHAR(20) DEFAULT '' NOT NULL,
        ward_code VARCHAR(20) DEFAULT '' NOT NULL,
        province VARCHAR(191) DEFAULT '' NOT NULL,
        district VARCHAR(191) DEFAULT '' NOT NULL,
        ward VARCHAR(191) DEFAULT '' NOT NULL,
        address_line VARCHAR(255) DEFAULT '' NOT NULL,
        note TEXT NULL,
        subtotal DECIMAL(15,2) DEFAULT 0 NOT NULL,
        shipping_fee DECIMAL(15,2) DEFAULT 0 NOT NULL,
        discount_amount DECIMAL(15,2) DEFAULT 0 NOT NULL,
        grand_total DECIMAL(15,2) DEFAULT 0 NOT NULL,
        payment_provider VARCHAR(30) DEFAULT '' NOT NULL,
        provider_ref VARCHAR(100) DEFAULT '' NOT NULL,
        provider_transaction_no VARCHAR(100) DEFAULT '' NOT NULL,
        provider_bank_code VARCHAR(50) DEFAULT '' NOT NULL,
        provider_response_code VARCHAR(20) DEFAULT '' NOT NULL,
        cart_snapshot LONGTEXT NULL,
        meta_json LONGTEXT NULL,
        paid_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY order_code (order_code),
        KEY provider_ref (provider_ref)
    ) {$charset_collate};";

    $sql2 = "CREATE TABLE {$items} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
        product_title VARCHAR(255) DEFAULT '' NOT NULL,
        product_link VARCHAR(255) DEFAULT '' NOT NULL,
        image VARCHAR(255) DEFAULT '' NOT NULL,
        color VARCHAR(100) DEFAULT '' NOT NULL,
        size VARCHAR(100) DEFAULT '' NOT NULL,
        qty INT UNSIGNED DEFAULT 1 NOT NULL,
        unit_price DECIMAL(15,2) DEFAULT 0 NOT NULL,
        line_total DECIMAL(15,2) DEFAULT 0 NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY order_id (order_id)
    ) {$charset_collate};";

    dbDelta($sql1);
    dbDelta($sql2);
    update_option('vs_commerce_db_version', '1.1.0');
}
add_action('init', 'vs_commerce_install_tables');
add_action('after_switch_theme', 'vs_commerce_install_tables');

function vs_commerce_generate_order_code()
{
    global $wpdb;
    $table = vs_commerce_order_table();
    $date_prefix = current_time('Ymd');
    $like = 'VS' . $date_prefix . '%';
    $last_code = $wpdb->get_var($wpdb->prepare("SELECT order_code FROM {$table} WHERE order_code LIKE %s ORDER BY id DESC LIMIT 1", $like));
    $sequence = 1;
    if ($last_code && preg_match('/(\d{4})$/', $last_code, $m)) {
        $sequence = ((int) $m[1]) + 1;
    }
    return sprintf('VS%s%04d', $date_prefix, $sequence);
}

function vs_commerce_order_get($order_id)
{
    global $wpdb;
    $order = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . vs_commerce_order_table() . ' WHERE id = %d LIMIT 1', $order_id), ARRAY_A);
    if (!$order) {
        return null;
    }

    $order['items'] = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . vs_commerce_order_item_table() . ' WHERE order_id = %d ORDER BY id ASC', $order_id), ARRAY_A) ?: [];
    return $order;
}

function vs_commerce_order_get_by_code($order_code)
{
    global $wpdb;
    $order_id = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . vs_commerce_order_table() . ' WHERE order_code = %s LIMIT 1', $order_code));
    return $order_id ? vs_commerce_order_get((int) $order_id) : null;
}

function vs_commerce_order_get_by_provider_ref($provider_ref)
{
    global $wpdb;
    $order_id = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . vs_commerce_order_table() . ' WHERE provider_ref = %s LIMIT 1', $provider_ref));
    return $order_id ? vs_commerce_order_get((int) $order_id) : null;
}

function vs_commerce_order_update($order_id, array $data)
{
    global $wpdb;
    $data['updated_at'] = current_time('mysql');
    return $wpdb->update(vs_commerce_order_table(), $data, ['id' => absint($order_id)]) !== false;
}

function vs_commerce_order_create(array $data)
{
    global $wpdb;

    $cart_items = vs_commerce_get_cart_items();
    if (empty($cart_items)) {
        return new WP_Error('empty_cart', 'Giỏ hàng đang trống.');
    }

    $totals = vs_commerce_get_cart_totals($cart_items);
    $subtotal = (float) ($totals['subtotal'] ?? 0);
    $shipping_fee = (float) ($data['shipping_fee'] ?? 0);
    $discount = (float) ($data['discount_amount'] ?? 0);
    $grand_total = max(0, $subtotal + $shipping_fee - $discount);
    $now = current_time('mysql');

    $insert = [
        'order_code' => vs_commerce_generate_order_code(),
        'cart_token' => vs_commerce_get_cart_token(),
        'status' => 'pending',
        'payment_method' => sanitize_key($data['payment_method'] ?? 'cod'),
        'payment_status' => 'pending',
        'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
        'customer_phone' => sanitize_text_field($data['customer_phone'] ?? ''),
        'customer_email' => sanitize_email($data['customer_email'] ?? ''),
        'address_mode' => in_array(($data['address_mode'] ?? 'new'), ['new', 'old'], true) ? $data['address_mode'] : 'new',
        'province_code' => sanitize_text_field($data['province_code'] ?? ''),
        'district_code' => sanitize_text_field($data['district_code'] ?? ''),
        'ward_code' => sanitize_text_field($data['ward_code'] ?? ''),
        'province' => sanitize_text_field($data['province'] ?? ''),
        'district' => sanitize_text_field($data['district'] ?? ''),
        'ward' => sanitize_text_field($data['ward'] ?? ''),
        'address_line' => sanitize_text_field($data['address_line'] ?? ''),
        'note' => sanitize_textarea_field($data['note'] ?? ''),
        'subtotal' => $subtotal,
        'shipping_fee' => $shipping_fee,
        'discount_amount' => $discount,
        'grand_total' => $grand_total,
        'payment_provider' => sanitize_key($data['payment_provider'] ?? ''),
        'provider_bank_code' => sanitize_text_field($data['payment_bank_code'] ?? ''),
        'cart_snapshot' => wp_json_encode($cart_items),
        'meta_json' => wp_json_encode([
            'shipping_method' => sanitize_text_field($data['shipping_method'] ?? ''),
            'coupon_code' => sanitize_text_field($data['coupon_code'] ?? ''),
            'address_mode_label' => vs_commerce_address_mode_label($data['address_mode'] ?? 'new'),
            'full_address_text' => trim(implode(', ', array_filter([
                sanitize_text_field($data['address_line'] ?? ''),
                sanitize_text_field($data['ward'] ?? ''),
                sanitize_text_field($data['district'] ?? ''),
                sanitize_text_field($data['province'] ?? ''),
            ]))),
        ]),
        'created_at' => $now,
        'updated_at' => $now,
    ];

    $done = $wpdb->insert(vs_commerce_order_table(), $insert);
    if (!$done) {
        return new WP_Error('db_insert_failed', 'Không thể tạo đơn hàng.');
    }

    $order_id = (int) $wpdb->insert_id;
    foreach ($cart_items as $item) {
        $qty = max(1, absint($item['qty'] ?? 1));
        $price = (float) ($item['price'] ?? 0);
        $wpdb->insert(vs_commerce_order_item_table(), [
            'order_id' => $order_id,
            'product_id' => absint($item['product_id'] ?? 0),
            'product_title' => (string) ($item['title'] ?? ''),
            'product_link' => (string) ($item['link'] ?? ''),
            'image' => (string) ($item['image'] ?? ''),
            'color' => (string) ($item['color'] ?? ''),
            'size' => (string) ($item['size'] ?? ''),
            'qty' => $qty,
            'unit_price' => $price,
            'line_total' => $qty * $price,
            'created_at' => $now,
        ]);
    }

$order = vs_commerce_order_get($order_id);

return $order;
}

function vs_commerce_order_mark_pending_payment($order_id, $provider_ref, $bank_code = '')
{
    return vs_commerce_order_update($order_id, [
        'status' => 'awaiting_payment',
        'payment_status' => 'pending',
        'payment_provider' => 'vnpay',
        'provider_ref' => sanitize_text_field($provider_ref),
        'provider_bank_code' => sanitize_text_field($bank_code),
    ]);
}

function vs_commerce_order_mark_paid($order_id, array $payment_data = [])
{
    $order_before = vs_commerce_order_get($order_id);
    $was_paid = $order_before && ($order_before['payment_status'] ?? '') === 'paid';

    $result = vs_commerce_order_update($order_id, [
        'status' => 'paid',
        'payment_status' => 'paid',
        'provider_transaction_no' => sanitize_text_field($payment_data['transaction_no'] ?? ''),
        'provider_bank_code' => sanitize_text_field($payment_data['bank_code'] ?? ''),
        'provider_response_code' => sanitize_text_field($payment_data['response_code'] ?? '00'),
        'paid_at' => current_time('mysql'),
    ]);

    $order = vs_commerce_order_get($order_id);

    if ($order && !empty($order['cart_token'])) {
        vs_commerce_clear_cart($order['cart_token']);
    }

    if (!$was_paid && $order) {
        do_action('vs_commerce_order_paid', $order_id, $order, $payment_data);
    }

    return $result;
}

function vs_commerce_order_mark_failed($order_id, array $payment_data = [])
{
    return vs_commerce_order_update($order_id, [
        'status' => 'payment_failed',
        'payment_status' => 'failed',
        'provider_transaction_no' => sanitize_text_field($payment_data['transaction_no'] ?? ''),
        'provider_bank_code' => sanitize_text_field($payment_data['bank_code'] ?? ''),
        'provider_response_code' => sanitize_text_field($payment_data['response_code'] ?? ''),
    ]);
}