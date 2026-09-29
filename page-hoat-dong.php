<?php
/**
 * Template Name: Page Hoat Dong
 */

defined('ABSPATH') || exit;

get_header();

$paged = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);

$theme_uri = get_template_directory_uri();
$fallback_image = $theme_uri . '/assets/images/hoat-dong/background-tintuc.jpg';

$featured_query = new WP_Query([
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => 1,
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
]);

$exclude_ids = [];

if ($featured_query->have_posts()) {
    while ($featured_query->have_posts()) {
        $featured_query->the_post();
        $exclude_ids[] = get_the_ID();
    }

    wp_reset_postdata();
}

$news_query = new WP_Query([
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => 9,
    'paged'               => $paged,
    'post__not_in'        => $paged === 1 ? $exclude_ids : [],
    'ignore_sticky_posts' => true,
]);
?>

<main class="activity_page" id="main-content">
    <section class="activity_page__hero" aria-labelledby="activity-page-title">
        <div class="container">
            <div class="activity_page__hero_inner">
                <p class="activity_page__kicker">HOẠT ĐỘNG</p>

                <h1 class="activity_page__title" id="activity-page-title">
                    Tin tức, sự kiện
                </h1>

                <p class="activity_page__desc">
                    Cập nhật các hoạt động tập luyện, giao lưu, sự kiện cộng đồng, cột mốc rèn luyện và những câu chuyện đáng chú ý trong hành trình phát triển của Vector Sport và Vovinam Cụm 5 Hà Nội.
                </p>
            </div>
        </div>
    </section>

    <?php if ($paged === 1 && $featured_query->have_posts()) : ?>
        <section class="activity_page__featured" aria-labelledby="activity-featured-title">
            <div class="container">
                <h2 class="activity_page__section_title" id="activity-featured-title">
                    Bài viết mới nhất
                </h2>

                <?php
                while ($featured_query->have_posts()) :
                    $featured_query->the_post();

                    $post_id = get_the_ID();
                    $post_title = get_the_title($post_id);
                    $post_url = get_permalink($post_id);

                    $thumb = has_post_thumbnail($post_id)
                        ? get_the_post_thumbnail_url($post_id, 'large')
                        : $fallback_image;

                    $categories = get_the_category($post_id);
                    $primary_cat = !empty($categories) ? $categories[0]->name : 'Hoạt động';
                ?>
                    <article class="activity_page__featured_post">
                        <a
                            class="activity_page__featured_media"
                            href="<?php echo esc_url($post_url); ?>"
                            aria-label="<?php echo esc_attr('Xem bài viết: ' . $post_title); ?>"
                        >
                            <img
                                src="<?php echo esc_url($thumb); ?>"
                                alt="<?php echo esc_attr($post_title); ?>"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                                width="960"
                                height="640">
                        </a>

                        <div class="activity_page__featured_body">
                            <span class="activity_page__featured_cat">
                                <?php echo esc_html($primary_cat); ?>
                            </span>

                            <h3 class="activity_page__featured_title">
                                <a href="<?php echo esc_url($post_url); ?>">
                                    <?php echo esc_html($post_title); ?>
                                </a>
                            </h3>

                            <div class="activity_page__meta">
                                <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                    <?php echo esc_html(get_the_date('d/m/Y', $post_id)); ?>
                                </time>
                            </div>

                            <p class="activity_page__featured_excerpt">
                                <?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 32, '...')); ?>
                            </p>

                            <a
                                class="activity_page__readmore"
                                href="<?php echo esc_url($post_url); ?>"
                                aria-label="<?php echo esc_attr('Xem chi tiết bài viết: ' . $post_title); ?>"
                            >
                                Xem chi tiết
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="activity_page__listing" aria-labelledby="activity-listing-title">
        <div class="container">
            <div class="activity_page__heading">
                <h2 class="activity_page__section_title" id="activity-listing-title">
                    <?php echo $paged > 1 ? 'Bài viết hoạt động' : 'Các bài viết khác'; ?>
                </h2>
            </div>

            <?php if ($news_query->have_posts()) : ?>
                <div class="activity_page__grid">
                    <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                        <?php
                        $post_id = get_the_ID();
                        $post_title = get_the_title($post_id);
                        $post_url = get_permalink($post_id);

                        $thumb = has_post_thumbnail($post_id)
                            ? get_the_post_thumbnail_url($post_id, 'medium_large')
                            : $fallback_image;

                        $categories = get_the_category($post_id);
                        $primary_cat = !empty($categories) ? $categories[0]->name : 'Hoạt động';
                        ?>

                        <article class="activity_page__card">
                            <a
                                class="activity_page__card_media"
                                href="<?php echo esc_url($post_url); ?>"
                                aria-label="<?php echo esc_attr('Xem bài viết: ' . $post_title); ?>"
                            >
                                <img
                                    src="<?php echo esc_url($thumb); ?>"
                                    alt="<?php echo esc_attr($post_title); ?>"
                                    loading="lazy"
                                    decoding="async"
                                    width="420"
                                    height="280">
                            </a>

                            <div class="activity_page__card_body">
                                <span class="activity_page__card_cat">
                                    <?php echo esc_html($primary_cat); ?>
                                </span>

                                <h3 class="activity_page__card_title">
                                    <a href="<?php echo esc_url($post_url); ?>">
                                        <?php echo esc_html($post_title); ?>
                                    </a>
                                </h3>

                                <div class="activity_page__meta">
                                    <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                        <?php echo esc_html(get_the_date('d/m/Y', $post_id)); ?>
                                    </time>
                                </div>

                                <p class="activity_page__card_excerpt">
                                    <?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 18, '...')); ?>
                                </p>

                                <a
                                    class="activity_page__readmore"
                                    href="<?php echo esc_url($post_url); ?>"
                                    aria-label="<?php echo esc_attr('Xem chi tiết bài viết: ' . $post_title); ?>"
                                >
                                    Xem chi tiết
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>

                <?php
                $pagination = paginate_links([
                    'total'     => $news_query->max_num_pages,
                    'current'   => $paged,
                    'type'      => 'list',
                    'prev_text' => '←',
                    'next_text' => '→',
                    'end_size'  => 1,
                    'mid_size'  => 1,
                ]);

                if ($pagination) :
                ?>
                    <nav class="activity_page__pagination" aria-label="Phân trang bài viết">
                        <?php echo wp_kses_post($pagination); ?>
                    </nav>
                <?php endif; ?>

            <?php else : ?>
                <div class="activity_page__empty">
                    <h2>Chưa có bài viết nào</h2>
                    <p>Hiện tại chưa có nội dung hoạt động để hiển thị. Bạn có thể quay lại sau khi bài viết được cập nhật.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>