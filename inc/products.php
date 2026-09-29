<?php
/**
 * Vector Sport - CPT product pagination as /product/page-2/
 * Paste into functions.php
 */

add_action('init', function () {
  $pt = get_post_type_object('product');
  if (!$pt) return;

  // Xác định archive slug thực tế của CPT product
  // - Nếu has_archive là string: dùng nó
  // - Nếu không: dùng rewrite slug
  $archive_slug = 'product';
  if (is_string($pt->has_archive) && $pt->has_archive !== '') {
    $archive_slug = $pt->has_archive;
  } elseif (!empty($pt->rewrite) && !empty($pt->rewrite['slug'])) {
    $archive_slug = $pt->rewrite['slug'];
  }
  $archive_slug = trim($archive_slug, '/');

  // /{archive_slug}/page-2/
  add_rewrite_rule(
    '^' . $archive_slug . '/page-(\d+)/?$',
    'index.php?post_type=product&paged=$matches[1]',
    'top'
  );

  // (tuỳ chọn) chặn trường hợp WP hiểu nhầm "page" là slug sản phẩm
  // Không bắt buộc, nhưng giúp giảm rủi ro
}, 20);

/**
 * Flush rewrite 1 lần sau khi bạn thêm code
 * (đỡ phải tự Save Permalinks)
 */
add_action('admin_init', function () {
  if (get_option('vs_product_page_dash_rewrite_flushed') === '1') return;
  flush_rewrite_rules(false);
  update_option('vs_product_page_dash_rewrite_flushed', '1');
});

/**
 * Convert link paginate từ /page/2/ -> /page-2/
 * để dù template đang dùng paginate_links kiểu mặc định vẫn ra đúng URL
 */
add_filter('paginate_links', function ($link) {
  if (!$link) return $link;

  // Chỉ áp dụng cho context product archive (hoặc query post_type=product)
  global $wp_query;
  $pt = $wp_query ? $wp_query->get('post_type') : null;
  $is_product = is_post_type_archive('product')
    || ($pt === 'product')
    || (is_array($pt) && in_array('product', $pt, true));

  if (!$is_product) return $link;

  $pt_obj = get_post_type_object('product');
  $archive_slug = 'product';
  if ($pt_obj) {
    if (is_string($pt_obj->has_archive) && $pt_obj->has_archive !== '') {
      $archive_slug = $pt_obj->has_archive;
    } elseif (!empty($pt_obj->rewrite) && !empty($pt_obj->rewrite['slug'])) {
      $archive_slug = $pt_obj->rewrite['slug'];
    }
  }
  $archive_slug = trim($archive_slug, '/');

  // /product/page/2/  -> /product/page-2/
  $pattern = '~/' . preg_quote($archive_slug, '~') . '/page/(\d+)/~';
  $link = preg_replace($pattern, '/' . $archive_slug . '/page-$1/', $link);

  return $link;
});

/**
 * Redirect 301 từ /product/page/2/ -> /product/page-2/
 * để link cũ không bị 404
 */
add_action('template_redirect', function () {
  if (!is_post_type_archive('product')) return;

  $uri = $_SERVER['REQUEST_URI'] ?? '';
  if (!$uri) return;

  // Nếu đã là page-2 thì thôi
  if (strpos($uri, 'page-') !== false) return;

  if (preg_match('~/page/(\d+)/?~', $uri, $m)) {
    $n = (int) $m[1];
    if ($n < 2) return;

    $target = trailingslashit(get_post_type_archive_link('product')) . 'page-' . $n . '/';
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if ($qs) $target .= '?' . $qs;

    wp_safe_redirect($target, 301);
    exit;
  }
});