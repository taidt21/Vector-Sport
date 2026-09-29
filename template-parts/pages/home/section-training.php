<?php
/**
 * Section: Training (Home)
 */

$contact_url = home_url('/lien-he/');
$about_url   = home_url('/gioi-thieu/');

$training_programs = [
    [
        'label' => 'Thiếu nhi',
        'title' => 'Lớp Vovinam cho trẻ em và học sinh',
        'desc'  => 'Giúp các em phát triển thể chất, tăng sự tự tin, hình thành tính kỷ luật và thói quen vận động tích cực.',
        'meta'  => 'Phù hợp với phụ huynh đang tìm môi trường vừa rèn luyện vừa giáo dục.',
    ],
    [
        'label' => 'Nhập môn',
        'title' => 'Lớp cho người mới bắt đầu',
        'desc'  => 'Dành cho học viên muốn làm quen với Vovinam từ nền tảng cơ bản, dễ theo và có định hướng rõ ràng.',
        'meta'  => 'Phù hợp cho học sinh, sinh viên và người lớn muốn bắt đầu từ con số 0.',
    ],
    [
        'label' => 'Phát triển',
        'title' => 'Hành trình tập luyện lâu dài',
        'desc'  => 'Từ kỹ thuật cơ bản, thể lực đến các cột mốc thăng đai và sinh hoạt cộng đồng, học viên có thêm động lực để gắn bó lâu dài.',
        'meta'  => 'Phù hợp cho người muốn duy trì việc tập luyện nghiêm túc.',
    ],
];

$training_badges = [
    'Tự vệ',
    'Thể lực',
    'Kỷ luật',
    'Tự tin',
];

$training_highlights = [
    'Rèn kỹ năng tự vệ, thể lực và tinh thần kỷ luật',
    'Lộ trình tập luyện rõ ràng, phù hợp cho nhiều độ tuổi',
    'Môi trường tích cực để học viên gắn bó và tiến bộ lâu dài',
];
?>

<section class="home_training" id="training" aria-labelledby="home-training-title">
    <div class="container">
        <div class="home_training__shell">
            <div class="home_training__intro">
                <p class="home_training__kicker">Đào tạo Vovinam</p>

                <h2 class="home_training__title" id="home-training-title">
                    Lớp Vovinam có lộ trình rõ ràng và môi trường tập luyện tích cực
                </h2>

                <p class="home_training__desc">
                    Vector Sport xây dựng lớp học Vovinam dành cho thiếu nhi, học sinh, sinh viên và người mới bắt đầu. Học viên được rèn thể lực, kỹ thuật, tinh thần kỷ luật và sự tự tin qua từng giai đoạn tập luyện.
                </p>

                <ul class="home_training__badges" aria-label="Giá trị rèn luyện chính">
                    <?php foreach ($training_badges as $badge): ?>
                        <li class="home_training__badge">
                            <?php echo esc_html($badge); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <ul class="home_training__highlights">
                    <?php foreach ($training_highlights as $item): ?>
                        <li><?php echo esc_html($item); ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="home_training__actions" aria-label="Hành động đăng ký và tìm hiểu lớp học">
                    <a
                        class="home_training__btn home_training__btn--primary"
                        href="<?php echo esc_url($contact_url); ?>"
                        aria-label="Đăng ký tư vấn lớp học Vovinam tại Vector Sport"
                        data-cta="home_training_contact"
                    >
                        Đăng ký tư vấn
                    </a>

                    <a
                        class="home_training__btn home_training__btn--ghost"
                        href="<?php echo esc_url($about_url); ?>"
                        aria-label="Tìm hiểu thêm về chương trình đào tạo Vovinam tại Vector Sport"
                        data-cta="home_training_learn_more"
                    >
                        Tìm hiểu thêm
                    </a>
                </div>
            </div>

            <div class="home_training__programs">
                <?php foreach ($training_programs as $item): ?>
                    <article class="home_training__program">
                        <span class="home_training__program_label">
                            <?php echo esc_html($item['label']); ?>
                        </span>

                        <h3 class="home_training__program_title">
                            <?php echo esc_html($item['title']); ?>
                        </h3>

                        <p class="home_training__program_desc">
                            <?php echo esc_html($item['desc']); ?>
                        </p>

                        <p class="home_training__program_meta">
                            <?php echo esc_html($item['meta']); ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>