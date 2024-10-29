<?php
function beam_checkout_add_gateway_class($gateways) {
    $gateways[] = 'BeamCheckout_PaymentGateway';
    return $gateways;
}

function beam_checkout_init_gateway_class() {
    class BeamCheckout_PaymentGateway extends WC_Payment_Gateway {
        private static $beam_settings = [
            'activate' => [
                'enabled' => [
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable Beam Checkout Gateway',
                    'type'        => 'checkbox',
                    'default'     => 'no',
                ],
            ],
            'merchant' => [
                'merchant_id' => [
                    'title'       => 'Merchant ID',
                    'type'        => 'text',
                ],
            ],
            'prod_credential' => [
                'api_key' => [
                    'title'       => 'API Key',
                    'type'        => 'text',
                ],
                'secret_key' => [
                    'title'       => 'Secret Key',
                    'type'        => 'text',
                ],
            ],
            'test_credential' => [
                'test_mode' => [
                    'title'       => 'Test mode',
                    'label'       => 'Enable test mode',
                    'type'        => 'checkbox',
                    'description' => 'Place the payment gateway in test mode using test API keys.',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ],
                'test_api_key' => [
                    'title'       => 'API Key (Test mode)',
                    'type'        => 'text',
                    'description' => 'Only for test environment',
                    'desc_tip'    => true,
                ],
                'test_secret_key' => [
                    'title'       => 'Secret Key (Test mode)',
                    'type'        => 'text',
                    'description' => 'Only for test environment',
                    'desc_tip'    => true,
                ],
            ],
            'customer_and_payment' => [
                'ask_for_customer_details' => [
                    'title'       => 'Ask for customer details',
                    'type'        => 'checkbox',
                    'default'     => 'yes',
                ],
                'installment' => [
                    'title'       => 'Installment',
                    'label'       => 'Enable installment (via credit card)',
                    'type'        => 'checkbox',
                ],
                'bnpl' => [
                    'title'       => 'Buy now pay later',
                    'label'       => 'Enable buy now pay later (via Atome)',
                    'type'        => 'checkbox',
                ],
            ],
            'product_page' => [
                'product_page_placement' => [
                    'title'       => 'Enable',
                    'label'       => 'Place checkout button on product page',
                    'type'        => 'checkbox',
                    'default'     => 'yes',
                ],
                'product_page_margin_top' => [
                    'title'       => 'Margin Top',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'product_page_margin_bottom' => [
                    'title'       => 'Margin Bottom',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'product_page_margin_left' => [
                    'title'       => 'Margin Left',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'product_page_margin_right' => [
                    'title'       => 'Margin Right',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
            ],
            'cart_page' => [
                'cart_page_placement' => [
                    'title'       => 'Enable',
                    'label'       => 'Place checkout button on cart page',
                    'type'        => 'checkbox',
                    'default'     => 'yes',
                ],
                'cart_page_margin_top' => [
                    'title'       => 'Margin Top',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'cart_page_margin_bottom' => [
                    'title'       => 'Margin Bottom',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'cart_page_margin_left' => [
                    'title'       => 'Margin Left',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'cart_page_margin_right' => [
                    'title'       => 'Margin Right',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
            ],
            'checkout_page' => [
                'checkout_page_placement' => [
                    'title'       => 'Enable',
                    'label'       => 'Place checkout button on checkout page',
                    'type'        => 'checkbox',
                    'default'     => 'yes',
                ],
                'checkout_page_margin_top' => [
                    'title'       => 'Margin Top',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'checkout_page_margin_bottom' => [
                    'title'       => 'Margin Bottom',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'checkout_page_margin_left' => [
                    'title'       => 'Margin Left',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'checkout_page_margin_right' => [
                    'title'       => 'Margin Right',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
            ],
            'cart_dropdown' => [
                'cart_dropdown_placement' => [
                    'title'       => 'Enable',
                    'label'       => 'Place checkout button on cart widget',
                    'type'        => 'checkbox',
                    'default'     => 'yes',
                ],
                'cart_dropdown_margin_top' => [
                    'title'       => 'Margin Top',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'cart_dropdown_margin_bottom' => [
                    'title'       => 'Margin Bottom',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'cart_dropdown_margin_left' => [
                    'title'       => 'Margin Left',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
                'cart_dropdown_margin_right' => [
                    'title'       => 'Margin Right',
                    'type'        => 'select',
                    'default'     => 'none',
                    'options'     => [
                        'none'    => 'None',
                        'small'   => 'Small (8px)',
                        'medium'  => 'Medium (16px)',
                        'large'   => 'Large (24px)',
                    ],
                ],
            ],
            'custom_placement' => [
                'custom_placement' => [
                    'title'       => 'Enable shortcode',
                    'label'       => 'Enable shortcode for beam checkout button',
                    'type'        => 'checkbox',
                ],
            ],
        ];

        public function __construct() {
            $this->id = 'beam-checkout';
            $this->has_fields = false;
            $this->method_title = 'Beam Checkout';
            $this->method_description = 'Beam Checkout button for a smoother checkout experience';

            $this->init_form_fields();
            $this->init_settings();

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        public function init_form_fields() {
            foreach (self::$beam_settings as $section => $settings) {
                foreach ($settings as $key => $setting) {
                    $this->form_fields[$key] = $setting;
                }
            }
        }

        function admin_options() {
            ?>
            <table class="form-table">
                <?php
                $this->generate_settings_html(self::$beam_settings['activate']);
                ?>
            </table>
            <hr />
            <h1>Credentials</h1>
            <table class="form-table">
                <?php
                $this->generate_settings_html(self::$beam_settings['merchant']);
                ?>
            </table>
            <div style="border: 1px solid #08154D; border-radius: 8px; padding: 16px; margin: 16px 0;">
                <h3>Production</h3>
                <table class="form-table">
                    <?php
                    $this->generate_settings_html(self::$beam_settings['prod_credential']);
                    ?>
                </table>
            </div>
            <div style="border: 1px solid #08154D; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <h3>Test Environment</h3>
                <table class="form-table">
                    <?php
                    $this->generate_settings_html(self::$beam_settings['test_credential']);
                    ?>
                </table>
            </div>
            <hr />
            <h1>Payment Methods & Customer Data</h1>
            <table class="form-table">
                <?php
                $this->generate_settings_html(self::$beam_settings['customer_and_payment']);
                ?>
            </table>
            <hr />
            <h1>Placement</h1>
            <div style="border: 1px solid #08154D; border-radius: 8px; padding: 16px; margin: 16px 0;">
                <h3>Product Page</h3>
                <table class="form-table">
                    <?php
                    $this->generate_settings_html(self::$beam_settings['product_page']);
                    ?>
                </table>
            </div>
            <div style="border: 1px solid #08154D; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <h3>Cart Page</h3>
                <table class="form-table">
                    <?php
                    $this->generate_settings_html(self::$beam_settings['cart_page']);
                    ?>
                </table>
            </div>
            <div style="border: 1px solid #08154D; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <h3>Checkout Page</h3>
                <table class="form-table">
                    <?php
                    $this->generate_settings_html(self::$beam_settings['checkout_page']);
                    ?>
                </table>
            </div>
            <div style="#D5FFED; border: 1px solid #08154D; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <h3>Cart Widget</h3>
                <table class="form-table">
                    <?php
                    $this->generate_settings_html(self::$beam_settings['cart_dropdown']);
                    ?>
                </table>
            </div>
            <div style="#D5FFED; border: 1px solid #08154D; border-radius: 8px; padding: 16px;">
                <h3>Custom Placement</h3>
                <table class="form-table">
                    <?php
                    $this->generate_settings_html(self::$beam_settings['custom_placement']);
                    ?>
                </table>
            </div>
            <?php
        }
    }
}
