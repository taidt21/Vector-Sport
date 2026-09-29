<?php
/**
 * Single Product - Vector Sport
 *
 * Lưu ý:
 * - Nếu site của bạn đang gắn product với taxonomy category khác, chỉ cần đổi 1 dòng:
 *   $product_category_taxonomy = 'category';
 * - Tag NEW đang lấy từ taxonomy: tag_san_pham
 */

get_header();

$product_category_taxonomy = 'product_category';
$product_tag_taxonomy      = 'tag_san_pham';

if (!function_exists('vs_sp_to_number')) {
    function vs_sp_to_number($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^\d]/', '', $value);

        return $value !== '' ? (float) $value : 0;
    }
}

if (!function_exists('vs_sp_format_price')) {
    function vs_sp_format_price($value)
    {
        $number = vs_sp_to_number($value);

        if ($number <= 0) {
            return '';
        }

        return number_format($number, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('vs_sp_guess_color_hex')) {
    function vs_sp_guess_color_hex($color_name)
    {
        $color_name = mb_strtolower(trim((string) $color_name), 'UTF-8');

        $map = [
            'xanh lá'     => '#4f7d1f',
            'xanh la'     => '#4f7d1f',
            'xanh ngọc'   => '#8ac7bc',
            'xanh ngoc'   => '#8ac7bc',
            'xanh biển'   => '#1f4d9c',
            'xanh bien'   => '#1f4d9c',
            'xanh navy'   => '#05053f',
            'navy'        => '#05053f',
            'xanh dương'  => '#7ea7df',
            'xanh duong'  => '#7ea7df',
            'hồng'        => '#ee7b96',
            'hong'        => '#ee7b96',
            'đỏ'          => '#e61d5f',
            'do'          => '#e61d5f',
            'đỏ hồng'     => '#e61d5f',
            'đỏ đô'       => '#9c0f2b',
            'đỏ độ'      => '#9c0f2b',
            'tím'         => '#8d6ad0',
            'tim'         => '#8d6ad0',
            'trắng'       => '#f2f2f2',
            'trang'       => '#f2f2f2',
            'xám'         => '#b7b7b7',
            'xam'         => '#b7b7b7',
            'đen'         => '#111111',
            'den'         => '#111111',
            'be'          => '#d8c4a4',
            'kem'         => '#f2eadc',
            'vàng'        => '#f1ca3a',
            'vang'        => '#f1ca3a',
            'cam'         => '#f28d35',
        ];

        if (isset($map[$color_name])) {
            return $map[$color_name];
        }

        if (preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color_name)) {
            return $color_name;
        }

        return '#d9d9d9';
    }
}

if (!function_exists('vs_sp_normalize_gallery')) {
    function vs_sp_normalize_gallery($gallery)
    {
        $items = [];

        if (empty($gallery) || !is_array($gallery)) {
            return $items;
        }

        foreach ($gallery as $image) {
            $full  = '';
            $thumb = '';
            $alt   = '';

            if (is_array($image)) {
                $full  = $image['url'] ?? '';
                $thumb = $image['sizes']['medium'] ?? ($image['sizes']['thumbnail'] ?? $full);
                $alt   = $image['alt'] ?? '';

                if (!$full && !empty($image['ID'])) {
                    $full  = wp_get_attachment_image_url((int) $image['ID'], 'full');
                    $thumb = wp_get_attachment_image_url((int) $image['ID'], 'medium');
                    $alt   = get_post_meta((int) $image['ID'], '_wp_attachment_image_alt', true);
                }
            } elseif (is_numeric($image)) {
                $full  = wp_get_attachment_image_url((int) $image, 'full');
                $thumb = wp_get_attachment_image_url((int) $image, 'medium');
                $alt   = get_post_meta((int) $image, '_wp_attachment_image_alt', true);
            } elseif (is_string($image)) {
                $full = $image;
                $thumb = $image;
            }

            if ($full) {
                $items[] = [
                    'full'  => $full,
                    'thumb' => $thumb ?: $full,
                    'alt'   => $alt,
                ];
            }
        }

        return $items;
    }
}

if (!function_exists('vs_sp_get_recent_ids')) {
    function vs_sp_get_recent_ids($current_product_id)
    {
        $ids = [];

        if ($current_product_id > 0) {
            $ids[] = (int) $current_product_id;
        }

        if (!empty($_COOKIE['vs_recent_products'])) {
            $cookie_value = sanitize_text_field(wp_unslash($_COOKIE['vs_recent_products']));
            $cookie_ids   = array_filter(array_map('absint', explode(',', $cookie_value)));

            foreach ($cookie_ids as $cookie_id) {
                if ($cookie_id && $cookie_id !== (int) $current_product_id) {
                    $ids[] = $cookie_id;
                }
            }
        }

        return array_slice(array_values(array_unique($ids)), 0, 5);
    }
}

if (!function_exists('vs_sp_render_loop_card')) {
    function vs_sp_render_loop_card($product_id, $product_tag_taxonomy)
    {
        $title        = get_the_title($product_id);
        $permalink    = get_permalink($product_id);
        $thumb        = get_the_post_thumbnail_url($product_id, 'large');
        $current_price = vs_sp_to_number(get_field('gia_tien', $product_id));
        $old_price     = vs_sp_to_number(get_field('gia_cu', $product_id));
        $price_html    = vs_sp_format_price($current_price);
        $old_html      = vs_sp_format_price($old_price);
        $discount      = ($old_price > $current_price && $current_price > 0)
            ? '-' . round((($old_price - $current_price) / $old_price) * 100) . '%'
            : '';

        $color_rows    = get_field('cac_mau_sac', $product_id);
        $first_color_name = '';
        $first_color_value = '';
        $first_color_hex = '#d9d9d9';
        $first_color_gallery = [];

        if (!empty($color_rows) && is_array($color_rows)) {
            $first_row = $color_rows[0];
            $first_color_name = trim((string) ($first_row['ten_mau_sac'] ?? ''));
            $first_color_value = trim((string) ($first_row['mau_sac'] ?? ''));
            $first_color_hex = vs_sp_guess_color_hex($first_color_value ?: $first_color_name);
            $first_color_gallery = vs_sp_normalize_gallery($first_row['danh_sach_anh_san_pham'] ?? []);
        }

        if (!$thumb && !empty($first_color_gallery[0]['full'])) {
            $thumb = $first_color_gallery[0]['full'];
        }

        $terms = get_the_terms($product_id, $product_tag_taxonomy);
        $badge_terms = [];

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $badge_terms[] = $term->name;
            }
        }
        ?>
<article class="vs-sp-card">
    <a class="vs-sp-card__thumb" href="<?php echo esc_url($permalink); ?>">
        <?php if (!empty($badge_terms)) : ?>
        <div class="vs-sp-card__badges">
            <?php foreach ($badge_terms as $badge) : ?>
            <?php $is_soldout = mb_stripos($badge, 'hết hàng', 0, 'UTF-8') !== false; ?>
            <span class="vs-sp-card__badge <?php echo $is_soldout ? 'is-soldout' : 'is-new'; ?>">
                <?php echo esc_html($badge); ?>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($thumb) : ?>
        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>">
        <?php endif; ?>
    </a>

    <div class="vs-sp-card__body">
        <h3 class="vs-sp-card__title">
            <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
        </h3>

        <?php if ($price_html) : ?>
        <div class="vs-sp-card__price-row">
            <span class="vs-sp-card__price-current"><?php echo esc_html($price_html); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($old_html || $discount) : ?>
        <div class="vs-sp-card__price-sub">
            <?php if ($old_html) : ?>
            <span class="vs-sp-card__price-old"><?php echo esc_html($old_html); ?></span>
            <?php endif; ?>

            <?php if ($discount) : ?>
            <span class="vs-sp-card__discount"><?php echo esc_html($discount); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($first_color_name || $first_color_value) : ?>
        <div class="vs-sp-card__swatch-wrap">
            <span class="vs-sp-card__swatch" style="--swatch-color: <?php echo esc_attr($first_color_hex); ?>;"></span>
        </div>
        <?php endif; ?>
    </div>
</article>
<?php
    }
}
?>

<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>
<?php
        $product_id        = get_the_ID();
        $product_title     = get_the_title();
        $product_permalink = get_permalink();
        $product_content   = apply_filters('the_content', get_the_content());
        $product_excerpt   = get_the_excerpt();
        $product_thumb     = get_the_post_thumbnail_url($product_id, 'full');

        $ma_san_pham = trim((string) get_field('ma_san_pham', $product_id));

        $gia_tien_number = vs_sp_to_number(get_field('gia_tien', $product_id));
        $gia_cu_number   = vs_sp_to_number(get_field('gia_cu', $product_id));

        $gia_tien = vs_sp_format_price($gia_tien_number);
        $gia_cu   = vs_sp_format_price($gia_cu_number);

        $tiet_kiem = ($gia_cu_number > $gia_tien_number && $gia_tien_number > 0)
            ? vs_sp_format_price($gia_cu_number - $gia_tien_number)
            : '';

        $phan_tram_giam = ($gia_cu_number > $gia_tien_number && $gia_tien_number > 0)
            ? '-' . round((($gia_cu_number - $gia_tien_number) / $gia_cu_number) * 100) . '%'
            : '';

        $size_rows = get_field('cac_size_san_pham', $product_id);
        $product_sizes = [];

        if (!empty($size_rows) && is_array($size_rows)) {
            foreach ($size_rows as $size_row) {
                $size = trim((string) ($size_row['kich_co'] ?? ''));
                if ($size !== '') {
                    $product_sizes[] = $size;
                }
            }
        }

        $selected_size = !empty($product_sizes) ? $product_sizes[0] : '';

        $color_rows = get_field('cac_mau_sac', $product_id);
        $product_colors = [];

        if (!empty($color_rows) && is_array($color_rows)) {
            foreach ($color_rows as $index => $color_row) {
                $color_name  = trim((string) ($color_row['ten_mau_sac'] ?? ''));
                $color_value = trim((string) ($color_row['mau_sac'] ?? ''));
                $gallery     = vs_sp_normalize_gallery($color_row['danh_sach_anh_san_pham'] ?? []);

                if (empty($gallery) && $product_thumb) {
                    $gallery[] = [
                        'full'  => $product_thumb,
                        'thumb' => $product_thumb,
                        'alt'   => $product_title,
                    ];
                }

                $product_colors[] = [
                    'key'    => 'color-' . $index,
                    'label'  => $color_name ?: ($color_value ?: 'Mặc định'),
                    'hex'    => vs_sp_guess_color_hex($color_value ?: $color_name),
                    'images' => $gallery,
                ];
            }
        }

        if (empty($product_colors)) {
            $product_colors[] = [
                'key'    => 'default',
                'label'  => 'Mặc định',
                'hex'    => '#d9d9d9',
                'images' => $product_thumb ? [[
                    'full'  => $product_thumb,
                    'thumb' => $product_thumb,
                    'alt'   => $product_title,
                ]] : [],
            ];
        }

        $selected_color = $product_colors[0];
        $active_gallery = $selected_color['images'];

        $tag_terms  = get_the_terms($product_id, $product_tag_taxonomy);
        $badge_text = '';
        if (!empty($tag_terms) && !is_wp_error($tag_terms)) {
            $badge_text = $tag_terms[0]->name;
        }

        $category_terms = get_the_terms($product_id, $product_category_taxonomy);
        $category_ids   = [];
        $primary_category = null;

        if (!empty($category_terms) && !is_wp_error($category_terms)) {
            $primary_category = $category_terms[0];
            foreach ($category_terms as $term) {
                $category_ids[] = $term->term_id;
            }
        }

        $related_args = [
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'posts_per_page'      => 5,
            'post__not_in'        => [$product_id],
            'ignore_sticky_posts' => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
        ];

        if (!empty($category_ids)) {
            $related_args['tax_query'] = [
                [
                    'taxonomy' => $product_category_taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $category_ids,
                ],
            ];
        }

        $related_query = new WP_Query($related_args);

        $recent_ids = vs_sp_get_recent_ids($product_id);
        $recent_query = null;

        if (!empty($recent_ids)) {
            $recent_query = new WP_Query([
                'post_type'           => 'product',
                'post_status'         => 'publish',
                'posts_per_page'      => 5,
                'post__in'            => $recent_ids,
                'orderby'             => 'post__in',
                'ignore_sticky_posts' => true,
            ]);
        }

        $gallery_json = wp_json_encode($product_colors);
        ?>

<main class="vs-sp js-vs-single-product" data-product-id="<?php echo esc_attr($product_id); ?>"
    data-gallery='<?php echo esc_attr($gallery_json); ?>'>
    <div class="vs-sp__container container">
        <nav class="vs-sp__breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a>

            <?php if ($primary_category) : ?>
            <span>/</span>
            <a
                href="<?php echo esc_url(get_term_link($primary_category)); ?>"><?php echo esc_html($primary_category->name); ?></a>
            <?php endif; ?>

            <span>/</span>
            <span><?php echo esc_html($product_title); ?></span>
        </nav>

        <section class="vs-sp__top">
            <div class="vs-sp__gallery-wrap">
                <div class="vs-sp__thumbs">
                    <div class="vs-sp__thumbs-swiper swiper js-gallery-thumbs-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($active_gallery as $image) : ?>
                            <div class="vs-sp__thumb swiper-slide">
                                <img src="<?php echo esc_url($image['thumb']); ?>"
                                    alt="<?php echo esc_attr($image['alt'] ?: $product_title); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="vs-sp__main-gallery">
                    <div class="vs-sp__main-image swiper js-gallery-main-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($active_gallery as $image) : ?>
                            <div class="vs-sp__main-slide swiper-slide">
                                <img src="<?php echo esc_url($image['full']); ?>"
                                    alt="<?php echo esc_attr($image['alt'] ?: $product_title); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($active_gallery) > 1) : ?>
                        <button class="vs-sp__gallery-next js-gallery-next" type="button" aria-label="Ảnh tiếp theo">
                            <span>&#8250;</span>
                        </button>
                        <button class="vs-sp__gallery-prev js-gallery-prev" type="button" aria-label="Ảnh trước">
                            <span>&#8249;</span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <div
                        class="vs-sp__dots js-gallery-dots<?php echo count($active_gallery) > 1 ? '' : ' is-hidden'; ?>">
                    </div>
                </div>
            </div>

            <div class="vs-sp__side">
                <div class="vs-sp__summary">
                    <h1 class="vs-sp__title"><?php echo esc_html($product_title); ?></h1>

                    <?php if ($ma_san_pham) : ?>
                    <div class="vs-sp__sku-wrap">
                        <span>Mã sản phẩm:</span>
                        <strong><?php echo esc_html($ma_san_pham); ?></strong>
                    </div>
                    <?php endif; ?>

                    <div class="vs-sp__price-block">
                        <div class="vs-sp__price-row">
                            <?php if ($gia_tien) : ?>
                            <span class="vs-sp__price-current"><?php echo esc_html($gia_tien); ?></span>
                            <?php endif; ?>

                            <?php if ($gia_cu) : ?>
                            <span class="vs-sp__price-old"><?php echo esc_html($gia_cu); ?></span>
                            <?php endif; ?>

                            <?php if ($phan_tram_giam) : ?>
                            <span class="vs-sp__discount"><?php echo esc_html($phan_tram_giam); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($tiet_kiem) : ?>
                        <div class="vs-sp__saving">(Tiết kiệm <?php echo esc_html($tiet_kiem); ?>)</div>
                        <?php endif; ?>
                    </div>

                    <?php if ($badge_text) : ?>
                    <div class="vs-sp__badge-row">
                        <span class="vs-sp__badge"><?php echo esc_html($badge_text); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="vs-sp__promo-box">
                        <div class="vs-sp__promo-title">🎁 ƯU ĐÃI THÁNG 4</div>
                        <ul>
                            <li>TẶNG 2 ĐÔI VỚ CHO ĐƠN 550K - Freeship cho đơn từ 200K</li>
                            <li>COMBO ĐỒ BỘ 599K/3 bộ <a href="#">TẠI ĐÂY</a></li>
                            <li>COMBO 370K/2 bộ <a href="#">TẠI ĐÂY</a></li>
                            <li>ĐỒNG GIÁ TỪ 99K <a href="#">TẠI ĐÂY</a></li>
                        </ul>
                    </div>

                    <form class="vs-sp__form js-product-form" action="#" method="post">
                        <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                        <input type="hidden" name="selected_color" class="js-selected-color"
                            value="<?php echo esc_attr($selected_color['label']); ?>">
                        <input type="hidden" name="selected_size" class="js-selected-size"
                            value="<?php echo esc_attr($selected_size); ?>">
                        <input type="hidden" name="quantity" class="js-selected-quantity" value="1">

                        <div class="vs-sp__option-group">
                            <div class="vs-sp__option-label">Màu sắc: <strong
                                    class="js-color-name"><?php echo esc_html($selected_color['label']); ?></strong>
                            </div>
                            <div class="vs-sp__color-list">
                                <?php foreach ($product_colors as $index => $color) : ?>
                                <button
                                    class="vs-sp__color-swatch js-color-swatch <?php echo $index === 0 ? 'is-active' : ''; ?>"
                                    type="button" data-color-index="<?php echo esc_attr($index); ?>"
                                    data-color-name="<?php echo esc_attr($color['label']); ?>">
                                    <span style="--swatch-color: <?php echo esc_attr($color['hex']); ?>;"></span>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="vs-sp__option-group">
                            <div class="vs-sp__size-head">
                                <div class="vs-sp__option-label">Kích cỡ: <strong
                                        class="js-size-name"><?php echo esc_html($selected_size); ?></strong></div>
                                <a class="vs-sp__size-guide" href="#">Hướng dẫn chọn size</a>
                            </div>

                            <?php if (!empty($product_sizes)) : ?>
                            <div class="vs-sp__size-list">
                                <?php foreach ($product_sizes as $index => $size) : ?>
                                <button
                                    class="vs-sp__size-item js-size-item <?php echo $index === 0 ? 'is-active' : ''; ?>"
                                    type="button" data-size="<?php echo esc_attr($size); ?>">
                                    <?php echo esc_html($size); ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="vs-sp__action-row">
                            <div class="vs-sp__qty js-qty-box">
                                <button type="button" class="vs-sp__qty-btn js-qty-minus">−</button>
                                <span class="vs-sp__qty-value js-qty-value">1</span>
                                <button type="button" class="vs-sp__qty-btn js-qty-plus">+</button>
                            </div>

                            <button class="vs-sp__add-cart" type="button">THÊM VÀO GIỎ</button>
                        </div>

                        <button class="vs-sp__buy-now" type="button">MUA NGAY - ĐỔI TRẢ 60 NGÀY</button>
                    </form>

                    <div class="vs-sp__support-row">
                        <div class="vs-sp__support-item">
                            <span class="vs-sp__support-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 7h11v8H3z" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M14 10h3l4 4v1h-7z" stroke="currentColor" stroke-width="1.8" />
                                    <circle cx="7" cy="18" r="2" stroke="currentColor" stroke-width="1.8" />
                                    <circle cx="18" cy="18" r="2" stroke="currentColor" stroke-width="1.8" />
                                </svg>
                            </span>
                            <span>Đổi trả 60 ngày dễ dàng bằng số điện thoại</span>
                        </div>

                        <div class="vs-sp__support-item">
                            <span class="vs-sp__support-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="1.8" opacity=".25" />
                                    <path
                                        d="M7 12.5c0-2.5 1.7-4.5 4-4.5 1.2 0 2.3.5 3 1.3.5.5.9 1.2 1 2l1.9-.3c-.4-1.4-1.1-2.5-2.1-3.4A6.5 6.5 0 0 0 11 6c-3.6 0-6 2.6-6 6 0 3.6 2.6 6 6.1 6 2.9 0 5.2-1.5 6.1-4l-1.9-.5c-.7 1.6-2.2 2.5-4.2 2.5-2.3 0-4.1-1.6-4.1-3.5Z"
                                        fill="currentColor" />
                                </svg>
                            </span>
                            <span>Tích điểm tất cả (kể cả sản phẩm khuyến mãi)</span>
                        </div>

                        <div class="vs-sp__support-item">
                            <span class="vs-sp__support-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 5h16v10H4z" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M8 19h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    <path d="M7 9h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    <path d="M7 12h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </span>
                            <span>Hotline 07.6886.5858 (T2-CN 8:30-22:00)</span>
                        </div>
                    </div>
                </div>

                <section class="vs-sp__detail-wrap">
                    <div class="vs-sp__detail-main">
                        <div class="vs-sp-accordion js-accordion is-open">
                            <button class="vs-sp-accordion__head js-accordion-head" type="button">
                                <span>Mô tả sản phẩm</span>
                                <span class="vs-sp-accordion__arrow"></span>
                            </button>
                            <div class="vs-sp-accordion__body js-accordion-body">
                                <div class="vs-sp-accordion__content js-readmore-content">
                                    <?php echo $product_content; ?>
                                </div>
                                <button class="vs-sp-accordion__more js-readmore-toggle" type="button">Xem thêm</button>
                            </div>
                        </div>

                        <div class="vs-sp-accordion js-accordion">
                            <button class="vs-sp-accordion__head js-accordion-head" type="button">
                                <span>Chính sách giao hàng</span>
                                <span class="vs-sp-accordion__arrow"></span>
                            </button>
                            <div class="vs-sp-accordion__body js-accordion-body">
                                <div class="vs-sp-accordion__content">
                                    <h3>THỜI GIAN VẬN CHUYỂN TRUNG BÌNH</h3>

                                    <p>Đơn nội thành Hà Nội: Khách hàng sẽ nhận được trong vòng 1-2 ngày kể từ khi đơn
                                        hàng được xác nhận (trừ trường hợp đơn hàng có vấn đề trong quá trình xử lý,
                                        CSKH của Vector Sport sẽ liên hệ với khách trước về thời gian nhận hàng dự kiến).
                                    </p>

                                    <p>Các khu vực khác: 2-5 ngày (thường là 2-4 ngày), nếu sau 3 ngày kể từ khi đặt
                                        hàng mà quý khách chưa nhận được cuộc gọi giao hàng vui lòng xin liên hệ Vector
                                        Sport để được hỗ trợ (không bao gồm chủ nhật và ngày lễ).

                                    <p>Trường hợp nhận hàng gấp: Quý khách vui lòng liên hệ hotline 09.4509.0089 để được
                                        hỗ trợ. Hiện nay, Vector Sport chỉ hỗ trợ đơn gấp trong ngày tại Tp. Hà Nội.</p>
                                </div>
                            </div>
                        </div>

                        <div class="vs-sp-accordion js-accordion">
                            <button class="vs-sp-accordion__head js-accordion-head" type="button">
                                <span>Chính sách đổi trả</span>
                                <span class="vs-sp-accordion__arrow"></span>
                            </button>
                            <div class="vs-sp-accordion__body js-accordion-body">
                                <div class="vs-sp-accordion__content">
                                    <div class="return-policy">
                                        <h3>1. Điều kiện đổi trả</h3>
                                        <p>
                                            Với những sản phẩm đã mua tại Vector Sport, 60 ngày kể từ khi nhận sản phẩm,
                                            quý khách sẽ được đổi hàng hoặc trả hàng với các lý do sau đây:
                                        </p>

                                        <ul>
                                            <li>Sản phẩm không đúng mô tả</li>
                                            <li>Sản phẩm bị lỗi do nhà sản xuất</li>
                                            <li>Sản phẩm không vừa size</li>
                                            <li>Quý khách không còn hài lòng với sản phẩm đã mua (chi tiết bên dưới)*
                                            </li>
                                        </ul>

                                        <p><strong>Một số lưu ý:</strong></p>
                                        <p>
                                            Vector Sport sẽ không hỗ trợ đổi/trả cho những dòng sản phẩm sau đây: Giảm
                                            giá trên 40%, Đồng giá, Quà tặng trong chương trình khuyến mãi, hoặc một số
                                            chương trình đặc biệt được thông báo trước cho quý khách. Tuy nhiên, nếu quý
                                            khách thật sự cần hỗ trợ, xin vui lòng liên hệ Vector Sport nhé.
                                        </p>

                                        <h3>2. Quy định chi tiết về việc đổi trả</h3>
                                        <ul>
                                            <li>Sản phẩm còn nguyên tag, chưa qua sử dụng hoặc giặt ủi, không bị dơ bẩn.
                                            </li>
                                            <li>
                                                Quý khách vui lòng cung cấp số điện thoại hoặc hóa đơn mua hàng
                                                (Đối với trường hợp được tặng, vui lòng cung cấp số điện thoại người
                                                mua).
                                            </li>
                                            <li>
                                                Sản phẩm đổi phải có giá trị bằng hoặc cao hơn giá trị sản phẩm cũ.
                                                Trong trường hợp đổi qua sản phẩm có giá trị thấp hơn, khách hàng sẽ
                                                được hoàn tiền thừa vào tài khoản mua hàng cho lần kế tiếp.
                                            </li>
                                            <li>
                                                Trường hợp sản phẩm cần đổi đang trong chương trình khuyến mãi, quý
                                                khách vui lòng đổi trong thời gian diễn ra chương trình nhằm được áp
                                                dụng mức giá ưu đãi.
                                            </li>
                                        </ul>

                                        <h3>3.1 Cách thức đổi trả sản phẩm</h3>

                                        <h4>Đối với khách hàng Website/Cửa hàng</h4>
                                        <ul>
                                            <li>
                                                Liên hệ đội ngũ CSKH qua:
                                                <a href="tel:0945090089">hotline (09.4509.0089)</a>
                                                hoặc bất kì một phương tiện truyền thông nào của Vector Sport
                                                (Fanpage/Zalo OA/Instagram) để được hướng dẫn chi tiết và nhanh nhất.
                                            </li>
                                            <li>Hoặc đến cửa hàng gần nhất của Vector Sport.</li>
                                        </ul>

                                        <h3>Đối với khách hàng Shopee/Tiktok</h3>
                                        <ul>
                                            <li>
                                                <strong>Trả hàng:</strong> vui lòng liên hệ Shopee/Tiktok để tiến hành
                                                trả hàng theo quy định của sàn TMĐT.
                                            </li>
                                            <li>
                                                <strong>Đổi hàng:</strong> Áp dụng chính sách đổi hàng tương tự khi mua
                                                tại Website/Cửa hàng.
                                            </li>
                                        </ul>

                                        <h3>3.2 Hoàn tiền với sản phẩm trả</h3>
                                        <p>
                                            Giá trị tiền hoàn sẽ được tính vào công nợ và quản lý bằng số điện thoại
                                            khách hàng.
                                            Tiền hoàn sẽ được dùng cho các đơn hàng tiếp theo.
                                        </p>

                                        <p>
                                            <strong>Lưu ý:</strong> giá trị đổi của sản phẩm được tính theo giá trị của
                                            sản phẩm tại thời điểm khách hàng thanh toán.
                                        </p>

                                        <h3>4. Chi phí đổi trả sản phẩm</h3>
                                        <ul>
                                            <li>Không mất phí đối với những lý do khách quan: Lỗi sản xuất, giao nhầm,
                                                giao thiếu.</li>
                                            <li>Miễn phí 01 lần trong trường hợp lý do chủ quan: không vừa size, không
                                                thích sản phẩm*</li>
                                            <li>
                                                Từ lần đổi thứ 2, quý khách vui lòng thanh toán phí vận chuyển trong
                                                trường hợp sản phẩm cần chuyển về đổi tại kho hàng của Vector Sport.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <div class="vs-sp-sticky js-product-sticky">
            <div class="vs-sp-sticky__inner">
                <div class="vs-sp-sticky__info">
                    <?php if ($product_thumb) : ?>
                    <img class="js-sticky-image"
                        src="<?php echo esc_url(!empty($active_gallery[0]['thumb']) ? $active_gallery[0]['thumb'] : $product_thumb); ?>"
                        alt="<?php echo esc_attr($product_title); ?>">
                    <?php endif; ?>

                    <div>
                        <div class="vs-sp-sticky__price-row">
                            <?php if ($gia_tien) : ?>
                            <span class="vs-sp-sticky__price-current"><?php echo esc_html($gia_tien); ?></span>
                            <?php endif; ?>

                            <?php if ($gia_cu) : ?>
                            <span class="vs-sp-sticky__price-old"><?php echo esc_html($gia_cu); ?></span>
                            <?php endif; ?>

                            <?php if ($phan_tram_giam) : ?>
                            <span class="vs-sp-sticky__discount"><?php echo esc_html($phan_tram_giam); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($tiet_kiem) : ?>
                        <div class="vs-sp-sticky__saving">(Tiết kiệm <?php echo esc_html($tiet_kiem); ?>)</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="vs-sp-sticky__controls">
                    <select class="vs-sp-sticky__select js-sticky-color">
                        <?php foreach ($product_colors as $index => $color) : ?>
                        <option value="<?php echo esc_attr($index); ?>"><?php echo esc_html($color['label']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <select class="vs-sp-sticky__select js-sticky-size">
                        <?php foreach ($product_sizes as $index => $size) : ?>
                        <option value="<?php echo esc_attr($size); ?>"><?php echo esc_html($size); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="vs-sp-sticky__qty js-qty-box">
                        <button type="button" class="vs-sp__qty-btn js-qty-minus">−</button>
                        <span class="vs-sp__qty-value js-qty-value">1</span>
                        <button type="button" class="vs-sp__qty-btn js-qty-plus">+</button>
                    </div>

                    <button class="vs-sp-sticky__button" type="button">THÊM VÀO GIỎ</button>
                </div>
            </div>
        </div>

        <?php if ($related_query->have_posts()) : ?>
        <section class="vs-sp-list-block">
            <h2 class="vs-sp-list-block__title">Sản phẩm cùng loại</h2>
            <div class="vs-sp-list-block__scroller">
                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                <?php vs_sp_render_loop_card(get_the_ID(), $product_tag_taxonomy); ?>
                <?php endwhile; ?>
            </div>
        </section>
        <?php wp_reset_postdata(); ?>
        <?php endif; ?>

        <?php if ($recent_query && $recent_query->have_posts()) : ?>
        <section class="vs-sp-list-block">
            <h2 class="vs-sp-list-block__title">Sản phẩm đã xem</h2>
            <div class="vs-sp-list-block__scroller">
                <?php while ($recent_query->have_posts()) : $recent_query->the_post(); ?>
                <?php vs_sp_render_loop_card(get_the_ID(), $product_tag_taxonomy); ?>
                <?php endwhile; ?>
            </div>
        </section>
        <?php wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>
</main>
<?php endwhile; ?>
<?php endif; ?>

<?php get_footer(); ?>