<?php
/**
 * Template Name: Page Chuong Trinh Trai Nghiem
 */

defined('ABSPATH') || exit;

get_header();

$theme_uri = get_template_directory_uri();

$contact_url = home_url('/lien-he/');
$hotline = '0988 812 246';
$hotline_tel = '0988812246';

$images = [
    'hero_main'    => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/hero-main.jpg',
    'hero_small_1' => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/hero-small-1.jpg',
    'hero_small_2' => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/hero-small-2.jpg',

    'station_1'    => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/station-1.jpg',
    'station_2'    => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/station-2.jpg',
    'station_3'    => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/station-3.jpg',
    'station_4'    => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/station-4.jpg',

    'moment_1'     => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/moment-1.jpg',
    'moment_2'     => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/moment-2.jpg',
    'moment_3'     => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/moment-3.jpg',
    'moment_4'     => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/moment-4.jpg',

    'school'       => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/school.jpg',
    'group'        => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/group.jpg',
    'club'         => $theme_uri . '/assets/images/chuong-trinh-trai-nghiem/club.jpg',
];

$ticket_points = [
    'Làm quen Vovinam',
    'Rèn kỷ luật',
    'Tăng tự tin',
    'Phối hợp nhóm',
];

$stations = [
    [
        'number' => '01',
        'title'  => 'Làm quen và khởi động',
        'desc'   => 'Học viên bắt đầu bằng hoạt động làm quen, khởi động nhẹ và tạo năng lượng để sẵn sàng tham gia.',
        'skill'  => 'Lắng nghe, tập trung',
        'image'  => $images['station_1'],
        'alt'    => 'Học viên làm quen và khởi động trong chương trình trải nghiệm Vovinam',
    ],
    [
        'number' => '02',
        'title'  => 'Tập thử Vovinam',
        'desc'   => 'Các em được làm quen với một số nội dung Vovinam cơ bản, bài tập phản xạ và phối hợp đơn giản.',
        'skill'  => 'Kỷ luật, kiểm soát cơ thể',
        'image'  => $images['station_2'],
        'alt'    => 'Học viên tập thử các động tác Vovinam cơ bản',
    ],
    [
        'number' => '03',
        'title'  => 'Thử thách nhóm',
        'desc'   => 'Hoạt động nhóm giúp học viên phối hợp cùng nhau, chủ động hơn và mạnh dạn hơn trong tập thể.',
        'skill'  => 'Hợp tác, tự tin',
        'image'  => $images['station_3'],
        'alt'    => 'Học viên tham gia thử thách nhóm trong chương trình trải nghiệm',
    ],
    [
        'number' => '04',
        'title'  => 'Tổng kết cuối buổi',
        'desc'   => 'Học viên cùng nhìn lại những gì đã tham gia, ghi nhận trải nghiệm và hiểu rõ giá trị của buổi học.',
        'skill'  => 'Trách nhiệm, tự nhận thức',
        'image'  => $images['station_4'],
        'alt'    => 'Học viên tổng kết cuối buổi trải nghiệm Vovinam',
    ],
];

$stamps = [
    [
        'title' => 'Tự tin',
        'desc'  => 'Dám thử và dám tham gia',
    ],
    [
        'title' => 'Tập trung',
        'desc'  => 'Lắng nghe và làm theo hướng dẫn',
    ],
    [
        'title' => 'Hợp tác',
        'desc'  => 'Phối hợp cùng bạn trong nhóm',
    ],
    [
        'title' => 'Kỷ luật',
        'desc'  => 'Tôn trọng nguyên tắc chung',
    ],
    [
        'title' => 'Chủ động',
        'desc'  => 'Tự giác tham gia hoạt động',
    ],
    [
        'title' => 'Trách nhiệm',
        'desc'  => 'Hoàn thành phần việc của mình',
    ],
];

$formats = [
    [
        'title' => 'Tổ chức tại trường học',
        'desc'  => 'Phù hợp cho hoạt động ngoại khóa, ngày hội kỹ năng, tiết trải nghiệm hoặc chương trình vận động theo chủ đề.',
        'image' => $images['school'],
        'alt'   => 'Chương trình trải nghiệm Vovinam tổ chức tại trường học',
    ],
    [
        'title' => 'Tổ chức theo nhóm riêng',
        'desc'  => 'Dành cho nhóm học sinh, phụ huynh hoặc tập thể muốn có một buổi hoạt động vừa vận động vừa tăng sự gắn kết.',
        'image' => $images['group'],
        'alt'   => 'Chương trình trải nghiệm Vovinam cho nhóm học sinh và phụ huynh',
    ],
    [
        'title' => 'Trải nghiệm tại CLB',
        'desc'  => 'Giúp học viên cảm nhận trực tiếp môi trường tập luyện, tinh thần lớp học và văn hóa sinh hoạt Vovinam.',
        'image' => $images['club'],
        'alt'   => 'Học viên trải nghiệm môi trường tập luyện tại câu lạc bộ Vovinam',
    ],
];

$passport_notes = [
    'Có thể điều chỉnh theo độ tuổi và số lượng học viên',
    'Có thể triển khai ngắn gọn hoặc mở rộng theo mục tiêu',
    'Phù hợp cho thiếu nhi, học sinh, nhóm trải nghiệm và trường học',
    'Nội dung kết hợp vận động, kỷ luật và kỹ năng sống',
];
?>

<main class="exp_passport" id="main-content">
    <section class="exp_passport__hero" aria-labelledby="exp-passport-title">
        <div class="container">
            <div class="exp_passport__hero_grid">
                <div class="exp_passport__hero_content">
                    <p class="exp_passport__eyebrow">Chương trình trải nghiệm</p>

                    <h1 class="exp_passport__hero_title" id="exp-passport-title">
                        Chương trình trải nghiệm Vovinam
                    </h1>

                    <p class="exp_passport__hero_desc">
                        Một chương trình kết hợp Vovinam, vận động nhóm và kỹ năng sống. Học viên không chỉ được vận động mà còn học cách tự tin hơn, tập trung hơn và phối hợp tốt hơn trong tập thể.
                    </p>

                    <div class="exp_passport__hero_actions" aria-label="Hành động tư vấn chương trình trải nghiệm">
                        <a
                            class="exp_passport__btn exp_passport__btn--primary"
                            href="<?php echo esc_url($contact_url); ?>"
                            aria-label="Liên hệ tư vấn chương trình trải nghiệm Vovinam"
                        >
                            Tư vấn chương trình
                        </a>

                        <a
                            class="exp_passport__btn exp_passport__btn--light"
                            href="<?php echo esc_url('tel:' . $hotline_tel); ?>"
                            aria-label="<?php echo esc_attr('Gọi hotline tư vấn chương trình trải nghiệm ' . $hotline); ?>"
                        >
                            Gọi hotline
                        </a>
                    </div>
                </div>

                <div class="exp_passport__hero_visual">
                    <div class="exp_passport__hero_main">
                        <img
                            src="<?php echo esc_url($images['hero_main']); ?>"
                            alt="Học viên tham gia chương trình trải nghiệm Vovinam"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                            width="720"
                            height="760">
                    </div>

                    <div class="exp_passport__hero_small exp_passport__hero_small--one">
                        <img
                            src="<?php echo esc_url($images['hero_small_1']); ?>"
                            alt="Hoạt động vận động cho học viên trong chương trình trải nghiệm"
                            loading="lazy"
                            decoding="async"
                            width="320"
                            height="240">
                    </div>

                    <div class="exp_passport__hero_small exp_passport__hero_small--two">
                        <img
                            src="<?php echo esc_url($images['hero_small_2']); ?>"
                            alt="Hoạt động nhóm và kỹ năng sống cho học viên"
                            loading="lazy"
                            decoding="async"
                            width="320"
                            height="240">
                    </div>

                    <div class="exp_passport__hero_note">
                        <span>Trọng tâm</span>
                        <strong>Vovinam + kỹ năng sống</strong>
                    </div>
                </div>
            </div>

            <ul class="exp_passport__hero_points" aria-label="Các điểm nổi bật của chương trình">
                <?php foreach ($ticket_points as $point) : ?>
                    <li><?php echo esc_html($point); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="exp_passport__stations" aria-labelledby="exp-passport-stations-title">
        <div class="container">
            <div class="exp_passport__section_head">
                <span>04 trạm trải nghiệm</span>

                <h2 id="exp-passport-stations-title">
                    Một buổi diễn ra thế nào?
                </h2>
            </div>

            <div class="exp_passport__station_board">
                <?php foreach ($stations as $station) : ?>
                    <article class="exp_passport__station">
                        <div class="exp_passport__station_media">
                            <img
                                src="<?php echo esc_url($station['image']); ?>"
                                alt="<?php echo esc_attr($station['alt']); ?>"
                                loading="lazy"
                                decoding="async"
                                width="520"
                                height="360">
                        </div>

                        <div class="exp_passport__station_body">
                            <span class="exp_passport__station_no" aria-hidden="true">
                                <?php echo esc_html($station['number']); ?>
                            </span>

                            <h3><?php echo esc_html($station['title']); ?></h3>

                            <p><?php echo esc_html($station['desc']); ?></p>

                            <strong>Kỹ năng: <?php echo esc_html($station['skill']); ?></strong>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="exp_passport__stamps" aria-labelledby="exp-passport-stamps-title">
        <div class="container">
            <div class="exp_passport__stamp_intro">
                <span>Skill stamps</span>

                <h2 id="exp-passport-stamps-title">
                    Kỹ năng từ trải nghiệm thật
                </h2>
            </div>

            <div class="exp_passport__stamp_grid">
                <?php foreach ($stamps as $stamp) : ?>
                    <article class="exp_passport__stamp">
                        <h3><?php echo esc_html($stamp['title']); ?></h3>
                        <p><?php echo esc_html($stamp['desc']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="exp_passport__moments" aria-labelledby="exp-passport-moments-title">
        <div class="container">
            <div class="exp_passport__section_head">
                <span>Khoảnh khắc thực tế</span>

                <h2 id="exp-passport-moments-title">
                    Nhìn rõ hơn về chương trình
                </h2>
            </div>

            <div class="exp_passport__moment_grid">
                <figure class="exp_passport__moment exp_passport__moment--large">
                    <img
                        src="<?php echo esc_url($images['moment_1']); ?>"
                        alt="Hoạt động trải nghiệm Vovinam trong không khí tập thể"
                        loading="lazy"
                        decoding="async"
                        width="760"
                        height="640">
                </figure>

                <figure class="exp_passport__moment">
                    <img
                        src="<?php echo esc_url($images['moment_2']); ?>"
                        alt="Học viên vận động và phối hợp theo nhóm"
                        loading="lazy"
                        decoding="async"
                        width="420"
                        height="320">
                </figure>

                <figure class="exp_passport__moment">
                    <img
                        src="<?php echo esc_url($images['moment_3']); ?>"
                        alt="Học viên rèn kỹ năng qua hoạt động trải nghiệm"
                        loading="lazy"
                        decoding="async"
                        width="420"
                        height="320">
                </figure>

                <figure class="exp_passport__moment exp_passport__moment--wide">
                    <img
                        src="<?php echo esc_url($images['moment_4']); ?>"
                        alt="Không khí chương trình trải nghiệm Vovinam cho học viên"
                        loading="lazy"
                        decoding="async"
                        width="760"
                        height="420">
                </figure>
            </div>
        </div>
    </section>

    <section class="exp_passport__formats" aria-labelledby="exp-passport-formats-title">
        <div class="container">
            <div class="exp_passport__section_head">
                <span>Format triển khai</span>

                <h2 id="exp-passport-formats-title">
                    Tổ chức linh hoạt
                </h2>
            </div>

            <div class="exp_passport__format_list">
                <?php foreach ($formats as $format) : ?>
                    <article class="exp_passport__format">
                        <img
                            src="<?php echo esc_url($format['image']); ?>"
                            alt="<?php echo esc_attr($format['alt']); ?>"
                            loading="lazy"
                            decoding="async"
                            width="520"
                            height="360">

                        <div class="exp_passport__format_content">
                            <h3><?php echo esc_html($format['title']); ?></h3>
                            <p><?php echo esc_html($format['desc']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="exp_passport__notes" aria-labelledby="exp-passport-notes-title">
        <div class="container">
            <div class="exp_passport__notes_inner">
                <div class="exp_passport__notes_title">
                    <span>Program notes</span>

                    <h2 id="exp-passport-notes-title">
                        Chương trình có thể điều chỉnh theo nhóm tham gia
                    </h2>
                </div>

                <ul class="exp_passport__notes_list">
                    <?php foreach ($passport_notes as $note) : ?>
                        <li><?php echo esc_html($note); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <section class="exp_passport__final" aria-labelledby="exp-passport-final-title">
        <div class="container">
            <div class="exp_passport__final_inner">
                <span>Sẵn sàng bắt đầu?</span>

                <h2 id="exp-passport-final-title">
                    Cùng xây chương trình phù hợp
                </h2>

                <p>
                    Liên hệ để được tư vấn format theo độ tuổi, số lượng học viên, thời lượng và mục tiêu tổ chức.
                </p>

                <div class="exp_passport__hero_actions exp_passport__hero_actions--center" aria-label="Hành động cuối trang">
                    <a
                        class="exp_passport__btn exp_passport__btn--primary"
                        href="<?php echo esc_url($contact_url); ?>"
                        aria-label="Liên hệ tư vấn chương trình trải nghiệm Vovinam"
                    >
                        Liên hệ tư vấn
                    </a>

                    <a
                        class="exp_passport__btn exp_passport__btn--ghost"
                        href="<?php echo esc_url('tel:' . $hotline_tel); ?>"
                        aria-label="<?php echo esc_attr('Gọi hotline tư vấn chương trình trải nghiệm ' . $hotline); ?>"
                    >
                        Gọi hotline
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
$service_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => 'Chương trình trải nghiệm Vovinam',
    'description' => 'Chương trình kết hợp Vovinam, vận động nhóm và kỹ năng sống dành cho thiếu nhi, học sinh, nhóm trải nghiệm và trường học.',
    'provider' => [
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
    ],
    'areaServed' => [
        '@type' => 'Place',
        'name' => 'Hà Nội',
    ],
    'serviceType' => 'Chương trình trải nghiệm Vovinam',
    'url' => get_permalink(),
];
?>

<script type="application/ld+json">
<?php echo wp_json_encode($service_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<?php
get_footer();