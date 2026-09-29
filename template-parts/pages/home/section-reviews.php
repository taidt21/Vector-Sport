<?php
/**
 * Section: Reviews / Customer Concerns (Home)
 */

$reviews = [
    [
        'name' => 'Phụ huynh',
        'text' => 'Môi trường học có an toàn không, con có được rèn tính kỷ luật, sự tự tin và thói quen vận động đều đặn hay không.',
    ],
    [
        'name' => 'Học viên mới',
        'text' => 'Lộ trình tập luyện có dễ theo không, người mới bắt đầu có được hướng dẫn từ cơ bản và cảm nhận được sự tiến bộ sau từng giai đoạn hay không.',
    ],
    [
        'name' => 'Người mua sản phẩm',
        'text' => 'Nên chọn võ phục, đồ tập hay phụ kiện nào phù hợp với nhu cầu thực tế để chuẩn bị đúng ngay từ đầu.',
    ],
];
?>

<section class="home_reviews" id="reviews" aria-labelledby="home-reviews-title">
    <div class="container">
        <div class="home_section_heading">
            <div class="home_section_heading__content">
                <p class="home_section_heading__kicker">Điều khách hàng quan tâm</p>

                <h2 class="home_section_heading__title" id="home-reviews-title">
                    Trước khi đăng ký lớp học hoặc chọn đồ tập, khách hàng thường cần gì?
                </h2>

                <p class="home_section_heading__desc">
                    Vector Sport tập trung giải đáp những nhu cầu thực tế của phụ huynh, học viên và người mua sản phẩm: môi trường tập luyện, lộ trình học và cách chọn đồ tập phù hợp.
                </p>
            </div>
        </div>

        <div class="home_reviews__grid">
            <?php foreach ($reviews as $item): ?>
                <article class="home_reviews__card">
                    <div class="home_reviews__quote" aria-hidden="true">“</div>

                    <p class="home_reviews__text">
                        <?php echo esc_html($item['text']); ?>
                    </p>

                    <h3 class="home_reviews__name">
                        <?php echo esc_html($item['name']); ?>
                    </h3>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>