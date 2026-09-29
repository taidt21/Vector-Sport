<?php
get_header();

$home_url    = home_url('/');
$shop_url    = post_type_exists('product') ? get_post_type_archive_link('product') : home_url('/san-pham/');
$contact_url = home_url('/lien-he/');
?>

<main class="vs-404-page">
    <section class="vs-404">
        <div class="container">
            <div class="vs-404__inner">
                <div class="vs-404__content">
                    <span class="vs-404__eyebrow">Vector Sport</span>

                    <h1 class="vs-404__code">404</h1>

                    <h2 class="vs-404__title">
                        Trang bạn đang tìm không tồn tại
                    </h2>

                    <p class="vs-404__desc">
                        Có thể đường dẫn đã thay đổi, nội dung đã được di chuyển hoặc bạn nhập sai địa chỉ.
                        Hãy quay về trang chủ, tiếp tục xem sản phẩm hoặc tìm nhanh nội dung bạn cần.
                    </p>

                    <form class="vs-404__search" method="get" action="<?php echo esc_url($home_url); ?>">
                        <label class="screen-reader-text" for="vs-404-search">
                            Tìm kiếm
                        </label>
                        <input
                            id="vs-404-search"
                            type="search"
                            name="s"
                            placeholder="Tìm sản phẩm, bài viết..."
                            value="<?php echo esc_attr(get_search_query()); ?>"
                        />
                        <button type="submit">Tìm kiếm</button>
                    </form>

                    <div class="vs-404__actions">
                        <a class="vs-404__btn vs-404__btn--primary" href="<?php echo esc_url($home_url); ?>">
                            Về trang chủ
                        </a>

                        <a class="vs-404__btn vs-404__btn--secondary" href="<?php echo esc_url($shop_url); ?>">
                            Xem sản phẩm
                        </a>

                        <a class="vs-404__btn vs-404__btn--ghost" href="<?php echo esc_url($contact_url); ?>">
                            Liên hệ hỗ trợ
                        </a>
                    </div>
                </div>

                <div class="vs-404__visual">
                    <div class="vs-404__card">
                        <div class="vs-404__badge">Oops!</div>

                        <div class="vs-404__number-wrap">
                            <span>4</span>
                            <span class="vs-404__ball"></span>
                            <span>4</span>
                        </div>

                        <p class="vs-404__card-text">
                            Không tìm thấy trang, nhưng bạn vẫn có thể tiếp tục khám phá các sản phẩm
                            thể thao, phụ kiện và nội dung nổi bật tại Vector Sport.
                        </p>

                        <ul class="vs-404__quick-links">
                            <li>
                                <a href="<?php echo esc_url($shop_url); ?>">Danh sách sản phẩm</a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url($home_url); ?>">Trang chủ</a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url($contact_url); ?>">Hỗ trợ khách hàng</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>