<?php 
// Đăng ký giải đấu Vovinam — tách thành nhiều file theo nhóm chức năng
// (trước là 1 file duy nhất ~2600 dòng, rất khó dò lỗi cú pháp khi có
// vấn đề — nay mỗi file chỉ còn vài trăm dòng, dễ định vị hơn nhiều).
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-schema.php';
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-xlsx.php';
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-settings.php';
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-avatar.php';
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-register-form.php';
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-portal-auth.php';
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-portal-actions.php';
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-portal-render.php';
require_once get_template_directory() . '/inc/vector_sport/tournament/tournament-admin.php';
?>