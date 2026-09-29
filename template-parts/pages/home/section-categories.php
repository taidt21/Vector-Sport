<?php
/**
 * Section: Categories (Home)
 */

$theme_uri = get_template_directory_uri();

$product_url = home_url('/san-pham/');
$contact_url = home_url('/lien-he/');

$categories = [
    [
        'title' => 'Lớp học Vovinam',
        'desc'  => 'Dành cho thiếu nhi, học sinh, sinh viên và người mới bắt đầu muốn rèn thể lực, kỹ thuật và tinh thần kỷ luật.',
        'image' => $theme_uri . '/assets/images/home/about/about-1.jpg',
        'alt'   => 'Lớp học Vovinam cho thiếu nhi và học sinh tại Vector Sport',
        'url'   => $contact_url,
        'cta'   => 'Đăng ký tư vấn',
    ],
    [
        'title' => 'Võ phục & đai tập',
        'desc'  => 'Những sản phẩm cơ bản cần có để bắt đầu hành trình tập luyện Vovinam một cách gọn gàng và đúng chuẩn.',
        'image' => $theme_uri . '/assets/images/home/hero/background-1.jpg',
        'alt'   => 'Võ phục và đai tập Vovinam tại Vector Sport',
        'url'   => $product_url,
        'cta'   => 'Xem sản phẩm',
    ],
    [
        'title' => 'Đồ tập & bảo hộ',
        'desc'  => 'Phù hợp cho nhu cầu tập luyện thường xuyên, hỗ trợ vận động an toàn và thoải mái hơn trong quá trình học.',
        'image' => $theme_uri . '/assets/images/home/hero/background-2.jpg',
        'alt'   => 'Đồ tập và phụ kiện bảo hộ thể thao cho học viên',
        'url'   => $product_url,
        'cta'   => 'Xem sản phẩm',
    ],
    [
        'title' => 'Phụ kiện & đồng phục đội nhóm',
        'desc'  => 'Giải pháp dành cho học viên, câu lạc bộ và đội nhóm cần chuẩn bị đồng bộ khi tập luyện và sinh hoạt.',
        'image' => $theme_uri . '/assets/images/home/about/about-2.jpg',
        'alt'   => 'Phụ kiện và đồng phục đội nhóm cho câu lạc bộ Vovinam',
        'url'   => $product_url,
        'cta'   => 'Xem sản phẩm',
    ],
];
?>

<section class="home_categories" id="categories" aria-labelledby="home-categories-title">
    <div class="container">
        <div class="home_section_heading">
            <div class="home_section_heading__content">
                <p class="home_section_heading__kicker">Danh mục nổi bật</p>

                <h2 class="home_section_heading__title" id="home-categories-title">
                    Khám phá lớp học Vovinam và sản phẩm tập luyện nổi bật
                </h2>

                <p class="home_section_heading__desc">
                    Từ lớp học Vovinam bài bản đến võ phục, đồ tập và phụ kiện, Vector Sport mang đến những lựa chọn thiết thực cho học viên, phụ huynh và người yêu thể thao.
                </p>
            </div>

            <a
                class="home_section_heading__link"
                href="<?php echo esc_url($product_url); ?>"
                aria-label="Xem tất cả sản phẩm Vector Sport"
            >
                Xem sản phẩm
            </a>
        </div>

        <div class="home_categories__grid">
            <?php foreach ($categories as $item): ?>
                <a
                    class="home_categories__card"
                    href="<?php echo esc_url($item['url']); ?>"
                    aria-label="<?php echo esc_attr($item['cta'] . ': ' . $item['title']); ?>"
                >
                    <div class="home_categories__media">
                        <img
                            src="<?php echo esc_url($item['image']); ?>"
                            alt="<?php echo esc_attr($item['alt']); ?>"
                            loading="lazy"
                            decoding="async"
                            width="420"
                            height="320"
                        />
                    </div>

                    <div class="home_categories__body">
                        <h3 class="home_categories__title">
                            <?php echo esc_html($item['title']); ?>
                        </h3>

                        <p class="home_categories__desc">
                            <?php echo esc_html($item['desc']); ?>
                        </p>

                        <span class="home_categories__more">
                            <?php echo esc_html($item['cta']); ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path fill="currentColor" d="M13 5l7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5z"/>
                            </svg>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>