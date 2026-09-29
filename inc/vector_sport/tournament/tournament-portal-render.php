<?php
defined('ABSPATH') || exit;

/* =========================================================================
 * TRANG "QUẢN LÝ ĐỘI" (front-end, không phải wp-admin) — trưởng đoàn xem/
 * sửa đúng đội của mình. Gọi từ page-quan-ly-doi.php.
 * ========================================================================= */

function vs_render_team_portal_page() {
    if (!is_user_logged_in()) {
        vs_render_team_portal_login_form();
        return;
    }

    $registration = vs_get_my_tournament_registration();

    if (!$registration) {
        ?>
<div class="team_portal_page__empty">
    <h1>Chưa có đội nào được đăng ký</h1>
    <p>Tài khoản này chưa gắn với đơn vị nào. Đăng ký đội tại trang đăng ký giải đấu.</p>
    <a class="tournament_register_page__btn tournament_register_page__btn--primary"
        href="<?php echo esc_url(home_url('/dang-ky-giai-vovinam/')); ?>">
        Đăng ký đội ngay
    </a>
</div>
<?php
        return;
    }

    $view = isset($_GET['view']) ? sanitize_key(wp_unslash($_GET['view'])) : '';
    $athlete_id = isset($_GET['athlete_id']) ? absint($_GET['athlete_id']) : 0;
    $staff_id = isset($_GET['staff_id']) ? absint($_GET['staff_id']) : 0;

    if ($view === 'add_athlete') {
        vs_render_team_portal_add_athletes_form($registration);
    } elseif ($view === 'edit_athlete' && $athlete_id > 0) {
        vs_render_team_portal_athlete_form($registration, $athlete_id);
    } elseif ($view === 'add_staff') {
        vs_render_team_portal_add_staffs_form($registration);
    } elseif ($view === 'edit_staff' && $staff_id > 0) {
        vs_render_team_portal_staff_form($registration, $staff_id);
    } elseif ($view === 'edit_team') {
        vs_render_team_portal_team_form($registration);
    } else {
        vs_render_team_portal_dashboard($registration);
    }
}

function vs_team_portal_notice() {
    $msg_map = [
        'team_saved'      => ['success', 'Đã lưu thông tin đội.'],
        'athlete_saved'   => ['success', 'Đã lưu thông tin VĐV.'],
        'athlete_deleted' => ['success', 'Đã xoá VĐV.'],
        'staff_saved'     => ['success', 'Đã lưu thông tin.'],
        'staff_deleted'   => ['success', 'Đã xoá.'],
        'invalid'         => ['error', 'Thông tin chưa hợp lệ — vui lòng kiểm tra lại.'],
        'avatar_invalid'  => ['error', 'Ảnh không hợp lệ — chỉ nhận JPG/PNG/WEBP, tối đa 5MB.'],
    ];

    $key = isset($_GET['vs_msg']) ? sanitize_key(wp_unslash($_GET['vs_msg'])) : '';

    // Hiện rõ LÝ DO CỤ THỂ đọc file thất bại (thay vì 1 câu chung chung
    // cho mọi trường hợp) — giúp trưởng đoàn (và cả việc chẩn đoán sau
    // này) biết chính xác cần sửa gì.
    if ($key === 'import_error') {
        $chi_tiet = isset($_GET['chi_tiet']) ? sanitize_text_field(wp_unslash(rawurldecode($_GET['chi_tiet']))) : '';
        $text = 'Không đọc được file.';
        if ($chi_tiet !== '') {
            $text .= ' Chi tiết: ' . $chi_tiet;
        } else {
            $text .= ' Hãy chắc đây là file .xlsx hợp lệ (xuất từ Excel/Google Sheets/LibreOffice).';
        }
        printf(
            '<div class="tournament_register_page__notice tournament_register_page__notice--error">%s</div>',
            esc_html($text)
        );
        return;
    }

    // Kết quả nhập Excel — số liệu động (bao nhiêu thêm được/bỏ qua),
    // không cố định như các thông báo khác nên xử lý riêng.
    if ($key === 'import_done') {
        $added = isset($_GET['added']) ? absint($_GET['added']) : 0;
        $updated = isset($_GET['updated']) ? absint($_GET['updated']) : 0;
        $skipped = isset($_GET['skipped']) ? absint($_GET['skipped']) : 0;

        $text = sprintf('Đã thêm %d VĐV mới từ file Excel.', $added);
        if ($updated > 0) {
            $text .= sprintf(
                ' Cập nhật lại thông tin cho %d VĐV đã có sẵn (trùng họ tên + năm sinh với người trong đội).',
                $updated
            );
        }
        if ($skipped > 0) {
            $text .= sprintf(
                ' Bỏ qua %d dòng không hợp lệ (thiếu thông tin, sai định dạng năm sinh/giới tính/nhóm tuổi).',
                $skipped
            );
        }

        printf(
            '<div class="tournament_register_page__notice tournament_register_page__notice--%s">%s</div>',
            ($added > 0 || $updated > 0) ? 'success' : 'error',
            esc_html($text)
        );

        // Tên nội dung trong file KHÔNG KHỚP tuyệt đối với danh sách đã
        // cấu hình — VĐV vẫn được thêm, chỉ THIẾU đúng phần nội dung
        // này. Hiện rõ TÊN NÀO để trưởng đoàn tự sửa lại trong file hoặc
        // chọn lại đúng ở màn "Sửa VĐV" — không tự ý "đoán" khớp gần
        // đúng vì rủi ro gán nhầm.
        $unmatched = get_transient('vs_import_unmatched_' . get_current_user_id());
        if (!empty($unmatched)) {
            delete_transient('vs_import_unmatched_' . get_current_user_id());
            echo '<div class="tournament_register_page__notice tournament_register_page__notice--error">';
            echo 'Những nội dung sau trong file KHÔNG khớp với danh sách đã cấu hình (VĐV vẫn được thêm, chỉ thiếu đúng nội dung này — vào "Sửa VĐV" để chọn lại, hoặc sửa đúng tên trong file rồi nhập lại):';
            echo '<ul style="margin: 8px 0 0 18px;">';
            foreach ($unmatched as $nd) {
                echo '<li>' . esc_html($nd) . '</li>';
            }
            echo '</ul></div>';
        }

        return;
    }

    if ($key === 'staff_bulk_saved') {
        $count = isset($_GET['count']) ? absint($_GET['count']) : 0;
        printf(
            '<div class="tournament_register_page__notice tournament_register_page__notice--success">%s</div>',
            esc_html(sprintf('Đã thêm %d thành viên ban huấn luyện.', $count))
        );
        return;
    }

    if ($key === 'staff_leader_exists') {
        printf(
            '<div class="tournament_register_page__notice tournament_register_page__notice--error">%s</div>',
            esc_html('Mỗi đội chỉ được có 1 Trưởng đoàn. Vui lòng kiểm tra lại vai trò đã chọn.')
        );
        return;
    }

    if ($key === 'athletes_bulk_deleted') {
        $count = isset($_GET['count']) ? absint($_GET['count']) : 0;
        printf(
            '<div class="tournament_register_page__notice tournament_register_page__notice--%s">%s</div>',
            $count > 0 ? 'success' : 'error',
            $count > 0 ? esc_html(sprintf('Đã xoá %d VĐV.', $count)) : 'Không xoá được VĐV nào — vui lòng thử lại.'
        );
        return;
    }

    if (!isset($msg_map[$key])) {
        return;
    }

    [$type, $text] = $msg_map[$key];
    printf(
        '<div class="tournament_register_page__notice tournament_register_page__notice--%s">%s</div>',
        esc_attr($type),
        esc_html($text)
    );
}

function vs_render_team_portal_dashboard($registration) {
    global $wpdb;

    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;
    $athletes = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$ath_table} WHERE registration_id = %d ORDER BY id ASC", $registration->id)
    );

    $portal_base = home_url('/quan-ly-doi/');
    $team_logo_url = !empty($registration->logo_id)
        ? wp_get_attachment_url((int) $registration->logo_id)
        : false;

    ?>
<div class="team_portal_page__head">
    <div>
        <p class="tournament_register_page__eyebrow">Quản lý đội</p>
        <h1 class="tournament_register_page__title"><?php echo esc_html($registration->ten_don_vi); ?></h1>
    </div>
    <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
        href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">
        Đăng xuất
    </a>
</div>

<?php vs_team_portal_notice(); ?>

<div class="tournament_register_page__card">
    <div class="team_portal_page__section_head">
        <h2>Thông tin đội</h2>
        <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
            href="<?php echo esc_url(add_query_arg('view', 'edit_team', $portal_base)); ?>">
            Sửa thông tin
        </a>
    </div>
    <table class="team_portal_page__info_table">
        <tbody>
            <tr class="team_portal_page__info_logo_row">
                <th>Logo đơn vị / đội</th>
                <td>
                    <div class="team_portal_page__team_logo">
                        <?php if ($team_logo_url) : ?>
                        <img src="<?php echo esc_url($team_logo_url); ?>"
                            alt="<?php echo esc_attr($registration->ten_don_vi); ?>">
                        <?php else : ?>
                        <span>Chưa có logo</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Người liên hệ</th>
                <td><?php echo esc_html($registration->nguoi_lien_he); ?></td>
            </tr>
            <tr>
                <th>Số điện thoại</th>
                <td><?php echo esc_html($registration->phone); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo esc_html($registration->email); ?></td>
            </tr>
            <tr>
                <th>Ghi chú</th>
                <td><?php echo nl2br(esc_html($registration->ghi_chu ?: 'Không có')); ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="tournament_register_page__card">
    <div class="team_portal_page__section_head">
        <h2>Danh sách VĐV (<?php echo count($athletes); ?>)</h2>
        <div class="team_portal_page__section_actions">
            <button type="submit" form="vs-bulk-delete-form" id="vs-bulk-delete-btn"
                class="tournament_register_page__remove_athlete" disabled
                onclick="return confirm('Xoá ' + document.getElementById('vs-selected-count').textContent + ' VĐV đã chọn? Không thể hoàn tác.');">
                Xoá đã chọn (<span id="vs-selected-count">0</span>)
            </button>
            <a class="tournament_register_page__btn tournament_register_page__btn--primary"
                href="<?php echo esc_url(add_query_arg('view', 'add_athlete', $portal_base)); ?>">
                + Thêm VĐV
            </a>
        </div>
    </div>

    <table class="team_portal_page__athlete_table">
        <thead>
            <tr>
                <th style="width: 32px;">
                    <input type="checkbox" id="vs-select-all-athletes" aria-label="Chọn tất cả">
                </th>
                <th>Ảnh</th>
                <th>Họ tên</th>
                <th>Năm sinh</th>
                <th>Giới tính</th>
                <th>Nhóm tuổi</th>
                <th>Nội dung đăng ký</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($athletes) : ?>
            <?php foreach ($athletes as $a) : ?>
            <tr>
                <td>
                    <input type="checkbox" class="vs-athlete-checkbox" value="<?php echo esc_attr($a->id); ?>"
                        aria-label="Chọn <?php echo esc_attr($a->ho_ten); ?>">
                </td>
                <td>
                    <?php $avatar_url = !empty($a->anh_dai_dien_id) ? wp_get_attachment_image_url($a->anh_dai_dien_id, 'thumbnail') : false; ?>
                    <form method="post" enctype="multipart/form-data" class="team_portal_page__avatar_form">
                        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
                        <input type="hidden" name="vs_team_portal_action" value="update_athlete_avatar">
                        <input type="hidden" name="athlete_id" value="<?php echo esc_attr($a->id); ?>">
                        <label class="team_portal_page__avatar_upload_label" title="Bấm để đổi ảnh">
                            <?php if ($avatar_url) : ?>
                            <img src="<?php echo esc_url($avatar_url); ?>" alt=""
                                class="team_portal_page__avatar_thumb">
                            <?php else : ?>
                            <span class="team_portal_page__avatar_placeholder" aria-hidden="true">+</span>
                            <?php endif; ?>
                            <input type="file" name="anh_dai_dien" accept="image/jpeg,image/png,image/webp"
                                class="team_portal_page__avatar_upload_input">
                        </label>
                    </form>
                </td>
                <td><?php echo esc_html($a->ho_ten); ?></td>
                <td><?php echo (int) $a->nam_sinh; ?></td>
                <td><?php echo esc_html($a->gioi_tinh); ?></td>
                <td><?php echo esc_html(vs_get_tournament_age_group_label($a->nhom_tuoi)); ?></td>
                <td><?php echo esc_html($a->noi_dung ?: '—'); ?></td>
                <td>
                    <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
                        href="<?php echo esc_url(add_query_arg(['view' => 'edit_athlete', 'athlete_id' => absint($a->id)], $portal_base)); ?>">
                        Sửa
                    </a>
                    <form method="post" style="display:inline-block;"
                        onsubmit="return confirm('Xoá VĐV &quot;<?php echo esc_js($a->ho_ten); ?>&quot;? Không thể hoàn tác.');">
                        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
                        <input type="hidden" name="vs_team_portal_action" value="delete_athlete">
                        <input type="hidden" name="athlete_id" value="<?php echo esc_attr($a->id); ?>">
                        <button type="submit" class="tournament_register_page__remove_athlete">Xoá</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php else : ?>
            <tr>
                <td colspan="8">Chưa có VĐV nào — bấm "+ Thêm VĐV" ở trên để bắt đầu.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <form method="post" id="vs-bulk-delete-form">
        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
        <input type="hidden" name="vs_team_portal_action" value="delete_athletes_bulk">
    </form>
</div>

<?php
    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;
    $staff_list = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$staff_table} WHERE registration_id = %d ORDER BY id ASC", $registration->id)
    );
?>
<div class="tournament_register_page__card">
    <div class="team_portal_page__section_head">
        <h2>Trưởng đoàn &amp; Huấn luyện viên (<?php echo count($staff_list); ?>)</h2>
        <div class="team_portal_page__section_actions">
            <a class="tournament_register_page__btn tournament_register_page__btn--primary"
                href="<?php echo esc_url(add_query_arg('view', 'add_staff', $portal_base)); ?>">
                + Thêm người
            </a>
        </div>
    </div>

    <table class="team_portal_page__athlete_table">
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Họ tên</th>
                <th>Vai trò</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($staff_list) : ?>
            <?php foreach ($staff_list as $s) : ?>
            <tr>
                <td>
                    <?php $avatar_url = !empty($s->anh_dai_dien_id) ? wp_get_attachment_image_url($s->anh_dai_dien_id, 'thumbnail') : false; ?>
                    <form method="post" enctype="multipart/form-data" class="team_portal_page__avatar_form">
                        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
                        <input type="hidden" name="vs_team_portal_action" value="update_staff_avatar">
                        <input type="hidden" name="staff_id" value="<?php echo esc_attr($s->id); ?>">
                        <label class="team_portal_page__avatar_upload_label" title="Bấm để đổi ảnh">
                            <?php if ($avatar_url) : ?>
                            <img src="<?php echo esc_url($avatar_url); ?>" alt=""
                                class="team_portal_page__avatar_thumb">
                            <?php else : ?>
                            <span class="team_portal_page__avatar_placeholder" aria-hidden="true">+</span>
                            <?php endif; ?>
                            <input type="file" name="anh_dai_dien" accept="image/jpeg,image/png,image/webp"
                                class="team_portal_page__avatar_upload_input">
                        </label>
                    </form>
                </td>
                <td><?php echo esc_html($s->ho_ten); ?></td>
                <td><?php echo esc_html(vs_staff_role_label($s->vai_tro)); ?></td>
                <td>
                    <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
                        href="<?php echo esc_url(add_query_arg(['view' => 'edit_staff', 'staff_id' => absint($s->id)], $portal_base)); ?>">
                        Sửa
                    </a>
                    <form method="post" style="display:inline-block;"
                        onsubmit="return confirm('Xoá &quot;<?php echo esc_js($s->ho_ten); ?>&quot;? Không thể hoàn tác.');">
                        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
                        <input type="hidden" name="vs_team_portal_action" value="delete_staff">
                        <input type="hidden" name="staff_id" value="<?php echo esc_attr($s->id); ?>">
                        <button type="submit" class="tournament_register_page__remove_athlete">Xoá</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php else : ?>
            <tr>
                <td colspan="4">Chưa có trưởng đoàn/HLV nào — bấm "+ Thêm người" ở trên để bắt đầu.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
}

/**
 * Thêm NHIỀU thành viên Ban huấn luyện trong một lần gửi.
 * Trưởng đoàn chỉ được có tối đa 1 người trong mỗi đội; HLV có thể nhiều người.
 */
function vs_render_team_portal_add_staffs_form($registration) {
    global $wpdb;

    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;
    $has_leader = (bool) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$staff_table} WHERE registration_id = %d AND vai_tro = %s",
            $registration->id,
            'truong_doan'
        )
    );
    ?>
<p>
    <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
        href="<?php echo esc_url(home_url('/quan-ly-doi/')); ?>">
        ← Quay lại
    </a>
</p>

<div class="tournament_register_page__card vs-staff-bulk-card">
    <div class="vs-athlete-block-head vs-staff-bulk-head">
        <div class="vs-athlete-block-head__title">
            <span class="vs-athlete-block-head__icon" aria-hidden="true">✚</span>
            <div>
                <h2>Đăng ký ban huấn luyện</h2>
                <p>Thêm nhiều thành viên cùng lúc. Mỗi đội chỉ được có 1 Trưởng đoàn.</p>
            </div>
        </div>

        <button type="button"
            class="tournament_register_page__btn tournament_register_page__btn--ghost"
            id="tournament-add-staff">
            + Thêm người
        </button>
    </div>

    <?php if ($has_leader) : ?>
    <div class="vs-staff-bulk-card__leader-note" data-existing-team-leader="1">
        Đội đã có <strong>Trưởng đoàn</strong>. Các dòng mới chỉ có thể đăng ký Huấn luyện viên.
    </div>
    <?php else : ?>
    <div data-existing-team-leader="0" hidden></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="vs-staff-bulk-form">
        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
        <input type="hidden" name="vs_team_portal_action" value="add_staff_bulk">

        <div id="tournament-staff-rows" class="vs-staff-bulk-rows">
            <?php vs_render_bulk_staff_row(0, $has_leader); ?>
        </div>

        <div class="vs-staff-form__actions vs-staff-bulk-actions">
            <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
                href="<?php echo esc_url(home_url('/quan-ly-doi/')); ?>">
                Huỷ
            </a>
            <button type="submit" class="tournament_register_page__btn tournament_register_page__btn--primary">
                Thêm ban huấn luyện
            </button>
        </div>
    </form>
</div>

<template id="tournament-staff-row-template">
    <?php vs_render_bulk_staff_row('__INDEX__', $has_leader); ?>
</template>
<?php
}

/**
 * Một khối nhân sự trong form thêm nhiều Ban huấn luyện.
 */
function vs_render_bulk_staff_row($index, $has_leader = false) {
    $stt = is_numeric($index) ? ((int) $index + 1) : 1;
    ?>
<div class="vs-staff-bulk-row" data-staff-row>
    <div class="vs-staff-bulk-row__head">
        <strong>Nhân sự #<span data-staff-no><?php echo esc_html((string) $stt); ?></span></strong>
        <button type="button" class="tournament_register_page__remove_athlete" data-remove-staff
            aria-label="Xoá nhân sự này">
            Xoá
        </button>
    </div>

    <div class="vs-staff-bulk-row__body">
        <div class="vs-staff-bulk-row__media">
            <div class="vs-staff-avatar-box">
                <div class="vs-staff-avatar-box__preview" data-bulk-staff-avatar-preview>
                    <span class="vs-staff-avatar-box__placeholder" aria-hidden="true">👤</span>
                </div>

                <label class="vs-staff-avatar-box__button">
                    <input type="file"
                        name="<?php echo esc_attr('staff_avatar[' . $index . ']'); ?>"
                        accept="image/jpeg,image/png,image/webp"
                        data-bulk-staff-avatar-input>
                    <span>Chọn ảnh</span>
                </label>

                <span class="tournament_register_page__field_hint vs-staff-avatar-box__hint">
                    Không bắt buộc — JPG/PNG/WEBP, tối đa 5MB.
                </span>
            </div>
        </div>

        <div class="vs-staff-bulk-row__content">
            <div class="vs-staff-bulk-row__section-head">
                <h3>Thông tin nhân sự</h3>
                <p>Nhập họ tên và chọn đúng vai trò trong đoàn.</p>
            </div>

            <div class="vs-staff-bulk-row__fields">
                <div class="tournament_register_page__field">
                    <label>Họ tên <span aria-hidden="true">*</span></label>
                    <input type="text"
                        name="<?php echo esc_attr('staff[' . $index . '][ho_ten]'); ?>"
                        placeholder="Nhập họ và tên"
                        required aria-required="true">
                </div>

                <div class="tournament_register_page__field">
                    <label>Vai trò <span aria-hidden="true">*</span></label>
                    <select name="<?php echo esc_attr('staff[' . $index . '][vai_tro]'); ?>"
                        required aria-required="true" data-staff-role-select>
                        <option value="">Chọn vai trò</option>
                        <?php foreach (VS_TOURNAMENT_STAFF_ROLES as $vt) : ?>
                        <option value="<?php echo esc_attr($vt); ?>"
                            <?php disabled($has_leader && $vt === 'truong_doan'); ?>>
                            <?php echo esc_html(vs_staff_role_label($vt)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="vs-staff-form__note">
                <strong>Lưu ý:</strong>
                Trưởng đoàn chỉ được khai báo 1 người; Huấn luyện viên có thể thêm nhiều người.
            </div>
        </div>
    </div>
</div>
<?php
}

/**
 * Thêm mới (staff_id = 0) hoặc sửa (staff_id > 0) 1 Trưởng đoàn/HLV —
 * dùng chung 1 hàm vì form giống hệt nhau, chỉ khác có sẵn dữ liệu hay
 * không. Khác VĐV (tách hẳn 2 hàm add/edit) vì form này đơn giản hơn
 * nhiều (không có nhóm tuổi/nội dung phụ thuộc lẫn nhau), không cần
 * tách riêng cho dễ đọc.
 */
function vs_render_team_portal_staff_form($registration, $staff_id = 0) {
    global $wpdb;

    $staff_table = $wpdb->prefix . VS_TOURNAMENT_STAFF_TABLE;
    $staff = null;

    if ($staff_id > 0) {
        // registration_id = đúng đội của tôi trong điều kiện tìm — không
        // cho xem/sửa được người của đội khác dù biết staff_id.
        $staff = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$staff_table} WHERE id = %d AND registration_id = %d",
                $staff_id,
                $registration->id
            )
        );

        if (!$staff) {
            echo '<p>Không tìm thấy người này trong đội của bạn.</p>';
            echo '<p><a class="tournament_register_page__btn tournament_register_page__btn--ghost" href="' . esc_url(home_url('/quan-ly-doi/')) . '">← Quay lại</a></p>';
            return;
        }
    }

    $current_avatar_url = !empty($staff->anh_dai_dien_id)
        ? wp_get_attachment_image_url($staff->anh_dai_dien_id, 'medium')
        : false;

    ?>
<p>
    <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
        href="<?php echo esc_url(home_url('/quan-ly-doi/')); ?>">
        ← Quay lại
    </a>
</p>

<div class="tournament_register_page__card vs-staff-form-card">
    <div class="vs-staff-form-card__head">
        <div class="vs-staff-form-card__title">
            <span class="vs-staff-form-card__icon" aria-hidden="true">✚</span>
            <div>
                <h2><?php echo $staff ? 'Sửa thông tin nhân sự' : 'Đăng ký ban huấn luyện'; ?></h2>
                <p>
                    <?php echo $staff
                        ? 'Cập nhật họ tên, vai trò hoặc ảnh đại diện của thành viên.'
                        : 'Thêm Trưởng đoàn / Huấn luyện viên tham gia cùng đơn vị.'; ?>
                </p>
            </div>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="vs-staff-form">
        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
        <input type="hidden" name="vs_team_portal_action" value="save_staff">
        <input type="hidden" name="staff_id" value="<?php echo esc_attr($staff->id ?? 0); ?>">

        <div class="vs-staff-form__panel">
            <div class="vs-staff-form__media">
                <div class="vs-staff-avatar-box">
                    <div class="vs-staff-avatar-box__preview" data-staff-avatar-preview>
                        <?php if ($current_avatar_url) : ?>
                        <img src="<?php echo esc_url($current_avatar_url); ?>" alt=""
                            class="vs-staff-avatar-box__img">
                        <?php else : ?>
                        <span class="vs-staff-avatar-box__placeholder" aria-hidden="true">👤</span>
                        <?php endif; ?>
                    </div>

                    <label class="vs-staff-avatar-box__button">
                        <input type="file" name="anh_dai_dien" accept="image/jpeg,image/png,image/webp"
                            data-staff-avatar-input>
                        <span><?php echo $staff ? 'Đổi ảnh' : 'Chọn ảnh'; ?></span>
                    </label>

                    <span class="tournament_register_page__field_hint vs-staff-avatar-box__hint">
                        <?php echo $staff ? 'Để trống nếu không muốn đổi ảnh. ' : 'Không bắt buộc — '; ?>
                        JPG/PNG/WEBP, tối đa 5MB.
                    </span>
                </div>
            </div>

            <div class="vs-staff-form__content">
                <div class="vs-staff-form__section-head">
                    <h3>Thông tin nhân sự</h3>
                    <p>Nhập đúng thông tin thành viên tham gia giải đấu.</p>
                </div>

                <div class="vs-staff-form__fields">
                    <div class="tournament_register_page__field vs-staff-form__field--full">
                        <label>Họ tên <span aria-hidden="true">*</span></label>
                        <input type="text" name="ho_ten" value="<?php echo esc_attr($staff->ho_ten ?? ''); ?>"
                            placeholder="Nhập họ và tên" required aria-required="true">
                    </div>

                    <div class="tournament_register_page__field vs-staff-form__field--full">
                        <label>Vai trò <span aria-hidden="true">*</span></label>
                        <select name="vai_tro" required aria-required="true">
                            <option value="">Chọn vai trò</option>
                            <?php foreach (VS_TOURNAMENT_STAFF_ROLES as $vt) : ?>
                            <option value="<?php echo esc_attr($vt); ?>" <?php selected($staff->vai_tro ?? '', $vt); ?>>
                                <?php echo esc_html(vs_staff_role_label($vt)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="vs-staff-form__note">
                    <strong>Lưu ý:</strong>
                    Mỗi người chỉ nên được khai báo một lần với đúng vai trò trong đoàn.
                </div>
            </div>
        </div>

        <div class="vs-staff-form__actions">
            <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
                href="<?php echo esc_url(home_url('/quan-ly-doi/')); ?>">
                Huỷ
            </a>
            <button type="submit" class="tournament_register_page__btn tournament_register_page__btn--primary">
                <?php echo $staff ? 'Lưu thay đổi' : 'Thêm nhân sự'; ?>
            </button>
        </div>
    </form>
</div>

<script>
(function() {
    var input = document.querySelector('[data-staff-avatar-input]');
    var preview = document.querySelector('[data-staff-avatar-preview]');

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function() {
        var file = this.files && this.files[0];
        if (!file || !file.type.match(/^image\//)) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function(event) {
            preview.innerHTML = '';
            var img = document.createElement('img');
            img.src = event.target.result;
            img.alt = '';
            img.className = 'vs-staff-avatar-box__img';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
})();
</script>
<?php
}

function vs_render_team_portal_team_form($registration) {
    $team_logo_url = !empty($registration->logo_id)
        ? wp_get_attachment_url((int) $registration->logo_id)
        : false;
    ?>
<p>
    <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
        href="<?php echo esc_url(home_url('/quan-ly-doi/')); ?>">
        ← Quay lại
    </a>
</p>
<div class="tournament_register_page__card" style="max-width: 640px;">
    <h2>Sửa thông tin đội</h2>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
        <input type="hidden" name="vs_team_portal_action" value="update_team">

        <div class="tournament_register_page__grid">
            <div class="tournament_register_page__field tournament_register_page__field--full team_portal_page__team_logo_field">
                <label>Logo đơn vị / đội</label>
                <div class="team_portal_page__team_logo_edit">
                    <div class="team_portal_page__team_logo team_portal_page__team_logo--edit" data-team-logo-preview>
                        <?php if ($team_logo_url) : ?>
                        <img src="<?php echo esc_url($team_logo_url); ?>"
                            alt="<?php echo esc_attr($registration->ten_don_vi); ?>">
                        <?php else : ?>
                        <span>Chưa có logo</span>
                        <?php endif; ?>
                    </div>
                    <div class="team_portal_page__team_logo_upload">
                        <label class="team_portal_page__team_logo_picker">
                            <input type="file" name="logo_don_vi"
                                accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                data-team-logo-input>
                            <span><?php echo $team_logo_url ? 'Đổi logo' : 'Chọn logo'; ?></span>
                        </label>
                        <span class="tournament_register_page__field_hint">
                            PNG/JPG/WEBP/SVG. Chọn ảnh mới sẽ xem trước ngay tại đây.
                        </span>
                    </div>
                </div>
            </div>

            <div class="tournament_register_page__field">
                <label>Tên đơn vị / đoàn <span aria-hidden="true">*</span></label>
                <input type="text" name="ten_don_vi" value="<?php echo esc_attr($registration->ten_don_vi); ?>"
                    required>
            </div>
            <div class="tournament_register_page__field">
                <label>Người liên hệ <span aria-hidden="true">*</span></label>
                <input type="text" name="nguoi_lien_he" value="<?php echo esc_attr($registration->nguoi_lien_he); ?>"
                    required>
            </div>
            <div class="tournament_register_page__field">
                <label>Số điện thoại <span aria-hidden="true">*</span></label>
                <input type="tel" name="phone" value="<?php echo esc_attr($registration->phone); ?>" required>
            </div>
            <div class="tournament_register_page__field tournament_register_page__field--full">
                <label>Ghi chú</label>
                <textarea name="ghi_chu" rows="3"><?php echo esc_textarea($registration->ghi_chu); ?></textarea>
            </div>
        </div>

        <p class="tournament_register_page__hint" style="margin-top: 14px;">
            Email đăng nhập không đổi được ở đây — liên hệ Ban tổ chức nếu cần đổi email.
        </p>

        <div class="tournament_register_page__form_actions" style="margin-top: 18px;">
            <button type="submit" class="tournament_register_page__btn tournament_register_page__btn--primary">
                Lưu thay đổi
            </button>
        </div>
    </form>
</div>
<script>
(function() {
    var input = document.querySelector('[data-team-logo-input]');
    var preview = document.querySelector('[data-team-logo-preview]');

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function() {
        var file = this.files && this.files[0];

        if (!file) {
            return;
        }

        var allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
        if (allowed.indexOf(file.type) === -1) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function(event) {
            preview.innerHTML = '';

            var img = document.createElement('img');
            img.src = event.target.result;
            img.alt = '';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
})();
</script>
<?php
}

/**
 * Thêm NHIỀU VĐV cùng lúc — 1 form, nhiều dòng, thêm/bớt dòng bằng JS
 * (assets/js/tournament-register.js, cùng cơ chế đã dùng ở form đăng ký
 * cũ). Nộp lên đều đi qua action riêng "add_athletes_bulk", khác với sửa
 * 1 VĐV (action "save_athlete") ở hàm vs_render_team_portal_athlete_form.
 */
function vs_render_team_portal_add_athletes_form($registration) {
    $events_by_group = vs_get_tournament_events_by_nhom_tuoi();
    $gioi_tinh_options = ['Nam', 'Nữ'];
    $group_labels = vs_get_tournament_age_groups();
    $group_labels['hon_hop'] = 'Hỗn hợp';
    $template_url = get_option('vs_tournament_athlete_template_url', '');

    ?>
<p>
    <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
        href="<?php echo esc_url(home_url('/quan-ly-doi/')); ?>">
        ← Quay lại
    </a>
</p>

<div class="tournament_register_page__card vs-athlete-import-card">
    <div class="vs-athlete-block-head vs-athlete-block-head--import">
        <div class="vs-athlete-block-head__title">
            <span class="vs-athlete-block-head__icon" aria-hidden="true">⇪</span>
            <div>
                <h2>Nhập nhanh từ Excel</h2>
                <p>Tải file mẫu và import danh sách vận động viên từ Excel.</p>
            </div>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="vs-athlete-import-form">
        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
        <input type="hidden" name="vs_team_portal_action" value="import_athletes_excel">

        <div class="vs-athlete-import-form__layout">
            <label class="vs-athlete-dropzone" for="vs-excel-file-input">
                <input id="vs-excel-file-input" type="file" name="excel_file" accept=".xlsx" required aria-required="true"
                    data-excel-file-input>
                <span class="vs-athlete-dropzone__icon" aria-hidden="true">⇪</span>
                <span class="vs-athlete-dropzone__content">
                    <span class="vs-athlete-dropzone__title">Kéo thả file Excel vào đây hoặc <strong>chọn tệp</strong></span>
                    <span class="vs-athlete-dropzone__subtitle">Hỗ trợ .xlsx</span>
                    <span class="vs-athlete-dropzone__filename" data-excel-file-name>Chưa có tệp nào được chọn</span>
                </span>
            </label>

            <div class="vs-athlete-import-form__actions">
                <?php if ($template_url) : ?>
                <a class="tournament_register_page__btn tournament_register_page__btn--ghost vs-athlete-action-btn"
                    href="<?php echo esc_url($template_url); ?>" download>
                    Tải file mẫu
                </a>
                <?php endif; ?>

                <button type="submit"
                    class="tournament_register_page__btn tournament_register_page__btn--primary vs-athlete-action-btn">
                    Nhập từ Excel
                </button>
            </div>
        </div>
    </form>

    <details class="vs-athlete-valid-details">
        <summary>Xem danh sách nội dung hợp lệ (để gõ đúng cột "Nội dung")</summary>
        <div class="vs-athlete-valid-details__body">
            <p class="tournament_register_page__field_hint" style="margin-bottom: 14px;">
                Chỉ điền được chữ (họ tên, năm sinh, giới tính, nhóm tuổi, nội dung), ảnh vẫn thêm riêng từng người sau.
                Cột "Nội dung" phải gõ đúng tên nội dung, nhiều nội dung cách nhau bằng dấu chấm phẩy (;).
            </p>
            <?php foreach (vs_get_tournament_event_groups() as $group) : ?>
            <?php if (!empty($events_by_group[$group])) : ?>
            <div class="vs-athlete-valid-details__group">
                <p><?php echo esc_html($group_labels[$group]); ?></p>
                <ul>
                    <?php foreach ($events_by_group[$group] as $ev) : ?>
                    <li><?php echo esc_html($ev); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </details>
</div>

<div class="tournament_register_page__card vs-athlete-bulk-card">
    <div class="vs-athlete-block-head vs-athlete-block-head--manual">
        <div class="vs-athlete-block-head__title">
            <span class="vs-athlete-block-head__icon" aria-hidden="true">✚</span>
            <div>
                <h2>Đăng ký từng vận động viên</h2>
                <p>Thêm nhanh nhiều VĐV, mỗi dòng là một hồ sơ dự thi.</p>
            </div>
        </div>

        <button type="button" class="tournament_register_page__btn tournament_register_page__btn--ghost"
            id="tournament-add-athlete">
            + Thêm vận động viên
        </button>
    </div>

    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
        <input type="hidden" name="vs_team_portal_action" value="add_athletes_bulk">

        <div id="tournament-athlete-rows" class="vs-athlete-rows">
            <?php vs_render_bulk_athlete_row(0, $gioi_tinh_options, $events_by_group); ?>
        </div>

        <div class="tournament_register_page__form_actions vs-athlete-submit_actions" style="margin-top: 18px;">
            <button type="submit" class="tournament_register_page__btn tournament_register_page__btn--primary">
                Thêm VĐV
            </button>
        </div>
    </form>
</div>

<template id="tournament-athlete-row-template">
    <?php vs_render_bulk_athlete_row('__INDEX__', $gioi_tinh_options, $events_by_group); ?>
</template>
<?php
}

/**
 * 1 dòng VĐV trong form thêm-nhiều-VĐV — $index là số nguyên (dòng thật)
 * hoặc chuỗi "__INDEX__" (trong thẻ template, JS tự thay số khi nhân bản).
 * Cấu trúc field athlete[INDEX][...] khớp đúng cách vs_handle_team_portal_
 * actions() đọc $_POST['athlete'] lúc xử lý add_athletes_bulk.
 */
function vs_render_bulk_athlete_row($index, array $gioi_tinh_options, array $events_by_group) {
    $stt = is_numeric($index) ? ((int) $index + 1) : 1;
    ?>
<div class="tournament_register_page__athlete_row vs-athlete-row" data-athlete-row>
    <div class="tournament_register_page__athlete_row_head vs-athlete-row__head">
        <strong>VĐV #<span data-athlete-no><?php echo esc_html((string) $stt); ?></span></strong>
        <button type="button" class="tournament_register_page__remove_athlete" data-remove-athlete
            aria-label="Xoá dòng này">
            Xoá
        </button>
    </div>

    <div class="vs-athlete-row__body">
        <div class="vs-athlete-row__media">
            <div class="vs-athlete-avatar-box">
                <div class="vs-athlete-avatar-box__preview" data-avatar-preview>
                    <span class="vs-athlete-avatar-box__placeholder" aria-hidden="true">👤</span>
                </div>

                <label class="vs-athlete-avatar-box__button">
                    <input type="file" name="<?php echo esc_attr('athlete[' . $index . '][anh_dai_dien]'); ?>"
                        accept="image/jpeg,image/png,image/webp" data-avatar-input>
                    <span>Chọn ảnh</span>
                </label>

                <span class="tournament_register_page__field_hint vs-athlete-avatar-box__hint">
                    Không bắt buộc — JPG/PNG/WEBP, tối đa 5MB.
                </span>
            </div>
        </div>

        <div class="vs-athlete-row__content">
            <div class="vs-athlete-row__fields">
                <div class="tournament_register_page__field">
                    <label>Họ tên <span aria-hidden="true">*</span></label>
                    <input type="text" name="<?php echo esc_attr('athlete[' . $index . '][ho_ten]'); ?>" required
                        aria-required="true" placeholder="Nhập họ và tên vận động viên">
                </div>

                <div class="tournament_register_page__field">
                    <label>Năm sinh <span aria-hidden="true">*</span></label>
                    <input type="number" name="<?php echo esc_attr('athlete[' . $index . '][nam_sinh]'); ?>" min="1970"
                        max="<?php echo esc_attr(gmdate('Y')); ?>" required aria-required="true"
                        placeholder="Nhập năm sinh (ví dụ: 2010)">
                </div>

                <div class="tournament_register_page__field">
                    <label>Giới tính <span aria-hidden="true">*</span></label>
                    <select name="<?php echo esc_attr('athlete[' . $index . '][gioi_tinh]'); ?>" required aria-required="true">
                        <option value="">Chọn giới tính</option>
                        <?php foreach ($gioi_tinh_options as $g) : ?>
                        <option value="<?php echo esc_attr($g); ?>"><?php echo esc_html($g); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="tournament_register_page__field">
                    <label>Nhóm tuổi <span aria-hidden="true">*</span></label>
                    <select name="<?php echo esc_attr('athlete[' . $index . '][nhom_tuoi]'); ?>" required aria-required="true"
                        data-nhom-tuoi-select>
                        <option value="">Chọn nhóm tuổi</option>
                        <?php foreach (vs_get_tournament_age_groups() as $group_key => $group_label) : ?>
                        <option value="<?php echo esc_attr($group_key); ?>"><?php echo esc_html($group_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="tournament_register_page__field tournament_register_page__field--full vs-athlete-events-field">
                    <label>Nội dung đăng ký <span aria-hidden="true">*</span></label>
                    <p class="tournament_register_page__events_hint" data-nt-hint>
                        Chọn ít nhất một nội dung phù hợp.
                    </p>

                    <div class="tournament_register_page__events_box vs-athlete-events" data-nt-box>
                        <?php foreach (vs_get_tournament_event_groups() as $group) : ?>
                        <?php foreach (($events_by_group[$group] ?? []) as $ev) : ?>
                        <label class="tournament_register_page__event_check vs-athlete-event-chip"
                            data-nt-group="<?php echo esc_attr($group); ?>">
                            <input type="checkbox" name="<?php echo esc_attr('athlete[' . $index . '][noi_dung][]'); ?>"
                                value="<?php echo esc_attr($ev); ?>">
                            <span class="vs-athlete-event-chip__mark" aria-hidden="true"></span>
                            <span class="vs-athlete-event-chip__text"><?php echo esc_html($ev); ?></span>
                        </label>
                        <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
/**
 * Sửa ĐÚNG 1 VĐV đã có sẵn — thêm VĐV mới dùng hàm riêng
 * (vs_render_team_portal_add_athletes_form), cho phép nhiều dòng cùng lúc.
 */
function vs_render_team_portal_athlete_form($registration, $athlete_id) {
    global $wpdb;

    $ath_table = $wpdb->prefix . VS_TOURNAMENT_ATH_TABLE;
    // registration_id = đúng đội của tôi trong điều kiện tìm — không
    // cho xem/sửa được VĐV của đội khác dù biết athlete_id.
    $athlete = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$ath_table} WHERE id = %d AND registration_id = %d",
            $athlete_id,
            $registration->id
        )
    );

    if (!$athlete) {
        echo '<p>Không tìm thấy VĐV này trong đội của bạn.</p>';
        echo '<p><a class="tournament_register_page__btn tournament_register_page__btn--ghost" href="' . esc_url(home_url('/quan-ly-doi/')) . '">← Quay lại</a></p>';
        return;
    }

    $events_by_group = vs_get_tournament_events_by_nhom_tuoi();
    $selected_events = array_filter(array_map('trim', preg_split('/[,;]/', (string) $athlete->noi_dung)));
    $current_nhom_tuoi = (string) $athlete->nhom_tuoi;

    ?>
<p>
    <a class="tournament_register_page__btn tournament_register_page__btn--ghost"
        href="<?php echo esc_url(home_url('/quan-ly-doi/')); ?>">
        ← Quay lại
    </a>
</p>

<div class="tournament_register_page__card">
    <h2>Sửa VĐV</h2>

    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('vs_team_portal_' . $registration->id, 'vs_team_portal_nonce'); ?>
        <input type="hidden" name="vs_team_portal_action" value="save_athlete">
        <input type="hidden" name="athlete_id" value="<?php echo esc_attr($athlete->id); ?>">

        <div class="tournament_register_page__grid">
            <div class="tournament_register_page__field">
                <label>Họ tên <span aria-hidden="true">*</span></label>
                <input type="text" name="ho_ten" value="<?php echo esc_attr($athlete->ho_ten); ?>" required>
            </div>
            <div class="tournament_register_page__field">
                <label>Năm sinh <span aria-hidden="true">*</span></label>
                <input type="number" name="nam_sinh" min="1970" max="<?php echo esc_attr(gmdate('Y')); ?>"
                    value="<?php echo esc_attr($athlete->nam_sinh); ?>" required>
            </div>
            <div class="tournament_register_page__field">
                <label>Giới tính <span aria-hidden="true">*</span></label>
                <select name="gioi_tinh" required>
                    <option value="">Chọn giới tính</option>
                    <?php foreach (['Nam', 'Nữ'] as $g) : ?>
                    <option value="<?php echo esc_attr($g); ?>" <?php selected($athlete->gioi_tinh, $g); ?>>
                        <?php echo esc_html($g); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="tournament_register_page__field">
                <label>Nhóm tuổi <span aria-hidden="true">*</span></label>
                <select name="nhom_tuoi" required data-nhom-tuoi-select>
                    <option value="">Chọn nhóm tuổi</option>
                    <?php foreach (vs_get_tournament_age_groups() as $group_key => $group_label) : ?>
                    <option value="<?php echo esc_attr($group_key); ?>" <?php selected($current_nhom_tuoi, (string) $group_key); ?>>
                        <?php echo esc_html($group_label); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="tournament_register_page__field">
                <label>Ảnh đại diện</label>
                <?php if (!empty($athlete->anh_dai_dien_id)) : ?>
                <?php $current_avatar_url = wp_get_attachment_image_url($athlete->anh_dai_dien_id, 'thumbnail'); ?>
                <?php if ($current_avatar_url) : ?>
                <img src="<?php echo esc_url($current_avatar_url); ?>" alt="" class="team_portal_page__avatar_preview">
                <?php endif; ?>
                <?php endif; ?>
                <input type="file" name="anh_dai_dien" accept="image/jpeg,image/png,image/webp">
                <span class="tournament_register_page__field_hint">
                    Không bắt buộc — chọn ảnh mới để thay, để trống thì giữ nguyên ảnh cũ. JPG/PNG/WEBP, tối đa 5MB.
                </span>
            </div>

            <div class="tournament_register_page__field tournament_register_page__field--full">
                <label>Nội dung đăng ký <span aria-hidden="true">*</span></label>
                <p class="tournament_register_page__events_hint" data-nt-hint>
                    Chọn nhóm tuổi ở trên để hiện đúng nội dung phù hợp.
                </p>
                <div class="tournament_register_page__events_box" data-nt-box>
                    <?php foreach (vs_get_tournament_event_groups() as $group) : ?>
                    <?php foreach (($events_by_group[$group] ?? []) as $ev) : ?>
                    <label class="tournament_register_page__event_check"
                        data-nt-group="<?php echo esc_attr($group); ?>">
                        <input type="checkbox" name="noi_dung[]" value="<?php echo esc_attr($ev); ?>"
                            <?php checked(in_array($ev, $selected_events, true)); ?>>
                        <?php echo esc_html($ev); ?>
                    </label>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="tournament_register_page__form_actions" style="margin-top: 18px;">
            <button type="submit" class="tournament_register_page__btn tournament_register_page__btn--primary">
                Lưu thay đổi
            </button>
        </div>
    </form>
</div>

<script>
(function() {
    var box = document.querySelector('[data-nt-box]');
    var select = document.querySelector('[data-nhom-tuoi-select]');
    var hint = document.querySelector('[data-nt-hint]');
    if (!box || !select) return;

    function filterEvents() {
        var selected = select.value;
        box.querySelectorAll('[data-nt-group]').forEach(function(label) {
            var group = label.getAttribute('data-nt-group');
            var show = group === 'hon_hop' || (selected && group === selected);
            label.style.display = show ? '' : 'none';
            if (!show) {
                var cb = label.querySelector('input[type="checkbox"]');
                if (cb) cb.checked = false;
            }
        });
        if (hint) hint.style.display = selected ? 'none' : '';
    }

    select.addEventListener('change', filterEvents);
    filterEvents();
})();
</script>
<?php
}
