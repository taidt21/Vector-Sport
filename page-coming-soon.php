<?php
/*
Template Name: Coming Soon
*/

get_header();

$home_url    = home_url('/');
$contact_url = home_url('/lien-he/');
?>

<main class="vs-coming-simple-page">
    <section class="vs-coming-simple">
        <div class="container">
            <div class="vs-coming-simple__inner">
                <div class="vs-coming-simple__content">
                    <span class="vs-coming-simple__eyebrow">Vector Sport</span>

                    <h1 class="vs-coming-simple__title">
                        Coming Soon
                    </h1>

                    <p class="vs-coming-simple__desc">
                        Chúng tôi đang hoàn thiện trang này để mang đến trải nghiệm tốt hơn cho bạn.
                    </p>

                    <div class="vs-coming-simple__actions">
                        <a class="vs-coming-simple__btn vs-coming-simple__btn--primary" href="<?php echo esc_url($home_url); ?>">
                            Về trang chủ
                        </a>

                        <a class="vs-coming-simple__btn vs-coming-simple__btn--ghost" href="<?php echo esc_url($contact_url); ?>">
                            Liên hệ
                        </a>
                    </div>
                </div>

                <div class="vs-coming-simple__visual">
                    <div class="vs-coming-simple__card">
                        <span class="vs-coming-simple__mark">
                            <img src="/wp-content/themes/vietvodao/assets/icon/logo-main.svg" alt="Vector Sport">
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>