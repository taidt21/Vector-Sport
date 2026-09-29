<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Orders menu under Product
 * Gắn file này vào cuối inc/vs-commerce.php
 * hoặc require_once từ functions.php / vs-commerce.php.
 */

if (!function_exists('vs_commerce_orders_table_name')) {
    function vs_commerce_orders_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'vs_orders';
    }
}

if (!function_exists('vs_commerce_order_items_table_name')) {
    function vs_commerce_order_items_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'vs_order_items';
    }
}

if (!function_exists('vs_commerce_order_status_options')) {
    function vs_commerce_order_status_options()
    {
        return [
            'pending'          => 'Chờ xác nhận',
            'awaiting_payment' => 'Chờ thanh toán',
            'processing'       => 'Đang xử lý',
            'paid'             => 'Đã thanh toán',
            'completed'        => 'Hoàn thành',
            'payment_failed'   => 'Thanh toán thất bại',
            'cancelled'        => 'Đã hủy',
            'refunded'         => 'Đã hoàn tiền',
        ];
    }
}

if (!function_exists('vs_commerce_payment_status_options')) {
    function vs_commerce_payment_status_options()
    {
        return [
            'pending'  => 'Chờ thanh toán',
            'unpaid'   => 'Chưa thanh toán',
            'paid'     => 'Đã thanh toán',
            'failed'   => 'Thất bại',
            'refunded' => 'Đã hoàn tiền',
        ];
    }
}

if (!function_exists('vs_commerce_payment_method_options')) {
    function vs_commerce_payment_method_options()
    {
        return [
            'cod'   => 'Thanh toán khi nhận hàng',
            'vnpay' => 'VNPAY',
        ];
    }
}

if (!function_exists('vs_commerce_get_status_label')) {
    function vs_commerce_get_status_label($value, $type = 'order')
    {
        $value = (string) $value;
        $options = $type === 'payment'
            ? vs_commerce_payment_status_options()
            : vs_commerce_order_status_options();

        return $options[$value] ?? $value;
    }
}

if (!function_exists('vs_commerce_get_payment_method_label')) {
    function vs_commerce_get_payment_method_label($value)
    {
        $value = (string) $value;
        $options = vs_commerce_payment_method_options();
        return $options[$value] ?? strtoupper($value);
    }
}

if (!function_exists('vs_commerce_admin_money')) {
    function vs_commerce_admin_money($amount)
    {
        $amount = (float) $amount;
        return number_format($amount, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('vs_commerce_admin_status_badge')) {
    function vs_commerce_admin_status_badge($value, $type = 'order')
    {
        $value = (string) $value;
        $map = [
            'pending'          => 'is-pending',
            'awaiting_payment' => 'is-awaiting',
            'processing'       => 'is-processing',
            'paid'             => 'is-paid',
            'completed'        => 'is-completed',
            'payment_failed'   => 'is-failed',
            'failed'           => 'is-failed',
            'cancelled'        => 'is-cancelled',
            'refunded'         => 'is-refunded',
            'unpaid'           => 'is-unpaid',
        ];

        $class = $map[$value] ?? 'is-default';
        $label = vs_commerce_get_status_label($value, $type);

        return '<span class="vs-order-badge ' . esc_attr($class) . '">' . esc_html($label) . '</span>';
    }
}

if (!function_exists('vs_commerce_admin_address_badge')) {
    function vs_commerce_admin_address_badge($mode)
    {
        if ((string) $mode !== 'old') {
            return '';
        }

        return '<span class="vs-order-badge is-old-address">Địa chỉ trước sáp nhập</span>';
    }
}

if (!function_exists('vs_commerce_admin_allowed_status')) {
    function vs_commerce_admin_allowed_status($value, $type = 'order')
    {
        $value = sanitize_key((string) $value);
        $options = $type === 'payment'
            ? vs_commerce_payment_status_options()
            : vs_commerce_order_status_options();

        return array_key_exists($value, $options) ? $value : '';
    }
}

if (!function_exists('vs_commerce_admin_payment_summary')) {
    function vs_commerce_admin_payment_summary($payment_method, $payment_status)
    {
        $method_label = vs_commerce_get_payment_method_label($payment_method);
        $status_badge = vs_commerce_admin_status_badge($payment_status, 'payment');

        return '<div class="vs-payment-summary">'
            . '<div class="vs-payment-summary__method">' . esc_html($method_label) . '</div>'
            . '<div class="vs-payment-summary__status">' . $status_badge . '</div>'
            . '</div>';
    }
}

if (!function_exists('vs_commerce_admin_list_base_args')) {
    function vs_commerce_admin_list_base_args($search = '', $status = '', $payment_method = '')
    {
        return [
            'post_type'      => 'product',
            'page'           => 'vs-commerce-orders',
            's'              => $search,
            'status'         => $status,
            'payment_method' => $payment_method,
        ];
    }
}

if (!function_exists('vs_commerce_render_admin_orders_pagination')) {
    function vs_commerce_render_admin_orders_pagination($paged, $total_pages, $total_items, array $base_args = [], $position = 'top')
    {
        if ($total_pages <= 1) {
            return;
        }

        $paged = max(1, (int) $paged);
        $total_pages = max(1, (int) $total_pages);
        $base_url = admin_url('edit.php');

        $build_url = static function ($page) use ($base_args, $base_url) {
            $page = max(1, (int) $page);
            return add_query_arg(array_merge($base_args, ['paged' => $page]), $base_url);
        };

        $first_disabled = $paged <= 1;
        $last_disabled = $paged >= $total_pages;
        $wrapper_class = $position === 'bottom' ? 'tablenav bottom' : 'tablenav top';

        echo '<div class="' . esc_attr($wrapper_class) . '">';
        echo '<div class="displaying-num">' . esc_html(number_format_i18n($total_items)) . ' đơn hàng</div>';
        echo '<div class="tablenav-pages' . ($total_pages <= 1 ? ' one-page' : '') . '">';
        echo '<span class="displaying-num vs-orders-display-num-mobile">' . esc_html(number_format_i18n($total_items)) . ' đơn hàng</span>';
        echo '<span class="pagination-links">';

        if ($first_disabled) {
            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            echo '<a class="first-page button" href="' . esc_url($build_url(1)) . '"><span class="screen-reader-text">Trang đầu</span><span aria-hidden="true">&laquo;</span></a>';
            echo '<a class="prev-page button" href="' . esc_url($build_url($paged - 1)) . '"><span class="screen-reader-text">Trang trước</span><span aria-hidden="true">&lsaquo;</span></a>';
        }

        echo '<span class="paging-input">';
        echo '<label for="current-page-selector" class="screen-reader-text">Trang hiện tại</label>';
        echo '<span class="tablenav-paging-text">' . esc_html(number_format_i18n($paged)) . ' trên <span class="total-pages">' . esc_html(number_format_i18n($total_pages)) . '</span></span>';
        echo '</span>';

        if ($last_disabled) {
            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            echo '<a class="next-page button" href="' . esc_url($build_url($paged + 1)) . '"><span class="screen-reader-text">Trang sau</span><span aria-hidden="true">&rsaquo;</span></a>';
            echo '<a class="last-page button" href="' . esc_url($build_url($total_pages)) . '"><span class="screen-reader-text">Trang cuối</span><span aria-hidden="true">&raquo;</span></a>';
        }

        echo '</span>';
        echo '</div>';
        echo '</div>';
    }
}

add_action('admin_menu', 'vs_commerce_register_admin_orders_menu', 30);
function vs_commerce_register_admin_orders_menu()
{
    add_submenu_page(
        'edit.php?post_type=product',
        'Quản lý đơn hàng',
        'Đơn hàng',
        'manage_options',
        'vs-commerce-orders',
        'vs_commerce_render_admin_orders_page'
    );
}

add_action('admin_post_vs_commerce_update_order_admin', 'vs_commerce_handle_admin_order_update');
function vs_commerce_handle_admin_order_update()
{
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập.');
    }

    check_admin_referer('vs_commerce_update_order_admin');

    global $wpdb;

    $order_id = absint($_POST['order_id'] ?? 0);
    if (!$order_id) {
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vs-commerce-orders'));
        exit;
    }

    $current_order = $wpdb->get_row(
        $wpdb->prepare('SELECT * FROM ' . vs_commerce_orders_table_name() . ' WHERE id = %d LIMIT 1', $order_id),
        ARRAY_A
    );

    if (!$current_order) {
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vs-commerce-orders'));
        exit;
    }

    $redirect_view = sanitize_key($_POST['redirect_view'] ?? 'detail');
    $status = vs_commerce_admin_allowed_status(wp_unslash($_POST['status'] ?? $current_order['status']), 'order');
    if ($status === '') {
        $status = (string) $current_order['status'];
    }

    if ($redirect_view === 'list') {
        $payment_status = (string) ($current_order['payment_status'] ?? 'pending');
        $note = (string) ($current_order['note'] ?? '');
        $provider_ref = (string) ($current_order['provider_ref'] ?? '');
        $provider_txn = (string) ($current_order['provider_transaction_no'] ?? '');
        $response_code = (string) ($current_order['provider_response_code'] ?? '');
    } else {
        $payment_status = vs_commerce_admin_allowed_status(wp_unslash($_POST['payment_status'] ?? $current_order['payment_status']), 'payment');
        if ($payment_status === '') {
            $payment_status = (string) $current_order['payment_status'];
        }

        $note = sanitize_textarea_field(wp_unslash($_POST['note'] ?? $current_order['note'] ?? ''));
        $provider_ref = sanitize_text_field(wp_unslash($_POST['provider_ref'] ?? $current_order['provider_ref'] ?? ''));
        $provider_txn = sanitize_text_field(wp_unslash($_POST['provider_transaction_no'] ?? $current_order['provider_transaction_no'] ?? ''));
        $response_code = sanitize_text_field(wp_unslash($_POST['provider_response_code'] ?? $current_order['provider_response_code'] ?? ''));
    }

    if ($status === 'completed') {
        $payment_status = 'paid';
    }

    $wpdb->update(
        vs_commerce_orders_table_name(),
        [
            'status'                  => $status,
            'payment_status'          => $payment_status,
            'note'                    => $note,
            'provider_ref'            => $provider_ref,
            'provider_transaction_no' => $provider_txn,
            'provider_response_code'  => $response_code,
            'updated_at'              => current_time('mysql'),
        ],
        ['id' => $order_id],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%s'],
        ['%d']
    );

    if ($redirect_view === 'list') {
        $redirect_args = array_merge(
            vs_commerce_admin_list_base_args(
                sanitize_text_field(wp_unslash($_POST['redirect_search'] ?? '')),
                sanitize_key($_POST['redirect_status_filter'] ?? ''),
                sanitize_key($_POST['redirect_payment_method_filter'] ?? '')
            ),
            [
                'updated' => 1,
                'paged'   => max(1, absint($_POST['redirect_paged'] ?? 1)),
            ]
        );

        $redirect = add_query_arg($redirect_args, admin_url('edit.php'));
    } else {
        $redirect = add_query_arg([
            'post_type' => 'product',
            'page'      => 'vs-commerce-orders',
            'view'      => 'detail',
            'order_id'  => $order_id,
            'updated'   => 1,
        ], admin_url('edit.php'));
    }

    wp_safe_redirect($redirect);
    exit;
}

function vs_commerce_render_admin_orders_page()
{
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập.');
    }

    $view = sanitize_key($_GET['view'] ?? 'list');

    echo '<div class="wrap vs-orders-admin">';
    echo '<h1 class="wp-heading-inline">Quản lý đơn hàng</h1>';

    if ($view === 'detail') {
        vs_commerce_render_admin_order_detail();
    } else {
        vs_commerce_render_admin_orders_list();
    }

    echo '</div>';

    vs_commerce_render_admin_orders_style();
}

function vs_commerce_render_admin_orders_list()
{
    global $wpdb;

    $table = vs_commerce_orders_table_name();

    $search         = sanitize_text_field(wp_unslash($_GET['s'] ?? ''));
    $status         = sanitize_key($_GET['status'] ?? '');
    $payment_method = sanitize_key($_GET['payment_method'] ?? '');
    $paged          = max(1, absint($_GET['paged'] ?? 1));
    $per_page       = 20;
    $offset         = ($paged - 1) * $per_page;

    $where  = ' WHERE 1=1 ';
    $params = [];

    if ($search !== '') {
        $where .= ' AND (order_code LIKE %s OR customer_name LIKE %s OR customer_phone LIKE %s OR customer_email LIKE %s) ';
        $like = '%' . $wpdb->esc_like($search) . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if ($status !== '') {
        $where .= ' AND status = %s ';
        $params[] = $status;
    }

    if ($payment_method !== '') {
        $where .= ' AND payment_method = %s ';
        $params[] = $payment_method;
    }

    $count_sql = "SELECT COUNT(*) FROM {$table} {$where}";
    if (!empty($params)) {
        $count_sql = $wpdb->prepare($count_sql, $params);
    }
    $total_items = (int) $wpdb->get_var($count_sql);

    $sql = "SELECT * FROM {$table} {$where} ORDER BY id DESC LIMIT %d OFFSET %d";
    $query_params = $params;
    $query_params[] = $per_page;
    $query_params[] = $offset;
    $sql = $wpdb->prepare($sql, $query_params);

    $orders = $wpdb->get_results($sql, ARRAY_A);
    $total_pages = max(1, (int) ceil($total_items / $per_page));
    $base_args = vs_commerce_admin_list_base_args($search, $status, $payment_method);

    if (!empty($_GET['updated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Cập nhật đơn hàng thành công.</p></div>';
    }

    echo '<form method="get" class="vs-orders-filter">';
    echo '<input type="hidden" name="post_type" value="product">';
    echo '<input type="hidden" name="page" value="vs-commerce-orders">';
    echo '<input type="search" name="s" placeholder="Tìm mã đơn, tên, số điện thoại, email" value="' . esc_attr($search) . '">';

    echo '<select name="status">';
    echo '<option value="">Tất cả trạng thái đơn</option>';
    foreach (vs_commerce_order_status_options() as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($status, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';

    echo '<select name="payment_method">';
    echo '<option value="">Tất cả phương thức</option>';
    foreach (vs_commerce_payment_method_options() as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($payment_method, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';

    submit_button('Lọc', 'primary', '', false);
    echo '</form>';

    vs_commerce_render_admin_orders_pagination($paged, $total_pages, $total_items, $base_args);

    echo '<table class="widefat fixed striped vs-orders-table">';
    echo '<thead><tr>';
    echo '<th style="width: 120px;">Mã đơn</th>';
    echo '<th style="width:120px;">Khách hàng</th>';
    echo '<th style="width: 170px;">Thanh toán</th>';
    echo '<th style="width: 150px;">Trạng thái đơn</th>';
    echo '<th style="width: 130px; text-align:right;">Tổng tiền</th>';
    echo '<th style="width: 160px;">Ngày tạo</th>';
    echo '<th style="width: 210px;">Cập nhật nhanh</th>';
    echo '<th style="width: 90px;">Chi tiết</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    if (!empty($orders)) {
        foreach ($orders as $order) {
            $detail_url = add_query_arg([
                'post_type' => 'product',
                'page'      => 'vs-commerce-orders',
                'view'      => 'detail',
                'order_id'  => (int) $order['id'],
            ], admin_url('edit.php'));

            echo '<tr>';
            echo '<td><strong>' . esc_html($order['order_code']) . '</strong></td>';
            echo '<td>';
            echo '<div><strong>' . esc_html($order['customer_name']) . '</strong></div>';
            echo '<div>' . esc_html($order['customer_phone']) . '</div>';
            if (!empty($order['customer_email'])) {
                echo '<div>' . esc_html($order['customer_email']) . '</div>';
            }
            echo '</td>';
            echo '<td>' . vs_commerce_admin_payment_summary($order['payment_method'], $order['payment_status']) . '</td>';
            echo '<td>' . vs_commerce_admin_status_badge($order['status'], 'order') . '</td>';
            echo '<td style="text-align:right;"><strong>' . esc_html(vs_commerce_admin_money($order['grand_total'])) . '</strong></td>';
            echo '<td>' . esc_html(mysql2date('d/m/Y H:i', $order['created_at'])) . '</td>';

            echo '<td>';
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="vs-quick-update-form">';
            wp_nonce_field('vs_commerce_update_order_admin');
            echo '<input type="hidden" name="action" value="vs_commerce_update_order_admin">';
            echo '<input type="hidden" name="order_id" value="' . esc_attr((int) $order['id']) . '">';
            echo '<input type="hidden" name="redirect_view" value="list">';
            echo '<input type="hidden" name="redirect_paged" value="' . esc_attr($paged) . '">';
            echo '<input type="hidden" name="redirect_status_filter" value="' . esc_attr($status) . '">';
            echo '<input type="hidden" name="redirect_payment_method_filter" value="' . esc_attr($payment_method) . '">';
            echo '<input type="hidden" name="redirect_search" value="' . esc_attr($search) . '">';

            echo '<label class="screen-reader-text" for="vs_order_status_' . esc_attr((int) $order['id']) . '">Trạng thái đơn</label>';
            echo '<select id="vs_order_status_' . esc_attr((int) $order['id']) . '" name="status">';
            foreach (vs_commerce_order_status_options() as $key => $label) {
                echo '<option value="' . esc_attr($key) . '" ' . selected($order['status'], $key, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';

            echo '<button type="submit" class="button button-primary button-small">Lưu</button>';
            echo '</form>';
            echo '</td>';

            echo '<td><a class="button button-small" href="' . esc_url($detail_url) . '">Xem</a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8">Chưa có đơn hàng nào.</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';

    vs_commerce_render_admin_orders_pagination($paged, $total_pages, $total_items, $base_args, 'bottom');
}

function vs_commerce_render_admin_order_detail()
{
    global $wpdb;

    $order_id = absint($_GET['order_id'] ?? 0);
    if (!$order_id) {
        echo '<p>Không tìm thấy đơn hàng.</p>';
        return;
    }

    $order = $wpdb->get_row($wpdb->prepare(
        'SELECT * FROM ' . vs_commerce_orders_table_name() . ' WHERE id = %d LIMIT 1',
        $order_id
    ), ARRAY_A);

    if (!$order) {
        echo '<p>Đơn hàng không tồn tại.</p>';
        return;
    }

    $items = $wpdb->get_results($wpdb->prepare(
        'SELECT * FROM ' . vs_commerce_order_items_table_name() . ' WHERE order_id = %d ORDER BY id ASC',
        $order_id
    ), ARRAY_A);

    $back_url = admin_url('edit.php?post_type=product&page=vs-commerce-orders');

    if (!empty($_GET['updated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Cập nhật đơn hàng thành công.</p></div>';
    }

    echo '<p><a href="' . esc_url($back_url) . '">← Quay lại danh sách đơn hàng</a></p>';
    echo '<div class="vs-order-detail-grid">';

    echo '<div class="vs-order-card">';
    echo '<h2>Thông tin đơn hàng</h2>';
    echo '<table class="form-table"><tbody>';
    echo '<tr><th>Mã đơn</th><td><strong>' . esc_html($order['order_code']) . '</strong></td></tr>';
    echo '<tr><th>Khách hàng</th><td>' . esc_html($order['customer_name']) . '</td></tr>';
    echo '<tr><th>Điện thoại</th><td>' . esc_html($order['customer_phone']) . '</td></tr>';
    echo '<tr><th>Email</th><td>' . esc_html($order['customer_email']) . '</td></tr>';
    echo '<tr><th>Phương thức thanh toán</th><td>' . esc_html(vs_commerce_get_payment_method_label($order['payment_method'])) . '</td></tr>';
    echo '<tr><th>Trạng thái thanh toán</th><td>' . vs_commerce_admin_status_badge($order['payment_status'], 'payment') . '</td></tr>';
    echo '<tr><th>Trạng thái đơn</th><td>' . vs_commerce_admin_status_badge($order['status'], 'order') . '</td></tr>';
    echo '<tr><th>Địa chỉ</th><td>' . vs_commerce_admin_address_badge($order['address_mode'] ?? 'new') . '<div style="margin-top:8px;">' . esc_html(trim($order['address_line'] . ', ' . $order['ward'] . ', ' . $order['district'] . ', ' . $order['province'], ', ')) . '</div></td></tr>';
    echo '<tr><th>Mã địa chỉ</th><td>' . esc_html(implode(' / ', array_filter([
        $order['province_code'] ?? '',
        $order['district_code'] ?? '',
        $order['ward_code'] ?? '',
    ]))) . '</td></tr>';
    echo '<tr><th>Tổng tiền</th><td><strong>' . esc_html(vs_commerce_admin_money($order['grand_total'])) . '</strong></td></tr>';
    echo '<tr><th>Tạo lúc</th><td>' . esc_html(mysql2date('d/m/Y H:i:s', $order['created_at'])) . '</td></tr>';
    echo '</tbody></table>';
    echo '</div>';

    echo '<div class="vs-order-card">';
    echo '<h2>Cập nhật trạng thái</h2>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('vs_commerce_update_order_admin');
    echo '<input type="hidden" name="action" value="vs_commerce_update_order_admin">';
    echo '<input type="hidden" name="order_id" value="' . esc_attr($order_id) . '">';
    echo '<input type="hidden" name="redirect_view" value="detail">';

    echo '<table class="form-table"><tbody>';

    echo '<tr><th>Trạng thái đơn</th><td><select name="status">';
    foreach (vs_commerce_order_status_options() as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($order['status'], $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></td></tr>';

    echo '<tr><th>Trạng thái thanh toán</th><td><select name="payment_status">';
    foreach (vs_commerce_payment_status_options() as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($order['payment_status'], $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></td></tr>';

    echo '<tr><th>Phương thức</th><td><strong>' . esc_html(vs_commerce_get_payment_method_label($order['payment_method'])) . '</strong></td></tr>';
    echo '<tr><th>Cổng thanh toán</th><td>' . esc_html($order['payment_provider']) . '</td></tr>';
    echo '<tr><th>Mã tham chiếu</th><td><input type="text" class="regular-text" name="provider_ref" value="' . esc_attr($order['provider_ref']) . '"></td></tr>';
    echo '<tr><th>Mã giao dịch</th><td><input type="text" class="regular-text" name="provider_transaction_no" value="' . esc_attr($order['provider_transaction_no']) . '"></td></tr>';
    echo '<tr><th>Mã phản hồi</th><td><input type="text" class="regular-text" name="provider_response_code" value="' . esc_attr($order['provider_response_code']) . '"></td></tr>';
    echo '<tr><th>Ghi chú</th><td><textarea name="note" rows="5" class="large-text">' . esc_textarea($order['note']) . '</textarea></td></tr>';

    echo '</tbody></table>';

    submit_button('Lưu thay đổi');
    echo '</form>';
    echo '</div>';

    echo '</div>';

    echo '<div class="vs-order-card" style="margin-top:16px;">';
    echo '<h2>Sản phẩm trong đơn</h2>';
    echo '<table class="widefat striped">';
    echo '<thead><tr>';
    echo '<th>Sản phẩm</th>';
    echo '<th>Màu / Size</th>';
    echo '<th style="width:90px;">SL</th>';
    echo '<th style="width:140px; text-align:right;">Đơn giá</th>';
    echo '<th style="width:140px; text-align:right;">Thành tiền</th>';
    echo '</tr></thead><tbody>';

    if (!empty($items)) {
        foreach ($items as $item) {
            echo '<tr>';
            echo '<td>';
            echo '<div style="display:flex; gap:12px; align-items:flex-start;">';
            if (!empty($item['image'])) {
                echo '<img src="' . esc_url($item['image']) . '" alt="" style="width:52px;height:52px;object-fit:cover;border:1px solid #e5e7eb;border-radius:6px;">';
            }
            echo '<div>';
            echo '<strong>' . esc_html($item['product_title']) . '</strong>';
            if (!empty($item['product_link'])) {
                echo '<div><a href="' . esc_url($item['product_link']) . '" target="_blank" rel="noopener">Xem sản phẩm</a></div>';
            }
            echo '</div></div>';
            echo '</td>';
            echo '<td>' . esc_html(trim($item['color'] . ' / ' . $item['size'], ' /')) . '</td>';
            echo '<td>' . esc_html($item['qty']) . '</td>';
            echo '<td style="text-align:right;">' . esc_html(vs_commerce_admin_money($item['unit_price'])) . '</td>';
            echo '<td style="text-align:right;"><strong>' . esc_html(vs_commerce_admin_money($item['line_total'])) . '</strong></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">Không có sản phẩm trong đơn.</td></tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}

function vs_commerce_render_admin_orders_style()
{
    ?>
<style>
.vs-orders-admin .vs-orders-filter {
    display: flex;
    gap: 8px;
    align-items: center;
    margin: 16px 0;
    flex-wrap: wrap;
}

.vs-orders-admin .vs-orders-filter input[type="search"] {
    min-width: 320px;
}

.vs-orders-admin .tablenav-pages {
    display: flex;
    align-items: center;
    gap: 8px;
}

.vs-orders-admin .tablenav-pages .pagination-links {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.vs-orders-admin .tablenav-pages .paging-input {
    margin: 0 4px;
}

.vs-orders-admin .vs-orders-display-num-mobile {
    display: none;
}

.vs-orders-admin .tablenav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin: 12px 0;
}

.vs-order-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
}

.vs-order-badge.is-pending,
.vs-order-badge.is-awaiting,
.vs-order-badge.is-unpaid {
    background: #fff7ed;
    color: #c2410c;
}

.vs-order-badge.is-processing {
    background: #eff6ff;
    color: #1d4ed8;
}

.vs-order-badge.is-paid,
.vs-order-badge.is-completed {
    background: #ecfdf5;
    color: #047857;
}

.vs-order-badge.is-failed,
.vs-order-badge.is-cancelled,
.vs-order-badge.is-refunded {
    background: #fef2f2;
    color: #b91c1c;
}

.vs-order-badge.is-old-address {
    background: #f5f3ff;
    color: #6d28d9;
}

.vs-order-badge.is-default {
    background: #f3f4f6;
    color: #374151;
}

.vs-payment-summary {
    display: grid;
    gap: 6px;
}

.vs-payment-summary__method {
    font-weight: 600;
    color: #111827;
}

.vs-order-detail-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 16px;
    margin-top: 12px;
}

.vs-order-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
}

.vs-order-card h2 {
    margin-top: 0;
    margin-bottom: 12px;
}

.vs-quick-update-form {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
    align-items: center;
}

.vs-quick-update-form select {
    width: 100%;
    min-width: 0;
}

.vs-orders-table td {
    vertical-align: top;
}

@media (max-width: 960px) {
    .vs-order-detail-grid {
        grid-template-columns: 1fr;
    }

    .vs-orders-admin .vs-orders-filter input[type="search"] {
        min-width: 0;
        width: 100%;
    }

    .vs-orders-admin .tablenav {
        flex-direction: column;
        align-items: flex-start;
    }

    .vs-quick-update-form {
        grid-template-columns: 1fr;
    }
}
</style>
<?php
}