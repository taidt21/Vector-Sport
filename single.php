<?php
defined('ABSPATH') || exit;

get_header();

$theme_uri = get_template_directory_uri();
$fallback_image = $theme_uri . '/assets/images/pages/hoat-dong/post-fallback.webp';
$activities_url = home_url('/hoat-dong/');
$contact_url = home_url('/lien-he/');
$vovinam_url = home_url('/vovinam/');

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $post_id = get_the_ID();
        $post_title = get_the_title($post_id);

        $thumb = has_post_thumbnail($post_id)
            ? get_the_post_thumbnail_url($post_id, 'full')
            : $fallback_image;

        $categories = get_the_category($post_id);
        $primary_cat = !empty($categories) ? $categories[0] : null;
        $primary_cat_name = $primary_cat ? $primary_cat->name : 'Hoạt động';
        $primary_cat_link = $primary_cat ? get_category_link($primary_cat->term_id) : '';

        $related_args = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => 3,
            'post__not_in'        => [$post_id],
            'ignore_sticky_posts' => true,
        ];

        if ($primary_cat) {
            $related_args['category__in'] = [$primary_cat->term_id];
        }

        $related_query = new WP_Query($related_args);
?>

<main class="single_post_page" id="main-content">
    <section class="single_post_page__hero" aria-labelledby="single-post-title"  style="--single-post-hero-bg: url('<?php echo esc_url($thumb); ?>');">
        <div class="container">
            <nav class="single_post_page__breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a>
                <span aria-hidden="true">/</span>
                <a href="<?php echo esc_url($activities_url); ?>">Hoạt động</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page"><?php echo esc_html($post_title); ?></span>
            </nav>

            <div class="single_post_page__hero_inner">
                <div class="single_post_page__meta_top">
                    <?php if ($primary_cat_link) : ?>
                        <a class="single_post_page__cat" href="<?php echo esc_url($primary_cat_link); ?>">
                            <?php echo esc_html($primary_cat_name); ?>
                        </a>
                    <?php else : ?>
                        <span class="single_post_page__cat"><?php echo esc_html($primary_cat_name); ?></span>
                    <?php endif; ?>

                    <time class="single_post_page__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                        <?php echo esc_html(get_the_date('d/m/Y')); ?>
                    </time>
                </div>

                <h1 class="single_post_page__title" id="single-post-title">
                    <?php echo esc_html($post_title); ?>
                </h1>

                <?php if (has_excerpt($post_id)) : ?>
                    <p class="single_post_page__excerpt">
                        <?php echo esc_html(get_the_excerpt($post_id)); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="single_post_page__main">
        <div class="container">
            <div class="single_post_page__layout">
                <article class="single_post_page__article">
                    <!-- <div class="single_post_page__featured_media">
                        <img
                            src="<?php echo esc_url($thumb); ?>"
                            alt="<?php echo esc_attr($post_title); ?>"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                            width="960"
                            height="640">
                    </div> -->

                    <div class="single_post_page__content">
                        <?php the_content(); ?>

                        <?php
                        wp_link_pages([
                            'before' => '<div class="single_post_page__page_links">',
                            'after'  => '</div>',
                        ]);
                        ?>
                    </div>

                    <div class="single_post_page__article_footer">
                        <a class="single_post_page__back_link" href="<?php echo esc_url($activities_url); ?>">
                            ← Quay lại trang Hoạt động
                        </a>
                    </div>
                </article>

                <aside class="single_post_page__sidebar" aria-label="Thông tin và liên hệ bài viết">
                    <div class="single_post_page__sidebar_box">
                        <h3 class="single_post_page__sidebar_title">Thông tin bài viết</h3>

                        <div class="single_post_page__info_list">
                            <div class="single_post_page__info_item">
                                <span class="single_post_page__info_label">Danh mục</span>
                                <span class="single_post_page__info_value"><?php echo esc_html($primary_cat_name); ?></span>
                            </div>

                            <div class="single_post_page__info_item">
                                <span class="single_post_page__info_label">Ngày đăng</span>
                                <time class="single_post_page__info_value" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                    <?php echo esc_html(get_the_date('d/m/Y')); ?>
                                </time>
                            </div>

                            <div class="single_post_page__info_item">
                                <span class="single_post_page__info_label">Tác giả</span>
                                <span class="single_post_page__info_value"><?php echo esc_html(get_the_author()); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="single_post_page__sidebar_box">
                        <h3 class="single_post_page__sidebar_title">Liên hệ nhanh</h3>

                        <p class="single_post_page__sidebar_desc">
                            Nếu bạn muốn tìm hiểu thêm về lớp học, hoạt động hoặc môi trường tập luyện phù hợp, hãy liên hệ với Vector Sport để được tư vấn.
                        </p>

                        <div class="single_post_page__sidebar_actions">
                            <a class="single_post_page__btn single_post_page__btn--primary" href="<?php echo esc_url($contact_url); ?>">
                                Liên hệ tư vấn
                            </a>

                            <a class="single_post_page__btn single_post_page__btn--ghost" href="<?php echo esc_url($vovinam_url); ?>">
                                Xem lớp Vovinam
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <?php if ($related_query->have_posts()) : ?>
        <section class="single_post_page__related" aria-labelledby="single-related-title">
            <div class="container">
                <div class="single_post_page__heading">
                    <p class="single_post_page__heading_kicker">Bài viết liên quan</p>

                    <h2 class="single_post_page__heading_title" id="single-related-title">
                        Tiếp tục khám phá các hoạt động khác
                    </h2>
                </div>

                <div class="single_post_page__related_grid">
                    <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                        <?php
                        $related_id = get_the_ID();
                        $related_title = get_the_title($related_id);

                        $related_thumb = has_post_thumbnail($related_id)
                            ? get_the_post_thumbnail_url($related_id, 'medium_large')
                            : $fallback_image;

                        $related_categories = get_the_category($related_id);
                        $related_primary_cat = !empty($related_categories) ? $related_categories[0]->name : 'Hoạt động';
                        ?>

                        <article class="single_post_page__related_card">
                            <a
                                class="single_post_page__related_media"
                                href="<?php echo esc_url(get_permalink($related_id)); ?>"
                                aria-label="<?php echo esc_attr('Xem bài viết: ' . $related_title); ?>"
                            >
                                <img
                                    src="<?php echo esc_url($related_thumb); ?>"
                                    alt="<?php echo esc_attr($related_title); ?>"
                                    loading="lazy"
                                    decoding="async"
                                    width="420"
                                    height="280">
                            </a>

                            <div class="single_post_page__related_body">
                                <span class="single_post_page__related_cat">
                                    <?php echo esc_html($related_primary_cat); ?>
                                </span>

                                <h3 class="single_post_page__related_title">
                                    <a href="<?php echo esc_url(get_permalink($related_id)); ?>">
                                        <?php echo esc_html($related_title); ?>
                                    </a>
                                </h3>

                                <p class="single_post_page__related_excerpt">
                                    <?php echo esc_html(wp_trim_words(get_the_excerpt($related_id), 18, '...')); ?>
                                </p>

                                <a class="single_post_page__readmore" href="<?php echo esc_url(get_permalink($related_id)); ?>">
                                    Xem chi tiết
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php
    endwhile;
endif;

get_footer();