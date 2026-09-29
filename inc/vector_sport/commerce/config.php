<?php
if (!defined('ABSPATH')) {
    exit;
}

// CONFIG
// =========================================================
if (!defined('VS_VNPAY_TMN_CODE')) {
    define('VS_VNPAY_TMN_CODE', 'FT5SDMGM');
}

if (!defined('VS_VNPAY_HASH_SECRET')) {
    define('VS_VNPAY_HASH_SECRET', 'HQ22DI5PYHCT292WRUJO8EEHG821EVZ3');
}

if (!defined('VS_VNPAY_PAYMENT_URL')) {
    define('VS_VNPAY_PAYMENT_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
}

if (!defined('VS_VNPAY_DEBUG')) {
    define('VS_VNPAY_DEBUG', false);
}
//TEST INFOR
/*

Ngân hàng: NCB
Số thẻ: 9704198526191432198
Tên chủ thẻ: NGUYEN VAN A
Ngày phát hành: 07/15
Mật khẩu OTP: 123456

*/