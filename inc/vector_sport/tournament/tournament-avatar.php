<?php
defined('ABSPATH') || exit;

/* =========================================================================
 * ẢNH ĐẠI DIỆN VĐV — upload qua Media Library thật của WordPress, lưu
 * attachment ID (không lưu URL trực tiếp).
 * ========================================================================= */

const VS_AVATAR_ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const VS_AVATAR_MAX_BYTES = 5 * 1024 * 1024; // 5MB

/**
 * Upload 1 ảnh đại diện — dùng media_handle_upload() thật của WordPress
 * (không tự move_uploaded_file() tay), để có sẵn thumbnail, quản lý được
 * trong Thư viện Media như ảnh bình thường khác.
 *
 * @param array $file Mảng 1 file ĐÃ CHUẨN HOÁ theo cấu trúc $_FILES gốc
 *                     (name, type, tmp_name, error, size) — với dòng
 *                     nhiều VĐV, dùng vs_normalize_bulk_athlete_files()
 *                     trước khi gọi hàm này.
 * @return int|false|null int = attachment ID mới; null = không có file
 *                         nào được chọn (bỏ qua, không phải lỗi); false =
 *                         có chọn file nhưng sai loại/quá dung lượng/lỗi.
 */
function vs_handle_athlete_avatar_upload($file) {
    if (empty($file['tmp_name']) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ((int) $file['size'] > VS_AVATAR_MAX_BYTES) {
        return false;
    }

    $file_type = wp_check_filetype($file['name']);
    if (!in_array($file_type['type'], VS_AVATAR_ALLOWED_TYPES, true)) {
        return false;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    // media_handle_upload() đọc thẳng $_FILES[$file_id] — "mượn" 1 key
    // tạm để dùng đúng hàm chuẩn của WP (tự lo validate, di chuyển file,
    // tạo attachment post, sinh thumbnail) thay vì viết lại từ đầu.
    $tmp_key = 'vs_avatar_tmp_' . uniqid();
    $_FILES[$tmp_key] = $file;

    $attachment_id = media_handle_upload($tmp_key, 0);

    unset($_FILES[$tmp_key]);

    if (is_wp_error($attachment_id)) {
        error_log('VS Avatar Upload Error: ' . $attachment_id->get_error_message());
        return false;
    }

    return (int) $attachment_id;
}

/**
 * $_FILES['athlete']['name'][INDEX]['anh_dai_dien'] — PHP "xoay ngược"
 * cấu trúc file input dạng mảng lồng nhau: 5 thuộc tính (name/type/
 * tmp_name/error/size) nằm NGOÀI CÙNG, index từng dòng nằm BÊN TRONG mỗi
 * thuộc tính, không theo cách tự nhiên "mỗi file 1 nhóm 5 thuộc tính".
 * Hàm này xoay lại cho đúng tự nhiên — trả về mảng theo index dòng, mỗi
 * phần tử là 1 file chuẩn {name, type, tmp_name, error, size}, sẵn sàng
 * truyền thẳng vào vs_handle_athlete_avatar_upload().
 */
function vs_normalize_bulk_athlete_files() {
    if (empty($_FILES['athlete']['name']) || !is_array($_FILES['athlete']['name'])) {
        return [];
    }

    $result = [];
    foreach ($_FILES['athlete']['name'] as $index => $fields) {
        if (!isset($fields['anh_dai_dien'])) {
            continue;
        }
        $result[$index] = [
            'name'     => $_FILES['athlete']['name'][$index]['anh_dai_dien'],
            'type'     => $_FILES['athlete']['type'][$index]['anh_dai_dien'],
            'tmp_name' => $_FILES['athlete']['tmp_name'][$index]['anh_dai_dien'],
            'error'    => $_FILES['athlete']['error'][$index]['anh_dai_dien'],
            'size'     => $_FILES['athlete']['size'][$index]['anh_dai_dien'],
        ];
    }

    return $result;
}
