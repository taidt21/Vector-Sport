<?php
if (!defined('ABSPATH')) {
    exit;
}

// HELPERS
// =========================================================
function vs_commerce_log($message, $context = [])
{
    if (!VS_VNPAY_DEBUG) {
        return;
    }

    if (!empty($context)) {
        $message .= ' | ' . wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    error_log('[VS_COMMERCE] ' . $message);
}

function vs_commerce_to_number($value)
{
    if (is_numeric($value)) {
        return (float) $value;
    }

    $value = trim((string) $value);
    if ($value === '') {
        return 0;
    }

    $value = preg_replace('/[^\d]/', '', $value);
    return $value !== '' ? (float) $value : 0;
}

function vs_commerce_format_price($value)
{
    $number = vs_commerce_to_number($value);
    return number_format($number, 0, ',', '.') . 'đ';
}

function vs_commerce_address_mode_label($mode)
{
    return $mode === 'old' ? 'Địa chỉ trước sáp nhập' : 'Địa chỉ';
}

function vs_commerce_unsign($text)
{
    $text = remove_accents((string) $text);
    $text = preg_replace('/[^A-Za-z0-9\s\-]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}



function vs_commerce_is_valid_phone($phone)
{
    $digits = preg_replace('/\D+/', '', (string) $phone);
    return (bool) preg_match('/^(0|84)?\d{9,10}$/', $digits);
}

function vs_commerce_get_vnpay_order_amount(array $order)
{
    return (int) round(((float) ($order['grand_total'] ?? 0)) * 100);
}

function vs_commerce_get_cart_response_data($items = null)
{
    $items = is_array($items) ? array_values($items) : vs_commerce_get_cart_items();
    $totals = vs_commerce_get_cart_totals($items);

    return [
        'html' => vs_commerce_get_drawer_html(),
        'count' => (int) ($totals['count'] ?? 0),
        'subtotal_html' => (string) ($totals['subtotal_html'] ?? '0đ'),
        'grand_total_html' => (string) ($totals['subtotal_html'] ?? '0đ'),
        'checkout_cart_html' => vs_commerce_render_checkout_cart_html(),
    ];
}

function vs_commerce_normalize_gallery($gallery)
{
    $items = [];
    if (empty($gallery) || !is_array($gallery)) {
        return $items;
    }

    foreach ($gallery as $image) {
        $full  = '';
        $thumb = '';
        $alt   = '';

        if (is_array($image)) {
            $full  = $image['url'] ?? '';
            $thumb = $image['sizes']['medium'] ?? ($image['sizes']['thumbnail'] ?? $full);
            $alt   = $image['alt'] ?? '';

            if (!$full && !empty($image['ID'])) {
                $full  = wp_get_attachment_image_url((int) $image['ID'], 'full');
                $thumb = wp_get_attachment_image_url((int) $image['ID'], 'medium');
                $alt   = get_post_meta((int) $image['ID'], '_wp_attachment_image_alt', true);
            }
        } elseif (is_numeric($image)) {
            $full  = wp_get_attachment_image_url((int) $image, 'full');
            $thumb = wp_get_attachment_image_url((int) $image, 'medium');
            $alt   = get_post_meta((int) $image, '_wp_attachment_image_alt', true);
        } elseif (is_string($image)) {
            $full = $thumb = $image;
        }

        if ($full) {
            $items[] = ['full' => $full, 'thumb' => $thumb ?: $full, 'alt' => $alt];
        }
    }

    return $items;
}
