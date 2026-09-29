<?php
/**
 * Section: Featured Products (Home)
 */

$theme_uri = get_template_directory_uri();

$product_url = home_url('/product/');
$contact_url = home_url('/lien-he/');

$products = [
    [
        'badge'     => 'Cơ bản',
        'title'     => 'Võ phục và đai tập',
        'desc'      => 'Nhóm sản phẩm phù hợp cho học viên mới bắt đầu hoặc đang cần chuẩn bị trang phục tập luyện gọn gàng, đúng chuẩn.',
        'price'     => 'Liên hệ tư vấn',
        'old_price' => '',
        'image'     => $theme_uri . '/assets/images/home/about/about-1.jpg',
        'alt'       => 'Võ phục và đai tập Vovinam cho học viên mới bắt đầu',
        'url'       => $product_url,
    ],
    [
        'badge'     => 'Phổ biến',
        'title'     => 'Đồ tập thể thao',
        'desc'      => 'Áo, quần và đồ tập phục vụ nhu cầu vận động hằng ngày với cảm giác thoải mái và dễ sử dụng.',
        'price'     => 'Liên hệ tư vấn',
        'old_price' => '',
        'image'     => $theme_uri . '/assets/images/home/hero/background-1.jpg',
        'alt'       => 'Đồ tập thể thao thoải mái cho học viên và người tập luyện hằng ngày',
        'url'       => $product_url,
    ],
    [
        'badge'     => 'Thiết yếu',
        'title'     => 'Phụ kiện tập luyện',
        'desc'      => 'Những món phụ kiện cần thiết giúp học viên chuẩn bị đầy đủ hơn khi học võ và tham gia các buổi tập.',
        'price'     => 'Liên hệ tư vấn',
        'old_price' => '',
        'image'     => $theme_uri . '/assets/images/home/hero/background-2.jpg',
        'alt'       => 'Phụ kiện tập luyện cần thiết cho học viên Vovinam',
        'url'       => $product_url,
    ],
    [
        'badge'     => 'Đội nhóm',
        'title'     => 'Đồng phục cho câu lạc bộ',
        'desc'      => 'Giải pháp phù hợp cho lớp học, câu lạc bộ hoặc đội nhóm cần đồng bộ hình ảnh khi tập luyện và sinh hoạt.',
        'price'     => 'Liên hệ tư vấn',
        'old_price' => '',
        'image'     => $theme_uri . '/assets/images/home/about/about-2.jpg',
        'alt'       => 'Đồng phục câu lạc bộ Vovinam và đội nhóm thể thao',
        'url'       => $product_url,
    ],
];
?>

<section class="home_featured" id="products" aria-labelledby="home-featured-title">
    <div class="container">
        <div class="home_section_heading">
            <div class="home_section_heading__content">
                <p class="home_section_heading__kicker">Sản phẩm nổi bật</p>

                <h2 class="home_section_heading__title" id="home-featured-title">
                    Những nhóm sản phẩm được quan tâm nhiều
                </h2>

                <p class="home_section_heading__desc">
                    Vector Sport tập trung vào các sản phẩm thiết thực cho quá trình học Vovinam, tập luyện thể thao và chuẩn bị đồ dùng hằng ngày.
                </p>
            </div>

            <a
                class="home_section_heading__link"
                href="<?php echo esc_url($product_url); ?>"
                aria-label="Xem tất cả sản phẩm Vector Sport"
            >
                Xem tất cả
            </a>
        </div>

        <div class="home_featured__list">
            <?php foreach ($products as $item): ?>
                <article class="home_featured__card">
                    <a
                        class="home_featured__media"
                        href="<?php echo esc_url($item['url']); ?>"
                        aria-label="<?php echo esc_attr('Xem nhóm sản phẩm: ' . $item['title']); ?>"
                    >
                        <span class="home_featured__badge">
                            <?php echo esc_html($item['badge']); ?>
                        </span>

                        <img
                            src="<?php echo esc_url($item['image']); ?>"
                            alt="<?php echo esc_attr($item['alt']); ?>"
                            loading="lazy"
                            decoding="async"
                            width="420"
                            height="320"
                        />
                    </a>

                    <div class="home_featured__body">
                        <h3 class="home_featured__title">
                            <a href="<?php echo esc_url($item['url']); ?>">
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        </h3>

                        <p class="home_featured__desc">
                            <?php echo esc_html($item['desc']); ?>
                        </p>

                        <div class="home_featured__price" aria-label="Thông tin giá">
                            <span class="home_featured__price_current">
                                <?php echo esc_html($item['price']); ?>
                            </span>

                            <?php if (!empty($item['old_price'])): ?>
                                <span class="home_featured__price_old">
                                    <?php echo esc_html($item['old_price']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="home_featured__actions">
                            <a
                                class="home_featured__btn home_featured__btn--primary"
                                href="<?php echo esc_url($item['url']); ?>"
                                aria-label="<?php echo esc_attr('Xem sản phẩm thuộc nhóm ' . $item['title']); ?>"
                            >
                                Xem sản phẩm
                            </a>

                            <a
                                class="home_featured__btn home_featured__btn--ghost"
                                href="<?php echo esc_url($contact_url); ?>"
                                aria-label="<?php echo esc_attr('Nhận tư vấn nhanh về ' . $item['title']); ?>"
                            >
                                Tư vấn nhanh
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>