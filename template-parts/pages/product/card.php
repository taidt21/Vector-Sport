<?php
/**
 * Product card template.
 * Chỉ render dữ liệu được truyền từ archive-product.php hoặc AJAX renderer.
 */

defined('ABSPATH') || exit;

$product_id = isset($args['product_id']) ? (int) $args['product_id'] : 0;
$product_title = isset($args['product_title']) ? (string) $args['product_title'] : '';
$product_url = isset($args['product_url']) ? (string) $args['product_url'] : '#';
$product_image = isset($args['product_image']) ? (string) $args['product_image'] : '';
$product_price = isset($args['product_price']) ? (int) $args['product_price'] : 0;
$product_old_price = isset($args['product_old_price']) ? (int) $args['product_old_price'] : 0;
$product_code = isset($args['product_code']) ? (string) $args['product_code'] : '';
$product_badge = isset($args['product_badge']) ? (string) $args['product_badge'] : '';
$product_colors = !empty($args['product_colors']) && is_array($args['product_colors']) ? $args['product_colors'] : array();

if ($product_image === '' && !empty($product_colors[0]['image'])) {
  $product_image = (string) $product_colors[0]['image'];
}

if ($product_image === '') {
  $product_image = get_template_directory_uri() . '/assets/icon/logo-main.svg';
}

$discount_percent = 0;
if ($product_old_price > 0 && $product_price > 0 && $product_old_price > $product_price) {
  $discount_percent = (int) round((($product_old_price - $product_price) / $product_old_price) * 100);
}
?>

<article class="product_card"<?php echo $product_id ? ' data-product-id="' . esc_attr($product_id) . '"' : ''; ?>>
  <a href="<?php echo esc_url($product_url); ?>" class="product_card__link">
    <div class="product_card__media">
      <?php if ($product_badge !== '') : ?>
        <span class="product_card__badge"><?php echo esc_html($product_badge); ?></span>
      <?php endif; ?>

      <div class="product_card__imgwrap">
        <img
          class="product_card__img js-product-card-image"
          src="<?php echo esc_url($product_image); ?>"
          data-default-image="<?php echo esc_url($product_image); ?>"
          alt="<?php echo esc_attr($product_title); ?>"
          loading="lazy"
          decoding="async"
        />
      </div>
    </div>
  </a>

  <div class="product_card__content">
    <h3 class="product_card__name">
      <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product_title); ?></a>
    </h3>

    <?php if ($product_price > 0) : ?>
      <div class="product_card__price_current"><?php echo esc_html(number_format_i18n($product_price)); ?>đ</div>
      <div class="product_card__price_meta">
        <?php if ($product_old_price > 0) : ?>
          <span class="product_card__price_old"><?php echo esc_html(number_format_i18n($product_old_price)); ?>đ</span>
        <?php endif; ?>
        <?php if ($discount_percent > 0) : ?>
          <span class="product_card__discount">-<?php echo esc_html($discount_percent); ?>%</span>
        <?php endif; ?>
      </div>
    <?php else : ?>
      <div class="product_card__price_current">Liên hệ</div>
    <?php endif; ?>

    <?php if (!empty($product_colors)) : ?>
      <div class="product_card__swatches" aria-label="Màu sắc sản phẩm">
        <?php foreach ($product_colors as $index => $color) : ?>
          <?php
          $color_hex = !empty($color['hex']) ? (string) $color['hex'] : '#d8d8d8';
          $color_label = !empty($color['label']) ? (string) $color['label'] : 'Màu sản phẩm';
          $color_image = !empty($color['image']) ? (string) $color['image'] : $product_image;
          ?>
          <button
            type="button"
            class="product_card__swatch js-product-card-color <?php echo $index === 0 ? 'is-active' : ''; ?>"
            style="--swatch-color: <?php echo esc_attr($color_hex); ?>;"
            data-image="<?php echo esc_url($color_image); ?>"
            data-color-value="<?php echo esc_attr($color['value'] ?? ''); ?>"
            aria-label="<?php echo esc_attr($color_label); ?>"
            aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"
          ></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</article>
