<?php
/**
 * Section: Activities (Home)
 */

$theme_uri = get_template_directory_uri();

$activities_url = home_url('/hoat-dong/');

$activities = [
    [
        'title' => 'Không khí lớp học và quá trình tập luyện',
        'desc'  => 'Những hình ảnh tập luyện đều đặn giúp phụ huynh và học viên thấy rõ môi trường học có thực, có hướng dẫn và có tính kết nối.',
        'image' => $theme_uri . '/assets/images/home/about/about-1.jpg',
        'alt'   => 'Học viên Vovinam tập luyện trong lớp học Vector Sport',
        'url'   => $activities_url,
    ],
    [
        'title' => 'Cột mốc thăng đai và sự tiến bộ',
        'desc'  => 'Thăng đai là một trong những điểm giúp hành trình rèn luyện trở nên có mục tiêu hơn và tạo động lực cho học viên theo lâu dài.',
        'image' => $theme_uri . '/assets/images/home/hero/background-1.jpg',
        'alt'   => 'Học viên Vovinam tham gia hoạt động thăng đai tại Vector Sport',
        'url'   => $activities_url,
    ],
    [
        'title' => 'Giao lưu và tinh thần cộng đồng',
        'desc'  => 'Các hoạt động giao lưu giúp học viên thêm gắn bó với môi trường học, đồng thời tạo nên một cộng đồng tích cực và truyền cảm hứng.',
        'image' => $theme_uri . '/assets/images/home/hero/background-2.jpg',
        'alt'   => 'Hoạt động giao lưu cộng đồng của học viên Vovinam Vector Sport',
        'url'   => $activities_url,
    ],
];
?>

<section class="home_activities" id="activities" aria-labelledby="home-activities-title">
    <div class="container">
        <div class="home_section_heading">
            <div class="home_section_heading__content">
                <p class="home_section_heading__kicker">Hoạt động lớp học & cộng đồng</p>

                <h2 class="home_section_heading__title" id="home-activities-title">
                    Hình ảnh tập luyện tạo nên niềm tin thực tế
                </h2>

                <p class="home_section_heading__desc">
                    Từ giờ học, sinh hoạt tập thể đến các cột mốc thăng đai, mỗi hoạt động đều phản ánh rõ môi trường tập luyện nghiêm túc và tinh thần gắn kết tại Vector Sport.
                </p>
            </div>
        </div>

        <div class="home_activities__grid">
            <?php foreach ($activities as $item): ?>
                <article class="home_activities__card">
                    <a
                        class="home_activities__media"
                        href="<?php echo esc_url($item['url']); ?>"
                        aria-label="<?php echo esc_attr('Xem thêm về ' . $item['title']); ?>"
                    >
                        <img
                            src="<?php echo esc_url($item['image']); ?>"
                            alt="<?php echo esc_attr($item['alt']); ?>"
                            loading="lazy"
                            decoding="async"
                            width="420"
                            height="320"
                        />
                    </a>

                    <div class="home_activities__body">
                        <h3 class="home_activities__title">
                            <a href="<?php echo esc_url($item['url']); ?>">
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        </h3>

                        <p class="home_activities__desc">
                            <?php echo esc_html($item['desc']); ?>
                        </p>

                        <a
                            class="home_activities__link"
                            href="<?php echo esc_url($item['url']); ?>"
                            aria-label="<?php echo esc_attr('Tìm hiểu thêm về ' . $item['title']); ?>"
                        >
                            Tìm hiểu thêm
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>