<?php
/**
 * Template Name: Vector Checkout
 */
if (!defined('ABSPATH')) {
    exit;
}
get_header();

$account_url = home_url('/account/');
$cart_items = function_exists('vs_commerce_get_cart_items') ? vs_commerce_get_cart_items() : [];
$cart_totals = function_exists('vs_commerce_get_cart_totals') ? vs_commerce_get_cart_totals($cart_items) : ['subtotal' => 0, 'subtotal_html' => '0đ', 'count' => 0];
$grand_total_html = $cart_totals['subtotal_html'] ?? '0đ';
$cart_count = (int) ($cart_totals['count'] ?? 0);
$is_cart_empty = $cart_count < 1;

$payment_status = isset($_GET['vs_payment']) ? sanitize_text_field(wp_unslash($_GET['vs_payment'])) : '';
$order_code = isset($_GET['vs_order']) ? sanitize_text_field(wp_unslash($_GET['vs_order'])) : '';

$notice_map = [
    'success' => ['class' => 'is-success', 'title' => 'Thanh toán thành công', 'text' => $order_code ? sprintf('Đơn hàng %s đã được thanh toán thành công.', $order_code) : 'Thanh toán thành công.'],
    'cod' => ['class' => 'is-success', 'title' => 'Đặt hàng thành công', 'text' => $order_code ? sprintf('Đơn hàng %s đã được ghi nhận. Bạn sẽ thanh toán khi nhận hàng.', $order_code) : 'Đơn hàng đã được ghi nhận.'],
    'failed' => ['class' => 'is-error', 'title' => 'Thanh toán chưa thành công', 'text' => 'Giao dịch chưa hoàn tất. Bạn có thể thử lại bằng VNPAY hoặc chọn COD.'],
    'invalid-signature' => ['class' => 'is-error', 'title' => 'Không xác minh được giao dịch', 'text' => 'Phản hồi thanh toán không hợp lệ.'],
    'invalid-amount' => ['class' => 'is-error', 'title' => 'Sai lệch số tiền giao dịch', 'text' => 'Số tiền thanh toán không khớp với đơn hàng.'],
    'order-not-found' => ['class' => 'is-error', 'title' => 'Không tìm thấy đơn hàng', 'text' => 'Không tìm thấy đơn hàng tương ứng với giao dịch vừa rồi.'],
];
$notice = $notice_map[$payment_status] ?? null;
?>
<main class="vs-checkout js-vs-checkout">
    <form
        class="js-vs-checkout-form"
        action=""
        method="post"
        novalidate
        data-initial-cart-count="<?php echo esc_attr($cart_count); ?>"
    >
        <div class="vs-checkout__container container">
            <?php if ($notice) : ?>
                <div class="vs-checkout-notice <?php echo esc_attr($notice['class']); ?>">
                    <strong><?php echo esc_html($notice['title']); ?></strong>
                    <span><?php echo esc_html($notice['text']); ?></span>
                </div>
            <?php endif; ?>

            <div class="vs-checkout-form-status js-checkout-form-status" aria-live="polite"></div>

            <div class="vs-checkout__grid">
                <section class="vs-checkout__main">
                    <div class="vs-checkout-card vs-checkout-login">
                        <div class="vs-checkout-login__text">Đăng nhập để mua hàng tiện lợi hơn, lưu thông tin nhận hàng và theo dõi đơn nhanh chóng.</div>
                        <a href="<?php echo esc_url($account_url); ?>" class="vs-checkout-login__btn">Đăng nhập</a>
                    </div>

                    <div class="vs-checkout-card vs-checkout-section">
                        <h2 class="vs-checkout-section__title">Thông tin giao hàng</h2>
                        <div class="vs-checkout-form">
                            <div class="vs-checkout-form__field js-checkout-field">
                                <label for="vs-customer-name">Họ và tên</label>
                                <input
                                    id="vs-customer-name"
                                    type="text"
                                    name="customer_name"
                                    placeholder="Nhập họ và tên"
                                    autocomplete="name"
                                    data-label="Họ và tên"
                                    required
                                >
                            </div>

                            <div class="vs-checkout-form__field vs-checkout-form__field--phone js-checkout-field">
                                <label for="vs-customer-phone">Số điện thoại</label>
                                <input
                                    id="vs-customer-phone"
                                    type="tel"
                                    name="customer_phone"
                                    placeholder="Nhập số điện thoại"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    data-label="Số điện thoại"
                                    required
                                >
                                <span class="vs-checkout-form__flag">🇻🇳</span>
                            </div>

                            <div class="vs-checkout-form__field js-checkout-field">
                                <label for="vs-customer-email">Email</label>
                                <input
                                    id="vs-customer-email"
                                    type="email"
                                    name="customer_email"
                                    placeholder="Nhập email"
                                    autocomplete="email"
                                    data-label="Email"
                                >
                            </div>

                            <div class="vs-checkout-address-mode js-address-mode-group">
                                <label class="vs-checkout-address-mode__item is-active">
                                    <input type="radio" name="address_mode" value="new" checked>
                                    <span class="vs-checkout-address-mode__radio"></span>
                                    <span>Địa chỉ</span>
                                </label>
                                <label class="vs-checkout-address-mode__item">
                                    <input type="radio" name="address_mode" value="old">
                                    <span class="vs-checkout-address-mode__radio"></span>
                                    <span>Địa chỉ trước sáp nhập</span>
                                </label>
                            </div>

                            <input type="hidden" name="province" value="">
                            <input type="hidden" name="district" value="">
                            <input type="hidden" name="ward" value="">

                            <div class="vs-checkout-form__field vs-checkout-form__field--select js-checkout-field">
                                <label for="vs-province-code">Tỉnh / Thành phố</label>
                                <select id="vs-province-code" name="province_code" class="js-address-province" data-label="Tỉnh / Thành phố" required>
                                    <option value="">Chọn Tỉnh / Thành phố</option>
                                </select>
                            </div>

                            <div class="vs-checkout-form__field vs-checkout-form__field--select js-address-district-field js-checkout-field">
                                <label for="vs-district-code" class="js-address-district-label">Quận / Huyện</label>
                                <select id="vs-district-code" name="district_code" class="js-address-district" data-label="Quận / Huyện" disabled>
                                    <option value="">Chọn Quận / Huyện</option>
                                </select>
                            </div>

                            <div class="vs-checkout-form__field vs-checkout-form__field--select js-checkout-field">
                                <label for="vs-ward-code" class="js-address-ward-label">Phường / Xã / Đặc khu</label>
                                <select id="vs-ward-code" name="ward_code" class="js-address-ward" data-label="Phường / Xã / Đặc khu" disabled required>
                                    <option value="">Chọn Phường / Xã</option>
                                </select>
                            </div>

                            <div class="vs-checkout-form__field js-checkout-field">
                                <label for="vs-address-line">Địa chỉ cụ thể</label>
                                <input
                                    id="vs-address-line"
                                    type="text"
                                    name="address_line"
                                    placeholder="Địa chỉ, tên đường, số nhà"
                                    autocomplete="street-address"
                                    data-label="Địa chỉ cụ thể"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="vs-checkout-card vs-checkout-section">
                        <h2 class="vs-checkout-section__title">Phương thức giao hàng</h2>
                        <div class="vs-checkout-payment js-shipping-group">
                            <label class="vs-checkout-payment__item is-active">
                                <input type="radio" name="shipping_method" value="standard" checked>
                                <span class="vs-checkout-payment__radio"></span>
                                <span class="vs-checkout-payment__logo vs-checkout-payment__logo--icon">🚚</span>
                                <span class="vs-checkout-payment__text">Giao hàng tiêu chuẩn<small>Miễn phí vận chuyển cho đơn từ 200.000đ</small></span>
                            </label>
                        </div>
                    </div>

                    <div class="vs-checkout-card vs-checkout-section">
                        <h2 class="vs-checkout-section__title">Phương thức thanh toán</h2>
                        <div class="vs-checkout-payment js-payment-group">
                            <label class="vs-checkout-payment__item is-active">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <span class="vs-checkout-payment__radio"></span>
                                <span class="vs-checkout-payment__logo vs-checkout-payment__logo--icon">💵</span>
                                <span class="vs-checkout-payment__text">COD - Thanh toán khi nhận hàng<small>Thanh toán tiền mặt sau khi nhận và kiểm tra đơn hàng</small></span>
                            </label>
                            <label class="vs-checkout-payment__item">
                                <input type="radio" name="payment_method" value="vnpay">
                                <span class="vs-checkout-payment__radio"></span>
                                <span class="vs-checkout-payment__logo">
                                    <img src="/wp-content/themes/vietvodao/assets/icon/VNPAY_.svg" alt="VNPAY">
                                </span>
                                <span class="vs-checkout-payment__text">Thanh toán online qua cổng VNPAY<small>Hỗ trợ thẻ ATM nội địa, QR ngân hàng, Visa, Mastercard, JCB</small></span>
                            </label>
                        </div>
                        <input type="hidden" name="payment_bank_code" value="">
                    </div>

                    <div class="vs-checkout-card vs-checkout-section vs-checkout-note">
                        <h2 class="vs-checkout-section__title">Ghi chú</h2>
                        <textarea name="note" rows="3" placeholder="Ghi chú đơn hàng"></textarea>
                    </div>
                </section>

                <aside class="vs-checkout__sidebar">
                    <div class="vs-checkout-card vs-checkout-cart-card">
                        <h2 class="vs-checkout-section__title">Giỏ hàng</h2>
                        <div class="vs-checkout-cart js-checkout-cart-list"><?php echo function_exists('vs_commerce_render_checkout_cart_html') ? vs_commerce_render_checkout_cart_html() : '<div class="vs-checkout-cart__empty">Giỏ hàng của bạn đang trống.</div>'; ?></div>
                    </div>

                    <div class="vs-checkout-card vs-checkout-promo-card">
                        <h2 class="vs-checkout-section__title">Mã khuyến mãi</h2>
                        <div class="vs-checkout-promo__form">
                            <input type="text" name="coupon_code" placeholder="Nhập mã khuyến mãi" autocomplete="off">
                        </div>
                        <p class="vs-checkout-promo__help">Bạn có thể nhập mã giảm giá ở đây. Hệ thống sẽ kiểm tra và áp dụng khi đặt hàng nếu mã hợp lệ.</p>
                    </div>

                    <div class="vs-checkout-card vs-checkout-summary-card">
                        <h2 class="vs-checkout-section__title">Tổng cộng</h2>
                        <div class="vs-checkout-summary__row"><span>Tạm tính</span><strong class="js-checkout-subtotal"><?php echo esc_html($cart_totals['subtotal_html'] ?? '0đ'); ?></strong></div>
                        <div class="vs-checkout-summary__row"><span>Phí vận chuyển</span><strong>Miễn phí</strong></div>
                        <div class="vs-checkout-summary__row vs-checkout-summary__row--total"><span>Tổng thanh toán</span><strong class="js-checkout-grand-total"><?php echo esc_html($grand_total_html); ?></strong></div>
                        <button type="submit" class="vs-checkout-summary__submit js-checkout-submit" <?php disabled($is_cart_empty); ?>>Đặt hàng</button>
                        <?php if ($is_cart_empty) : ?>
                            <p class="vs-checkout-summary__hint">Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi đặt hàng.</p>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </div>

        <div class="vs-checkout-sticky">
            <div class="vs-checkout-sticky__total js-checkout-mobile-total"><?php echo esc_html($grand_total_html); ?></div>
            <button type="submit" class="vs-checkout-sticky__button js-checkout-submit" <?php disabled($is_cart_empty); ?>>Đặt hàng</button>
        </div>
    </form>
</main>
<?php get_footer(); ?>
