<?php
function is_beam_checkout_enabled() {
    return sanitize_text_field(WC()->payment_gateways->payment_gateways()['beam-checkout']->enabled) === 'yes';
}

function get_beam_checkout_config($key, $default = null) {
    $beam_settings = WC()->payment_gateways->payment_gateways()['beam-checkout']->settings;
    if (isset($beam_settings[$key])) {
        return sanitize_text_field($beam_settings[$key]);
    }
    return $default;
}

function is_beam_checkout_config_enabled($key) {
    return get_beam_checkout_config($key) === 'yes';
}

function get_beam_checkout_setting_url($links) {
    $settings_link =
        "<a href='" .
        get_bloginfo('wpurl') .
        '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=beam-checkout' .
        "'>" .
        __('Settings') .
        '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

function beam_checkout_sanitize_class_name_from_atts($atts) {
    if (array_key_exists('class_name', $atts)) {
        return sanitize_html_class($atts['class_name']);
    } else {
        return '';
    }
}

function beam_checkout_cleanup() {
    delete_option('beam_checkout_merchant_config');
}

function remove_beam_from_default_gateway($available_gateways) {
    unset($available_gateways['beam-checkout']);
    return $available_gateways;
}
