<?php
/**
 * Product archive helpers + AJAX.
 *
 * Hướng final:
 * - Danh mục dùng taxonomy URL riêng, ví dụ /product_category/vovinam/
 * - Archive tổng và taxonomy archive dùng chung cùng 1 partial giao diện
 * - Chỉ còn 1 nơi cấu hình per_page
 */

defined('ABSPATH') || exit;

if (!function_exists('vs_pa_get_products_per_page')) {
  function vs_pa_get_products_per_page() {
    return (int) apply_filters('vs_pa_products_per_page', 12);
  }
}

if (!function_exists('vs_pa_get_category_taxonomy')) {
  function vs_pa_get_category_taxonomy($post_type = 'product') {
    $preferred = array('product_category', 'product_cat', 'category');
    $taxonomies = get_object_taxonomies($post_type, 'objects');

    foreach ($preferred as $taxonomy) {
      if (!empty($taxonomies[$taxonomy]) && !empty($taxonomies[$taxonomy]->hierarchical)) {
        return $taxonomy;
      }
    }

    foreach ($taxonomies as $taxonomy => $object) {
      if (!empty($object->hierarchical)) {
        return $taxonomy;
      }
    }

    return '';
  }
}

if (!function_exists('vs_pa_get_current_category_term')) {
  function vs_pa_get_current_category_term($category_taxonomy) {
    if ($category_taxonomy && is_tax($category_taxonomy)) {
      $term = get_queried_object();
      if ($term && !is_wp_error($term)) {
        return $term;
      }
    }

    return null;
  }
}

if (!function_exists('vs_pa_normalize_string_array')) {
  function vs_pa_normalize_string_array($values) {
    if (!is_array($values)) {
      $values = array($values);
    }

    $values = array_map(function ($value) {
      return sanitize_text_field(wp_unslash($value));
    }, $values);

    $values = array_filter($values, function ($value) {
      return $value !== '';
    });

    return array_values(array_unique($values));
  }
}

if (!function_exists('vs_pa_get_price_ranges')) {
  function vs_pa_get_price_ranges() {
    return array(
      'under-50000' => array('label' => 'Giá dưới 50,000đ', 'min' => 0, 'max' => 49999),
      '50000-100000' => array('label' => '50,000đ - 100,000đ', 'min' => 50000, 'max' => 100000),
      '100000-200000' => array('label' => '100,000đ - 200,000đ', 'min' => 100000, 'max' => 200000),
      '200000-500000' => array('label' => '200,000đ - 500,000đ', 'min' => 200000, 'max' => 500000),
      'over-1000000' => array('label' => 'Giá trên 1,000,000đ', 'min' => 1000000, 'max' => 0),
    );
  }
}

if (!function_exists('vs_pa_get_archive_url')) {
  function vs_pa_get_archive_url($post_type = 'product', $category_taxonomy = '', $current_category_id = 0) {
    if ($category_taxonomy && $current_category_id > 0) {
      $link = get_term_link((int) $current_category_id, $category_taxonomy);
      if (!is_wp_error($link) && !empty($link)) {
        return $link;
      }
    }

    return get_post_type_archive_link($post_type);
  }
}


if (!function_exists('vs_pa_get_image_url')) {
  function vs_pa_get_image_url($image, $size = 'large') {
    if (empty($image)) {
      return '';
    }

    if (is_array($image) && !empty($image['url'])) {
      return (string) $image['url'];
    }

    if (is_numeric($image)) {
      $url = wp_get_attachment_image_url((int) $image, $size);
      return $url ? (string) $url : '';
    }

    if (is_string($image)) {
      return $image;
    }

    return '';
  }
}

if (!function_exists('vs_pa_get_color_hex')) {
  function vs_pa_get_color_hex($row) {
    $row = is_array($row) ? $row : array();
    $hex = isset($row['mau_sac']) ? trim((string) $row['mau_sac']) : '';

    return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $hex) ? $hex : '';
  }
}

if (!function_exists('vs_pa_get_color_label')) {
  function vs_pa_get_color_label($row) {
    $row = is_array($row) ? $row : array();
    $label = isset($row['ten_mau_sac']) ? trim((string) $row['ten_mau_sac']) : '';

    return $label !== '' ? $label : 'Màu sản phẩm';
  }
}

if (!function_exists('vs_pa_get_color_image')) {
  function vs_pa_get_color_image($row) {
    $row = is_array($row) ? $row : array();

    if (!empty($row['anh_dai_dien'])) {
      $image_url = vs_pa_get_image_url($row['anh_dai_dien']);
      if ($image_url !== '') {
        return $image_url;
      }
    }

    if (!empty($row['danh_sach_anh_san_pham']) && is_array($row['danh_sach_anh_san_pham'])) {
      foreach ($row['danh_sach_anh_san_pham'] as $image) {
        $image_url = vs_pa_get_image_url($image);
        if ($image_url !== '') {
          return $image_url;
        }
      }
    }

    return '';
  }
}

if (!function_exists('vs_pa_get_base_post_ids')) {
  function vs_pa_get_base_post_ids($state = array()) {
    $post_type = sanitize_key(isset($state['post_type']) ? $state['post_type'] : 'product');
    $category_taxonomy = sanitize_key(isset($state['category_taxonomy']) ? $state['category_taxonomy'] : vs_pa_get_category_taxonomy($post_type));
    $current_category_id = isset($state['current_category_id']) ? (int) $state['current_category_id'] : 0;
    $search = isset($state['s']) ? sanitize_text_field(wp_unslash($state['s'])) : '';

    $args = array(
      'post_type' => $post_type,
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'fields' => 'ids',
      'ignore_sticky_posts' => true,
      'orderby' => 'date',
      'order' => 'DESC',
      's' => $search,
    );

    if ($category_taxonomy && $current_category_id > 0) {
      $args['tax_query'] = array(
        array(
          'taxonomy' => $category_taxonomy,
          'field' => 'term_id',
          'terms' => array($current_category_id),
        ),
      );
    }

    $query = new WP_Query($args);

    return !empty($query->posts) ? array_map('intval', $query->posts) : array();
  }
}

if (!function_exists('vs_pa_price_matches')) {
  function vs_pa_price_matches($price, $selected_prices) {
    $selected_prices = vs_pa_normalize_string_array($selected_prices);
    if (empty($selected_prices)) {
      return true;
    }

    $ranges = vs_pa_get_price_ranges();

    foreach ($selected_prices as $range_key) {
      if (empty($ranges[$range_key])) {
        continue;
      }

      $min = (int) $ranges[$range_key]['min'];
      $max = (int) $ranges[$range_key]['max'];

      if ($max > 0 && $price >= $min && $price <= $max) {
        return true;
      }

      if ($max === 0 && $price >= $min) {
        return true;
      }
    }

    return false;
  }
}

if (!function_exists('vs_pa_get_product_color_values')) {
  function vs_pa_get_product_color_values($fields) {
    $fields = is_array($fields) ? $fields : array();
    $rows = isset($fields['cac_mau_sac']) && is_array($fields['cac_mau_sac']) ? $fields['cac_mau_sac'] : array();
    $values = array();

    foreach ($rows as $row) {
      $hex = vs_pa_get_color_hex($row);
      if ($hex !== '') {
        $values[] = $hex;
      }
    }

    return array_values(array_unique($values));
  }
}

if (!function_exists('vs_pa_get_available_colors')) {
  function vs_pa_get_available_colors($state = array()) {
    $post_ids = vs_pa_get_base_post_ids($state);
    $colors = array();

    foreach ($post_ids as $post_id) {
      $fields = function_exists('get_fields') ? get_fields($post_id) : array();
      $fields = is_array($fields) ? $fields : array();
      $rows = isset($fields['cac_mau_sac']) && is_array($fields['cac_mau_sac']) ? $fields['cac_mau_sac'] : array();

      foreach ($rows as $row) {
        $hex = vs_pa_get_color_hex($row);
        $label = vs_pa_get_color_label($row);
        if ($hex === '') {
          continue;
        }

        if (!isset($colors[$hex])) {
          $colors[$hex] = array(
            'value' => $hex,
            'label' => $label,
            'hex' => $hex,
          );
        }
      }
    }

    return array_values($colors);
  }
}

if (!function_exists('vs_pa_get_filtered_post_ids')) {
  function vs_pa_get_filtered_post_ids($state = array()) {
    $selected_colors = vs_pa_normalize_string_array(isset($state['colors']) ? $state['colors'] : array());
    $selected_prices = vs_pa_normalize_string_array(isset($state['prices']) ? $state['prices'] : array());
    $post_ids = vs_pa_get_base_post_ids($state);
    $matched = array();

    foreach ($post_ids as $post_id) {
      $fields = function_exists('get_fields') ? get_fields($post_id) : array();
      $fields = is_array($fields) ? $fields : array();
      $price = isset($fields['gia_tien']) ? (int) $fields['gia_tien'] : 0;
      $product_colors = vs_pa_get_product_color_values($fields);

      $match_color = empty($selected_colors) || !empty(array_intersect($selected_colors, $product_colors));
      $match_price = vs_pa_price_matches($price, $selected_prices);

      if ($match_color && $match_price) {
        $matched[] = (int) $post_id;
      }
    }

    return $matched;
  }
}

if (!function_exists('vs_pa_paginate_ids')) {
  function vs_pa_paginate_ids($post_ids, $paged = 1, $per_page = null) {
    $paged = max(1, (int) $paged);
    $per_page = $per_page === null ? vs_pa_get_products_per_page() : (int) $per_page;
    $per_page = max(1, $per_page);
    $offset = ($paged - 1) * $per_page;

    return array_slice(array_values($post_ids), $offset, $per_page);
  }
}

if (!function_exists('vs_pa_render_pagination')) {
  function vs_pa_render_pagination($args = array()) {
    $current_page = max(1, (int) ($args['current_page'] ?? 1));
    $total_items = max(0, (int) ($args['total_items'] ?? 0));
    $per_page = isset($args['per_page']) ? (int) $args['per_page'] : vs_pa_get_products_per_page();
    $per_page = max(1, $per_page);
    $base_url = !empty($args['base_url']) ? $args['base_url'] : home_url('/');
    $colors = vs_pa_normalize_string_array($args['colors'] ?? array());
    $prices = vs_pa_normalize_string_array($args['prices'] ?? array());
    $search = !empty($args['s']) ? sanitize_text_field($args['s']) : '';

    $total_pages = (int) ceil($total_items / $per_page);
    if ($total_pages <= 1) {
      return '';
    }

    $add_args = array();
    if ($search !== '') {
      $add_args['s'] = $search;
    }
    if (!empty($colors)) {
      $add_args['colors'] = $colors;
    }
    if (!empty($prices)) {
      $add_args['prices'] = $prices;
    }

    $pretty = (bool) get_option('permalink_structure');
    $base = $pretty ? trailingslashit($base_url) . '%_%' : add_query_arg('paged', '%#%', $base_url);
    $format = $pretty ? 'page-%#%/' : '';

    $html = paginate_links(array(
      'base' => $base,
      'format' => $format,
      'current' => $current_page,
      'total' => $total_pages,
      'mid_size' => 1,
      'prev_text' => '&lsaquo;',
      'next_text' => '&rsaquo;',
      'type' => 'list',
      'add_args' => $add_args,
    ));

    if (!$html) {
      return '';
    }

    return '<nav class="product_pagination" aria-label="Phân trang sản phẩm">' . $html . '</nav>';
  }
}

if (!function_exists('vs_pa_get_product_badge')) {
  function vs_pa_get_product_badge($post_id) {
    $badge_terms = get_the_terms((int) $post_id, 'tag_san_pham');

    if (!empty($badge_terms) && !is_wp_error($badge_terms)) {
      return (string) $badge_terms[0]->name;
    }

    return '';
  }
}

if (!function_exists('vs_pa_build_product_card_data')) {
  function vs_pa_build_product_card_data($post_id) {
    $post_id = (int) $post_id;
    $fields = function_exists('get_fields') ? get_fields($post_id) : array();
    $fields = is_array($fields) ? $fields : array();

    $product_title = get_the_title($post_id);
    $product_url = get_permalink($post_id);
    $product_image = get_the_post_thumbnail_url($post_id, 'large');
    $product_price = isset($fields['gia_tien']) ? (int) $fields['gia_tien'] : 0;
    $product_old_price = isset($fields['gia_cu']) ? (int) $fields['gia_cu'] : 0;
    $product_code = isset($fields['ma_san_pham']) ? (string) $fields['ma_san_pham'] : '';
    $product_badge = vs_pa_get_product_badge($post_id);
    $product_colors = array();

    $color_rows = isset($fields['cac_mau_sac']) && is_array($fields['cac_mau_sac']) ? $fields['cac_mau_sac'] : array();
    foreach ($color_rows as $row) {
      $color_hex = vs_pa_get_color_hex($row);
      if ($color_hex === '') {
        continue;
      }

      $product_colors[] = array(
        'value' => $color_hex,
        'label' => vs_pa_get_color_label($row),
        'hex' => $color_hex,
        'image' => vs_pa_get_color_image($row),
      );
    }

    if (!$product_image && !empty($product_colors[0]['image'])) {
      $product_image = $product_colors[0]['image'];
    }

    return array(
      'product_id' => $post_id,
      'product_title' => $product_title,
      'product_url' => $product_url,
      'product_image' => $product_image,
      'product_price' => $product_price,
      'product_old_price' => $product_old_price,
      'product_code' => $product_code,
      'product_badge' => $product_badge,
      'product_colors' => $product_colors,
    );
  }
}

if (!function_exists('vs_pa_render_cards_html')) {
  function vs_pa_render_cards_html($post_ids) {
    $post_ids = array_values(array_map('intval', (array) $post_ids));

    if (empty($post_ids)) {
      return '<div class="product_empty"><h2>Không tìm thấy sản phẩm phù hợp</h2><p>Hãy thử bỏ bớt điều kiện lọc hoặc chuyển sang danh mục khác.</p></div>';
    }

    ob_start();
    foreach ($post_ids as $post_id) {
      get_template_part('template-parts/pages/product/card', null, vs_pa_build_product_card_data($post_id));
    }

    return ob_get_clean();
  }
}

if (!function_exists('vs_pa_register_page_dash_rewrite_rules')) {
  function vs_pa_register_page_dash_rewrite_rules() {
    $post_type = 'product';
    $archive_link = get_post_type_archive_link($post_type);

    if ($archive_link) {
      $archive_path = trim((string) wp_parse_url($archive_link, PHP_URL_PATH), '/');
      if ($archive_path !== '') {
        add_rewrite_rule(
          '^' . preg_quote($archive_path, '#') . '/page-([0-9]+)/?$',
          'index.php?post_type=' . $post_type . '&paged=$matches[1]',
          'top'
        );
      }
    }

    $taxonomies = get_object_taxonomies($post_type, 'objects');
    foreach ($taxonomies as $taxonomy => $taxonomy_object) {
      if (empty($taxonomy_object->rewrite['slug'])) {
        continue;
      }

      $taxonomy_slug = trim((string) $taxonomy_object->rewrite['slug'], '/');
      if ($taxonomy_slug === '') {
        continue;
      }

      $query_var = !empty($taxonomy_object->query_var) ? $taxonomy_object->query_var : $taxonomy;
      $term_pattern = !empty($taxonomy_object->rewrite['hierarchical']) ? '(.+?)' : '([^/]+)';

      add_rewrite_rule(
        '^' . preg_quote($taxonomy_slug, '#') . '/' . $term_pattern . '/page-([0-9]+)/?$',
        'index.php?' . $query_var . '=$matches[1]&paged=$matches[2]',
        'top'
      );
    }
  }
}
add_action('init', 'vs_pa_register_page_dash_rewrite_rules', 20);

if (!function_exists('vs_pa_flush_page_dash_rewrite_once')) {
  function vs_pa_flush_page_dash_rewrite_once() {
    $version_key = 'vs_pa_page_dash_rewrite_version';
    $version = 'final_taxonomy_v1';

    if (get_option($version_key) !== $version) {
      flush_rewrite_rules(false);
      update_option($version_key, $version);
    }
  }
}
add_action('admin_init', 'vs_pa_flush_page_dash_rewrite_once');

if (!function_exists('vs_pa_sync_main_query_posts_per_page')) {
  function vs_pa_sync_main_query_posts_per_page($query) {
    if (!($query instanceof WP_Query) || is_admin() || !$query->is_main_query()) {
      return;
    }

    $post_type = 'product';
    $category_taxonomy = vs_pa_get_category_taxonomy($post_type);

    if ($query->is_post_type_archive($post_type) || ($category_taxonomy && $query->is_tax($category_taxonomy))) {
      $query->set('posts_per_page', vs_pa_get_products_per_page());
      if ($query->is_tax($category_taxonomy)) {
        $query->set('post_type', $post_type);
      }
    }
  }
}
add_action('pre_get_posts', 'vs_pa_sync_main_query_posts_per_page');

if (!function_exists('vs_pa_ajax_filter_products')) {
  function vs_pa_ajax_filter_products() {
    check_ajax_referer('vs_product_archive_nonce', 'nonce');

    $post_type = sanitize_key($_POST['post_type'] ?? 'product');
    $category_taxonomy = sanitize_key($_POST['category_taxonomy'] ?? vs_pa_get_category_taxonomy($post_type));
    $current_category_id = isset($_POST['current_category_id']) ? (int) $_POST['current_category_id'] : 0;
    $search = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';
    $selected_colors = vs_pa_normalize_string_array($_POST['colors'] ?? ($_POST['colors[]'] ?? array()));
    $selected_prices = vs_pa_normalize_string_array($_POST['prices'] ?? ($_POST['prices[]'] ?? array()));
    $paged = max(1, (int) ($_POST['paged'] ?? 1));
    $per_page = vs_pa_get_products_per_page();

    $state = array(
      'post_type' => $post_type,
      'category_taxonomy' => $category_taxonomy,
      'current_category_id' => $current_category_id,
      's' => $search,
      'colors' => $selected_colors,
      'prices' => $selected_prices,
    );

    $matched_ids = vs_pa_get_filtered_post_ids($state);
    $paged_ids = vs_pa_paginate_ids($matched_ids, $paged, $per_page);
    $archive_url = vs_pa_get_archive_url($post_type, $category_taxonomy, $current_category_id);

    wp_send_json_success(array(
      'grid_html' => vs_pa_render_cards_html($paged_ids),
      'pagination_html' => vs_pa_render_pagination(array(
        'current_page' => $paged,
        'total_items' => count($matched_ids),
        'per_page' => $per_page,
        'base_url' => $archive_url,
        'colors' => $selected_colors,
        'prices' => $selected_prices,
        's' => $search,
      )),
      'found_posts' => count($matched_ids),
    ));
  }
}
add_action('wp_ajax_vs_filter_products', 'vs_pa_ajax_filter_products');
add_action('wp_ajax_nopriv_vs_filter_products', 'vs_pa_ajax_filter_products');
