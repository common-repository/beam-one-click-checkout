<?php
/**
Plugin Name:  Beam One-Click Checkout
Description:  Beam Checkout button for smoother checkout experience
Version:      1.3.3
Author:       Beam Checkout
Author URI:   https://www.beamcheckout.com/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

include_once('admin/payment.php');

include_once('includes/checkout-button.php');
include_once('includes/utils.php');

// ------------------------ admin/payment.php ----------------------------------
add_filter('woocommerce_payment_gateways', 'beam_checkout_add_gateway_class');
add_action('plugins_loaded', 'beam_checkout_init_gateway_class');

// ------------------------ includes/checkout-button.php -----------------------
add_action('plugins_loaded', 'beam_checkout_init_buttons');

// ------------------------ includes/utils.php ---------------------------------
// Add a link to Beam setting in the plugin page
add_filter('plugin_action_links', 'get_beam_checkout_setting_url', 10, 2);
// Removing Beam from payment gateway list in checkout page (because we already display a checkout button on the top)
add_filter('woocommerce_available_payment_gateways', 'remove_beam_from_default_gateway');

register_uninstall_hook(__FILE__, 'beam_checkout_cleanup');
