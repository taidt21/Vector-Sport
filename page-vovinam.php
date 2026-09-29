<?php
/**
 * Template Name: Page Vovinam
 */

defined('ABSPATH') || exit;

get_header();

$theme_uri = get_template_directory_uri();

$contact_url = home_url('/lien-he/');
$hotline = '0988 812 246';
$hotline_tel = '0988812246';
$address = 'GH3, CT17, Việt Hưng, Long Biên, Hà Nội';

$images = [
    'hero'       => $theme_uri . '/assets/images/tuyen-sinh-vovinam/hero_side_1.jpg',
    'class_main' => $theme_uri . '/assets/images/tuyen-sinh-vovinam/hero_side_2.jpg',
    'class_side' => $theme_uri . '/assets/images/tuyen-sinh-vovinam/class-side.jpg',
    'gallery_1'  => $theme_uri . '/assets/images/tuyen-sinh-vovinam/gallery-1.jpg',
    'gallery_2'  => $theme_uri . '/assets/images/tuyen-sinh-vovinam/gallery-2.jpg',
    'gallery_3'  => $theme_uri . '/assets/images/tuyen-sinh-vovinam/gallery-3.jpg',
];

$map_embed_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.3621277618486!2d105.9033221759991!3d21.058194086765564!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135a90035fb8683%3A0xa7675abe0638ca67!2zVm92aW5hbSBD4bulbSA1IEjDoCBO4buZaQ!5e0!3m2!1svi!2s!4v1777955568779!5m2!1svi!2s';
$map_open_url  = 'https://maps.app.goo.gl/h6Mmf7kRoPwd8Tr99';

$class_info = [
    [
        'label' => 'Độ tuổi',
        'value' => 'Từ 5 tuổi trở lên',
    ],
    [
        'label' => 'Khung giờ',
        'value' => 'Ca sáng, chiều, tối',
    ],
    [
        'label' => 'Hình thức',
        'value' => 'Lớp nhóm hoặc kèm riêng',
    ],
    [
        'label' => 'Khu vực',
        'value' => 'Việt Hưng, Long Biên, Hà Nội',
    ],
];

$training_values = [
    [
        'title' => 'Khỏe hơn qua từng buổi tập',
        'desc'  => 'Các bài tập giúp học viên vận động đều, cải thiện sức bền, phản xạ, sự linh hoạt và khả năng kiểm soát cơ thể.',
    ],
    [
        'title' => 'Kỷ luật hơn trong sinh hoạt',
        'desc'  => 'Việc đi học đều, lắng nghe hướng dẫn và hoàn thành từng phần bài tập giúp hình thành nề nếp một cách tự nhiên.',
    ],
    [
        'title' => 'Tự tin hơn khi ở trong tập thể',
        'desc'  => 'Học viên được khuyến khích tham gia, thể hiện và phối hợp cùng bạn tập trong môi trường tích cực.',
    ],
];

$audiences = [
    [
        'title' => 'Thiếu nhi',
        'desc'  => 'Làm quen vận động, rèn nề nếp, tăng sự mạnh dạn và tự tin từ sớm.',
    ],
    [
        'title' => 'Học sinh, sinh viên',
        'desc'  => 'Duy trì thể lực, giải tỏa năng lượng và xây dựng bản lĩnh qua môi trường tập luyện đều đặn.',
    ],
    [
        'title' => 'Người mới bắt đầu',
        'desc'  => 'Có thể bắt đầu từ nền tảng cơ bản, không cần kinh nghiệm võ thuật trước đó.',
    ],
];

$milestones = [
    [
        'title' => 'Làm quen lớp học',
        'desc'  => 'Học viên bắt đầu với nhịp tập phù hợp, được hướng dẫn từ những nội dung nền tảng.',
    ],
    [
        'title' => 'Rèn luyện đều đặn',
        'desc'  => 'Duy trì thói quen tập luyện, cải thiện thể lực và kỹ thuật theo từng giai đoạn.',
    ],
    [
        'title' => 'Tham gia hoạt động CLB',
        'desc'  => 'Giao lưu, sinh hoạt tập thể và các hoạt động cộng đồng giúp học viên gắn bó hơn.',
    ],
    [
        'title' => 'Chạm tới cột mốc mới',
        'desc'  => 'Thi đai, biểu diễn hoặc những mục tiêu nhỏ giúp học viên nhìn thấy sự trưởng thành của mình.',
    ],
];

$faq = [
    [
        'question' => 'Trẻ từ 5 tuổi đã có thể bắt đầu học chưa?',
        'answer'   => 'Có. Trẻ có thể bắt đầu với nhóm lớp phù hợp để làm quen vận động, nề nếp và môi trường tập thể.',
    ],
    [
        'question' => 'Người mới hoàn toàn có theo được không?',
        'answer'   => 'Có. Nội dung tập luyện có thể bắt đầu từ nền tảng cơ bản để người mới không bị áp lực.',
    ],
    [
        'question' => 'Có cần mua võ phục ngay từ buổi đầu không?',
        'answer'   => 'Không nhất thiết. Bạn có thể liên hệ trước để được tư vấn cách chuẩn bị phù hợp.',
    ],
];
?>

<main class="vovinam_rework" id="main-content">
    <section class="vovinam_rework__hero" aria-labelledby="vovinam-page-title">
        <div class="vovinam_rework__hero_bg" aria-hidden="true">
            <img
                src="<?php echo esc_url($images['hero']); ?>"
                alt=""
                loading="eager"
                decoding="async"
                fetchpriority="high"
                width="1920"
                height="960">
        </div>

        <div class="vovinam_rework__hero_overlay" aria-hidden="true"></div>

        <div class="container">
            <div class="vovinam_rework__hero_content">
                <p class="vovinam_rework__eyebrow">Tuyển sinh Vovinam Cụm 5 Hà Nội</p>

                <h1 class="vovinam_rework__hero_title" id="vovinam-page-title">
                    Tuyển sinh Vovinam Cụm 5 Hà Nội
                </h1>

                <p class="vovinam_rework__hero_desc">
                    Lớp Vovinam tại Long Biên dành cho thiếu nhi, học sinh, sinh viên và người mới bắt đầu. Mỗi buổi tập giúp học viên rèn sức khỏe, sự tự tin, tinh thần tự giác và khả năng gắn kết trong tập thể.
                </p>

                <div class="vovinam_rework__hero_actions" aria-label="Hành động đăng ký lớp Vovinam">
                    <a
                        class="vovinam_rework__btn vovinam_rework__btn--primary"
                        href="<?php echo esc_url($contact_url); ?>"
                        aria-label="Đăng ký tư vấn lớp Vovinam Cụm 5 Hà Nội"
                    >
                        Đăng ký tư vấn
                    </a>

                    <a
                        class="vovinam_rework__btn vovinam_rework__btn--light"
                        href="<?php echo esc_url('tel:' . $hotline_tel); ?>"
                        aria-label="<?php echo esc_attr('Gọi hotline tư vấn lớp Vovinam ' . $hotline); ?>"
                    >
                        Gọi hotline
                    </a>
                </div>
            </div>

            <div class="vovinam_rework__class_bar" aria-label="Thông tin nhanh về lớp Vovinam">
                <?php foreach ($class_info as $item) : ?>
                    <div class="vovinam_rework__class_bar_item">
                        <span><?php echo esc_html($item['label']); ?></span>
                        <strong><?php echo esc_html($item['value']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vovinam_rework__section vovinam_rework__section--intro" aria-labelledby="vovinam-intro-title">
        <div class="container">
            <div class="vovinam_rework__intro_grid">
                <div class="vovinam_rework__intro_media">
                    <div class="vovinam_rework__intro_media_main">
                        <img
                            src="<?php echo esc_url($images['class_main']); ?>"
                            alt="Không khí lớp học Vovinam cho thiếu nhi và học sinh tại Long Biên"
                            loading="lazy"
                            decoding="async"
                            width="720"
                            height="860">
                    </div>

                    <div class="vovinam_rework__intro_media_small">
                        <img
                            src="<?php echo esc_url($images['class_side']); ?>"
                            alt="Huấn luyện viên hướng dẫn học viên tập luyện Vovinam"
                            loading="lazy"
                            decoding="async"
                            width="420"
                            height="520">
                    </div>
                </div>

                <div class="vovinam_rework__intro_content">
                    <p class="vovinam_rework__section_kicker">Không khí lớp học</p>

                    <h2 class="vovinam_rework__section_title" id="vovinam-intro-title">
                        Không chỉ là học võ, đây là một môi trường để rèn nề nếp và bản lĩnh
                    </h2>

                    <div class="vovinam_rework__value_list">
                        <?php foreach ($training_values as $item) : ?>
                            <article class="vovinam_rework__value_item">
                                <h3><?php echo esc_html($item['title']); ?></h3>
                                <p><?php echo esc_html($item['desc']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="vovinam_rework__section vovinam_rework__section--audience" aria-labelledby="vovinam-audience-title">
        <div class="container">
            <div class="vovinam_rework__section_head">
                <p class="vovinam_rework__section_kicker">Phù hợp với ai</p>

                <h2 class="vovinam_rework__section_title" id="vovinam-audience-title">
                    Một lớp học có thể bắt đầu từ nhiều điểm xuất phát khác nhau
                </h2>
            </div>

            <div class="vovinam_rework__audience_row">
                <?php foreach ($audiences as $index => $item) : ?>
                    <article class="vovinam_rework__audience_item">
                        <span class="vovinam_rework__audience_number" aria-hidden="true">
                            <?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?>
                        </span>

                        <h3><?php echo esc_html($item['title']); ?></h3>
                        <p><?php echo esc_html($item['desc']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vovinam_rework__journey" aria-labelledby="vovinam-journey-title">
        <div class="container">
            <div class="vovinam_rework__journey_grid">
                <div class="vovinam_rework__journey_intro">
                    <p class="vovinam_rework__section_kicker">Hành trình rèn luyện</p>

                    <h2 class="vovinam_rework__section_title" id="vovinam-journey-title">
                        Có lớp học, có hoạt động, có cột mốc để học viên nhìn thấy mình trưởng thành
                    </h2>

                    <p class="vovinam_rework__section_desc">
                        Một môi trường tốt không chỉ giúp học viên đi tập đều hơn, mà còn tạo ra những khoảnh khắc đáng nhớ qua giao lưu, sinh hoạt CLB và các cột mốc rèn luyện.
                    </p>
                </div>

                <div class="vovinam_rework__milestone_list">
                    <?php foreach ($milestones as $index => $item) : ?>
                        <article class="vovinam_rework__milestone_item">
                            <span aria-hidden="true">
                                <?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?>
                            </span>

                            <div>
                                <h3><?php echo esc_html($item['title']); ?></h3>
                                <p><?php echo esc_html($item['desc']); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="vovinam_rework__section vovinam_rework__section--gallery" aria-labelledby="vovinam-gallery-title">
        <div class="container">
            <div class="vovinam_rework__section_head">
                <p class="vovinam_rework__section_kicker">Hoạt động thực tế</p>

                <h2 class="vovinam_rework__section_title" id="vovinam-gallery-title">
                    Những khoảnh khắc làm nên tinh thần tập thể
                </h2>
            </div>

            <div class="vovinam_rework__gallery_grid">
                <figure class="vovinam_rework__gallery_item vovinam_rework__gallery_item--large">
                    <img
                        src="<?php echo esc_url($images['gallery_1']); ?>"
                        alt="Học viên Vovinam tham gia hoạt động tập luyện thực tế"
                        loading="lazy"
                        decoding="async"
                        width="760"
                        height="640">
                </figure>

                <figure class="vovinam_rework__gallery_item">
                    <img
                        src="<?php echo esc_url($images['gallery_2']); ?>"
                        alt="Hoạt động giao lưu của học viên Vovinam Cụm 5 Hà Nội"
                        loading="lazy"
                        decoding="async"
                        width="420"
                        height="320">
                </figure>

                <figure class="vovinam_rework__gallery_item">
                    <img
                        src="<?php echo esc_url($images['gallery_3']); ?>"
                        alt="Cột mốc rèn luyện và sinh hoạt tập thể của lớp Vovinam"
                        loading="lazy"
                        decoding="async"
                        width="420"
                        height="320">
                </figure>
            </div>
        </div>
    </section>

    <section class="vovinam_rework__location" aria-labelledby="vovinam-location-title">
        <div class="container">
            <div class="vovinam_rework__location_grid">
                <div class="vovinam_rework__location_content">
                    <p class="vovinam_rework__section_kicker">Địa điểm tập luyện</p>

                    <h2 class="vovinam_rework__section_title" id="vovinam-location-title">
                        Tư vấn và đăng ký tại Việt Hưng, Long Biên
                    </h2>

                    <p class="vovinam_rework__section_desc">
                        Nếu bạn chưa chắc lớp nào phù hợp với độ tuổi, lịch học hoặc mục tiêu của học viên, hãy liên hệ trước để được tư vấn rõ hơn.
                    </p>

                    <div class="vovinam_rework__location_info">
                        <strong><?php echo esc_html($address); ?></strong>
                        <span><?php echo esc_html('Hotline: ' . $hotline); ?></span>
                    </div>

                    <div class="vovinam_rework__hero_actions" aria-label="Hành động đăng ký và xem bản đồ">
                        <a
                            class="vovinam_rework__btn vovinam_rework__btn--primary"
                            href="<?php echo esc_url($contact_url); ?>"
                            aria-label="Đăng ký tư vấn lớp Vovinam tại Long Biên"
                        >
                            Đăng ký tư vấn
                        </a>

                        <a
                            class="vovinam_rework__btn vovinam_rework__btn--ghost"
                            href="<?php echo esc_url($map_open_url); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Mở vị trí lớp Vovinam trên Google Maps"
                        >
                            Mở Google Maps
                        </a>
                    </div>
                </div>

                <div class="vovinam_rework__map">
                    <iframe
                        src="<?php echo esc_url($map_embed_url); ?>"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Bản đồ địa điểm tập Vovinam tại Việt Hưng, Long Biên, Hà Nội">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    <section class="vovinam_rework__faq_section" aria-labelledby="vovinam-faq-title">
        <div class="container">
            <div class="vovinam_rework__section_head">
                <p class="vovinam_rework__section_kicker">Câu hỏi thường gặp</p>

                <h2 class="vovinam_rework__section_title" id="vovinam-faq-title">
                    Một vài điều phụ huynh và học viên thường quan tâm
                </h2>
            </div>

            <div class="vovinam_rework__faq">
                <?php foreach ($faq as $index => $item) : ?>
                    <details class="vovinam_rework__faq_item" <?php echo $index === 0 ? 'open' : ''; ?>>
                        <summary class="vovinam_rework__faq_question">
                            <?php echo esc_html($item['question']); ?>
                        </summary>

                        <div class="vovinam_rework__faq_answer">
                            <p><?php echo esc_html($item['answer']); ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php
$faq_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];

foreach ($faq as $item) {
    $faq_schema['mainEntity'][] = [
        '@type' => 'Question',
        'name' => wp_strip_all_tags($item['question']),
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => wp_strip_all_tags($item['answer']),
        ],
    ];
}
?>

<script type="application/ld+json">
<?php echo wp_json_encode($faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<?php
get_footer();