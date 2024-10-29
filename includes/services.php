<?php
include_once('utils.php');

class BeamCheckout_Services {
    private static $namespace = 'beamcheckout/v1';

    public function __construct() {
        add_action('rest_api_init', function () {
            register_rest_route(self::$namespace, '/checkout', [
                'methods' => 'POST',
                'callback' => [$this, 'init_payment'],
                'permission_callback' => '__return_true',
            ]);

            register_rest_route(self::$namespace, '/cb', [
                'methods' => 'POST',
                'callback' => [$this, 'process_payment'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    function init_payment(WP_REST_Request $req): WP_REST_Response {
        $result = $this->create_order($req);

        $res = new WP_REST_Response($result);
        $res->set_status(200);

        return $res;
    }

    function process_payment(WP_REST_Request $req) {
        $res = new WP_REST_Response();

        if ($this->validate_payment($req)) {
            $order_id = $this->get_order_item($req['purchaseId']);

            if (is_null($order_id)) {
                return ['status' => 'Unable to get order from purchase ID'];
            }

            $order = wc_get_order($order_id);

            $this->add_customer_detail($order, $req['customer']);

            $res->set_data($this->update_order_status($order, $req['state']));
            $res->set_status(200);
        } else {
            $res->set_status(401);
        }

        return $res;
    }

    function validate_payment(WP_REST_Request $req) {
        $signature = sanitize_text_field(wp_unslash($_SERVER)['HTTP_X_HUB_SIGNATURE']);
        $key = base64_decode($this->get_secret_key());

        $body = str_replace("\\/", "/", json_encode($req->get_json_params(), JSON_UNESCAPED_UNICODE));

        return $signature === hash_hmac('sha256', $body, $key);
    }

    function fetch_payment(WC_Order $order) {
        $merchant_id = get_beam_checkout_config('merchant_id');
        $token = base64_encode(get_beam_checkout_config('merchant_id') . ':' . $this->get_api_key());

        $payment_options = ['creditCard', 'internetBanking', 'eWallet'];

        if (is_beam_checkout_config_enabled('installment')) {
            $payment_options[] = 'installmentsCc';
        }

        if (is_beam_checkout_config_enabled('bnpl')) {
            $payment_options[] = 'bnpl';
        }

        $data = [
            'order' => [
                'merchantReferenceId' => uniqid(),
                'description' => $this->get_order_description($order),
                'currencyCode' => $this->options['currency'] ?? 'THB',
                'totalAmount' => $order->get_subtotal(),
                'totalDiscount' => $order->get_total_discount(),
                'netAmount' => floatval($order->get_total())
            ],
            'redirectUrl' => $order->get_checkout_order_received_url(),
            'supportedPaymentMethods' => $payment_options
        ];

        if (is_beam_checkout_config_enabled('ask_for_customer_details')) {
            $data['requiredFieldsFormId'] = 'beamdatacompany-checkout';
        }

        $req = [
            'http' => [
                'header'  => 'Authorization: Basic ' . $token,
                'method'  => 'POST',
                'content' => json_encode($data)
            ]
        ];

        $context  = stream_context_create($req);
        $result = file_get_contents(
            $this->get_beam_purchases_endpoint() . $merchant_id,
            false,
            $context
        );

        return json_decode($result);
    }

    function create_order($req): array {
        $order = wc_create_order();

        $order->set_payment_method('beam-checkout');
        $order->set_payment_method_title('Beam Checkout');

        $is_empty = true;
        $unavailable_product = [];

        foreach ($req['lineItems'] as $item) {
            $product = wc_get_product($item['variation_id'] ?: $item['product_id']);
            if ($product) {
                $stock_status = $product->get_stock_status();
                $stock_quantity = $product->get_stock_quantity();
                $item_quantity = $item['quantity'];

                $is_invalid =
                    $stock_status === 'outofstock' ||
                    ($stock_status === 'instock' && !is_null($stock_quantity) && $item_quantity > $stock_quantity);

                if ($is_invalid) {
                    $unavailable_product[] = $product->get_data();
                } else {
                    $order->add_product($product, $item_quantity);
                    $is_empty = false;
                }
            }
        }

        if ($is_empty) {
            $order->delete();
            return ['error' => 'Empty order', 'unavailableProduct' => $unavailable_product];
        }

        foreach ($req['coupons'] as $coupon) {
            // PHP 7 backward compatibility
            if (strpos($coupon, 'wc_points_redemption_') === false) {
                $order->apply_coupon($coupon);
            }
        }

        $rewards_coupon = null;
        if (class_exists('WC_Points_Rewards_Discount') && class_exists('WC_Points_Rewards_Manager')) {
            if ($req['params']['is_discount_applied']) {
                $rewards_discount = $req['params']['rewards_discount'];
                $current_user = $req['params']['user_id'];

                $rewards_coupon = new WC_Coupon();
                $rewards_coupon->set_code(WC_Points_Rewards_Discount::generate_discount_code());
                $rewards_coupon->set_amount($rewards_discount);
                $rewards_coupon->set_usage_limit(1);
                $rewards_coupon->set_used_by([$current_user]);
                $rewards_coupon->set_virtual(true);
                $rewards_coupon->save();

                WC_Points_Rewards_Manager::decrease_points(
                    $current_user,
                    WC_Points_Rewards_Manager::calculate_points_for_discount($rewards_discount),
                    'beam-checkout',
                );
                $order->apply_coupon($rewards_coupon);
            }
        }

        $order->calculate_totals();

        $order->save();

        $order->update_status('Pending payment', 'Beam One-Click Checkout', TRUE);

        $result = $this->fetch_payment($order);

        if (!$result->paymentLink) {
            $order->delete();
            return ['error' => 'Unable to retrieve payment link'];
        }

        $order->set_transaction_id($result->purchaseId);
        $order->save();

        if ($rewards_coupon) {
            $rewards_coupon->delete();
        }

        return ['orderId' => $order->get_id(), 'paymentLink' => $result->paymentLink, 'unavailableProduct' => $unavailable_product];
    }

    function get_order_description(WC_Order $order) {
        $items = [];

        foreach ($order->get_items() as $item_id => $item) {
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();

            $items[] = $product_name . ' (' . $quantity . ')';
        }

        return join(', ', $items);
    }

    function get_order_item($purchase_id) {
        $query = new WC_Order_Query();
        $query->set('transaction_id', $purchase_id);
        $orders = $query->get_orders();

        if (count($orders) > 0) {
            return $orders[0]->get_id();
        }

        return null;
    }

    function add_customer_detail(WC_Order $order, $customer) {
        try {
            $order->set_billing_address_1($customer['billingAddress']['fullStreetAddress']);
            $order->set_billing_address_2($customer['billingAddress']['subDistrict'] . ' ' . $customer['billingAddress']['district']);
            $order->set_billing_city($customer['billingAddress']['city']);
            $order->set_billing_state($customer['billingAddress']['province']);
            $order->set_billing_country($customer['billingAddress']['country']);
            $order->set_billing_postcode($customer['billingAddress']['postCode']);
            $order->set_billing_first_name($customer['firstName']);
            $order->set_billing_last_name($customer['lastName']);
            $order->set_billing_email($customer['email']);
            $order->set_billing_phone($customer['contactNumber']);

            $order->set_shipping_address_1($customer['shippingAddress']['fullStreetAddress']);
            $order->set_shipping_address_2($customer['shippingAddress']['subDistrict'] . ' ' . $customer['shippingAddress']['district']);
            $order->set_shipping_city($customer['shippingAddress']['city']);
            $order->set_shipping_state($customer['shippingAddress']['province']);
            $order->set_shipping_country($customer['shippingAddress']['country']);
            $order->set_shipping_postcode($customer['shippingAddress']['postCode']);
            $order->set_shipping_first_name($customer['firstName']);
            $order->set_shipping_last_name($customer['lastName']);
            $order->set_shipping_phone($customer['contactNumber']);

            $order->save();
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    function update_order_status($order, $state) {
        if ($state === 'complete') {
            $order->payment_complete();
            return ['status' => 'Order payment is updated to complete'];
        }

        return ['status' => 'Order payment is not updated'];
    }

    function get_current_env_value($prod_env_value, $test_env_value) {
        if (is_beam_checkout_config_enabled('test_mode')) {
            return $test_env_value;
        }

        return $prod_env_value;
    }

    function get_api_key() {
        return $this->get_current_env_value(get_beam_checkout_config('api_key'), get_beam_checkout_config('test_api_key'));
    }

    function get_secret_key() {
        return $this->get_current_env_value(get_beam_checkout_config('secret_key'), get_beam_checkout_config('test_secret_key'));
    }

    function get_beam_purchases_endpoint() {
        return $this->get_current_env_value(
            'https://partner-api.beamdata.co/purchases/',
            'https://stg-partner-api.beamdata.co/purchases/'
        );
    }
}
