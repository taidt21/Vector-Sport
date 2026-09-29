<?php
/**
 * Archive template for CPT product.
 */

defined('ABSPATH') || exit;
get_header();

$shared_template = get_template_directory() . '/template-parts/pages/product/archive-content.php';

if (file_exists($shared_template)) {
  include $shared_template;
} else {
  echo '<main class="product_archive"><div class="container"><p>Thiếu file template-parts/pages/product/archive-content.php</p></div></main>';
}

get_footer();
