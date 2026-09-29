<?php
/**
 * Shared product archive content.
 * Dùng chung cho archive-product.php và taxonomy-product_category.php.
 */

defined('ABSPATH') || exit;

$post_type = 'product';
$card_template = 'template-parts/pages/product/card';
$per_page = function_exists('vs_pa_get_products_per_page') ? vs_pa_get_products_per_page() : 16;

$category_taxonomy = function_exists('vs_pa_get_category_taxonomy')
  ? vs_pa_get_category_taxonomy($post_type)
  : '';

$current_term = function_exists('vs_pa_get_current_category_term')
  ? vs_pa_get_current_category_term($category_taxonomy)
  : null;

$current_category_id = ($current_term && !is_wp_error($current_term)) ? (int) $current_term->term_id : 0;
$page_title = ($current_term && !is_wp_error($current_term) && !empty($current_term->name))
  ? $current_term->name
  : post_type_archive_title('', false);
$page_title = $page_title ?: 'Sản phẩm';

$paged = (int) get_query_var('paged');
if ($paged < 1 && isset($_GET['paged'])) {
  $paged = (int) wp_unslash($_GET['paged']);
}
if ($paged < 1 && !empty($_SERVER['REQUEST_URI'])) {
  if (preg_match('#/page-(\d+)/?$#', wp_unslash($_SERVER['REQUEST_URI']), $matches)) {
    $paged = (int) $matches[1];
  }
}
$paged = max(1, $paged);

$selected_colors = isset($_GET['colors']) && function_exists('vs_pa_normalize_string_array')
  ? vs_pa_normalize_string_array(wp_unslash($_GET['colors']))
  : array();

$selected_prices = isset($_GET['prices']) && function_exists('vs_pa_normalize_string_array')
  ? vs_pa_normalize_string_array(wp_unslash($_GET['prices']))
  : array();

$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$price_ranges = function_exists('vs_pa_get_price_ranges') ? vs_pa_get_price_ranges() : array();
$archive_url = function_exists('vs_pa_get_archive_url')
  ? vs_pa_get_archive_url($post_type, $category_taxonomy, $current_category_id)
  : get_post_type_archive_link($post_type);

$filter_state = array(
  'post_type' => $post_type,
  'category_taxonomy' => $category_taxonomy,
  'current_category_id' => $current_category_id,
  's' => $search,
  'colors' => $selected_colors,
  'prices' => $selected_prices,
);

$matched_ids = function_exists('vs_pa_get_filtered_post_ids')
  ? vs_pa_get_filtered_post_ids($filter_state)
  : array();

$paged_ids = function_exists('vs_pa_paginate_ids')
  ? vs_pa_paginate_ids($matched_ids, $paged, $per_page)
  : array();

$available_colors = function_exists('vs_pa_get_available_colors')
  ? vs_pa_get_available_colors(array(
      'post_type' => $post_type,
      'category_taxonomy' => $category_taxonomy,
      'current_category_id' => $current_category_id,
      's' => $search,
    ))
  : array();

$category_terms = $category_taxonomy
  ? get_terms(array(
      'taxonomy' => $category_taxonomy,
      'hide_empty' => false,
      'parent' => 0,
      'orderby' => 'menu_order name',
      'order' => 'ASC',
    ))
  : array();

$toolbar_category_terms = $category_terms;
$toolbar_all_products_url = get_post_type_archive_link($post_type);
?>

<main class="product_archive js-product-archive" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
    data-nonce="<?php echo esc_attr(wp_create_nonce('vs_product_archive_nonce')); ?>"
    data-post-type="<?php echo esc_attr($post_type); ?>"
    data-category-taxonomy="<?php echo esc_attr($category_taxonomy); ?>"
    data-current-category-id="<?php echo esc_attr($current_category_id); ?>"
    data-archive-url="<?php echo esc_url($archive_url); ?>">
    <div class="container">
        <nav class="product_archive__breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a>
            <span>/</span>
            <span><?php echo esc_html($page_title); ?></span>
        </nav>

        <div class="product_archive__header">
            <h1 class="product_archive__title"><?php echo esc_html($page_title); ?></h1>
        </div>

        <?php if (!empty($category_terms) && !is_wp_error($category_terms)) : ?>
        <nav class="product_archive__categories" aria-label="Danh mục sản phẩm">
            <a class="product_archive__category <?php echo !$current_category_id ? 'is-active' : ''; ?>"
                href="<?php echo esc_url($toolbar_all_products_url); ?>">Tất cả</a>
            <?php foreach ($category_terms as $term) : ?>
            <?php $term_link = get_term_link($term); ?>
            <?php if (is_wp_error($term_link)) continue; ?>
            <a class="product_archive__category <?php echo $current_category_id === (int) $term->term_id ? 'is-active' : ''; ?>"
                href="<?php echo esc_url($term_link); ?>"><?php echo esc_html($term->name); ?></a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <div class="product_archive__toolbar">
            <div class="product_archive__toolbar_left">
                <button type="button" class="product_archive__filter_toggle js-product-filter-open"
                    aria-controls="productFilterSidebar" aria-expanded="false">
                    <span class="product_archive__filter_icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M3 5H21L14 13V20L10 18V13L3 5Z" stroke="currentColor" stroke-width="1.8"
                                stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span>Lọc</span>
                </button>

                <?php if (!empty($toolbar_category_terms) && !is_wp_error($toolbar_category_terms)) : ?>
                <div class="product_archive__category_select_wrap">
                    <label class="screen-reader-text" for="productArchiveCategorySelect">Chọn danh mục sản phẩm</label>
                    <select id="productArchiveCategorySelect" class="product_archive__category_select"
                        onchange="if(this.value){window.location.href=this.value;}">
                        <option value="<?php echo esc_url($toolbar_all_products_url); ?>" <?php selected($current_category_id, 0); ?>>
                            Tất cả danh mục
                        </option>
                        <?php foreach ($toolbar_category_terms as $term) : ?>
                        <?php $term_link = get_term_link($term); ?>
                        <?php if (is_wp_error($term_link)) continue; ?>
                        <option value="<?php echo esc_url($term_link); ?>"
                            <?php selected($current_category_id, (int) $term->term_id); ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="product_archive__summary js-product-summary"><?php echo esc_html(count($matched_ids)); ?> sản phẩm</div>
        </div>

        <div class="product_archive__layout">
            <aside class="product_filter js-product-filter" id="productFilterSidebar" aria-hidden="true">
                <div class="product_filter__overlay js-product-filter-close"></div>

                <div class="product_filter__panel">
                    <div class="product_filter__head">
                        <button type="button" class="product_filter__back js-product-filter-close" aria-label="Đóng bộ lọc">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M15 5L8 12L15 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div class="product_filter__title">Tìm theo</div>
                    </div>

                    <form class="product_filter__form js-product-filter-form"
                        action="<?php echo esc_url($archive_url ?: home_url('/')); ?>" method="get">
                        <?php if (!empty($search)) : ?>
                        <input type="hidden" name="s" value="<?php echo esc_attr($search); ?>" />
                        <?php endif; ?>

                        <?php if (!empty($available_colors)) : ?>
                        <section class="product_filter__section">
                            <h2 class="product_filter__section_title">Màu sắc</h2>
                            <div class="product_filter__options product_filter__options--colors">
                                <?php foreach ($available_colors as $index => $color) : ?>
                                <label class="product_filter__color_option <?php echo $index > 4 ? 'is-extra' : ''; ?>">
                                    <input type="checkbox" name="colors[]" value="<?php echo esc_attr($color['value']); ?>"
                                        <?php checked(in_array($color['value'], $selected_colors, true)); ?> />
                                    <span class="product_filter__color_swatch"
                                        style="--swatch-color: <?php echo esc_attr($color['hex']); ?>;"></span>
                                    <span class="product_filter__color_label"><?php echo esc_html($color['label']); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($available_colors) > 5) : ?>
                            <button type="button" class="product_filter__more js-product-filter-more">Xem thêm</button>
                            <?php endif; ?>
                        </section>
                        <?php endif; ?>

                        <?php if (!empty($price_ranges)) : ?>
                        <section class="product_filter__section">
                            <h2 class="product_filter__section_title">Mức giá</h2>
                            <div class="product_filter__options product_filter__options--price">
                                <?php foreach ($price_ranges as $key => $range) : ?>
                                <label class="product_filter__check_option">
                                    <input type="checkbox" name="prices[]" value="<?php echo esc_attr($key); ?>"
                                        <?php checked(in_array($key, $selected_prices, true)); ?> />
                                    <span class="product_filter__check_box"></span>
                                    <span class="product_filter__check_label"><?php echo esc_html($range['label']); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </section>
                        <?php endif; ?>

                        <div class="product_filter__actions">
                            <button type="button" class="product_filter__reset js-product-filter-reset">Xóa lọc</button>
                            <button type="button" class="product_filter__apply js-product-filter-close">Xem sản phẩm</button>
                        </div>
                    </form>
                </div>
            </aside>

            <section class="product_archive__content">
                <div class="product_archive__results js-product-results">
                    <div class="product_grid js-product-grid">
                        <?php if (!empty($paged_ids)) : ?>
                        <?php foreach ($paged_ids as $post_id) : ?>
                        <?php
              $card_args = function_exists('vs_pa_build_product_card_data')
                ? vs_pa_build_product_card_data($post_id)
                : array();

              if (!empty($card_args)) {
                get_template_part($card_template, null, $card_args);
              }
              ?>
                        <?php endforeach; ?>
                        <?php else : ?>
                        <div class="product_empty">
                            <h2>Không tìm thấy sản phẩm phù hợp</h2>
                            <p>Hãy thử bỏ bớt điều kiện lọc hoặc chuyển sang danh mục khác.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="js-product-pagination-wrap">
                        <?php if (function_exists('vs_pa_render_pagination')) : ?>
                        <?php echo wp_kses_post(vs_pa_render_pagination(array(
                'current_page' => $paged,
                'total_items' => count($matched_ids),
                'per_page' => $per_page,
                'base_url' => $archive_url,
                'colors' => $selected_colors,
                'prices' => $selected_prices,
                's' => $search,
              ))); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
