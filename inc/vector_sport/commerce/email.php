<?php
/**
 * Vector Sport Commerce Email
 */

if (!defined('ABSPATH')) {
    exit;
}

function vs_commerce_get_admin_email_recipients()
{
    if (function_exists('vs_get_contact_register_recipient_emails')) {
        $recipients = vs_get_contact_register_recipient_emails();
    } else {
        $admin_email = get_option('admin_email');
        $recipients = [];

        if ($admin_email && is_email($admin_email)) {
            $recipients[] = $admin_email;
        }
    }

    $recipients = apply_filters('vs_commerce_admin_email_recipients', $recipients);

    $recipients = array_filter(array_map('sanitize_email', (array) $recipients), 'is_email');

    return array_values(array_unique($recipients));
}

function vs_commerce_email_sent_key($order_id, $type)
{
    return 'vs_commerce_email_sent_' . absint($order_id) . '_' . sanitize_key($type);
}

function vs_commerce_email_was_sent($order_id, $type)
{
    return (bool) get_transient(vs_commerce_email_sent_key($order_id, $type));
}

function vs_commerce_mark_email_sent($order_id, $type)
{
    set_transient(vs_commerce_email_sent_key($order_id, $type), 1, 30 * DAY_IN_SECONDS);
}

function vs_commerce_get_order_items($order_id)
{
    global $wpdb;

    $table = vs_commerce_order_item_table();

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE order_id = %d ORDER BY id ASC",
            absint($order_id)
        ),
        ARRAY_A
    );
}

function vs_commerce_get_order_meta_array(array $order)
{
    $meta = [];

    if (!empty($order['meta_json'])) {
        $decoded = json_decode((string) $order['meta_json'], true);

        if (is_array($decoded)) {
            $meta = $decoded;
        }
    }

    return $meta;
}

function vs_commerce_build_order_email_body(array $order, $type = 'new_order')
{
    $items = vs_commerce_get_order_items((int) $order['id']);
    $meta = vs_commerce_get_order_meta_array($order);

    $order_code = (string) ($order['order_code'] ?? '');
    $payment_method = strtoupper((string) ($order['payment_method'] ?? ''));
    $payment_status = (string) ($order['payment_status'] ?? '');
    $order_status = (string) ($order['status'] ?? '');

    $address_mode_label = $meta['address_mode_label'] ?? vs_commerce_address_mode_label($order['address_mode'] ?? 'new');

    $full_address = trim(implode(', ', array_filter([
        $order['address_line'] ?? '',
        $order['ward'] ?? '',
        $order['district'] ?? '',
        $order['province'] ?? '',
    ])));

    $title = $type === 'payment_success'
        ? 'THANH TOÁN THÀNH CÔNG'
        : 'ĐƠN HÀNG MỚI';

    ob_start();
    ?>
    <div style="font-family:Arial,sans-serif;color:#222;font-size:14px;">
        <h2 style="margin:0 0 16px;color:#EC2024;">
            <?php echo esc_html($title); ?> - <?php echo esc_html($order_code); ?>
        </h2>

        <table cellpadding="8" cellspacing="0" border="0" style="border-collapse:collapse;width:100%;max-width:720px;">
            <tr>
                <td style="border:1px solid #eee;background:#fafafa;width:180px;"><strong>Mã đơn</strong></td>
                <td style="border:1px solid #eee;"><?php echo esc_html($order_code); ?></td>
            </tr>

            <tr>
                <td style="border:1px solid #eee;background:#fafafa;"><strong>Trạng thái đơn</strong></td>
                <td style="border:1px solid #eee;"><?php echo esc_html($order_status); ?></td>
            </tr>

            <tr>
                <td style="border:1px solid #eee;background:#fafafa;"><strong>Thanh toán</strong></td>
                <td style="border:1px solid #eee;">
                    <?php echo esc_html($payment_method); ?> - <?php echo esc_html($payment_status); ?>
                </td>
            </tr>

            <tr>
                <td style="border:1px solid #eee;background:#fafafa;"><strong>Khách hàng</strong></td>
                <td style="border:1px solid #eee;">
                    <?php echo esc_html($order['customer_name'] ?? ''); ?><br>
                    <?php echo esc_html($order['customer_phone'] ?? ''); ?><br>
                    <?php echo esc_html($order['customer_email'] ?? ''); ?>
                </td>
            </tr>

            <tr>
                <td style="border:1px solid #eee;background:#fafafa;"><strong>Loại địa chỉ</strong></td>
                <td style="border:1px solid #eee;"><?php echo esc_html($address_mode_label); ?></td>
            </tr>

            <tr>
                <td style="border:1px solid #eee;background:#fafafa;"><strong>Địa chỉ</strong></td>
                <td style="border:1px solid #eee;"><?php echo esc_html($full_address); ?></td>
            </tr>

            <?php if (!empty($order['note'])) : ?>
                <tr>
                    <td style="border:1px solid #eee;background:#fafafa;"><strong>Ghi chú</strong></td>
                    <td style="border:1px solid #eee;"><?php echo nl2br(esc_html($order['note'])); ?></td>
                </tr>
            <?php endif; ?>

            <tr>
                <td style="border:1px solid #eee;background:#fafafa;"><strong>Tổng tiền</strong></td>
                <td style="border:1px solid #eee;">
                    <strong><?php echo esc_html(vs_commerce_format_price($order['grand_total'] ?? 0)); ?></strong>
                </td>
            </tr>
        </table>

        <h3 style="margin:24px 0 12px;">Sản phẩm</h3>

        <table cellpadding="8" cellspacing="0" border="0" style="border-collapse:collapse;width:100%;max-width:720px;">
            <thead>
                <tr>
                    <th align="left" style="border:1px solid #eee;background:#fafafa;">Sản phẩm</th>
                    <th align="left" style="border:1px solid #eee;background:#fafafa;">Phân loại</th>
                    <th align="center" style="border:1px solid #eee;background:#fafafa;">SL</th>
                    <th align="right" style="border:1px solid #eee;background:#fafafa;">Thành tiền</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($items as $item) : ?>
                    <tr>
                        <td style="border:1px solid #eee;">
                            <?php echo esc_html($item['product_title'] ?? ''); ?>
                        </td>

                        <td style="border:1px solid #eee;">
                            <?php
                            $variant = trim(implode(' / ', array_filter([
                                $item['color'] ?? '',
                                $item['size'] ?? '',
                            ])));

                            echo esc_html($variant ?: 'Mặc định');
                            ?>
                        </td>

                        <td align="center" style="border:1px solid #eee;">
                            <?php echo esc_html((string) ($item['qty'] ?? 1)); ?>
                        </td>

                        <td align="right" style="border:1px solid #eee;">
                            <?php echo esc_html(vs_commerce_format_price($item['line_total'] ?? 0)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!empty($order['provider_transaction_no'])) : ?>
            <h3 style="margin:24px 0 12px;">Thông tin giao dịch</h3>

            <table cellpadding="8" cellspacing="0" border="0" style="border-collapse:collapse;width:100%;max-width:720px;">
                <tr>
                    <td style="border:1px solid #eee;background:#fafafa;width:180px;"><strong>Mã giao dịch</strong></td>
                    <td style="border:1px solid #eee;"><?php echo esc_html($order['provider_transaction_no']); ?></td>
                </tr>

                <tr>
                    <td style="border:1px solid #eee;background:#fafafa;"><strong>Ngân hàng</strong></td>
                    <td style="border:1px solid #eee;"><?php echo esc_html($order['provider_bank_code'] ?? ''); ?></td>
                </tr>

                <tr>
                    <td style="border:1px solid #eee;background:#fafafa;"><strong>Mã phản hồi</strong></td>
                    <td style="border:1px solid #eee;"><?php echo esc_html($order['provider_response_code'] ?? ''); ?></td>
                </tr>
            </table>
        <?php endif; ?>

        <p style="margin-top:24px;color:#666;">
            Email này được gửi tự động từ website <?php echo esc_html(get_bloginfo('name')); ?>.
        </p>
    </div>
    <?php

    return ob_get_clean();
}

function vs_commerce_send_admin_order_email($order_id, $type = 'new_order')
{
    $order_id = absint($order_id);

    if (!$order_id) {
        return false;
    }

    if (vs_commerce_email_was_sent($order_id, $type)) {
        return false;
    }

    $order = vs_commerce_order_get($order_id);

    if (!$order) {
        return false;
    }

    $recipients = vs_commerce_get_admin_email_recipients();

    if (empty($recipients)) {
        return false;
    }

    $order_code = (string) ($order['order_code'] ?? '');

    if ($type === 'payment_success') {
        $subject = '[Vector Sport] Thanh toán thành công - ' . $order_code;
    } else {
        $subject = '[Vector Sport] Có đơn hàng mới - ' . $order_code;
    }

    $body = vs_commerce_build_order_email_body($order, $type);

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
    ];

    $sent = wp_mail($recipients, $subject, $body, $headers);

    if ($sent) {
        vs_commerce_mark_email_sent($order_id, $type);
    }

    if (function_exists('vs_commerce_log')) {
        vs_commerce_log('Send admin order email', [
            'order_id' => $order_id,
            'type' => $type,
            'sent' => $sent ? 'yes' : 'no',
            'recipients' => $recipients,
        ]);
    }

    return $sent;
}

function vs_commerce_send_new_order_email($order_id, $order = null, $data = [])
{
    vs_commerce_send_admin_order_email($order_id, 'new_order');
}

function vs_commerce_send_payment_success_email($order_id, $order = null, $payment_data = [])
{
    vs_commerce_send_admin_order_email($order_id, 'payment_success');
}

add_action('vs_commerce_order_created', 'vs_commerce_send_new_order_email', 10, 3);
add_action('vs_commerce_order_paid', 'vs_commerce_send_payment_success_email', 10, 3);