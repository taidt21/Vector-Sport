<?php
if (!defined('ABSPATH')) {
    exit;
}

// CHECKOUT PAGE HELPERS
// =========================================================
function vs_commerce_render_checkout_cart_html()
{
    $items = vs_commerce_get_cart_items();
    if (empty($items)) {
        return '<div class="vs-checkout-cart__empty">Giỏ hàng của bạn đang trống.</div>';
    }

    ob_start();
    foreach ($items as $item) {
        $title = (string) ($item['title'] ?? 'Sản phẩm');
        $link = (string) ($item['link'] ?? '#');
        $image = (string) ($item['image'] ?? '');
        $qty = max(1, (int) ($item['qty'] ?? 1));
        $price = (float) ($item['price'] ?? 0);
        $line_total = $qty * $price;
        $color = (string) ($item['color'] ?? '');
        $size = (string) ($item['size'] ?? '');
        ?>
        <article class="vs-checkout-cart-item" data-key="<?php echo esc_attr($item['key'] ?? ''); ?>">
            <a href="<?php echo esc_url($link); ?>" class="vs-checkout-cart-item__thumb"><?php if ($image) : ?><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>"><?php endif; ?></a>
            <div class="vs-checkout-cart-item__content">
                <div class="vs-checkout-cart-item__head">
                    <a href="<?php echo esc_url($link); ?>" class="vs-checkout-cart-item__title"><?php echo esc_html($title); ?></a>
                    <button type="button" class="vs-checkout-cart-item__remove js-checkout-remove" data-key="<?php echo esc_attr($item['key'] ?? ''); ?>">×</button>
                </div>
                <div class="vs-checkout-cart-item__variant"><?php echo esc_html($color ?: 'Mặc định'); ?><?php echo $size ? ' / ' . esc_html($size) : ''; ?></div>
                <div class="vs-checkout-cart-item__bottom">
                    <div class="vs-checkout-cart-item__price"><?php echo esc_html(vs_commerce_format_price($line_total)); ?></div>
                    <div class="vs-checkout-cart-item__qty">
                        <button type="button" class="vs-checkout-cart-item__qty-btn js-checkout-qty-minus" data-key="<?php echo esc_attr($item['key'] ?? ''); ?>">−</button>
                        <span class="vs-checkout-cart-item__qty-value"><?php echo esc_html($qty); ?></span>
                        <button type="button" class="vs-checkout-cart-item__qty-btn js-checkout-qty-plus" data-key="<?php echo esc_attr($item['key'] ?? ''); ?>">+</button>
                    </div>
                </div>
            </div>
        </article>
        <?php
    }
    return ob_get_clean();
}
