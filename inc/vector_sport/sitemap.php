<?php
/**
 * Vector Sport - Custom XML Sitemap
 */

defined('ABSPATH') || exit;

function vs_sitemap_excluded_page_slugs() {
    return [
        'cart',
        'gio-hang',
        'checkout',
        'thanh-toan',
        'account',
        'tai-khoan',
        'thank-you',
        'cam-on',
        'search',
        'tim-kiem',
        'privacy-policy',
    ];
}

function vs_sitemap_public_post_types() {
    return [
        'page',
        'post',
        'product',
    ];
}

function vs_sitemap_taxonomies() {
    return [
        'category',
        'product_category',
    ];
}

function vs_sitemap_add_rewrite_rules() {
    add_rewrite_rule('^sitemap_index\.xml$', 'index.php?vs_sitemap=index', 'top');
    add_rewrite_rule('^page-sitemap\.xml$', 'index.php?vs_sitemap=post_type&vs_sitemap_type=page', 'top');
    add_rewrite_rule('^post-sitemap\.xml$', 'index.php?vs_sitemap=post_type&vs_sitemap_type=post', 'top');
    add_rewrite_rule('^product-sitemap\.xml$', 'index.php?vs_sitemap=post_type&vs_sitemap_type=product', 'top');
    add_rewrite_rule('^category-sitemap\.xml$', 'index.php?vs_sitemap=taxonomy&vs_sitemap_type=category', 'top');
    add_rewrite_rule('^product-category-sitemap\.xml$', 'index.php?vs_sitemap=taxonomy&vs_sitemap_type=product_category', 'top');
}
add_action('init', 'vs_sitemap_add_rewrite_rules');

function vs_sitemap_query_vars($vars) {
    $vars[] = 'vs_sitemap';
    $vars[] = 'vs_sitemap_type';

    return $vars;
}
add_filter('query_vars', 'vs_sitemap_query_vars');

function vs_sitemap_template_redirect() {
    $sitemap = get_query_var('vs_sitemap');

    if (!$sitemap) {
        return;
    }

    if ($sitemap === 'index') {
        vs_render_sitemap_index();
        exit;
    }

    if ($sitemap === 'post_type') {
        $post_type = sanitize_key(get_query_var('vs_sitemap_type'));
        vs_render_post_type_sitemap($post_type);
        exit;
    }

    if ($sitemap === 'taxonomy') {
        $taxonomy = sanitize_key(get_query_var('vs_sitemap_type'));
        vs_render_taxonomy_sitemap($taxonomy);
        exit;
    }

    status_header(404);
    exit;
}
add_action('template_redirect', 'vs_sitemap_template_redirect');

function vs_sitemap_header() {
    status_header(200);
    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
}

function vs_render_sitemap_index() {
    vs_sitemap_header();

    $home_url = home_url('/');

    $sitemaps = [
        [
            'loc'     => $home_url . 'page-sitemap.xml',
            'lastmod' => vs_get_latest_post_modified_date('page'),
        ],
        [
            'loc'     => $home_url . 'post-sitemap.xml',
            'lastmod' => vs_get_latest_post_modified_date('post'),
        ],
        [
            'loc'     => $home_url . 'product-sitemap.xml',
            'lastmod' => vs_get_latest_post_modified_date('product'),
        ],
        [
            'loc'     => $home_url . 'category-sitemap.xml',
            'lastmod' => current_time('Y-m-d'),
        ],
        [
            'loc'     => $home_url . 'product-category-sitemap.xml',
            'lastmod' => current_time('Y-m-d'),
        ],
    ];

    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($sitemaps as $sitemap) {
        echo "  <sitemap>\n";
        echo '    <loc>' . esc_url($sitemap['loc']) . "</loc>\n";

        if (!empty($sitemap['lastmod'])) {
            echo '    <lastmod>' . esc_html($sitemap['lastmod']) . "</lastmod>\n";
        }

        echo "  </sitemap>\n";
    }

    echo '</sitemapindex>';
}

function vs_render_post_type_sitemap($post_type) {
    if (!in_array($post_type, vs_sitemap_public_post_types(), true)) {
        status_header(404);
        return;
    }

    vs_sitemap_header();

    $excluded_slugs = vs_sitemap_excluded_page_slugs();

    $query = new WP_Query([
        'post_type'              => $post_type,
        'post_status'            => 'publish',
        'posts_per_page'         => 5000,
        'orderby'                => 'modified',
        'order'                  => 'DESC',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();

            if ($post_type === 'page') {
                $slug = get_post_field('post_name', $post_id);

                if (in_array($slug, $excluded_slugs, true)) {
                    continue;
                }
            }

            if (get_post_meta($post_id, '_vs_sitemap_exclude', true) === '1') {
                continue;
            }

            $permalink = get_permalink($post_id);

            if (!$permalink) {
                continue;
            }

            echo "  <url>\n";
            echo '    <loc>' . esc_url($permalink) . "</loc>\n";
            echo '    <lastmod>' . esc_html(get_the_modified_date('Y-m-d', $post_id)) . "</lastmod>\n";
            echo "  </url>\n";
        }

        wp_reset_postdata();
    }

    echo '</urlset>';
}

function vs_render_taxonomy_sitemap($taxonomy) {
    if (!in_array($taxonomy, vs_sitemap_taxonomies(), true)) {
        status_header(404);
        return;
    }

    if (!taxonomy_exists($taxonomy)) {
        status_header(404);
        return;
    }

    vs_sitemap_header();

    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'number'     => 5000,
    ]);

    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $term_link = get_term_link($term);

            if (is_wp_error($term_link)) {
                continue;
            }

            echo "  <url>\n";
            echo '    <loc>' . esc_url($term_link) . "</loc>\n";
            echo "  </url>\n";
        }
    }

    echo '</urlset>';
}

function vs_get_latest_post_modified_date($post_type) {
    $latest = get_posts([
        'post_type'              => $post_type,
        'post_status'            => 'publish',
        'posts_per_page'         => 1,
        'orderby'                => 'modified',
        'order'                  => 'DESC',
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    if (empty($latest)) {
        return current_time('Y-m-d');
    }

    return get_the_modified_date('Y-m-d', $latest[0]);
}

function vs_sitemap_add_to_robots_txt($output, $public) {
    if (!$public) {
        return $output;
    }

    $output .= "\nSitemap: " . home_url('/sitemap_index.xml') . "\n";

    return $output;
}
add_filter('robots_txt', 'vs_sitemap_add_to_robots_txt', 10, 2);