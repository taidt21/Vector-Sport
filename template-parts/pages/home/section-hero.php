<?php
/**
 * Section: Hero Slider (Home)
 */

$theme_uri = get_template_directory_uri();

$slides = [
    [
        'kicker' => 'TUYỂN SINH VOVINAM',
        'title'  => "Tuyển sinh lớp học\nVovinam tại Hà Nội",
        'desc'   => 'Lớp Vovinam dành cho thiếu nhi, học sinh và người mới bắt đầu. Môi trường tập luyện tích cực, kỷ luật và phù hợp để rèn luyện thể chất, tự tin và kỹ năng sống.',
        'image'  => $theme_uri . '/assets/images/home/hero/background-1.jpg',
        'image_alt' => 'Lớp học Vovinam cho thiếu nhi và học sinh tại Hà Nội',
        'btn'    => [
            'text' => 'Đăng ký học thử',
            'url'  => home_url('/lien-he/'),
        ],
        'cta_title' => 'Lớp học từ cơ bản đến nâng cao',
        'cta_sub'   => 'Phù hợp cho thiếu nhi, học sinh và người mới bắt đầu',
        'thumbs' => [
            $theme_uri . '/assets/images/home/hero/background-1.jpg',
            $theme_uri . '/assets/images/home/hero/background-1-2.jpg',
            $theme_uri . '/assets/images/home/hero/background-1-3.jpg',
            $theme_uri . '/assets/images/home/hero/background-1-4.jpg',
        ],
    ],
    [
        'kicker' => 'ĐỒ TẬP & PHỤ KIỆN',
        'title'  => "Đồ tập và phụ kiện\ncho học viên Vovinam",
        'desc'   => 'Vector Sport cung cấp võ phục, đai, đồ tập và phụ kiện thiết yếu giúp học viên chuẩn bị tốt hơn cho quá trình tập luyện hằng ngày.',
        'image'  => $theme_uri . '/assets/images/home/hero/background-2.jpg',
        'image_alt' => 'Võ phục, đồ tập và phụ kiện cho học viên Vovinam',
        'btn'    => [
            'text' => 'Xem sản phẩm',
            'url'  => home_url('/product/'),
        ],
        'cta_title' => 'Sản phẩm thiết yếu cho học viên',
        'cta_sub'   => 'Võ phục, đồ tập, phụ kiện và tư vấn nhanh theo nhu cầu',
        'thumbs' => [
            $theme_uri . '/assets/images/home/hero/background-2.jpg',
            $theme_uri . '/assets/images/home/hero/background-2-2.jpg',
            $theme_uri . '/assets/images/home/hero/background-2-3.jpg',
        ],
    ],
];
?>

<section class="home_hero" aria-labelledby="home-hero-title">
    <div class="home_hero__swiper swiper js-home-hero" aria-label="Nội dung nổi bật trên trang chủ Vector Sport">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $idx => $s): ?>
                <?php
                $thumbs = !empty($s['thumbs']) && is_array($s['thumbs']) ? $s['thumbs'] : [$s['image']];

                if (!empty($s['image'])) {
                    array_unshift($thumbs, $s['image']);
                }

                $thumbs_unique = [];
                $thumbs_clean = [];

                foreach ($thumbs as $thumb) {
                    if (!$thumb || isset($thumbs_unique[$thumb])) {
                        continue;
                    }

                    $thumbs_unique[$thumb] = true;
                    $thumbs_clean[] = $thumb;
                }

                $thumbs = $thumbs_clean;
                $title_tag = $idx === 0 ? 'h1' : 'h2';
                $title_id = $idx === 0 ? 'home-hero-title' : 'home-hero-title-' . $idx;
                $image_alt = !empty($s['image_alt']) ? $s['image_alt'] : $s['title'];
                ?>

                <div class="swiper-slide home_hero__slide" data-slide-key="<?php echo esc_attr($idx); ?>">
                    <div class="home_hero__bg" style="background-image:url('<?php echo esc_url($s['image']); ?>')" role="img" aria-label="<?php echo esc_attr($image_alt); ?>"></div>
                    <div class="home_hero__overlay" aria-hidden="true"></div>

                    <div class="container home_hero__container">
                        <div class="home_hero__content">
                            <p class="home_hero__kicker"><?php echo esc_html($s['kicker']); ?></p>

                            <<?php echo esc_attr($title_tag); ?> id="<?php echo esc_attr($title_id); ?>" class="home_hero__title">
                                <?php echo nl2br(esc_html($s['title'])); ?>
                            </<?php echo esc_attr($title_tag); ?>>

                            <p class="home_hero__desc"><?php echo esc_html($s['desc']); ?></p>

                            <div class="home_hero__cta">
                                <div class="home_hero__avatars" aria-label="Chọn ảnh nền">
                                    <?php foreach ($thumbs as $ti => $img): ?>
                                        <button
                                            type="button"
                                            class="home_hero__avatar js-hero-thumb <?php echo $ti === 0 ? 'is-active' : ''; ?>"
                                            data-slide-key="<?php echo esc_attr($idx); ?>"
                                            data-src="<?php echo esc_url($img); ?>"
                                            aria-pressed="<?php echo $ti === 0 ? 'true' : 'false'; ?>"
                                            aria-label="<?php echo esc_attr('Chọn ảnh minh họa ' . ($ti + 1)); ?>"
                                        >
                                            <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy" decoding="async" width="80" height="80" />
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <div class="home_hero__cta_text">
                                    <div class="home_hero__cta_title"><?php echo esc_html($s['cta_title']); ?></div>
                                    <div class="home_hero__cta_sub"><?php echo esc_html($s['cta_sub']); ?></div>
                                </div>

                                <a class="home_hero__btn" href="<?php echo esc_url($s['btn']['url']); ?>">
                                    <span><?php echo esc_html($s['btn']['text']); ?></span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path fill="currentColor" d="M13 5l7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5z" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div class="home_hero__spacer" aria-hidden="true"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="home_hero__pagination swiper-pagination" aria-hidden="true"></div>
    </div>
</section>