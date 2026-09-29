<?php
/**
 * Section: Why Choose (Home)
 */

$contact_url = home_url('/lien-he/');
$product_url = home_url('/san-pham/');

$stats = [
    [
        'number' => '01',
        'label'  => 'Lớp học phù hợp với nhiều độ tuổi và trình độ',
    ],
    [
        'number' => '02',
        'label'  => 'Rèn kỹ thuật, thể lực và tính kỷ luật song song',
    ],
    [
        'number' => '03',
        'label'  => 'Dễ chuẩn bị võ phục, đồ tập và phụ kiện cần thiết',
    ],
    [
        'number' => '04',
        'label'  => 'Có hoạt động kết nối giúp học viên gắn bó lâu dài',
    ],
];

$reasons = [
    [
        'title' => 'Môi trường dễ bắt đầu',
        'desc'  => 'Phù hợp cho người mới học, giúp học viên làm quen từng bước với Vovinam trong môi trường tích cực và có định hướng.',
    ],
    [
        'title' => 'Đồng hành từ lớp học đến đồ tập',
        'desc'  => 'Vector Sport hỗ trợ học viên từ quá trình tập luyện đến việc chuẩn bị võ phục, đồ tập và phụ kiện phù hợp với nhu cầu thực tế.',
    ],
    [
        'title' => 'Tập luyện có mục tiêu rõ ràng',
        'desc'  => 'Học viên có thêm động lực để tiến bộ qua từng giai đoạn, xây dựng thể lực, kỹ thuật và sự tự tin một cách bền vững.',
    ],
];
?>

<section class="home_why_choose" id="why-choose" aria-labelledby="home-why-choose-title">
    <div class="container">
        <div class="home_why_choose__heading">
            <p class="home_why_choose__kicker">Vì sao chọn Vector Sport</p>

            <h2 class="home_why_choose__title" id="home-why-choose-title">
                Bắt đầu học Vovinam và chuẩn bị đồ tập đúng nhu cầu
            </h2>

            <p class="home_why_choose__desc">
                Vector Sport tập trung xây dựng môi trường học Vovinam tích cực, đồng thời cung cấp võ phục, đồ tập và phụ kiện thiết thực cho quá trình rèn luyện.
            </p>
        </div>

        <ol class="home_why_choose__stats" aria-label="Các điểm nổi bật của Vector Sport">
            <?php foreach ($stats as $item): ?>
                <li class="home_why_choose__stat">
                    <span class="home_why_choose__stat_number" aria-hidden="true">
                        <?php echo esc_html($item['number']); ?>
                    </span>

                    <span class="home_why_choose__stat_label">
                        <?php echo esc_html($item['label']); ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>

        <div class="home_why_choose__reasons">
            <?php foreach ($reasons as $item): ?>
                <article class="home_why_choose__reason">
                    <div class="home_why_choose__reason_icon" aria-hidden="true"></div>

                    <h3 class="home_why_choose__reason_title">
                        <?php echo esc_html($item['title']); ?>
                    </h3>

                    <p class="home_why_choose__reason_desc">
                        <?php echo esc_html($item['desc']); ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="home_why_choose__actions" aria-label="Hành động nhanh">
            <a
                class="home_why_choose__btn home_why_choose__btn--primary"
                href="<?php echo esc_url($contact_url); ?>"
                aria-label="Nhận tư vấn lớp học Vovinam tại Vector Sport"
                data-cta="home_why_choose_contact"
            >
                Tư vấn lớp học
            </a>

            <a
                class="home_why_choose__btn home_why_choose__btn--ghost"
                href="<?php echo esc_url($product_url); ?>"
                aria-label="Xem võ phục, đồ tập và phụ kiện tại Vector Sport"
                data-cta="home_why_choose_products"
            >
                Xem sản phẩm
            </a>
        </div>
    </div>
</section>