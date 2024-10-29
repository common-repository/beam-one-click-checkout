<?php
include_once('script.php');
include_once('services.php');
include_once('style-loader.php');
include_once('utils.php');

function beam_checkout_init_buttons() {
    if (is_beam_checkout_enabled()) {
        new BeamCheckout_Services();
        new BeamCheckout_CheckoutButton();
    }
}

class BeamCheckout_CheckoutButton {
    private static $beam_checkout_btn_priority = 200;

    public function __construct() {
        add_action('wp_enqueue_scripts', 'beam_checkout_create_script');
        add_action('wp_enqueue_scripts', 'beam_checkout_load_custom_styles');

        $this->place_checkout_buttons();
    }

    function place_checkout_buttons() {
        if (is_beam_checkout_config_enabled('custom_placement')) {
            add_shortcode('beam_checkout_from_cart_button', [$this, 'create_button_from_cart_shortcode_handler']);
            add_shortcode('beam_checkout_from_product_button', [$this, 'create_button_from_product_shortcode_handler']);
        }

        if (is_beam_checkout_config_enabled('product_page_placement')) {
            add_action('woocommerce_after_add_to_cart_form', [$this, 'place_button_in_product_page'], self::$beam_checkout_btn_priority);
        }

        if (is_beam_checkout_config_enabled('cart_dropdown_placement')) {
            add_action('woocommerce_widget_shopping_cart_buttons', [$this, 'place_button_in_cart_widget'], self::$beam_checkout_btn_priority);
        }

        if (is_beam_checkout_config_enabled('cart_page_placement')) {
            add_action('woocommerce_proceed_to_checkout', [$this, 'place_button_in_cart_page'], self::$beam_checkout_btn_priority);
        }

        if (is_beam_checkout_config_enabled('checkout_page_placement')) {
            add_action('woocommerce_checkout_before_customer_details', [$this, 'place_button_in_checkout_page'], self::$beam_checkout_btn_priority);
        }
    }

    function place_button_in_product_page() {
        $this->create_button_from_product('product_page', '');
    }

    function place_button_in_cart_page() {
        $this->create_button_from_cart('cart_page', '');
    }

    function place_button_in_checkout_page() {
        $this->create_button_from_cart('checkout_page', '');
    }

    function place_button_in_cart_widget() {
        $this->create_button_from_cart('cart_dropdown', '');
    }

    function create_button_from_cart_shortcode_handler($atts) {
        $this->create_button_from_cart('', beam_checkout_sanitize_class_name_from_atts($atts));
    }

    function create_button_from_product_shortcode_handler($atts) {
        $this->create_button_from_product('', beam_checkout_sanitize_class_name_from_atts($atts));
    }

    function add_gutter_to_class($page) {
        $class_name = '';

        $margin_top = get_beam_checkout_config($page . '_margin_top', 'none');
        $margin_bottom = get_beam_checkout_config($page . '_margin_bottom', 'none');
        $margin_left = get_beam_checkout_config($page . '_margin_left', 'none');
        $margin_right = get_beam_checkout_config($page . '_margin_right', 'none');

        if ($margin_top !== 'none') {
            $class_name .= ' ' . $margin_top . '-margin-top';
        }
        if ($margin_bottom !== 'none') {
            $class_name .= ' ' . $margin_bottom . '-margin-bottom';
        }
        if ($margin_left !== 'none') {
            $class_name .= ' ' . $margin_left . '-margin-left';
        }
        if ($margin_right !== 'none') {
            $class_name .= ' ' . $margin_right . '-margin-right';
        }

        return $class_name;
    }

    function create_button_from_cart($page, $class_name) {
        $items = [];
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $items[] = [
                'product_id' => $cart_item['product_id'],
                'variation_id' => $cart_item['variation_id'],
                'quantity' => $cart_item['quantity'],
            ];
        }

        $coupons = [];
        $rewards_discount = 0;
        foreach (WC()->cart->get_applied_coupons() as $coupon_code) {
            $coupon = new WC_Coupon($coupon_code);
            // PHP 7 backward compatibility
            if (strpos($coupon_code, 'wc_points_redemption_') !== false) {
                $rewards_discount += $coupon->get_amount();
            }
            $coupons[] .= $coupon_code;
        }

        $is_discount_applied = false;
        if (class_exists('WC_Points_Rewards_Cart_Checkout') > 0) {
            $is_discount_applied = WC_Points_Rewards_Cart_Checkout::is_discount_applied();
        }

        $this->create_button_content(
            $items,
            $coupons,
            $class_name != '' ? $class_name : 'button beam-checkout-btn fullwidth' . $this->add_gutter_to_class($page),
            false,
            false,
            [
                'rewards_discount' => $rewards_discount,
                'is_discount_applied' => $is_discount_applied,
                'user_id' => get_current_user_id(),
            ],
        );
    }

    function add_variation_handler($product) {
        if ($product->is_type('variable')) {
            ?>
            <script>
                jQuery(document).ready(function($) {
                    $('input[name="variation_id"]').change(function() {
                        if('' !== $(this).val()) {
                            $('#beam-checkout-btn').removeAttr('disabled');
                        } else {
                            $('#beam-checkout-btn').attr('disabled', 'disabled');
                        }
                    });
                });
            </script>
            <?php
        }
    }

    function create_button_from_product($page, $class_name) {
        global $product;

        $items = [[
            'product_id' => $product->get_id(),
            'quantity' => 1,
        ]];

        $product_quantity = $product->get_stock_quantity();

        $this->add_variation_handler($product);

        if ($product_quantity === null || $product_quantity > 0) {
            $this->create_button_content(
                $items,
                [],
                $class_name !== '' ? $class_name : $this->add_gutter_to_class($page),
                true,
                $product->is_type('variable')
            );
        }
    }

    function create_button_content($items, $coupons, $class_name, $should_update_quantity = false, $disabled = false, $params = []) {
        ?>
        <button
            class="<?php esc_attr_e($class_name) ?>"
            id="beam-checkout-btn"
            type="button"
            onClick='handleClick(<?php echo wp_json_encode($items) ?>, <?php echo wp_json_encode($coupons) ?>, <?php esc_attr_e(var_export($should_update_quantity, true)) ?>, <?php echo wp_json_encode($params) ?>); return false;'
            <?php echo $disabled ? 'disabled="disabled"' : '' ?>
        >
            <div class="btn-wrapper">
                <svg class="beam-logo" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26">
                    <g data-name="Layer 2">
                        <g data-name="Group 76640">
                            <g data-name="Group 76641">
                                <circle data-name="Ellipse 845" cx="13" cy="13" r="13" style="fill:#fff"/>
                                <path data-name="Path 4" d="M9.61 7.6a3.21 3.21 0 0 0-.865-1.069 3.748 3.748 0 0 0-1.145-.62 2.822 2.822 0 0 0 1.239-1.078 2.946 2.946 0 0 0 .418-1.561A3.045 3.045 0 0 0 8.291.837 4.268 4.268 0 0 0 5.428 0H0v3.748l2.008-1.815h3.085a2.783 2.783 0 0 1 1.534.4 1.458 1.458 0 0 1 .623 1.329 1.5 1.5 0 0 1-.586 1.264 2.258 2.258 0 0 1-1.348.465H2.008L0 7.314v1.861l2.008-1.851h3.4a4.021 4.021 0 0 1 1.143.167 2.216 2.216 0 0 1 .957.558 1.477 1.477 0 0 1 .389 1.1 1.666 1.666 0 0 1-.669 1.459 2.923 2.923 0 0 1-1.728.473H2.008l-1.98 1.931h5.585a6.727 6.727 0 0 0 1.524-.176 4.128 4.128 0 0 0 1.394-.6 3.149 3.149 0 0 0 1.023-1.176 4.013 4.013 0 0 0 .39-1.877A3.417 3.417 0 0 0 9.61 7.6z" transform="translate(9.027 6.494)" style="fill:#08154d"/>
                            </g>
                        </g>
                    </g>
                </svg>
                <span>One-Click Checkout</span>
            </div>
        </button>
        <?php
    }
}