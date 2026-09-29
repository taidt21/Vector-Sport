<?php
if (!defined('ABSPATH')) {
    exit;
}

// CHECKOUT + VNPAY
// =========================================================
function vs_commerce_get_vnpay_config()
{
    $config = [
        'tmn_code' => VS_VNPAY_TMN_CODE,
        'hash_secret' => VS_VNPAY_HASH_SECRET,
        'payment_url' => VS_VNPAY_PAYMENT_URL,
        'return_url' => home_url('/wp-admin/admin-post.php?action=vs_vnpay_return'),
        'ipn_url' => home_url('/wp-admin/admin-post.php?action=vs_vnpay_ipn'),
    ];

    return apply_filters('vs_vnpay_config', $config);
}

function vs_commerce_vnpay_is_ready()
{
    $c = vs_commerce_get_vnpay_config();
    return !empty($c['tmn_code']) && !empty($c['hash_secret']) && !empty($c['payment_url']) && !empty($c['return_url']);
}

function vs_commerce_build_vnpay_payment_url(array $order, $bank_code = '')
{
    $config = vs_commerce_get_vnpay_config();

    if (empty($config['tmn_code']) || empty($config['hash_secret'])) {
        return new WP_Error('vnpay_not_ready', 'VNPAY chưa được cấu hình TMN Code / Hash Secret.');
    }

    $txn_ref = (string) ($order['order_code'] ?? '');
    $amount = vs_commerce_get_vnpay_order_amount($order);
    if ($txn_ref === '' || $amount <= 0) {
        return new WP_Error('vnpay_invalid_order', 'Dữ liệu đơn hàng không hợp lệ để tạo thanh toán VNPAY.');
    }

    $order_info = vs_commerce_unsign('Thanh toan don hang ' . $txn_ref);
    $create_date = current_time('YmdHis');
    $expire_date = date('YmdHis', strtotime('+15 minutes', current_time('timestamp')));

    $inputData = [
        'vnp_Version' => '2.1.0',
        'vnp_Command' => 'pay',
        'vnp_TmnCode' => $config['tmn_code'],
        'vnp_Amount' => $amount,
        'vnp_CreateDate' => $create_date,
        'vnp_CurrCode' => 'VND',
        'vnp_IpAddr' => sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')),
        'vnp_Locale' => 'vn',
        'vnp_OrderInfo' => $order_info,
        'vnp_OrderType' => 'other',
        'vnp_ReturnUrl' => $config['return_url'],
        'vnp_TxnRef' => $txn_ref,
        'vnp_ExpireDate' => $expire_date,
    ];

    $bank_code = strtoupper(trim((string) $bank_code));
    if (in_array($bank_code, ['VNPAYQR', 'VNBANK', 'INTCARD'], true)) {
        $inputData['vnp_BankCode'] = $bank_code;
    }

    ksort($inputData);
    $hashData = [];
    $query = [];
    foreach ($inputData as $key => $value) {
        $hashData[] = urlencode($key) . '=' . urlencode($value);
        $query[] = urlencode($key) . '=' . urlencode($value);
    }

    $hash_string = implode('&', $hashData);
    $secure_hash = hash_hmac('sha512', $hash_string, $config['hash_secret']);
    $payment_url = $config['payment_url'] . '?' . implode('&', $query) . '&vnp_SecureHash=' . $secure_hash;

    return [
        'payment_url' => $payment_url,
        'txn_ref' => $txn_ref,
    ];
}

function vs_commerce_validate_checkout_data(array $data)
{
    if (empty($data['customer_name'])) {
        return new WP_Error('missing_name', 'Vui lòng nhập họ và tên.');
    }
    if (empty($data['customer_phone'])) {
        return new WP_Error('missing_phone', 'Vui lòng nhập số điện thoại.');
    }
    if (!vs_commerce_is_valid_phone($data['customer_phone'])) {
        return new WP_Error('invalid_phone', 'Số điện thoại không hợp lệ.');
    }
    if (!empty($data['customer_email']) && !is_email($data['customer_email'])) {
        return new WP_Error('invalid_email', 'Email không hợp lệ.');
    }
    if (empty($data['address_line'])) {
        return new WP_Error('missing_address', 'Vui lòng nhập địa chỉ cụ thể.');
    }

    $address_mode = in_array(($data['address_mode'] ?? ''), ['new', 'old'], true) ? $data['address_mode'] : '';
    if ($address_mode === '') {
        return new WP_Error('invalid_address_mode', 'Vui lòng chọn loại địa chỉ.');
    }

    if (!in_array($data['payment_method'], ['cod', 'vnpay'], true)) {
        return new WP_Error('invalid_payment_method', 'Phương thức thanh toán không hợp lệ.');
    }

    if ($address_mode === 'old') {
        if (empty($data['province']) || empty($data['district']) || empty($data['ward'])) {
            return new WP_Error('missing_location', 'Vui lòng chọn đầy đủ Tỉnh/Thành, Quận/Huyện, Phường/Xã.');
        }
        if (empty($data['province_code']) || empty($data['district_code']) || empty($data['ward_code'])) {
            return new WP_Error('missing_location_code', 'Thông tin địa chỉ cũ chưa hợp lệ. Vui lòng chọn lại từ danh sách.');
        }
    } else {
        if (empty($data['province']) || empty($data['ward'])) {
            return new WP_Error('missing_location', 'Vui lòng chọn đầy đủ Tỉnh/Thành và Phường/Xã/Đặc khu.');
        }
        if (empty($data['province_code']) || empty($data['ward_code'])) {
            return new WP_Error('missing_location_code', 'Thông tin địa chỉ mới chưa hợp lệ. Vui lòng chọn lại từ danh sách.');
        }
    }

    return true;
}

function vs_commerce_checkout_success_url($order_code, $payment_status)
{
    return add_query_arg([
        'vs_order' => $order_code,
        'vs_payment' => $payment_status,
    ], home_url('/checkout/'));
}

function vs_commerce_handle_place_order()
{
    check_ajax_referer('vs_commerce_nonce', 'nonce');

    $data = [
        'customer_name' => sanitize_text_field(wp_unslash($_POST['customer_name'] ?? '')),
        'customer_phone' => sanitize_text_field(wp_unslash($_POST['customer_phone'] ?? '')),
        'customer_email' => sanitize_email(wp_unslash($_POST['customer_email'] ?? '')),
        'address_mode' => sanitize_key(wp_unslash($_POST['address_mode'] ?? 'new')),
        'province_code' => sanitize_text_field(wp_unslash($_POST['province_code'] ?? '')),
        'district_code' => sanitize_text_field(wp_unslash($_POST['district_code'] ?? '')),
        'ward_code' => sanitize_text_field(wp_unslash($_POST['ward_code'] ?? '')),
        'province' => sanitize_text_field(wp_unslash($_POST['province'] ?? '')),
        'district' => sanitize_text_field(wp_unslash($_POST['district'] ?? '')),
        'ward' => sanitize_text_field(wp_unslash($_POST['ward'] ?? '')), 
        'address_line' => sanitize_text_field(wp_unslash($_POST['address_line'] ?? '')),
        'note' => sanitize_textarea_field(wp_unslash($_POST['note'] ?? '')),
        'shipping_method' => sanitize_text_field(wp_unslash($_POST['shipping_method'] ?? 'standard')),
        'coupon_code' => sanitize_text_field(wp_unslash($_POST['coupon_code'] ?? '')),
        'payment_method' => sanitize_key(wp_unslash($_POST['payment_method'] ?? 'cod')),
        'payment_bank_code' => sanitize_text_field(wp_unslash($_POST['payment_bank_code'] ?? '')),
        'payment_provider' => '',
        'shipping_fee' => 0,
        'discount_amount' => 0,
    ];

    $valid = vs_commerce_validate_checkout_data($data);
    if (is_wp_error($valid)) {
        wp_send_json_error(['message' => $valid->get_error_message()], 422);
    }

    if ($data['payment_method'] === 'vnpay' && !vs_commerce_vnpay_is_ready()) {
        wp_send_json_error(['message' => 'VNPAY hiện chưa sẵn sàng. Vui lòng thử lại sau hoặc chọn COD.'], 500);
    }

    $data['payment_provider'] = $data['payment_method'] === 'vnpay' ? 'vnpay' : 'cod';

    $order = vs_commerce_order_create($data);
    if (is_wp_error($order)) {
        wp_send_json_error(['message' => $order->get_error_message()], 422);
    }

    if ($data['payment_method'] === 'cod') {
        vs_commerce_order_update((int) $order['id'], [
            'status' => 'processing',
            'payment_status' => 'unpaid',
            'payment_provider' => 'cod',
        ]);
        if (!empty($order['cart_token'])) {
            vs_commerce_clear_cart($order['cart_token']);
        }
        wp_send_json_success([
            'redirect_url' => vs_commerce_checkout_success_url($order['order_code'], 'cod'),
        ]);
    }

    $payment = vs_commerce_build_vnpay_payment_url($order, $data['payment_bank_code']);
    if (is_wp_error($payment)) {
        wp_send_json_error(['message' => $payment->get_error_message()], 500);
    }

    vs_commerce_order_mark_pending_payment((int) $order['id'], $payment['txn_ref'], $data['payment_bank_code']);

    wp_send_json_success([
        'redirect_url' => $payment['payment_url'],
    ]);
}
add_action('wp_ajax_vs_checkout_place_order', 'vs_commerce_handle_place_order');
add_action('wp_ajax_nopriv_vs_checkout_place_order', 'vs_commerce_handle_place_order');

function vs_commerce_verify_vnpay_signature($params)
{
    $config = vs_commerce_get_vnpay_config();
    $secure_hash = (string) ($params['vnp_SecureHash'] ?? '');
    if ($secure_hash === '' || empty($config['hash_secret'])) {
        return false;
    }

    $inputData = [];
    foreach ((array) $params as $key => $value) {
        if (strpos((string) $key, 'vnp_') === 0) {
            $inputData[$key] = (string) $value;
        }
    }

    unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
    ksort($inputData);

    $hashData = [];
    foreach ($inputData as $key => $value) {
        $hashData[] = urlencode($key) . '=' . urlencode($value);
    }

    $hash_string = implode('&', $hashData);
    $calc_hash = hash_hmac('sha512', $hash_string, $config['hash_secret']);
    return hash_equals($calc_hash, $secure_hash);
}

function vs_commerce_handle_vnpay_return()
{
    $params = wp_unslash($_GET);
    $txn_ref = sanitize_text_field($params['vnp_TxnRef'] ?? '');
    $response_code = sanitize_text_field($params['vnp_ResponseCode'] ?? '');
    $transaction_status = sanitize_text_field($params['vnp_TransactionStatus'] ?? '');
    $bank_code = sanitize_text_field($params['vnp_BankCode'] ?? '');
    $transaction_no = sanitize_text_field($params['vnp_TransactionNo'] ?? '');
    $amount = isset($params['vnp_Amount']) ? ((int) $params['vnp_Amount']) / 100 : 0;

    if (!vs_commerce_verify_vnpay_signature($params)) {
        wp_safe_redirect(vs_commerce_checkout_success_url($txn_ref, 'invalid-signature'));
        exit;
    }

    $order = vs_commerce_order_get_by_code($txn_ref);
    if (!$order) {
        wp_safe_redirect(vs_commerce_checkout_success_url($txn_ref, 'order-not-found'));
        exit;
    }

    if (vs_commerce_get_vnpay_order_amount($order) !== (int) round($amount * 100)) {
        wp_safe_redirect(vs_commerce_checkout_success_url($order['order_code'], 'invalid-amount'));
        exit;
    }

    if ($response_code === '00' && $transaction_status === '00') {
        vs_commerce_order_mark_paid((int) $order['id'], [
            'transaction_no' => $transaction_no,
            'bank_code' => $bank_code,
            'response_code' => $response_code,
        ]);
        wp_safe_redirect(vs_commerce_checkout_success_url($order['order_code'], 'success'));
        exit;
    }

    vs_commerce_order_mark_failed((int) $order['id'], [
        'transaction_no' => $transaction_no,
        'bank_code' => $bank_code,
        'response_code' => $response_code,
    ]);

    wp_safe_redirect(vs_commerce_checkout_success_url($order['order_code'], 'failed'));
    exit;
}
add_action('admin_post_vs_vnpay_return', 'vs_commerce_handle_vnpay_return');
add_action('admin_post_nopriv_vs_vnpay_return', 'vs_commerce_handle_vnpay_return');

function vs_commerce_handle_vnpay_ipn()
{
    $params = wp_unslash($_GET);
    $txn_ref = sanitize_text_field($params['vnp_TxnRef'] ?? '');
    $response_code = sanitize_text_field($params['vnp_ResponseCode'] ?? '');
    $transaction_status = sanitize_text_field($params['vnp_TransactionStatus'] ?? '');
    $bank_code = sanitize_text_field($params['vnp_BankCode'] ?? '');
    $transaction_no = sanitize_text_field($params['vnp_TransactionNo'] ?? '');
    $amount = isset($params['vnp_Amount']) ? ((int) $params['vnp_Amount']) / 100 : 0;

    if (!vs_commerce_verify_vnpay_signature($params)) {
        wp_send_json(['RspCode' => '97', 'Message' => 'Invalid signature']);
    }

    $order = vs_commerce_order_get_by_code($txn_ref);
    if (!$order) {
        wp_send_json(['RspCode' => '01', 'Message' => 'Order not found']);
    }

    if (vs_commerce_get_vnpay_order_amount($order) !== (int) round($amount * 100)) {
        wp_send_json(['RspCode' => '04', 'Message' => 'Invalid amount']);
    }

    if ($order['payment_status'] === 'paid') {
        wp_send_json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
    }

    if ($response_code === '00' && $transaction_status === '00') {
        vs_commerce_order_mark_paid((int) $order['id'], [
            'transaction_no' => $transaction_no,
            'bank_code' => $bank_code,
            'response_code' => $response_code,
        ]);
        wp_send_json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }

    vs_commerce_order_mark_failed((int) $order['id'], [
        'transaction_no' => $transaction_no,
        'bank_code' => $bank_code,
        'response_code' => $response_code,
    ]);
    wp_send_json(['RspCode' => '00', 'Message' => 'Confirm Success']);
}
add_action('admin_post_vs_vnpay_ipn', 'vs_commerce_handle_vnpay_ipn');
add_action('admin_post_nopriv_vs_vnpay_ipn', 'vs_commerce_handle_vnpay_ipn');
