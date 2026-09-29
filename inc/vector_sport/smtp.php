<?php
/**
 * Vector Sport SMTP Config
 */

defined('ABSPATH') || exit;

function vs_configure_smtp_mailer($phpmailer) {
    if (
        !defined('VS_SMTP_HOST') ||
        !defined('VS_SMTP_USERNAME') ||
        !defined('VS_SMTP_PASSWORD')
    ) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = VS_SMTP_HOST;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = defined('VS_SMTP_PORT') ? (int) VS_SMTP_PORT : 587;
    $phpmailer->SMTPSecure = defined('VS_SMTP_SECURE') ? VS_SMTP_SECURE : 'tls';
    $phpmailer->Username   = VS_SMTP_USERNAME;
    $phpmailer->Password   = VS_SMTP_PASSWORD;

    if (defined('VS_SMTP_FROM_EMAIL')) {
        $phpmailer->From = VS_SMTP_FROM_EMAIL;
    }

    if (defined('VS_SMTP_FROM_NAME')) {
        $phpmailer->FromName = VS_SMTP_FROM_NAME;
    }
}
add_action('phpmailer_init', 'vs_configure_smtp_mailer');