<?php
/**
 * Template Name: Sitemap
 */

get_header();

$excluded_page_slugs = [
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
];

$pages = get_posts([
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
]);

$product_categories = taxonomy_exists('product_category') ? get_terms([
    'taxonomy'   => 'product_category',
    'hide_empty' => true,
]) : [];

$products = get_posts([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

$posts = get_posts([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

$categories = get_terms([
    'taxonomy'   => 'category',
    'hide_empty' => true,
]);
?>

<main class="site-sitemap-page">
    <section class="site-sitemap-hero">
        <div class="container">
            <div class="site-sitemap-hero__content">
                <p class="site-sitemap-hero__eyebrow">Sitemap</p>

                <h1 class="site-sitemap-hero__title">
                    Sơ đồ trang Vector Sport
                </h1>

                <p class="site-sitemap-hero__desc">
                    Tổng hợp các trang chính, danh mục sản phẩm, sản phẩm và bài viết mới nhất trên website.
                </p>
            </div>
        </div>
    </section>

    <section class="site-sitemap-content">
        <div class="container">
            <div class="site-sitemap-grid">
                <div class="site-sitemap-card">
                    <h2>Trang chính</h2>

                    <ul>
                        <?php foreach ($pages as $page) : ?>
                            <?php
                            $slug = get_post_field('post_name', $page->ID);

                            if (in_array($slug, $excluded_page_slugs, true)) {
                                continue;
                            }
                            ?>

                            <li>
                                <a href="<?php echo esc_url(get_permalink($page->ID)); ?>">
                                    <?php echo esc_html(get_the_title($page->ID)); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php if (!empty($product_categories) && !is_wp_error($product_categories)) : ?>
                    <div class="site-sitemap-card">
                        <h2>Danh mục sản phẩm</h2>

                        <ul>
                            <?php foreach ($product_categories as $term) : ?>
                                <li>
                                    <a href="<?php echo esc_url(get_term_link($term)); ?>">
                                        <?php echo esc_html($term->name); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($products)) : ?>
                    <div class="site-sitemap-card">
                        <h2>Sản phẩm mới nhất</h2>

                        <ul>
                            <?php foreach ($products as $product) : ?>
                                <li>
                                    <a href="<?php echo esc_url(get_permalink($product->ID)); ?>">
                                        <?php echo esc_html(get_the_title($product->ID)); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                    <div class="site-sitemap-card">
                        <h2>Danh mục bài viết</h2>

                        <ul>
                            <?php foreach ($categories as $term) : ?>
                                <li>
                                    <a href="<?php echo esc_url(get_term_link($term)); ?>">
                                        <?php echo esc_html($term->name); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($posts)) : ?>
                    <div class="site-sitemap-card site-sitemap-card--wide">
                        <h2>Bài viết mới nhất</h2>

                        <ul class="site-sitemap-list--columns">
                            <?php foreach ($posts as $post_item) : ?>
                                <li>
                                    <a href="<?php echo esc_url(get_permalink($post_item->ID)); ?>">
                                        <?php echo esc_html(get_the_title($post_item->ID)); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="site-sitemap-card">
                    <h2>Hỗ trợ</h2>

                    <ul>
                        <li>
                            <a href="<?php echo esc_url(home_url('/lien-he/')); ?>">
                                Liên hệ / Đăng ký tư vấn
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo esc_url(home_url('/?s=vovinam')); ?>">
                                Tìm kiếm Vovinam
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo esc_url(home_url('/?s=vo-phuc')); ?>">
                                Tìm kiếm võ phục
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo esc_url(home_url('/?s=tất thể thao')); ?>">
                                Tìm kiếm tất thể thao
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>