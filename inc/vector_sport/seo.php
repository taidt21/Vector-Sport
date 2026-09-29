<?php
/**
 * Vector Sport - Lightweight SEO with ACF
 */

defined('ABSPATH') || exit;

function vs_seo_setup() {
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'vs_seo_setup');

function vs_seo_site_name() {
    return get_bloginfo('name') ?: 'Vector Sport';
}

function vs_seo_get_object_id() {
    if (is_singular()) {
        return get_the_ID();
    }

    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();

        if ($term && !is_wp_error($term) && !empty($term->taxonomy) && !empty($term->term_id)) {
            return $term->taxonomy . '_' . $term->term_id;
        }
    }

    return null;
}

function vs_seo_get_acf($field_name, $object_id = null) {
    if (function_exists('get_field')) {
        return get_field($field_name, $object_id);
    }

    if ($object_id && is_numeric($object_id)) {
        return get_post_meta((int) $object_id, $field_name, true);
    }

    return '';
}

function vs_seo_trim_text($text, $limit = 160) {
    $text = wp_strip_all_tags(strip_shortcodes((string) $text));
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    if ($text === '') {
        return '';
    }

    if (mb_strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $limit - 3)) . '...';
}

function vs_seo_default_description() {
    $description = get_bloginfo('description');

    if ($description) {
        return vs_seo_trim_text($description, 160);
    }

    return 'Vector Sport cung cấp lớp học Vovinam, võ phục, đồ tập và phụ kiện thể thao phù hợp cho học viên, phụ huynh, câu lạc bộ và đội nhóm.';
}

function vs_seo_get_image_url($image) {
    if (empty($image)) {
        return '';
    }

    if (is_array($image) && !empty($image['url'])) {
        return $image['url'];
    }

    if (is_numeric($image)) {
        return wp_get_attachment_image_url((int) $image, 'large') ?: '';
    }

    if (is_string($image)) {
        return $image;
    }

    return '';
}

function vs_seo_get_fallback_image() {
    if (is_singular() && has_post_thumbnail()) {
        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');

        if ($thumb) {
            return $thumb;
        }
    }

    $logo_id = get_theme_mod('custom_logo');

    if ($logo_id) {
        $logo = wp_get_attachment_image_url($logo_id, 'large');

        if ($logo) {
            return $logo;
        }
    }

    return get_template_directory_uri() . '/assets/images/logo.png';
}

function vs_seo_get_title() {
    $object_id = vs_seo_get_object_id();
    $site_name = vs_seo_site_name();

    $seo_title = $object_id ? vs_seo_get_acf('seo_title', $object_id) : '';

    if ($seo_title) {
        return vs_seo_trim_text($seo_title, 70);
    }

    if (is_front_page()) {
        return $site_name . ' - Lớp học Vovinam, võ phục và đồ tập thể thao';
    }

    if (is_singular()) {
        $title = single_post_title('', false);

        if (get_post_type() === 'product') {
            return $title . ' - Sản phẩm Vector Sport';
        }

        return $title . ' - ' . $site_name;
    }

    if (is_category() || is_tag() || is_tax()) {
        return single_term_title('', false) . ' - ' . $site_name;
    }

    if (is_post_type_archive('product')) {
        return 'Sản phẩm Vector Sport - Võ phục, đồ tập và phụ kiện';
    }

    if (is_search()) {
        return 'Tìm kiếm: ' . get_search_query() . ' - ' . $site_name;
    }

    if (is_404()) {
        return 'Không tìm thấy trang - ' . $site_name;
    }

    return wp_get_document_title();
}

function vs_seo_get_description() {
    $object_id = vs_seo_get_object_id();

    $seo_description = $object_id ? vs_seo_get_acf('seo_description', $object_id) : '';

    if ($seo_description) {
        return vs_seo_trim_text($seo_description, 160);
    }

    if (is_front_page()) {
        return vs_seo_default_description();
    }

    if (is_singular()) {
        global $post;

        if (!$post) {
            return vs_seo_default_description();
        }

        if (has_excerpt($post)) {
            return vs_seo_trim_text(get_the_excerpt($post), 160);
        }

        return vs_seo_trim_text($post->post_content, 160) ?: vs_seo_default_description();
    }

    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();

        if ($term && !is_wp_error($term) && !empty($term->description)) {
            return vs_seo_trim_text($term->description, 160);
        }

        return 'Khám phá nội dung liên quan tại Vector Sport.';
    }

    if (is_post_type_archive('product')) {
        return 'Khám phá võ phục, đồ tập, phụ kiện và các sản phẩm thể thao thiết thực tại Vector Sport.';
    }

    if (is_search()) {
        return 'Kết quả tìm kiếm cho từ khóa ' . get_search_query() . ' tại Vector Sport.';
    }

    return vs_seo_default_description();
}

function vs_seo_get_image() {
    $object_id = vs_seo_get_object_id();

    $seo_image = $object_id ? vs_seo_get_acf('seo_image', $object_id) : '';
    $seo_image_url = vs_seo_get_image_url($seo_image);

    if ($seo_image_url) {
        return $seo_image_url;
    }

    return vs_seo_get_fallback_image();
}

function vs_seo_get_default_canonical() {
    if (is_front_page()) {
        return home_url('/');
    }

    if (is_singular()) {
        return get_permalink();
    }

    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();

        if ($term && !is_wp_error($term)) {
            $term_link = get_term_link($term);

            if (!is_wp_error($term_link)) {
                return $term_link;
            }
        }
    }

    if (is_post_type_archive()) {
        $post_type = get_query_var('post_type');

        if (is_array($post_type)) {
            $post_type = reset($post_type);
        }

        $archive_link = get_post_type_archive_link($post_type);

        if ($archive_link) {
            return $archive_link;
        }
    }

    if (is_search()) {
        return home_url('/?s=' . rawurlencode(get_search_query()));
    }

    global $wp;

    if (!empty($wp->request)) {
        return home_url('/' . trailingslashit($wp->request));
    }

    return home_url('/');
}

function vs_seo_get_canonical() {
    $object_id = vs_seo_get_object_id();

    $seo_canonical = $object_id ? vs_seo_get_acf('seo_canonical', $object_id) : '';

    if ($seo_canonical) {
        return esc_url_raw($seo_canonical);
    }

    return vs_seo_get_default_canonical();
}

function vs_seo_get_robots() {
    $object_id = vs_seo_get_object_id();

    $seo_robots = $object_id ? vs_seo_get_acf('seo_robots', $object_id) : '';

    if ($seo_robots) {
        return sanitize_text_field($seo_robots);
    }

    if (is_search() || is_404() || is_paged()) {
        return 'noindex, follow';
    }

    return 'index, follow';
}

function vs_seo_filter_document_title_parts($title) {
    $title['title'] = vs_seo_get_title();

    unset($title['site']);
    unset($title['tagline']);

    return $title;
}
add_filter('document_title_parts', 'vs_seo_filter_document_title_parts', 20);

function vs_seo_render_meta_tags() {
    $title = vs_seo_get_title();
    $description = vs_seo_get_description();
    $image = vs_seo_get_image();
    $canonical = vs_seo_get_canonical();
    $robots = vs_seo_get_robots();
    $site_name = vs_seo_site_name();

    echo "\n" . '<!-- Vector Sport SEO -->' . "\n";

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";

    if ($canonical) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }

    echo '<meta property="og:locale" content="vi_VN">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";

    echo '<meta property="og:type" content="' . esc_attr(is_singular() ? 'article' : 'website') . '">' . "\n";

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";

    echo '<!-- /Vector Sport SEO -->' . "\n";
}
add_action('wp_head', 'vs_seo_render_meta_tags', 5);

function vs_seo_render_json_ld() {
    $site_name = vs_seo_site_name();
    $site_url = home_url('/');
    $logo = vs_seo_get_image();

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $site_url . '#organization',
                'name' => $site_name,
                'url' => $site_url,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $logo,
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $site_url . '#website',
                'url' => $site_url,
                'name' => $site_name,
                'publisher' => [
                    '@id' => $site_url . '#organization',
                ],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => home_url('/?s={search_term_string}'),
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ];

    if (is_singular()) {
        $schema['@graph'][] = [
            '@type' => get_post_type() === 'product' ? 'Product' : 'Article',
            '@id' => get_permalink() . '#content',
            'url' => get_permalink(),
            'name' => vs_seo_get_title(),
            'description' => vs_seo_get_description(),
            'image' => vs_seo_get_image(),
            'mainEntityOfPage' => get_permalink(),
        ];
    }

    echo "\n" . '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "\n" . '</script>' . "\n";
}
add_action('wp_head', 'vs_seo_render_json_ld', 20);

function vs_seo_render_custom_schema() {
    $object_id = vs_seo_get_object_id();

    if (!$object_id) {
        return;
    }

    $custom_schema = vs_seo_get_acf('seo_schema_json', $object_id);

    if (!$custom_schema) {
        return;
    }

    $decoded = json_decode($custom_schema, true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($decoded)) {
        return;
    }

    echo "\n" . '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "\n" . '</script>' . "\n";
}
add_action('wp_head', 'vs_seo_render_custom_schema', 30);