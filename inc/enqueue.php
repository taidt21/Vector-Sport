<?php
add_action('wp_enqueue_scripts', function () {
  if (!is_singular('product')) return;

  wp_enqueue_script(
    'vs-product-single',
    get_theme_file_uri('/assets/js/product-single.js'),
    ['jquery'],
    '1.0.0',
    true
  );
}, 20);