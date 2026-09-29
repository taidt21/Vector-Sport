<?php
/**
 * Section: FAQ (Home)
 */

$contact_url = home_url('/lien-he/');
$product_url = home_url('/san-pham/');

$faqs = [
    [
        'question' => 'Lớp Vovinam tại Vector Sport phù hợp với độ tuổi nào?',
        'answer'   => 'Lớp học phù hợp với thiếu nhi, học sinh, sinh viên và người mới bắt đầu. Tùy theo độ tuổi và thể lực, học viên sẽ được tư vấn môi trường tập luyện phù hợp để bắt đầu an toàn và dễ theo hơn.',
    ],
    [
        'question' => 'Người mới chưa từng học võ có đăng ký được không?',
        'answer'   => 'Có. Các lớp nhập môn được xây dựng để học viên làm quen từ nền tảng cơ bản như tư thế, kỹ thuật, thể lực và kỷ luật tập luyện. Người mới không cần có kinh nghiệm võ thuật trước đó.',
    ],
    [
        'question' => 'Phụ huynh có thể đăng ký học thử cho con không?',
        'answer'   => 'Phụ huynh có thể để lại thông tin tư vấn để Vector Sport hỗ trợ kiểm tra độ tuổi, nhu cầu và khung giờ phù hợp. Việc học thử giúp phụ huynh và học viên cảm nhận rõ hơn về môi trường tập luyện trước khi đăng ký lâu dài.',
    ],
    [
        'question' => 'Khi mới bắt đầu có cần mua võ phục ngay không?',
        'answer'   => 'Học viên mới có thể được tư vấn theo tình trạng thực tế. Nếu đã xác định tập luyện lâu dài, việc chuẩn bị võ phục, đai tập và phụ kiện cơ bản sẽ giúp quá trình học gọn gàng và đúng chuẩn hơn.',
    ],
    [
        'question' => 'Vector Sport có tư vấn chọn size võ phục và đồ tập không?',
        'answer'   => 'Có. Vector Sport có thể hỗ trợ tư vấn lựa chọn võ phục, đồ tập và phụ kiện theo độ tuổi, chiều cao, cân nặng và nhu cầu sử dụng để hạn chế chọn sai size hoặc mua thiếu sản phẩm cần thiết.',
    ],
    [
        'question' => 'Có hỗ trợ đồng phục cho câu lạc bộ hoặc đội nhóm không?',
        'answer'   => 'Có. Với các lớp học, câu lạc bộ hoặc đội nhóm cần chuẩn bị đồng bộ, Vector Sport có thể tư vấn nhóm sản phẩm phù hợp theo số lượng, mục đích sử dụng và yêu cầu hình ảnh chung.',
    ],
];
?>

<section class="home_faq" id="faq" aria-labelledby="home-faq-title">
    <div class="container">
        <div class="home_section_heading">
            <div class="home_section_heading__content">
                <p class="home_section_heading__kicker">Câu hỏi thường gặp</p>

                <h2 class="home_section_heading__title" id="home-faq-title">
                    Giải đáp nhanh trước khi đăng ký lớp học hoặc chọn đồ tập
                </h2>

                <p class="home_section_heading__desc">
                    Những câu hỏi phổ biến giúp phụ huynh, học viên và khách hàng hiểu rõ hơn về lớp Vovinam, võ phục,
                    đồ tập và phụ kiện tại Vector Sport.
                </p>
            </div>

            <a class="home_section_heading__link" href="<?php echo esc_url($contact_url); ?>"
                aria-label="Liên hệ Vector Sport để được tư vấn thêm">
                Cần tư vấn thêm?
            </a>
        </div>

        <div class="home_faq__layout">
            <div class="home_faq__list">
                <?php foreach ($faqs as $index => $item): ?>
                <?php
                    $faq_id = 'home-faq-item-' . $index;
                    $button_id = $faq_id . '-button';
                    $panel_id = $faq_id . '-panel';
                    $is_first = $index === 0;
                    ?>

                <article class="home_faq__item <?php echo $is_first ? 'is-active' : ''; ?>">
                    <h3 class="home_faq__question">
                        <button id="<?php echo esc_attr($button_id); ?>" class="home_faq__button js-home-faq-toggle"
                            type="button" aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
                            aria-controls="<?php echo esc_attr($panel_id); ?>">
                            <span><?php echo esc_html($item['question']); ?></span>

                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path fill="currentColor" d="M12 15.5 5.5 9l1.4-1.4 5.1 5.1 5.1-5.1L18.5 9 12 15.5z" />
                            </svg>
                        </button>
                    </h3>

                    <div id="<?php echo esc_attr($panel_id); ?>" class="home_faq__answer" role="region"
                        aria-labelledby="<?php echo esc_attr($button_id); ?>">
                        <div class="home_faq__answer_inner">
                            <p><?php echo esc_html($item['answer']); ?></p>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <aside class="home_faq__aside" aria-labelledby="home-faq-aside-title">
                <h3 class="home_faq__aside_title" id="home-faq-aside-title">
                    Bạn cần tư vấn trực tiếp?
                </h3>

                <p class="home_faq__aside_desc">
                    Gửi thông tin để Vector Sport hỗ trợ chọn lớp học, võ phục hoặc nhóm sản phẩm phù hợp với nhu cầu
                    thực tế.
                </p>

                <div class="home_faq__actions">
                    <a class="home_faq__btn home_faq__btn--primary" href="<?php echo esc_url($contact_url); ?>"
                        aria-label="Đăng ký tư vấn lớp học và sản phẩm tại Vector Sport" data-cta="home_faq_contact">
                        Đăng ký tư vấn
                    </a>

                    <a class="home_faq__btn home_faq__btn--ghost" href="<?php echo esc_url($product_url); ?>"
                        aria-label="Xem sản phẩm Vector Sport" data-cta="home_faq_products">
                        Xem sản phẩm
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>
<?php
$faq_schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => [],
];

foreach ($faqs as $item) {
    $faq_schema['mainEntity'][] = [
        '@type' => 'Question',
        'name'  => wp_strip_all_tags($item['question']),
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text'  => wp_strip_all_tags($item['answer']),
        ],
    ];
}
?>

<script type="application/ld+json">
<?php echo wp_json_encode($faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>