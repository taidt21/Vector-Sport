<?php
if (!defined('ABSPATH')) {
    exit;
}

// CART
// =========================================================
function vs_commerce_cart_cookie_name()
{
    return 'vs_cart_token';
}

function vs_commerce_cart_storage_key($token = '')
{
    $token = $token ?: vs_commerce_get_cart_token();
    return 'vs_cart_' . md5($token);
}

function vs_commerce_get_cart_token()
{
    $cookie_name = vs_commerce_cart_cookie_name();

    if (!empty($_COOKIE[$cookie_name])) {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', wp_unslash($_COOKIE[$cookie_name]));
    }

    $token = wp_generate_uuid4();

    setcookie(
        $cookie_name,
        $token,
        time() + (30 * DAY_IN_SECONDS),
        COOKIEPATH ?: '/',
        COOKIE_DOMAIN,
        is_ssl(),
        true
    );

    $_COOKIE[$cookie_name] = $token;
    return $token;
}

function vs_commerce_get_cart_items()
{
    $items = get_transient(vs_commerce_cart_storage_key());
    return is_array($items) ? array_values($items) : [];
}

function vs_commerce_save_cart_items(array $items)
{
    set_transient(vs_commerce_cart_storage_key(), array_values($items), 30 * DAY_IN_SECONDS);
}

function vs_commerce_clear_cart($token = '')
{
    delete_transient(vs_commerce_cart_storage_key($token));
}

function vs_commerce_get_product_image_by_color($product_id, $selected_color = '')
{
    $thumb = get_the_post_thumbnail_url($product_id, 'medium');
    $rows  = get_field('cac_mau_sac', $product_id);

    if (!empty($rows) && is_array($rows)) {
        foreach ($rows as $row) {
            $color_name  = trim((string) ($row['ten_mau_sac'] ?? ''));
            $color_value = trim((string) ($row['mau_sac'] ?? ''));
            $matched = false;

            if ($selected_color !== '') {
                $selected = mb_strtolower($selected_color, 'UTF-8');
                $matched = $selected === mb_strtolower($color_name, 'UTF-8') || $selected === mb_strtolower($color_value, 'UTF-8');
            }

            if ($matched || $selected_color === '') {
                $gallery = vs_commerce_normalize_gallery($row['danh_sach_anh_san_pham'] ?? []);
                if (!empty($gallery[0]['thumb'])) {
                    return $gallery[0]['thumb'];
                }
            }
        }
    }

    return $thumb ?: '';
}

function vs_commerce_build_cart_item($product_id, $color, $size, $qty)
{
    $product_id = absint($product_id);
    $qty = max(1, absint($qty));
    $color = sanitize_text_field(trim((string) $color));
    $size = sanitize_text_field(trim((string) $size));

    if (!$product_id || get_post_status($product_id) !== 'publish') {
        return null;
    }

    $price = vs_commerce_to_number(get_field('gia_tien', $product_id));
    if ($price <= 0) {
        return null;
    }

    $line_key = md5($product_id . '|' . $color . '|' . $size);

    return [
        'key' => $line_key,
        'product_id' => $product_id,
        'title' => get_the_title($product_id),
        'link' => get_permalink($product_id),
        'image' => vs_commerce_get_product_image_by_color($product_id, $color),
        'price' => $price,
        'qty' => $qty,
        'color' => $color,
        'size' => $size,
    ];
}

function vs_commerce_get_cart_totals($items = null)
{
    $items = is_array($items) ? $items : vs_commerce_get_cart_items();

    $subtotal = 0;
    $count = 0;
    foreach ($items as $item) {
        $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0));
        $count += (int) ($item['qty'] ?? 0);
    }

    $free_ship_target = 200000;
    $progress = $free_ship_target > 0 ? min(100, round(($subtotal / $free_ship_target) * 100)) : 0;
    $remaining = max(0, $free_ship_target - $subtotal);

    return [
        'subtotal' => $subtotal,
        'subtotal_html' => vs_commerce_format_price($subtotal),
        'count' => $count,
        'free_ship_target' => $free_ship_target,
        'progress' => $progress,
        'remaining' => $remaining,
        'remaining_html' => vs_commerce_format_price($remaining),
        'is_free_ship' => $subtotal >= $free_ship_target,
    ];
}

function vs_commerce_get_drawer_html()
{
    $items = vs_commerce_get_cart_items();
    $totals = vs_commerce_get_cart_totals($items);

    ob_start();
    ?>
    <div class="vs-cart">
        <div class="vs-cart__topbar">
            <span>FREESHIP CHO ĐƠN 200K - ĐỔI TRẢ MIỄN PHÍ 60 NGÀY</span>
            <button type="button" class="vs-cart__topbar-close js-vs-cart-close">×</button>
        </div>

        <div class="vs-cart__header">
            <div class="vs-cart__breadcrumb">Trang chủ / <span>Giỏ hàng (<?php echo esc_html($totals['count']); ?>)</span></div>
            <h2 class="vs-cart__title">Giỏ hàng</h2>
        </div>

        <div class="vs-cart__progress">
            <div class="vs-cart__progress-bar"><span style="width: <?php echo esc_attr($totals['progress']); ?>%;"></span></div>
            <strong><?php echo esc_html($totals['progress']); ?>%</strong>
        </div>

        <div class="vs-cart__progress-text">
            <?php if ($totals['is_free_ship']) : ?>
                Chúc mừng! Đơn hàng của bạn đã được Freeship 🎉
            <?php else : ?>
                Bạn chỉ cần mua thêm <strong><?php echo esc_html($totals['remaining_html']); ?></strong> để được Freeship
            <?php endif; ?>
        </div>

        <div class="vs-cart__body">
            <?php if (!empty($items)) : ?>
                <div class="vs-cart__items">
                    <?php foreach ($items as $item) : $line_total = ((float)$item['price']) * ((int)$item['qty']); ?>
                        <div class="vs-cart-item" data-key="<?php echo esc_attr($item['key']); ?>">
                            <button type="button" class="vs-cart-item__remove js-vs-cart-remove" data-key="<?php echo esc_attr($item['key']); ?>">×</button>
                            <a href="<?php echo esc_url($item['link']); ?>" class="vs-cart-item__thumb"><?php if ($item['image']) : ?><img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>"><?php endif; ?></a>
                            <div class="vs-cart-item__content">
                                <a href="<?php echo esc_url($item['link']); ?>" class="vs-cart-item__title"><?php echo esc_html($item['title']); ?></a>
                                <div class="vs-cart-item__meta"><?php echo esc_html($item['color']); ?><?php echo $item['size'] ? ' / ' . esc_html($item['size']) : ''; ?></div>
                                <div class="vs-cart-item__bottom">
                                    <div class="vs-cart-item__price"><?php echo esc_html(vs_commerce_format_price($line_total)); ?></div>
                                    <div class="vs-cart-item__qty">
                                        <button type="button" class="vs-cart-item__qty-btn js-vs-cart-qty-minus" data-key="<?php echo esc_attr($item['key']); ?>">−</button>
                                        <span class="vs-cart-item__qty-value"><?php echo esc_html($item['qty']); ?></span>
                                        <button type="button" class="vs-cart-item__qty-btn js-vs-cart-qty-plus" data-key="<?php echo esc_attr($item['key']); ?>">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="vs-cart__empty">Giỏ hàng của bạn đang trống.</div>
            <?php endif; ?>
        </div>

        <div class="vs-cart__footer">
            <div class="vs-cart__subtotal"><span>Tạm tính</span><strong><?php echo esc_html($totals['subtotal_html']); ?></strong></div>
            <a href="<?php echo esc_url(home_url('/checkout/')); ?>" class="vs-cart__checkout">Thanh Toán</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function vs_commerce_render_cart_response($items = null)
{
    wp_send_json_success(vs_commerce_get_cart_response_data($items));
}

add_action('wp_ajax_vs_cart_get', 'vs_commerce_ajax_cart_get');
add_action('wp_ajax_nopriv_vs_cart_get', 'vs_commerce_ajax_cart_get');
function vs_commerce_ajax_cart_get()
{
    check_ajax_referer('vs_commerce_nonce', 'nonce');
    vs_commerce_render_cart_response();
}

add_action('wp_ajax_vs_cart_add', 'vs_commerce_ajax_cart_add');
add_action('wp_ajax_nopriv_vs_cart_add', 'vs_commerce_ajax_cart_add');
function vs_commerce_ajax_cart_add()
{
    check_ajax_referer('vs_commerce_nonce', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    $color = sanitize_text_field(wp_unslash($_POST['color'] ?? ''));
    $size = sanitize_text_field(wp_unslash($_POST['size'] ?? ''));
    $qty = max(1, absint($_POST['qty'] ?? 1));

    $new_item = vs_commerce_build_cart_item($product_id, $color, $size, $qty);
    if (!$new_item) {
        wp_send_json_error(['message' => 'Không thể thêm sản phẩm vào giỏ hàng.'], 422);
    }

    $items = vs_commerce_get_cart_items();
    $found = false;
    foreach ($items as &$item) {
        if ($item['key'] === $new_item['key']) {
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $items[] = $new_item;
    }

    vs_commerce_save_cart_items($items);
    vs_commerce_render_cart_response();
}

add_action('wp_ajax_vs_cart_update_qty', 'vs_commerce_ajax_cart_update_qty');
add_action('wp_ajax_nopriv_vs_cart_update_qty', 'vs_commerce_ajax_cart_update_qty');
function vs_commerce_ajax_cart_update_qty()
{
    check_ajax_referer('vs_commerce_nonce', 'nonce');

    $key = sanitize_text_field(wp_unslash($_POST['key'] ?? ''));
    $qty = max(0, absint($_POST['qty'] ?? 1));
    $items = vs_commerce_get_cart_items();

    foreach ($items as $index => $item) {
        if ($item['key'] === $key) {
            if ($qty <= 0) {
                unset($items[$index]);
            } else {
                $items[$index]['qty'] = $qty;
            }
            break;
        }
    }

    vs_commerce_save_cart_items($items);
    vs_commerce_render_cart_response();
}

add_action('wp_ajax_vs_cart_remove', 'vs_commerce_ajax_cart_remove');
add_action('wp_ajax_nopriv_vs_cart_remove', 'vs_commerce_ajax_cart_remove');
function vs_commerce_ajax_cart_remove()
{
    check_ajax_referer('vs_commerce_nonce', 'nonce');

    $key = sanitize_text_field(wp_unslash($_POST['key'] ?? ''));
    $items = vs_commerce_get_cart_items();
    foreach ($items as $index => $item) {
        if ($item['key'] === $key) {
            unset($items[$index]);
            break;
        }
    }

    vs_commerce_save_cart_items($items);
    vs_commerce_render_cart_response();
}

function vs_commerce_cart_count()
{
    $totals = vs_commerce_get_cart_totals();
    return (int) ($totals['count'] ?? 0);
}
