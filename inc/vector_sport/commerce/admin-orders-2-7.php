<?php
/**
 * Vector Sport Commerce - Admin Orders
 */

if (!defined('ABSPATH')) {
    exit;
}

function vs_commerce_register_admin_orders_menu()
{
    add_submenu_page(
        'edit.php?post_type=product',
        'Quản lý đơn hàng',
        'Quản lý đơn hàng',
        'manage_options',
        'vs-commerce-orders',
        'vs_commerce_render_admin_orders_page'
    );
}
add_action('admin_menu', 'vs_commerce_register_admin_orders_menu');

function vs_commerce_admin_order_status_options()
{
    return [
        'pending'          => 'Chờ xử lý',
        'awaiting_payment' => 'Chờ thanh toán',
        'processing'       => 'Đang xử lý',
        'paid'             => 'Đã thanh toán',
        'shipping'         => 'Đang giao hàng',
        'completed'        => 'Hoàn thành',
        'cancelled'        => 'Đã hủy',
        'payment_failed'   => 'Thanh toán thất bại',
        'failed'           => 'Thất bại',
        'refunded'         => 'Đã hoàn tiền',
    ];
}

function vs_commerce_admin_payment_status_options()
{
    return [
        'pending'  => 'Chờ thanh toán',
        'unpaid'   => 'Chưa thanh toán',
        'paid'     => 'Đã thanh toán',
        'failed'   => 'Thanh toán lỗi',
        'refunded' => 'Đã hoàn tiền',
    ];
}

function vs_commerce_admin_payment_method_options()
{
    return [
        'cod'   => 'COD',
        'vnpay' => 'VNPAY',
    ];
}

function vs_commerce_admin_order_status_label($status)
{
    $status = sanitize_key((string) $status);
    $labels = vs_commerce_admin_order_status_options();

    return $labels[$status] ?? ($status ?: 'Không rõ');
}

function vs_commerce_admin_payment_status_label($status)
{
    $status = sanitize_key((string) $status);
    $labels = vs_commerce_admin_payment_status_options();

    return $labels[$status] ?? ($status ?: 'Không rõ');
}

function vs_commerce_admin_payment_method_label($method)
{
    $method = sanitize_key((string) $method);
    $labels = vs_commerce_admin_payment_method_options();

    return $labels[$method] ?? strtoupper($method ?: 'N/A');
}

function vs_commerce_admin_get_order_meta(array $order)
{
    if (function_exists('vs_commerce_get_order_meta_array')) {
        return vs_commerce_get_order_meta_array($order);
    }

    $meta = [];
    if (!empty($order['meta_json'])) {
        $decoded = json_decode((string) $order['meta_json'], true);
        if (is_array($decoded)) {
            $meta = $decoded;
        }
    }

    return $meta;
}

function vs_commerce_admin_status_badge($label, $type = 'default')
{
    $classes = [
        'default' => 'vs-order-badge-default',
        'good'    => 'vs-order-badge-good',
        'warn'    => 'vs-order-badge-warn',
        'bad'     => 'vs-order-badge-bad',
        'info'    => 'vs-order-badge-info',
    ];

    $class = $classes[$type] ?? $classes['default'];

    return sprintf(
        '<span class="vs-order-badge %s">%s</span>',
        esc_attr($class),
        esc_html($label)
    );
}

function vs_commerce_admin_order_status_badge($status)
{
    $status = sanitize_key((string) $status);
    $type = 'default';

    if (in_array($status, ['paid', 'completed', 'refunded'], true)) {
        $type = 'good';
    } elseif (in_array($status, ['pending', 'awaiting_payment', 'processing', 'shipping'], true)) {
        $type = 'warn';
    } elseif (in_array($status, ['cancelled', 'failed', 'payment_failed'], true)) {
        $type = 'bad';
    }

    return vs_commerce_admin_status_badge(vs_commerce_admin_order_status_label($status), $type);
}

function vs_commerce_admin_payment_status_badge($status)
{
    $status = sanitize_key((string) $status);
    $type = 'default';

    if (in_array($status, ['paid', 'refunded'], true)) {
        $type = 'good';
    } elseif (in_array($status, ['pending', 'unpaid'], true)) {
        $type = 'warn';
    } elseif ($status === 'failed') {
        $type = 'bad';
    }

    return vs_commerce_admin_status_badge(vs_commerce_admin_payment_status_label($status), $type);
}

function vs_commerce_admin_format_datetime($datetime)
{
    $datetime = trim((string) $datetime);
    if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
        return '—';
    }

    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return $datetime;
    }

    return date_i18n('d/m/Y H:i', $timestamp);
}

function vs_commerce_admin_full_address(array $order, array $meta = [])
{
    if (!empty($meta['full_address_text'])) {
        return (string) $meta['full_address_text'];
    }

    return trim(implode(', ', array_filter([
        $order['address_line'] ?? '',
        $order['ward'] ?? '',
        $order['district'] ?? '',
        $order['province'] ?? '',
    ])));
}

function vs_commerce_admin_orders_url(array $args = [])
{
    return add_query_arg(
        array_merge([
            'post_type' => 'product',
            'page'      => 'vs-commerce-orders',
        ], $args),
        admin_url('edit.php')
    );
}

function vs_commerce_admin_render_notice()
{
    $notice = isset($_GET['vs_notice']) ? sanitize_key(wp_unslash($_GET['vs_notice'])) : '';

    if ($notice === 'updated') {
        echo '<div class="notice notice-success is-dismissible"><p>Đã cập nhật đơn hàng.</p></div>';
    } elseif ($notice === 'no_change') {
        echo '<div class="notice notice-info is-dismissible"><p>Không có thay đổi nào cần lưu.</p></div>';
    } elseif ($notice === 'error') {
        echo '<div class="notice notice-error is-dismissible"><p>Không thể cập nhật đơn hàng. Vui lòng thử lại.</p></div>';
    }
}

function vs_commerce_admin_get_allowed_statuses()
{
    return array_keys(vs_commerce_admin_order_status_options());
}

function vs_commerce_admin_get_allowed_payment_statuses()
{
    return array_keys(vs_commerce_admin_payment_status_options());
}

function vs_commerce_handle_admin_update_order()
{
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền thực hiện thao tác này.');
    }

    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    if (!$order_id) {
        wp_die('Đơn hàng không hợp lệ.');
    }

    check_admin_referer('vs_commerce_update_order_' . $order_id);

    $order = vs_commerce_order_get($order_id);
    if (!$order) {
        wp_die('Không tìm thấy đơn hàng.');
    }

    $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';
    $payment_status = isset($_POST['payment_status']) ? sanitize_key(wp_unslash($_POST['payment_status'])) : '';
    $admin_note = isset($_POST['admin_note']) ? sanitize_textarea_field(wp_unslash($_POST['admin_note'])) : '';

    if (!in_array($status, vs_commerce_admin_get_allowed_statuses(), true)) {
        wp_die('Trạng thái đơn không hợp lệ.');
    }

    if (!in_array($payment_status, vs_commerce_admin_get_allowed_payment_statuses(), true)) {
        wp_die('Trạng thái thanh toán không hợp lệ.');
    }

    $old_status = (string) ($order['status'] ?? '');
    $old_payment_status = (string) ($order['payment_status'] ?? '');
    $changes = [];
    $update_data = [];

    if ($status !== $old_status) {
        $update_data['status'] = $status;
        $changes[] = sprintf(
            'Trạng thái đơn: %s → %s',
            vs_commerce_admin_order_status_label($old_status),
            vs_commerce_admin_order_status_label($status)
        );
    }

    if ($payment_status !== $old_payment_status) {
        $update_data['payment_status'] = $payment_status;
        $changes[] = sprintf(
            'Thanh toán: %s → %s',
            vs_commerce_admin_payment_status_label($old_payment_status),
            vs_commerce_admin_payment_status_label($payment_status)
        );

        if ($payment_status === 'paid' && empty($order['paid_at'])) {
            $update_data['paid_at'] = current_time('mysql');
        }
    }

    $meta = vs_commerce_admin_get_order_meta($order);

    if (!empty($changes) || $admin_note !== '') {
        $user = wp_get_current_user();

        if (!isset($meta['admin_notes']) || !is_array($meta['admin_notes'])) {
            $meta['admin_notes'] = [];
        }

        $note_entry = [
            'time'      => current_time('mysql'),
            'user_id'   => get_current_user_id(),
            'user_name' => $user && $user->exists() ? $user->display_name : 'Admin',
            'content'   => $admin_note,
            'changes'   => $changes,
        ];

        $meta['admin_notes'][] = $note_entry;

        if ($admin_note !== '') {
            $meta['internal_note'] = $admin_note;
        }

        $update_data['meta_json'] = wp_json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $redirect_url = vs_commerce_admin_orders_url([
        'order_id' => $order_id,
    ]);

    if (empty($update_data)) {
        wp_safe_redirect(add_query_arg('vs_notice', 'no_change', $redirect_url));
        exit;
    }

    $old_was_paid = $old_payment_status === 'paid';
    $updated = vs_commerce_order_update($order_id, $update_data);

    if (!$updated) {
        wp_safe_redirect(add_query_arg('vs_notice', 'error', $redirect_url));
        exit;
    }

    if (!$old_was_paid && $payment_status === 'paid') {
        $fresh_order = vs_commerce_order_get($order_id);
        if ($fresh_order) {
            do_action('vs_commerce_order_paid', $order_id, $fresh_order, [
                'source' => 'admin',
            ]);
        }
    }

    wp_safe_redirect(add_query_arg('vs_notice', 'updated', $redirect_url));
    exit;
}
add_action('admin_post_vs_commerce_update_order', 'vs_commerce_handle_admin_update_order');

function vs_commerce_render_admin_pagination($total_items, $total_pages, $current_page)
{
    $total_items  = (int) $total_items;
    $total_pages  = max(1, (int) $total_pages);
    $current_page = max(1, (int) $current_page);

    if ($total_items <= 0 || $total_pages <= 1) {
        return;
    }

    $base_url = remove_query_arg('paged');

    $first_page_url = esc_url(add_query_arg('paged', 1, $base_url));
    $prev_page_url  = esc_url(add_query_arg('paged', max(1, $current_page - 1), $base_url));
    $next_page_url  = esc_url(add_query_arg('paged', min($total_pages, $current_page + 1), $base_url));
    $last_page_url  = esc_url(add_query_arg('paged', $total_pages, $base_url));

    $is_first_page = $current_page <= 1;
    $is_last_page  = $current_page >= $total_pages;
    ?>

<div class="tablenav">
    <div class="tablenav-pages">
        <span class="displaying-num">
            <?php echo esc_html(number_format_i18n($total_items)); ?> mục
        </span>

        <span class="pagination-links">
            <?php if ($is_first_page) : ?>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
            <?php else : ?>
            <a class="first-page button" href="<?php echo $first_page_url; ?>">
                <span class="screen-reader-text">Trang đầu</span>
                <span aria-hidden="true">«</span>
            </a>

            <a class="prev-page button" href="<?php echo $prev_page_url; ?>">
                <span class="screen-reader-text">Trang trước</span>
                <span aria-hidden="true">‹</span>
            </a>
            <?php endif; ?>

            <span class="screen-reader-text">Trang hiện tại</span>

            <span id="table-paging" class="paging-input">
                <span class="tablenav-paging-text">
                    <?php echo esc_html(number_format_i18n($current_page)); ?>
                    <span class="total-pages">
                        trên <?php echo esc_html(number_format_i18n($total_pages)); ?>
                    </span>
                </span>
            </span>

            <?php if ($is_last_page) : ?>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
            <?php else : ?>
            <a class="next-page button" href="<?php echo $next_page_url; ?>">
                <span class="screen-reader-text">Trang sau</span>
                <span aria-hidden="true">›</span>
            </a>

            <a class="last-page button" href="<?php echo $last_page_url; ?>">
                <span class="screen-reader-text">Trang cuối</span>
                <span aria-hidden="true">»</span>
            </a>
            <?php endif; ?>
        </span>
    </div>
</div>

<?php
}

function vs_commerce_render_admin_orders_css()
{
    ?>
    <style>
        .vs-order-badge {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .vs-order-badge-default { background: #f6f7f7; border-color: #dcdcde; color: #2c3338; }
        .vs-order-badge-good { background: #edfaef; border-color: #8bcf96; color: #0a6b19; }
        .vs-order-badge-warn { background: #fff8e5; border-color: #f0c36d; color: #7a4b00; }
        .vs-order-badge-bad { background: #fcf0f1; border-color: #e68a94; color: #8a2424; }
        .vs-order-badge-info { background: #eef6ff; border-color: #8ec5ff; color: #0a4b78; }
        .vs-admin-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
            gap: 20px;
            margin-top: 16px;
        }
        .vs-admin-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 16px;
            margin-bottom: 16px;
        }
        .vs-admin-card h2 {
            margin: 0 0 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dcdcde;
            font-size: 16px;
        }
        .vs-admin-detail-table th {
            width: 180px;
            color: #50575e;
        }
        .vs-admin-items img {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            background: #f6f7f7;
        }
        .vs-order-total-table th,
        .vs-order-total-table td {
            padding: 6px 0;
        }
        .vs-order-total-table th { text-align: left; color: #50575e; }
        .vs-order-total-table td { text-align: right; }
        .vs-note-list {
            margin: 0;
        }
        .vs-note-list li {
            border-left: 3px solid #2271b1;
            background: #f6f7f7;
            padding: 10px 12px;
            margin: 0 0 10px;
        }
        .vs-muted { color: #646970; }
        .vs-form-row { margin-bottom: 14px; }
        .vs-form-row label { display: block; font-weight: 600; margin-bottom: 6px; }
        .vs-form-row select,
        .vs-form-row textarea { width: 100%; max-width: 100%; }
        @media (max-width: 1100px) {
            .vs-admin-grid { grid-template-columns: 1fr; }
        }
    </style>
    <?php
}

function vs_commerce_render_admin_order_detail($order_id)
{
    $order = vs_commerce_order_get($order_id);

    if (!$order) {
        ?>
        <div class="wrap">
            <h1>Không tìm thấy đơn hàng</h1>
            <p>Đơn hàng này không tồn tại hoặc đã bị xóa.</p>
            <p><a class="button" href="<?php echo esc_url(vs_commerce_admin_orders_url()); ?>">Quay lại danh sách</a></p>
        </div>
        <?php
        return;
    }

    $meta = vs_commerce_admin_get_order_meta($order);
    $items = is_array($order['items'] ?? null) ? $order['items'] : [];
    $admin_notes = isset($meta['admin_notes']) && is_array($meta['admin_notes']) ? array_reverse($meta['admin_notes']) : [];
    $address_mode_label = $meta['address_mode_label'] ?? vs_commerce_address_mode_label($order['address_mode'] ?? 'new');
    $full_address = vs_commerce_admin_full_address($order, $meta);
    $back_url = vs_commerce_admin_orders_url();
    $status = (string) ($order['status'] ?? '');
    $payment_status = (string) ($order['payment_status'] ?? '');
    ?>

    <div class="wrap">
        <?php vs_commerce_admin_render_notice(); ?>
        <?php vs_commerce_render_admin_orders_css(); ?>

        <h1 class="wp-heading-inline">
            Đơn hàng <?php echo esc_html($order['order_code'] ?? ('#' . $order_id)); ?>
        </h1>
        <a class="page-title-action" href="<?php echo esc_url($back_url); ?>">← Danh sách đơn</a>

        <div style="margin-top:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <?php echo vs_commerce_admin_order_status_badge($status); ?>
            <?php echo vs_commerce_admin_payment_status_badge($payment_status); ?>
            <span class="vs-muted">Tạo lúc <?php echo esc_html(vs_commerce_admin_format_datetime($order['created_at'] ?? '')); ?></span>
        </div>

        <div class="vs-admin-grid">
            <div>
                <div class="vs-admin-card">
                    <h2>Thông tin khách hàng</h2>
                    <table class="form-table vs-admin-detail-table" role="presentation">
                        <tr>
                            <th>Khách hàng</th>
                            <td><strong><?php echo esc_html($order['customer_name'] ?? ''); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Số điện thoại</th>
                            <td><?php echo esc_html($order['customer_phone'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo !empty($order['customer_email']) ? esc_html($order['customer_email']) : '—'; ?></td>
                        </tr>
                        <tr>
                            <th>Loại địa chỉ</th>
                            <td><?php echo esc_html($address_mode_label); ?></td>
                        </tr>
                        <tr>
                            <th>Địa chỉ giao hàng</th>
                            <td>
                                <strong><?php echo esc_html($full_address ?: '—'); ?></strong>
                                <div class="vs-muted" style="margin-top:6px;">
                                    Tỉnh/Thành: <?php echo esc_html($order['province'] ?? '—'); ?>
                                    <?php if (!empty($order['district'])) : ?>
                                        <br>Quận/Huyện: <?php echo esc_html($order['district']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($order['ward'])) : ?>
                                        <br>Phường/Xã: <?php echo esc_html($order['ward']); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Ghi chú khách</th>
                            <td><?php echo !empty($order['note']) ? nl2br(esc_html($order['note'])) : '—'; ?></td>
                        </tr>
                    </table>
                </div>

                <div class="vs-admin-card">
                    <h2>Sản phẩm trong đơn</h2>
                    <table class="widefat striped vs-admin-items">
                        <thead>
                            <tr>
                                <th style="width:70px;">Ảnh</th>
                                <th>Sản phẩm</th>
                                <th style="width:120px;">Phân loại</th>
                                <th style="width:70px;text-align:center;">SL</th>
                                <th style="width:130px;text-align:right;">Đơn giá</th>
                                <th style="width:130px;text-align:right;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)) : ?>
                                <?php foreach ($items as $item) : ?>
                                    <?php
                                    $variant = trim(implode(' / ', array_filter([
                                        $item['color'] ?? '',
                                        $item['size'] ?? '',
                                    ])));
                                    $product_link = (string) ($item['product_link'] ?? '');
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['image'])) : ?>
                                                <img src="<?php echo esc_url($item['image']); ?>" alt="">
                                            <?php else : ?>
                                                <span class="vs-muted">Không ảnh</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo esc_html($item['product_title'] ?? ''); ?></strong>
                                            <?php if ($product_link !== '') : ?>
                                                <br><a href="<?php echo esc_url($product_link); ?>" target="_blank" rel="noopener">Xem sản phẩm</a>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html($variant ?: 'Mặc định'); ?></td>
                                        <td style="text-align:center;"><?php echo esc_html((string) ($item['qty'] ?? 1)); ?></td>
                                        <td style="text-align:right;"><?php echo esc_html(vs_commerce_format_price($item['unit_price'] ?? 0)); ?></td>
                                        <td style="text-align:right;"><strong><?php echo esc_html(vs_commerce_format_price($item['line_total'] ?? 0)); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6">Không có sản phẩm trong đơn.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="vs-admin-card">
                    <h2>Lịch sử xử lý / ghi chú nội bộ</h2>

                    <?php if (!empty($meta['internal_note'])) : ?>
                        <p><strong>Ghi chú nội bộ mới nhất:</strong><br><?php echo nl2br(esc_html($meta['internal_note'])); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($admin_notes)) : ?>
                        <ul class="vs-note-list">
                            <?php foreach ($admin_notes as $note) : ?>
                                <?php
                                $note_time = $note['time'] ?? '';
                                $note_user = $note['user_name'] ?? 'Admin';
                                $note_content = $note['content'] ?? '';
                                $note_changes = isset($note['changes']) && is_array($note['changes']) ? $note['changes'] : [];
                                ?>
                                <li>
                                    <div class="vs-muted">
                                        <?php echo esc_html(vs_commerce_admin_format_datetime($note_time)); ?> bởi <?php echo esc_html($note_user); ?>
                                    </div>

                                    <?php if (!empty($note_changes)) : ?>
                                        <div style="margin-top:6px;">
                                            <?php foreach ($note_changes as $change) : ?>
                                                <div>• <?php echo esc_html($change); ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($note_content !== '') : ?>
                                        <div style="margin-top:8px;"><?php echo nl2br(esc_html($note_content)); ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="vs-muted">Chưa có ghi chú nội bộ.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="vs-admin-card">
                    <h2>Quản lý đơn</h2>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="vs_commerce_update_order">
                        <input type="hidden" name="order_id" value="<?php echo esc_attr((string) $order_id); ?>">
                        <?php wp_nonce_field('vs_commerce_update_order_' . $order_id); ?>

                        <div class="vs-form-row">
                            <label for="vs-order-status">Trạng thái đơn</label>
                            <select id="vs-order-status" name="status">
                                <?php foreach (vs_commerce_admin_order_status_options() as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="vs-form-row">
                            <label for="vs-payment-status">Trạng thái thanh toán</label>
                            <select id="vs-payment-status" name="payment_status">
                                <?php foreach (vs_commerce_admin_payment_status_options() as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($payment_status, $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="vs-form-row">
                            <label for="vs-admin-note">Ghi chú nội bộ</label>
                            <textarea id="vs-admin-note" name="admin_note" rows="4" placeholder="Ví dụ: Khách hẹn giao sau 18h, đã gọi xác nhận, lý do hủy..."></textarea>
                            <p class="description">Ghi chú sẽ được lưu vào lịch sử xử lý của đơn.</p>
                        </div>

                        <button type="submit" class="button button-primary">Cập nhật đơn</button>
                    </form>
                </div>

                <div class="vs-admin-card">
                    <h2>Tổng kết đơn</h2>
                    <table class="widefat vs-order-total-table">
                        <tbody>
                            <tr>
                                <th>Tạm tính</th>
                                <td><?php echo esc_html(vs_commerce_format_price($order['subtotal'] ?? 0)); ?></td>
                            </tr>
                            <tr>
                                <th>Phí vận chuyển</th>
                                <td><?php echo esc_html(vs_commerce_format_price($order['shipping_fee'] ?? 0)); ?></td>
                            </tr>
                            <tr>
                                <th>Giảm giá</th>
                                <td><?php echo esc_html(vs_commerce_format_price($order['discount_amount'] ?? 0)); ?></td>
                            </tr>
                            <tr>
                                <th><strong>Tổng cộng</strong></th>
                                <td><strong><?php echo esc_html(vs_commerce_format_price($order['grand_total'] ?? 0)); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="vs-admin-card">
                    <h2>Thông tin thanh toán</h2>
                    <table class="form-table vs-admin-detail-table" role="presentation">
                        <tr>
                            <th>Phương thức</th>
                            <td><?php echo esc_html(vs_commerce_admin_payment_method_label($order['payment_method'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <th>Trạng thái thanh toán</th>
                            <td><?php echo vs_commerce_admin_payment_status_badge($payment_status); ?></td>
                        </tr>
                        <tr>
                            <th>Provider</th>
                            <td><?php echo esc_html($order['payment_provider'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th>Mã tham chiếu</th>
                            <td><?php echo esc_html($order['provider_ref'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th>Mã giao dịch</th>
                            <td><?php echo esc_html($order['provider_transaction_no'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th>Ngân hàng</th>
                            <td><?php echo esc_html($order['provider_bank_code'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th>Mã phản hồi</th>
                            <td><?php echo esc_html($order['provider_response_code'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th>Thời gian thanh toán</th>
                            <td><?php echo esc_html(vs_commerce_admin_format_datetime($order['paid_at'] ?? '')); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="vs-admin-card">
                    <h2>Thông tin hệ thống</h2>
                    <table class="form-table vs-admin-detail-table" role="presentation">
                        <tr>
                            <th>ID đơn</th>
                            <td>#<?php echo esc_html((string) $order_id); ?></td>
                        </tr>
                        <tr>
                            <th>Cart token</th>
                            <td><?php echo esc_html($order['cart_token'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th>Ngày tạo</th>
                            <td><?php echo esc_html(vs_commerce_admin_format_datetime($order['created_at'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <th>Cập nhật cuối</th>
                            <td><?php echo esc_html(vs_commerce_admin_format_datetime($order['updated_at'] ?? '')); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
}

function vs_commerce_render_admin_orders_page()
{
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập trang này.');
    }

    $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
    if ($order_id > 0) {
        vs_commerce_render_admin_order_detail($order_id);
        return;
    }

    global $wpdb;

    $orders_table = vs_commerce_order_table();

    $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
    $per_page = 10;
    $offset = ($paged - 1) * $per_page;

    $status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
    $payment_method = isset($_GET['payment_method']) ? sanitize_key(wp_unslash($_GET['payment_method'])) : '';
    $payment_status = isset($_GET['payment_status']) ? sanitize_key(wp_unslash($_GET['payment_status'])) : '';
    $keyword = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

    $where = ['1=1'];
    $params = [];

    if ($status !== '') {
        $where[] = 'status = %s';
        $params[] = $status;
    }

    if ($payment_method !== '') {
        $where[] = 'payment_method = %s';
        $params[] = $payment_method;
    }

    if ($payment_status !== '') {
        $where[] = 'payment_status = %s';
        $params[] = $payment_status;
    }

    if ($keyword !== '') {
        $where[] = '(order_code LIKE %s OR customer_name LIKE %s OR customer_phone LIKE %s OR customer_email LIKE %s OR provider_ref LIKE %s OR provider_transaction_no LIKE %s)';
        $like = '%' . $wpdb->esc_like($keyword) . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $where_sql = implode(' AND ', $where);

    $count_sql = "SELECT COUNT(*) FROM {$orders_table} WHERE {$where_sql}";
    $total = !empty($params)
        ? (int) $wpdb->get_var($wpdb->prepare($count_sql, $params))
        : (int) $wpdb->get_var($count_sql);

    $query_sql = "SELECT * FROM {$orders_table} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";
    $query_params = array_merge($params, [$per_page, $offset]);

    $orders = $wpdb->get_results(
        $wpdb->prepare($query_sql, $query_params),
        ARRAY_A
    );

    $total_pages = (int) ceil($total / $per_page);
    ?>

<div class="wrap">
    <?php vs_commerce_admin_render_notice(); ?>
    <?php vs_commerce_render_admin_orders_css(); ?>

    <h1 class="wp-heading-inline">Quản lý đơn hàng</h1>

    <form method="get" style="margin: 16px 0;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="post_type" value="product">
        <input type="hidden" name="page" value="vs-commerce-orders">

        <select name="status">
            <option value="">Tất cả trạng thái đơn</option>
            <?php foreach (vs_commerce_admin_order_status_options() as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="payment_method">
            <option value="">Tất cả phương thức</option>
            <?php foreach (vs_commerce_admin_payment_method_options() as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($payment_method, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="payment_status">
            <option value="">Tất cả thanh toán</option>
            <?php foreach (vs_commerce_admin_payment_status_options() as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($payment_status, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="search" name="s" value="<?php echo esc_attr($keyword); ?>" placeholder="Tìm mã đơn, tên, SĐT, email, mã GD..." style="min-width:260px;">

        <button class="button">Lọc</button>
        <a class="button" href="<?php echo esc_url(vs_commerce_admin_orders_url()); ?>">Xóa lọc</a>
    </form>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 120px;">Mã đơn</th>
                <th style="width: 170px;">Khách hàng</th>
                <th style="width: 130px;">Số điện thoại</th>
                <th style="width: 130px;">Tổng tiền</th>
                <th style="width: 150px;">Trạng thái</th>
                <th style="width: 160px;">Thanh toán</th>
                <th style="width: 150px;">Ngày tạo</th>
                <th style="width: 90px;">Chi tiết</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($orders)) : ?>
                <?php foreach ($orders as $order) : ?>
                    <?php
                    $detail_url = vs_commerce_admin_orders_url([
                        'order_id' => (int) $order['id'],
                    ]);
                    ?>

                    <tr>
                        <td>
                            <strong><?php echo esc_html($order['order_code'] ?? ''); ?></strong>
                        </td>

                        <td>
                            <?php echo esc_html($order['customer_name'] ?? ''); ?>
                            <?php if (!empty($order['customer_email'])) : ?>
                                <br>
                                <small><?php echo esc_html($order['customer_email']); ?></small>
                            <?php endif; ?>
                        </td>

                        <td><?php echo esc_html($order['customer_phone'] ?? ''); ?></td>

                        <td>
                            <strong>
                                <?php echo esc_html(vs_commerce_format_price($order['grand_total'] ?? 0)); ?>
                            </strong>
                        </td>

                        <td>
                            <?php echo vs_commerce_admin_order_status_badge($order['status'] ?? ''); ?>
                        </td>

                        <td>
                            <strong><?php echo esc_html(vs_commerce_admin_payment_method_label($order['payment_method'] ?? '')); ?></strong>
                            <br>
                            <?php echo vs_commerce_admin_payment_status_badge($order['payment_status'] ?? ''); ?>
                        </td>

                        <td><?php echo esc_html(vs_commerce_admin_format_datetime($order['created_at'] ?? '')); ?></td>

                        <td>
                            <a class="button button-small" href="<?php echo esc_url($detail_url); ?>">
                                Xem
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8">Chưa có đơn hàng nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php vs_commerce_render_admin_pagination($total, $total_pages, $paged); ?>
</div>

<?php
}
