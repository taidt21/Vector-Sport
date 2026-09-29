<?php
/**
 * Template Name: Page Kem Rieng
 */

defined('ABSPATH') || exit;

get_header();

$theme_uri = get_template_directory_uri();

$contact_url = home_url('/lien-he/');
$vovinam_url = home_url('/vovinam/');
$hotline_tel = '0988812246';

$images = [
    'coach'    => $theme_uri . '/assets/images/tuyen-sinh-kem-rieng/coach.jpg',
    'training' => $theme_uri . '/assets/images/tuyen-sinh-kem-rieng/training.jpg',
];

$quick_info = [
    [
        'label' => 'Cho người mới',
        'value' => 'Bắt đầu chậm hơn, chắc hơn',
    ],
    [
        'label' => 'Cho trẻ nhỏ',
        'value' => 'Được theo sát và chỉnh nề nếp',
    ],
    [
        'label' => 'Cho người bận',
        'value' => 'Dễ sắp lịch hơn lớp cố định',
    ],
    [
        'label' => 'Cho mục tiêu riêng',
        'value' => 'Tập trung vào thể lực, kỹ thuật hoặc sự tự tin',
    ],
];

$compare_items = [
    [
        'label' => 'Nhịp học',
        'group' => 'Theo tiến độ chung của lớp',
        'private' => 'Điều chỉnh theo khả năng của từng người',
    ],
    [
        'label' => 'Thời gian',
        'group' => 'Theo khung giờ cố định',
        'private' => 'Linh hoạt hơn theo lịch cá nhân',
    ],
    [
        'label' => 'Mức độ theo sát',
        'group' => 'HLV quan sát theo nhóm',
        'private' => 'Sửa kỹ thuật và theo dõi sát hơn',
    ],
    [
        'label' => 'Mục tiêu',
        'group' => 'Rèn luyện theo chương trình chung',
        'private' => 'Tập trung vào mục tiêu cụ thể',
    ],
];

$suitable_cases = [
    'Bạn là người mới và muốn bắt đầu chắc hơn',
    'Bạn cần lịch học linh hoạt hơn lớp nhóm',
    'Bạn muốn được sửa kỹ thuật kỹ hơn',
    'Bạn muốn tập theo mục tiêu riêng như thể lực, tự tin hoặc nền tảng võ thuật',
    'Bạn cần một bước đệm trước khi tham gia lớp chung',
];

$process = [
    [
        'title' => 'Trao đổi nhu cầu',
        'desc'  => 'Xác định độ tuổi, mục tiêu, lịch sinh hoạt và mức độ hiện tại của người học.',
    ],
    [
        'title' => 'Đề xuất hướng tập',
        'desc'  => 'Huấn luyện viên gợi ý cách bắt đầu phù hợp thay vì áp một lộ trình cứng cho mọi người.',
    ],
    [
        'title' => 'Sắp lịch phù hợp',
        'desc'  => 'Thống nhất khung giờ, địa điểm và hình thức tập để việc học dễ duy trì hơn.',
    ],
    [
        'title' => 'Theo dõi và điều chỉnh',
        'desc'  => 'Trong quá trình tập, nội dung có thể được điều chỉnh theo tiến độ thực tế của học viên.',
    ],
];

$goals = [
    [
        'title' => 'Xây nền thể lực',
        'desc'  => 'Tăng sức bền, sự linh hoạt và thói quen vận động đều đặn.',
    ],
    [
        'title' => 'Làm quen Vovinam',
        'desc'  => 'Bắt đầu từ nền tảng cơ bản để người mới không bị ngợp.',
    ],
    [
        'title' => 'Tăng sự tự tin',
        'desc'  => 'Từng bước vượt qua sự ngại ngùng khi vận động và thể hiện bản thân.',
    ],
    [
        'title' => 'Sửa kỹ thuật',
        'desc'  => 'Phù hợp khi cần được quan sát kỹ và chỉnh động tác sát hơn.',
    ],
];

$faq = [
    [
        'question' => 'Kèm riêng có phù hợp với người mới hoàn toàn không?',
        'answer'   => 'Có. Người mới là nhóm rất phù hợp vì có thể bắt đầu chậm hơn, chắc hơn và được theo sát ngay từ đầu.',
    ],
    [
        'question' => 'Kèm riêng có linh hoạt thời gian hơn lớp nhóm không?',
        'answer'   => 'Có. Lịch tập có thể được trao đổi để phù hợp hơn với lịch học, lịch làm việc hoặc lịch sinh hoạt của người học.',
    ],
    [
        'question' => 'Có thể tập theo mục tiêu cá nhân không?',
        'answer'   => 'Có. Đây là điểm mạnh của hình thức kèm riêng. Nội dung tập có thể tập trung vào thể lực, kỹ thuật cơ bản, sự tự tin hoặc mục tiêu cụ thể khác.',
    ],
    [
        'question' => 'Có nên học kèm riêng trước khi vào lớp chung không?',
        'answer'   => 'Có thể. Với người mới hoặc còn ngại lớp đông, kèm riêng là bước đệm tốt để làm quen trước.',
    ],
];
?>

<main class="private_coach_page" id="main-content">
    <section class="private_coach_page__hero" aria-labelledby="private-coach-title">
        <div class="container">
            <div class="private_coach_page__hero_grid">
                <div class="private_coach_page__hero_content">
                    <p class="private_coach_page__eyebrow">Kèm riêng Vovinam</p>

                    <h1 class="private_coach_page__hero_title" id="private-coach-title">
                        Kèm riêng <br> Vovinam
                    </h1>

                    <p class="private_coach_page__hero_desc">
                        Kèm riêng phù hợp với người mới bắt đầu, người bận hoặc người cần được theo sát kỹ hơn trong quá trình tập luyện. Thay vì đi theo nhịp chung của lớp đông, học viên được định hướng theo mục tiêu, thể trạng và khả năng tiếp thu thực tế.
                    </p>

                    <div class="private_coach_page__hero_actions" aria-label="Hành động tư vấn kèm riêng">
                        <a
                            class="private_coach_page__btn private_coach_page__btn--primary"
                            href="<?php echo esc_url($contact_url); ?>"
                            aria-label="Đăng ký tư vấn kèm riêng Vovinam"
                        >
                            Đăng ký tư vấn
                        </a>

                        <a
                            class="private_coach_page__btn private_coach_page__btn--ghost"
                            href="<?php echo esc_url('tel:' . $hotline_tel); ?>"
                            aria-label="Gọi hotline tư vấn kèm riêng Vovinam"
                        >
                            Gọi hotline
                        </a>
                    </div>
                </div>

                <aside class="private_coach_page__consult_box" aria-label="Thông tin nhanh về kèm riêng Vovinam">
                    <div class="private_coach_page__consult_media">
                        <img
                            src="<?php echo esc_url($images['coach']); ?>"
                            alt="Huấn luyện viên hướng dẫn kèm riêng Vovinam cho học viên"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                            width="560"
                            height="640">
                    </div>

                    <div class="private_coach_page__consult_body">
                        <h2>Thông tin tư vấn</h2>

                        <div class="private_coach_page__quick_list">
                            <?php foreach ($quick_info as $item) : ?>
                                <div class="private_coach_page__quick_item">
                                    <span><?php echo esc_html($item['label']); ?></span>
                                    <strong><?php echo esc_html($item['value']); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="private_coach_page__section private_coach_page__section--compare" aria-labelledby="private-coach-compare-title">
        <div class="container">
            <div class="private_coach_page__section_head">
                <p class="private_coach_page__section_kicker">Khác gì với lớp nhóm</p>

                <h2 class="private_coach_page__section_title" id="private-coach-compare-title">
                    Kèm riêng không thay thế lớp nhóm, nhưng phù hợp khi bạn cần một cách bắt đầu cá nhân hơn
                </h2>
            </div>

            <div class="private_coach_page__compare_table" role="table" aria-label="So sánh lớp nhóm và kèm riêng Vovinam">
                <div class="private_coach_page__compare_row private_coach_page__compare_row--head" role="row">
                    <div role="columnheader">Tiêu chí</div>
                    <div role="columnheader">Lớp nhóm</div>
                    <div role="columnheader">Kèm riêng</div>
                </div>

                <?php foreach ($compare_items as $item) : ?>
                    <div class="private_coach_page__compare_row" role="row">
                        <div role="cell"><?php echo esc_html($item['label']); ?></div>
                        <div role="cell"><?php echo esc_html($item['group']); ?></div>
                        <div role="cell"><?php echo esc_html($item['private']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="private_coach_page__diagnose" aria-labelledby="private-coach-suitable-title">
        <div class="container">
            <div class="private_coach_page__diagnose_grid">
                <div class="private_coach_page__diagnose_content">
                    <p class="private_coach_page__section_kicker">Khi nào nên chọn kèm riêng</p>

                    <h2 class="private_coach_page__section_title" id="private-coach-suitable-title">
                        Nếu bạn cần sự linh hoạt và theo sát hơn, đây là lựa chọn đáng cân nhắc
                    </h2>

                    <p class="private_coach_page__section_desc">
                        Hình thức này đặc biệt phù hợp khi người học chưa sẵn sàng vào lớp đông, cần chỉnh kỹ thuật kỹ hơn hoặc muốn có một lộ trình rõ ràng theo mục tiêu riêng.
                    </p>

                    <ul class="private_coach_page__checklist">
                        <?php foreach ($suitable_cases as $item) : ?>
                            <li><?php echo esc_html($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="private_coach_page__diagnose_media">
                    <img
                        src="<?php echo esc_url($images['training']); ?>"
                        alt="Học viên tập luyện Vovinam theo hình thức kèm riêng"
                        loading="lazy"
                        decoding="async"
                        width="560"
                        height="420">
                </div>
            </div>
        </div>
    </section>

    <section class="private_coach_page__section private_coach_page__section--process" aria-labelledby="private-coach-process-title">
        <div class="container">
            <div class="private_coach_page__section_head">
                <p class="private_coach_page__section_kicker">Quy trình bắt đầu</p>

                <h2 class="private_coach_page__section_title" id="private-coach-process-title">
                    Bắt đầu từ nhu cầu thật, không phải một lộ trình cứng
                </h2>
            </div>

            <div class="private_coach_page__process_list">
                <?php foreach ($process as $index => $item) : ?>
                    <article class="private_coach_page__process_item">
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
    </section>

    <section class="private_coach_page__section private_coach_page__section--goals" aria-labelledby="private-coach-goals-title">
        <div class="container">
            <div class="private_coach_page__goals_grid">
                <div class="private_coach_page__goals_intro">
                    <p class="private_coach_page__section_kicker">Mục tiêu thường gặp</p>

                    <h2 class="private_coach_page__section_title" id="private-coach-goals-title">
                        Mỗi người có thể bắt đầu vì một lý do khác nhau
                    </h2>
                </div>

                <div class="private_coach_page__goal_cards">
                    <?php foreach ($goals as $item) : ?>
                        <article class="private_coach_page__goal_card">
                            <h3><?php echo esc_html($item['title']); ?></h3>
                            <p><?php echo esc_html($item['desc']); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="private_coach_page__faq_section" aria-labelledby="private-coach-faq-title">
        <div class="container">
            <div class="private_coach_page__section_head">
                <p class="private_coach_page__section_kicker">Câu hỏi thường gặp</p>

                <h2 class="private_coach_page__section_title" id="private-coach-faq-title">
                    Một vài điều người học thường muốn biết trước khi đăng ký kèm riêng
                </h2>
            </div>

            <div class="private_coach_page__faq">
                <?php foreach ($faq as $item) : ?>
                    <details class="private_coach_page__faq_item">
                        <summary class="private_coach_page__faq_question">
                            <?php echo esc_html($item['question']); ?>
                        </summary>

                        <div class="private_coach_page__faq_answer">
                            <p><?php echo esc_html($item['answer']); ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="private_coach_page__final_cta" aria-labelledby="private-coach-final-title">
        <div class="container">
            <div class="private_coach_page__final_box">
                <div>
                    <p class="private_coach_page__section_kicker">Tư vấn kèm riêng</p>

                    <h2 id="private-coach-final-title">
                        Muốn bắt đầu theo một lộ trình sát với bạn hơn?
                    </h2>

                    <p>
                        Liên hệ để được tư vấn hình thức kèm riêng phù hợp với độ tuổi, lịch sinh hoạt và mục tiêu tập luyện.
                    </p>
                </div>

                <div class="private_coach_page__final_actions" aria-label="Hành động cuối trang">
                    <a
                        class="private_coach_page__btn private_coach_page__btn--primary"
                        href="<?php echo esc_url($contact_url); ?>"
                        aria-label="Đăng ký tư vấn kèm riêng Vovinam"
                    >
                        Đăng ký tư vấn
                    </a>

                    <a
                        class="private_coach_page__btn private_coach_page__btn--ghost"
                        href="<?php echo esc_url($vovinam_url); ?>"
                        aria-label="Xem thông tin lớp Vovinam"
                    >
                        Xem lớp Vovinam
                    </a>
                </div>
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