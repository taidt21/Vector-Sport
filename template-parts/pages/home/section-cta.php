<?php
/**
 * Section: CTA (Home)
 */

$contact_url = home_url('/lien-he/');
$product_url = home_url('/san-pham/');
?>

<section class="home_cta" aria-labelledby="home-cta-title">
    <div class="container">
        <div class="home_cta__box">
            <div class="home_cta__content">
                <p class="home_cta__kicker">Bắt đầu cùng Vector Sport</p>

                <h2 class="home_cta__title" id="home-cta-title">
                    Sẵn sàng đăng ký lớp Vovinam hoặc chọn đồ tập phù hợp?
                </h2>

                <p class="home_cta__desc">
                    Nếu bạn đang tìm lớp Vovinam cho bản thân, cho con em hoặc cần chuẩn bị võ phục, đồ tập và phụ kiện, Vector Sport luôn sẵn sàng tư vấn theo nhu cầu thực tế.
                </p>
            </div>

            <div class="home_cta__actions" aria-label="Hành động nhanh">
                <a
                    class="home_cta__btn home_cta__btn--primary"
                    href="<?php echo esc_url($contact_url); ?>"
                    aria-label="Đăng ký tư vấn lớp học Vovinam tại Vector Sport"
                    data-cta="home_cta_contact"
                >
                    Tư vấn lớp học
                </a>

                <a
                    class="home_cta__btn home_cta__btn--ghost"
                    href="<?php echo esc_url($product_url); ?>"
                    aria-label="Xem sản phẩm võ phục, đồ tập và phụ kiện Vector Sport"
                    data-cta="home_cta_products"
                >
                    Xem sản phẩm
                </a>
            </div>
        </div>
    </div>
</section>