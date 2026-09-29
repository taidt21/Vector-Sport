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

function vs_commerce_admin_order_status_label($status)
{
    $labels = [
        'pending'   => 'Chờ xử lý',
        'paid'      => 'Đã thanh toán',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        'failed'    => 'Thất bại',
    ];

    return $labels[$status] ?? $status;
}

function vs_commerce_admin_payment_status_label($status)
{
    $labels = [
        'unpaid'  => 'Chưa thanh toán',
        'pending' => 'Chờ thanh toán',
        'paid'    => 'Đã thanh toán',
        'failed'  => 'Thanh toán lỗi',
    ];

    return $labels[$status] ?? $status;
}
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
function vs_commerce_render_admin_orders_page()
{
    if (!current_user_can('manage_options')) {
        wp_die('Bạn không có quyền truy cập trang này.');
    }

    global $wpdb;

    $orders_table = vs_commerce_order_table();

    $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
    $per_page = 10;
    $offset = ($paged - 1) * $per_page;

    $status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
    $keyword = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

    $where = ['1=1'];
    $params = [];

    if ($status !== '') {
        $where[] = 'status = %s';
        $params[] = $status;
    }

    if ($keyword !== '') {
        $where[] = '(order_code LIKE %s OR customer_name LIKE %s OR customer_phone LIKE %s)';
        $like = '%' . $wpdb->esc_like($keyword) . '%';
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
    <h1 class="wp-heading-inline">Quản lý đơn hàng</h1>

    <form method="get" style="margin: 16px 0;">
        <input type="hidden" name="post_type" value="product">
        <input type="hidden" name="page" value="vs-commerce-orders">

        <select name="status">
            <option value="">Tất cả trạng thái</option>
            <option value="pending" <?php selected($status, 'pending'); ?>>Chờ xử lý</option>
            <option value="paid" <?php selected($status, 'paid'); ?>>Đã thanh toán</option>
            <option value="completed" <?php selected($status, 'completed'); ?>>Hoàn thành</option>
            <option value="cancelled" <?php selected($status, 'cancelled'); ?>>Đã hủy</option>
            <option value="failed" <?php selected($status, 'failed'); ?>>Thất bại</option>
        </select>

        <input type="search" name="s" value="<?php echo esc_attr($keyword); ?>" placeholder="Tìm mã đơn, tên, SĐT...">

        <button class="button">Lọc</button>
    </form>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 120px;">Mã đơn</th>
                <th style="width: 140px;">Khách hàng</th>
                <th style="width: 140px;">Số điện thoại</th>
                <th style="width: 140px;">Tổng tiền</th>
                <th style="width: 140px;">Trạng thái</th>
                <th style="width: 150px;">Thanh toán</th>
                <th style="width: 160px;">Ngày tạo</th>
                <th style="width: 100px;">Chi tiết</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($orders)) : ?>
            <?php foreach ($orders as $order) : ?>
            <?php
                        $detail_url = add_query_arg([
                            'post_type' => 'product',
                            'page'      => 'vs-commerce-orders',
                            'order_id'  => (int) $order['id'],
                        ], admin_url('edit.php'));
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
                    <?php echo esc_html(vs_commerce_admin_order_status_label($order['status'] ?? '')); ?>
                </td>

                <td>
                    <?php echo esc_html(strtoupper($order['payment_method'] ?? '')); ?>
                    <br>
                    <small>
                        <?php echo esc_html(vs_commerce_admin_payment_status_label($order['payment_status'] ?? '')); ?>
                    </small>
                </td>

                <td><?php echo esc_html($order['created_at'] ?? ''); ?></td>

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