<?php
/**
 * Template Name: Page He Thong CLB
 */

defined('ABSPATH') || exit;

get_header();

$theme_uri = get_template_directory_uri();

$contact_url = home_url('/lien-he/');
$vovinam_url = home_url('/vovinam/');
$hotline = '0988 812 246';
$hotline_tel = '0988812246';
$address = 'GH3, CT17, Việt Hưng, Long Biên, Hà Nội';

$images = [
    'hero'     => $theme_uri . '/assets/images/tuyen-sinh-he-thong-clb/hero_main.jpg',
    'kids'     => $theme_uri . '/assets/images/tuyen-sinh-he-thong-clb/club_1.jpg',
    'students' => $theme_uri . '/assets/images/tuyen-sinh-he-thong-clb/club_2.jpg',
    'beginner' => $theme_uri . '/assets/images/tuyen-sinh-he-thong-clb/club_3.jpg',
    'activity' => $theme_uri . '/assets/images/tuyen-sinh-he-thong-clb/activity.jpg',
];

$map_embed_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.3621277618486!2d105.9033221759991!3d21.058194086765564!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135a90035fb8683%3A0xa7675abe0638ca67!2zVm92aW5hbSBD4bulbSA1IEjDoCBO4buZaQ!5e0!3m2!1svi!2s!4v1777955568779!5m2!1svi!2s';
$map_open_url  = 'https://maps.app.goo.gl/dyuxmXMhwnabHbeA7';

$network_stats = [
    [
        'label' => 'Môi trường',
        'value' => 'Tập luyện có nề nếp',
    ],
    [
        'label' => 'Tinh thần',
        'value' => 'Gắn kết trong tập thể',
    ],
    [
        'label' => 'Định hướng',
        'value' => 'Theo độ tuổi và mục tiêu',
    ],
];

$club_groups = [
    [
        'title'    => 'Nhóm thiếu nhi',
        'subtitle' => 'Rèn nề nếp và sự tự tin từ sớm',
        'desc'     => 'Phù hợp với trẻ cần môi trường vận động lành mạnh, học cách lắng nghe, tập trung và mạnh dạn hơn trong tập thể.',
        'meta'     => ['Từ 5 tuổi', 'Nề nếp', 'Vận động cơ bản'],
        'image'    => $images['kids'],
        'alt'      => 'Nhóm thiếu nhi tập luyện Vovinam trong hệ thống CLB Vector Sport',
    ],
    [
        'title'    => 'Nhóm học sinh, sinh viên',
        'subtitle' => 'Duy trì thể lực và xây bản lĩnh',
        'desc'     => 'Phù hợp với học sinh, sinh viên muốn rèn sức khỏe, giải tỏa năng lượng tích cực và tham gia một tập thể có kỷ luật.',
        'meta'     => ['Thể lực', 'Kỷ luật', 'Sinh hoạt tập thể'],
        'image'    => $images['students'],
        'alt'      => 'Nhóm học sinh sinh viên tập luyện Vovinam tại CLB',
    ],
    [
        'title'    => 'Nhóm người mới bắt đầu',
        'subtitle' => 'Bắt đầu từ nền tảng dễ theo',
        'desc'     => 'Dành cho người chưa từng học võ, muốn làm quen Vovinam theo cách nhẹ nhàng hơn và có môi trường đồng hành rõ ràng.',
        'meta'     => ['Nền tảng', 'Dễ bắt đầu', 'Không áp lực'],
        'image'    => $images['beginner'],
        'alt'      => 'Người mới bắt đầu làm quen lớp học Vovinam',
    ],
];

$choose_blocks = [
    [
        'title' => 'Theo độ tuổi',
        'items' => [
            'Trẻ nhỏ cần nhóm có nhịp học nhẹ hơn',
            'Học sinh cần môi trường vận động đều',
            'Người mới cần bắt đầu từ nền tảng',
        ],
    ],
    [
        'title' => 'Theo lịch sinh hoạt',
        'items' => [
            'Chọn khung giờ dễ duy trì lâu dài',
            'Ưu tiên điểm tập thuận tiện di chuyển',
            'Trao đổi trước nếu cần lịch linh hoạt hơn',
        ],
    ],
    [
        'title' => 'Theo mục tiêu',
        'items' => [
            'Rèn thể lực',
            'Tăng sự tự tin',
            'Học nề nếp, kỷ luật',
            'Làm quen môi trường Vovinam',
        ],
    ],
];

$activity_points = [
    [
        'title' => 'Sinh hoạt CLB',
        'desc'  => 'Giúp học viên có tập thể để gắn bó, cùng duy trì thói quen tập luyện và hỗ trợ nhau trong quá trình rèn luyện.',
    ],
    [
        'title' => 'Giao lưu và hoạt động cộng đồng',
        'desc'  => 'Tạo thêm cơ hội để học viên va chạm, mạnh dạn hơn và cảm nhận rõ tinh thần của một tập thể võ thuật.',
    ],
    [
        'title' => 'Cột mốc tập luyện',
        'desc'  => 'Các buổi kiểm tra, thi đai hoặc hoạt động biểu diễn giúp học viên nhìn thấy sự tiến bộ của mình qua từng giai đoạn.',
    ],
];

$join_steps = [
    [
        'title' => 'Liên hệ định hướng',
        'desc'  => 'Chia sẻ độ tuổi, khu vực, lịch sinh hoạt và nhu cầu để được tư vấn nhóm phù hợp.',
    ],
    [
        'title' => 'Chọn nhóm hoặc điểm tập',
        'desc'  => 'Dựa trên thông tin thực tế, đội ngũ sẽ gợi ý hướng tham gia dễ duy trì hơn.',
    ],
    [
        'title' => 'Tham gia và theo dõi',
        'desc'  => 'Học viên bắt đầu tập luyện, làm quen môi trường và được định hướng tiếp theo nếu cần.',
    ],
];

$faq = [
    [
        'question' => 'Hệ thống CLB có dành cho người mới không?',
        'answer'   => 'Có. Người mới có thể được định hướng nhóm phù hợp để bắt đầu từ nền tảng và không bị quá áp lực.',
    ],
    [
        'question' => 'Làm sao biết nên chọn nhóm nào?',
        'answer'   => 'Bạn nên dựa trên độ tuổi, lịch sinh hoạt, mục tiêu tập luyện và khu vực thuận tiện. Nếu chưa chắc, hãy liên hệ để được tư vấn.',
    ],
    [
        'question' => 'Có thể cho trẻ nhỏ tham gia không?',
        'answer'   => 'Có. Trẻ từ khoảng 5 tuổi trở lên có thể được tư vấn nhóm phù hợp để làm quen với vận động, nề nếp và môi trường tập thể.',
    ],
    [
        'question' => 'Có cần đăng ký trước khi đến không?',
        'answer'   => 'Nên liên hệ trước để được hướng dẫn đúng nhóm, đúng khung giờ và chuẩn bị phù hợp hơn.',
    ],
];
?>

<main class="club_hub_page" id="main-content">
    <section class="club_hub_page__hero" aria-labelledby="club-hub-title">
        <div class="container">
            <div class="club_hub_page__hero_grid">
                <div class="club_hub_page__hero_content">
                    <p class="club_hub_page__eyebrow">Hệ thống CLB</p>

                    <h1 class="club_hub_page__hero_title" id="club-hub-title">
                        Hệ thống CLB Vovinam
                    </h1>

                    <p class="club_hub_page__hero_desc">
                        Hệ thống CLB giúp học viên và phụ huynh dễ định hướng điểm bắt đầu phù hợp hơn theo độ tuổi, lịch sinh hoạt và mục tiêu rèn luyện. Không chỉ là nơi học võ, CLB còn là môi trường để duy trì thói quen, xây tinh thần tập thể và trưởng thành qua từng cột mốc.
                    </p>

                    <div class="club_hub_page__hero_actions" aria-label="Hành động tư vấn hệ thống CLB">
                        <a
                            class="club_hub_page__btn club_hub_page__btn--primary"
                            href="<?php echo esc_url($contact_url); ?>"
                            aria-label="Liên hệ để tìm CLB Vovinam phù hợp"
                        >
                            Liên hệ tìm CLB phù hợp
                        </a>

                        <a
                            class="club_hub_page__btn club_hub_page__btn--ghost"
                            href="<?php echo esc_url($vovinam_url); ?>"
                            aria-label="Xem thông tin lớp Vovinam"
                        >
                            Xem lớp Vovinam
                        </a>
                    </div>
                </div>

                <div class="club_hub_page__hero_media">
                    <img
                        src="<?php echo esc_url($images['hero']); ?>"
                        alt="Học viên trong hệ thống CLB Vovinam Vector Sport"
                        loading="eager"
                        decoding="async"
                        fetchpriority="high"
                        width="720"
                        height="640">
                </div>
            </div>

            <div class="club_hub_page__stat_row" aria-label="Điểm nổi bật của hệ thống CLB">
                <?php foreach ($network_stats as $item) : ?>
                    <div class="club_hub_page__stat_item">
                        <span><?php echo esc_html($item['label']); ?></span>
                        <strong><?php echo esc_html($item['value']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="club_hub_page__directory" aria-labelledby="club-directory-title">
        <div class="container">
            <div class="club_hub_page__section_head">
                <p class="club_hub_page__section_kicker">Nhóm CLB</p>

                <h2 class="club_hub_page__section_title" id="club-directory-title">
                    Mỗi nhóm học viên cần một môi trường bắt đầu khác nhau
                </h2>
            </div>

            <div class="club_hub_page__club_list">
                <?php foreach ($club_groups as $item) : ?>
                    <article class="club_hub_page__club_card">
                        <div class="club_hub_page__club_media">
                            <img
                                src="<?php echo esc_url($item['image']); ?>"
                                alt="<?php echo esc_attr($item['alt']); ?>"
                                loading="lazy"
                                decoding="async"
                                width="520"
                                height="360">
                        </div>

                        <div class="club_hub_page__club_body">
                            <span class="club_hub_page__club_subtitle">
                                <?php echo esc_html($item['subtitle']); ?>
                            </span>

                            <h3 class="club_hub_page__club_title">
                                <?php echo esc_html($item['title']); ?>
                            </h3>

                            <p class="club_hub_page__club_desc">
                                <?php echo esc_html($item['desc']); ?>
                            </p>

                            <div class="club_hub_page__club_tags" aria-label="<?php echo esc_attr('Đặc điểm của ' . $item['title']); ?>">
                                <?php foreach ($item['meta'] as $tag) : ?>
                                    <span><?php echo esc_html($tag); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="club_hub_page__chooser" aria-labelledby="club-chooser-title">
        <div class="container">
            <div class="club_hub_page__chooser_grid">
                <div class="club_hub_page__chooser_intro">
                    <p class="club_hub_page__section_kicker">Cách chọn CLB</p>

                    <h2 class="club_hub_page__section_title" id="club-chooser-title">
                        Chọn đúng môi trường giúp việc tập luyện dễ duy trì hơn
                    </h2>

                    <p class="club_hub_page__section_desc">
                        Một CLB phù hợp không chỉ nằm ở vị trí gần, mà còn ở độ tuổi, nhịp học, mục tiêu rèn luyện và cảm giác học viên có thể gắn bó lâu dài.
                    </p>
                </div>

                <div class="club_hub_page__chooser_board">
                    <?php foreach ($choose_blocks as $block) : ?>
                        <article class="club_hub_page__chooser_col">
                            <h3><?php echo esc_html($block['title']); ?></h3>

                            <ul>
                                <?php foreach ($block['items'] as $item) : ?>
                                    <li><?php echo esc_html($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="club_hub_page__location" aria-labelledby="club-location-title">
        <div class="container">
            <div class="club_hub_page__location_grid">
                <div class="club_hub_page__map">
                    <iframe
                        src="<?php echo esc_url($map_embed_url); ?>"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Bản đồ địa điểm tư vấn hệ thống CLB Vovinam tại Việt Hưng, Long Biên, Hà Nội">
                    </iframe>
                </div>

                <div class="club_hub_page__location_panel">
                    <p class="club_hub_page__section_kicker">Điểm liên hệ chính</p>

                    <h2 class="club_hub_page__section_title" id="club-location-title">
                        Tư vấn nhóm tập phù hợp tại Việt Hưng, Long Biên
                    </h2>

                    <p class="club_hub_page__section_desc">
                        Nếu bạn chưa rõ nên bắt đầu ở nhóm nào, hãy liên hệ để được định hướng theo độ tuổi, lịch học và nhu cầu thực tế.
                    </p>

                    <div class="club_hub_page__location_info">
                        <div>
                            <span>Địa điểm</span>
                            <strong><?php echo esc_html($address); ?></strong>
                        </div>

                        <div>
                            <span>Hotline</span>
                            <strong><?php echo esc_html($hotline); ?></strong>
                        </div>
                    </div>

                    <div class="club_hub_page__hero_actions" aria-label="Hành động liên hệ và xem bản đồ">
                        <a
                            class="club_hub_page__btn club_hub_page__btn--primary"
                            href="<?php echo esc_url($contact_url); ?>"
                            aria-label="Liên hệ tư vấn nhóm CLB Vovinam phù hợp"
                        >
                            Liên hệ tư vấn
                        </a>

                        <a
                            class="club_hub_page__btn club_hub_page__btn--ghost"
                            href="<?php echo esc_url($map_open_url); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Mở vị trí CLB Vovinam trên Google Maps"
                        >
                            Mở Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="club_hub_page__activity" aria-labelledby="club-activity-title">
        <div class="container">
            <div class="club_hub_page__activity_grid">
                <div class="club_hub_page__activity_media">
                    <img
                        src="<?php echo esc_url($images['activity']); ?>"
                        alt="Hoạt động sinh hoạt và giao lưu trong hệ thống CLB Vovinam"
                        loading="lazy"
                        decoding="async"
                        width="620"
                        height="520">
                </div>

                <div class="club_hub_page__activity_content">
                    <p class="club_hub_page__section_kicker">Hoạt động chung</p>

                    <h2 class="club_hub_page__section_title" id="club-activity-title">
                        CLB không chỉ là nơi tập, mà còn là nơi học viên có tập thể để trưởng thành
                    </h2>

                    <div class="club_hub_page__activity_list">
                        <?php foreach ($activity_points as $item) : ?>
                            <article class="club_hub_page__activity_item">
                                <h3><?php echo esc_html($item['title']); ?></h3>
                                <p><?php echo esc_html($item['desc']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="club_hub_page__join" aria-labelledby="club-join-title">
        <div class="container">
            <div class="club_hub_page__section_head">
                <p class="club_hub_page__section_kicker">Cách tham gia</p>

                <h2 class="club_hub_page__section_title" id="club-join-title">
                    3 bước đơn giản để được định hướng nhóm CLB phù hợp
                </h2>
            </div>

            <div class="club_hub_page__join_steps">
                <?php foreach ($join_steps as $index => $item) : ?>
                    <article class="club_hub_page__join_step">
                        <span aria-hidden="true">
                            <?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?>
                        </span>

                        <h3><?php echo esc_html($item['title']); ?></h3>
                        <p><?php echo esc_html($item['desc']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="club_hub_page__faq_section" aria-labelledby="club-faq-title">
        <div class="container">
            <div class="club_hub_page__section_head">
                <p class="club_hub_page__section_kicker">Câu hỏi thường gặp</p>

                <h2 class="club_hub_page__section_title" id="club-faq-title">
                    Một vài điều phụ huynh và học viên thường hỏi về hệ thống CLB
                </h2>
            </div>

            <div class="club_hub_page__faq">
                <?php foreach ($faq as $index => $item) : ?>
                    <details class="club_hub_page__faq_item" <?php echo $index === 0 ? 'open' : ''; ?>>
                        <summary class="club_hub_page__faq_question">
                            <?php echo esc_html($item['question']); ?>
                        </summary>

                        <div class="club_hub_page__faq_answer">
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

$service_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => 'Hệ thống CLB Vovinam',
    'description' => 'Tư vấn hệ thống CLB Vovinam phù hợp theo độ tuổi, lịch sinh hoạt và mục tiêu rèn luyện cho học viên tại Hà Nội.',
    'provider' => [
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
    ],
    'areaServed' => [
        '@type' => 'Place',
        'name' => 'Hà Nội',
    ],
    'serviceType' => 'Hệ thống CLB Vovinam',
    'url' => get_permalink(),
];
?>

<script type="application/ld+json">
<?php echo wp_json_encode($faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<script type="application/ld+json">
<?php echo wp_json_encode($service_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<?php
get_footer();