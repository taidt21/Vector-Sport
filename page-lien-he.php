<?php
/**
 * Template Name: Page Lien He
 */

defined('ABSPATH') || exit;

get_header();

$status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';

$map_embed_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.3621277618486!2d105.9033221759991!3d21.058194086765564!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135a90035fb8683%3A0xa7675abe0638ca67!2zVm92aW5hbSBD4bulbSA1IEjDoCBO4buZaQ!5e0!3m2!1svi!2s!4v1777955568779!5m2!1svi!2s';
$map_open_url  = 'https://maps.app.goo.gl/h6Mmf7kRoPwd8Tr99';

$hotline = '0988 812 246';
$hotline_tel = '0988812246';
$address = 'GH3, CT17, Việt Hưng, Long Biên, Hà Nội';

$contact_items = [
    [
        'label' => 'Hotline',
        'value' => $hotline,
        'url'   => 'tel:' . $hotline_tel,
        'external' => false,
    ],
    [
        'label' => 'Địa điểm',
        'value' => 'GH3, CT17, Việt Hưng, Hà Nội',
        'url'   => $map_open_url,
        'external' => true,
    ],
    [
        'label' => 'Hỗ trợ',
        'value' => 'Lớp Vovinam, kèm riêng, CLB, chương trình trải nghiệm',
        'url'   => '',
        'external' => false,
    ],
];

$interest_options = [
    'Lớp Vovinam',
    'Kèm riêng',
    'Hệ thống CLB',
    'Chương trình trải nghiệm',
    'Sản phẩm thể thao',
    'Tư vấn chung',
];

$time_options = [
    'Ca sáng',
    'Ca chiều',
    'Ca tối',
    'Cuối tuần',
    'Cần tư vấn thêm',
];

$status_messages = [
    'success' => [
        'class' => 'contact_hub_page__notice--success',
        'text'  => 'Gửi thông tin thành công. Vector Sport sẽ liên hệ lại với bạn sớm nhất.',
    ],
    'invalid' => [
        'class' => 'contact_hub_page__notice--error',
        'text'  => 'Vui lòng kiểm tra lại họ tên, số điện thoại và nội dung quan tâm.',
    ],
    'error' => [
        'class' => 'contact_hub_page__notice--error',
        'text'  => 'Có lỗi xảy ra khi gửi thông tin. Vui lòng thử lại hoặc gọi hotline.',
    ],
];
?>

<main class="contact_hub_page" id="main-content">
    <section class="contact_hub_page__hero" aria-labelledby="contact-page-title">
        <div class="container">
            <div class="contact_hub_page__hero_grid">
                <div class="contact_hub_page__hero_content">
                    <p class="contact_hub_page__eyebrow">Liên hệ Vector Sport</p>

                    <h1 class="contact_hub_page__title" id="contact-page-title">
                        Liên hệ<br>
                        và đăng ký tư vấn
                    </h1>

                    <p class="contact_hub_page__desc">
                        Để lại thông tin nếu bạn cần tư vấn lớp học Vovinam, kèm riêng, hệ thống CLB, chương trình trải nghiệm hoặc sản phẩm thể thao.
                    </p>

                    <div class="contact_hub_page__hero_actions" aria-label="Hành động liên hệ nhanh">
                        <a
                            class="contact_hub_page__btn contact_hub_page__btn--primary"
                            href="<?php echo esc_url('tel:' . $hotline_tel); ?>"
                            aria-label="<?php echo esc_attr('Gọi hotline Vector Sport ' . $hotline); ?>"
                        >
                            Gọi hotline
                        </a>

                        <a
                            class="contact_hub_page__btn contact_hub_page__btn--ghost"
                            href="<?php echo esc_url($map_open_url); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Mở vị trí Vector Sport trên Google Maps"
                        >
                            Mở Google Maps
                        </a>
                    </div>
                </div>

                <div class="contact_hub_page__quick_panel" aria-label="Thông tin liên hệ nhanh">
                    <?php foreach ($contact_items as $item) : ?>
                        <?php if (!empty($item['url'])) : ?>
                            <a
                                class="contact_hub_page__quick_item"
                                href="<?php echo esc_url($item['url']); ?>"
                                <?php echo !empty($item['external']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                            >
                                <span><?php echo esc_html($item['label']); ?></span>
                                <strong><?php echo esc_html($item['value']); ?></strong>
                            </a>
                        <?php else : ?>
                            <div class="contact_hub_page__quick_item">
                                <span><?php echo esc_html($item['label']); ?></span>
                                <strong><?php echo esc_html($item['value']); ?></strong>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="contact_hub_page__main" aria-labelledby="contact-form-title">
        <div class="container">
            <div class="contact_hub_page__layout">
                <div class="contact_hub_page__form_box">
                    <div class="contact_hub_page__form_head">
                        <p class="contact_hub_page__section_kicker">Biểu mẫu đăng ký</p>

                        <h2 id="contact-form-title">
                            Gửi thông tin tư vấn
                        </h2>

                        <p>
                            Điền thông tin ngắn gọn, Vector Sport sẽ liên hệ lại để hỗ trợ đúng nhu cầu hơn.
                        </p>
                    </div>

                    <?php if (isset($status_messages[$status])) : ?>
                        <div
                            class="contact_hub_page__notice <?php echo esc_attr($status_messages[$status]['class']); ?>"
                            role="status"
                            aria-live="polite"
                        >
                            <?php echo esc_html($status_messages[$status]['text']); ?>
                        </div>
                    <?php endif; ?>

                    <form
                        class="contact_hub_page__form"
                        method="post"
                        action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                    >
                        <input type="hidden" name="action" value="vs_contact_register_submit">
                        <?php wp_nonce_field('vs_contact_register_submit', 'vs_contact_register_nonce'); ?>

                        <div class="contact_hub_page__form_grid">
                            <div class="contact_hub_page__field">
                                <label for="contact_full_name">
                                    Họ và tên <span aria-hidden="true">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="contact_full_name"
                                    name="full_name"
                                    autocomplete="name"
                                    required
                                    aria-required="true"
                                >
                            </div>

                            <div class="contact_hub_page__field">
                                <label for="contact_phone">
                                    Số điện thoại <span aria-hidden="true">*</span>
                                </label>

                                <input
                                    type="tel"
                                    id="contact_phone"
                                    name="phone"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    required
                                    aria-required="true"
                                >
                            </div>

                            <div class="contact_hub_page__field">
                                <label for="contact_email">Email</label>

                                <input
                                    type="email"
                                    id="contact_email"
                                    name="email"
                                    autocomplete="email"
                                >
                            </div>

                            <div class="contact_hub_page__field">
                                <label for="contact_student_age">Độ tuổi học viên</label>

                                <input
                                    type="text"
                                    id="contact_student_age"
                                    name="student_age"
                                    placeholder="Ví dụ: 7 tuổi, THCS, người lớn"
                                    autocomplete="off"
                                >
                            </div>

                            <div class="contact_hub_page__field">
                                <label for="contact_interest">
                                    Nội dung quan tâm <span aria-hidden="true">*</span>
                                </label>

                                <select
                                    id="contact_interest"
                                    name="interest"
                                    required
                                    aria-required="true"
                                >
                                    <option value="">Chọn nội dung</option>

                                    <?php foreach ($interest_options as $option) : ?>
                                        <option value="<?php echo esc_attr($option); ?>">
                                            <?php echo esc_html($option); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="contact_hub_page__field">
                                <label for="contact_time">Khung giờ mong muốn</label>

                                <select id="contact_time" name="time_preference">
                                    <option value="">Chọn khung giờ</option>

                                    <?php foreach ($time_options as $option) : ?>
                                        <option value="<?php echo esc_attr($option); ?>">
                                            <?php echo esc_html($option); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="contact_hub_page__field contact_hub_page__field--full">
                                <label for="contact_message">Nội dung cần tư vấn</label>

                                <textarea
                                    id="contact_message"
                                    name="message"
                                    rows="6"
                                    placeholder="Ví dụ: Tôi muốn đăng ký lớp Vovinam cho bé 8 tuổi, cần tư vấn lịch học buổi tối."
                                ></textarea>
                            </div>
                        </div>

                        <div class="contact_hub_page__form_actions">
                            <button
                                type="submit"
                                class="contact_hub_page__btn contact_hub_page__btn--primary"
                            >
                                Gửi đăng ký
                            </button>

                            <a
                                class="contact_hub_page__btn contact_hub_page__btn--ghost"
                                href="<?php echo esc_url('tel:' . $hotline_tel); ?>"
                                aria-label="<?php echo esc_attr('Gọi hotline Vector Sport ' . $hotline); ?>"
                            >
                                Gọi ngay
                            </a>
                        </div>
                    </form>
                </div>

                <aside class="contact_hub_page__side" aria-label="Thông tin hỗ trợ và địa điểm">
                    <div class="contact_hub_page__side_card contact_hub_page__side_card--dark">
                        <span>Cần hỗ trợ nhanh?</span>

                        <strong><?php echo esc_html($hotline); ?></strong>

                        <p>
                            Gọi trực tiếp để được tư vấn lớp học, lịch tập, đăng ký trải nghiệm hoặc kèm riêng.
                        </p>

                        <a
                            href="<?php echo esc_url('tel:' . $hotline_tel); ?>"
                            aria-label="<?php echo esc_attr('Gọi hotline Vector Sport ' . $hotline); ?>"
                        >
                            Gọi hotline
                        </a>
                    </div>

                    <div class="contact_hub_page__side_card">
                        <h3>Địa điểm</h3>

                        <p><?php echo esc_html($address); ?></p>

                        <div class="contact_hub_page__mini_map">
                            <iframe
                                src="<?php echo esc_url($map_embed_url); ?>"
                                width="100%"
                                height="100%"
                                style="border:0;"
                                allowfullscreen
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Bản đồ vị trí Vector Sport tại Việt Hưng, Long Biên, Hà Nội">
                            </iframe>
                        </div>

                        <a
                            class="contact_hub_page__map_link"
                            href="<?php echo esc_url($map_open_url); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Mở vị trí Vector Sport trên Google Maps"
                        >
                            Mở Google Maps
                        </a>
                    </div>

                    <div class="contact_hub_page__side_card">
                        <h3>Nên chọn gì?</h3>

                        <ul class="contact_hub_page__hint_list">
                            <li>Chọn “Lớp Vovinam” nếu muốn học theo lớp nhóm.</li>
                            <li>Chọn “Kèm riêng” nếu cần lịch linh hoạt hơn.</li>
                            <li>Chọn “Chương trình trải nghiệm” nếu cần tổ chức cho nhóm hoặc trường học.</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();