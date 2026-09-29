<?php
/**
 * Section: About (Home)
 */

$theme_uri = get_template_directory_uri();

$img_main   = $theme_uri . '/assets/images/home/about/about-1.jpg';
$img_circle = $theme_uri . '/assets/images/home/about/about-2.jpg';

$about_url = home_url('/gioi-thieu/');
?>

<section class="home_about" id="about" aria-labelledby="home-about-title">
    <div class="container home_about__container">
        <div class="home_about__media">
            <div class="home_about__card">
                <img
                    src="<?php echo esc_url($img_main); ?>"
                    alt="Học viên tập luyện Vovinam tại Vector Sport"
                    loading="lazy"
                    decoding="async"
                />
                <span class="home_about__blob" aria-hidden="true"></span>
            </div>

            <div class="home_about__circle">
                <img
                    src="<?php echo esc_url($img_circle); ?>"
                    alt="Cộng đồng học viên Vovinam Vector Sport"
                    loading="lazy"
                    decoding="async"
                />
            </div>
        </div>

        <div class="home_about__content">
            <p class="home_about__kicker">Về Vector Sport</p>

            <h2 class="home_about__title" id="home-about-title">
                Đồng hành cùng học viên từ lớp học Vovinam đến đồ tập
            </h2>

            <p class="home_about__desc">
                Vector Sport phát triển theo hướng kết hợp đào tạo Vovinam và cung cấp võ phục, đồ tập, phụ kiện phục vụ quá trình rèn luyện. Chúng tôi hướng đến việc giúp học viên bắt đầu dễ hơn, tập luyện bài bản hơn và duy trì thói quen vận động lâu dài.
            </p>

            <div class="home_about__grid">
                <article class="home_about__item">
                    <h3 class="home_about__item_title">Lớp học phù hợp nhiều độ tuổi</h3>
                    <p class="home_about__item_desc">
                        Các lớp Vovinam phù hợp với thiếu nhi, học sinh và người mới bắt đầu, giúp học viên lựa chọn môi trường tập luyện đúng với thể lực và mục tiêu cá nhân.
                    </p>
                </article>

                <article class="home_about__item">
                    <h3 class="home_about__item_title">Sản phẩm phục vụ nhu cầu thật</h3>
                    <p class="home_about__item_desc">
                        Vector Sport tập trung vào võ phục, đồ tập, phụ kiện và các sản phẩm cần thiết cho học viên trong quá trình học võ, tập luyện và thi đấu.
                    </p>
                </article>

                <article class="home_about__item">
                    <h3 class="home_about__item_title">Môi trường rèn luyện tích cực</h3>
                    <p class="home_about__item_desc">
                        Học viên không chỉ học kỹ thuật Vovinam mà còn được rèn thể lực, tính kỷ luật, sự tự tin và tinh thần gắn kết trong tập thể.
                    </p>
                </article>

                <article class="home_about__item">
                    <h3 class="home_about__item_title">Cộng đồng tạo động lực lâu dài</h3>
                    <p class="home_about__item_desc">
                        Các hoạt động giao lưu, sinh hoạt CLB và cột mốc trong quá trình tập luyện giúp học viên có thêm động lực để gắn bó lâu dài với Vovinam.
                    </p>
                </article>
            </div>

            <a class="home_about__btn" href="<?php echo esc_url($about_url); ?>" aria-label="Tìm hiểu thêm về Vector Sport">
                Tìm hiểu thêm
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M13 5l7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5z"/>
                </svg>
            </a>
        </div>
    </div>
</section>