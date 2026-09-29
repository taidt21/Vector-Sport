<?php
/**
 * Search results template
 */

get_header();

$keyword = get_search_query();

$product_query = new WP_Query([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    's'              => $keyword,
    'posts_per_page' => 8,
]);

$post_query = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    's'              => $keyword,
    'posts_per_page' => 6,
]);
?>

<main class="search-page">
    <section class="search-hero">
        <div class="container">
            <div class="search-hero__inner">
                <p class="search-hero__eyebrow">Tìm kiếm</p>

                <h1 class="search-hero__title">
                    Kết quả tìm kiếm cho: 
                    <span><?php echo esc_html($keyword); ?></span>
                </h1>

                <form class="search-hero__form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <input 
                        type="search" 
                        name="s" 
                        value="<?php echo esc_attr($keyword); ?>" 
                        placeholder="Tìm sản phẩm, bài viết, lớp học..."
                    >

                    <button type="submit">
                        Tìm kiếm
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="search-results">
        <div class="container">
            <?php if ($product_query->have_posts()) : ?>
                <div class="search-section">
                    <div class="search-section__head">
                        <h2>Sản phẩm phù hợp</h2>
                        <p><?php echo esc_html($product_query->found_posts); ?> sản phẩm được tìm thấy</p>
                    </div>

                    <div class="search-products">
                        <?php while ($product_query->have_posts()) : $product_query->the_post(); ?>
                            <?php
                            $product_id = get_the_ID();
                            $price = get_field('gia_tien', $product_id);
                            $old_price = get_field('gia_cu', $product_id);
                            $thumbnail = get_the_post_thumbnail_url($product_id, 'medium_large');

                            if (!$thumbnail) {
                                $thumbnail = get_template_directory_uri() . '/assets/images/no-image.jpg';
                            }
                            ?>

                            <article class="search-product-card">
                                <a class="search-product-card__image" href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                </a>

                                <div class="search-product-card__body">
                                    <h3>
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>

                                    <?php if ($price) : ?>
                                        <div class="search-product-card__price">
                                            <strong><?php echo esc_html(number_format((float) $price, 0, ',', '.')); ?>đ</strong>

                                            <?php if ($old_price) : ?>
                                                <del><?php echo esc_html(number_format((float) $old_price, 0, ',', '.')); ?>đ</del>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <a class="search-product-card__link" href="<?php the_permalink(); ?>">
                                        Xem sản phẩm
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>

            <?php if ($post_query->have_posts()) : ?>
                <div class="search-section">
                    <div class="search-section__head">
                        <h2>Bài viết và hoạt động</h2>
                        <p><?php echo esc_html($post_query->found_posts); ?> bài viết được tìm thấy</p>
                    </div>

                    <div class="search-posts">
                        <?php while ($post_query->have_posts()) : $post_query->the_post(); ?>
                            <?php
                            $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');

                            if (!$thumbnail) {
                                $thumbnail = get_template_directory_uri() . '/assets/images/no-image.jpg';
                            }
                            ?>

                            <article class="search-post-card">
                                <a class="search-post-card__image" href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                </a>

                                <div class="search-post-card__body">
                                    <div class="search-post-card__meta">
                                        <?php echo esc_html(get_the_date('d/m/Y')); ?>
                                    </div>

                                    <h3>
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>

                                    <p>
                                        <?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?>
                                    </p>

                                    <a class="search-post-card__link" href="<?php the_permalink(); ?>">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>

            <?php if (!$product_query->have_posts() && !$post_query->have_posts()) : ?>
                <div class="search-empty">
                    <h2>Không tìm thấy kết quả phù hợp</h2>
                    <p>Hãy thử tìm bằng từ khóa ngắn hơn như “võ phục”, “Vovinam”, “tất thể thao”, “lớp học”.</p>

                    <form class="search-empty__form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <input 
                            type="search" 
                            name="s" 
                            placeholder="Nhập từ khóa khác..."
                        >

                        <button type="submit">
                            Tìm lại
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>