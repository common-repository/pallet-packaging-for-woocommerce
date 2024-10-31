<?php

/**
 * Add and show simple and variable products.
 * Class EnPalletProductDetail
 * @package EnPalletProductDetail
 */
if (!class_exists('EnPalletProductDetail')) {

    class EnPalletProductDetail
    {

        /**
         * Hook for call.
         * EnPalletProductDetail constructor.
         */
        public function __construct()
        {
            // Add simple product fields
            add_action('woocommerce_product_options_shipping', [$this, 'en_show_product_fields'], 120, 3);
            add_action('woocommerce_process_product_meta', [$this, 'en_save_product_fields'], 120, 1);

            // Add variable product fields.
            add_action('woocommerce_product_after_variable_attributes', [$this, 'en_show_product_fields'], 120, 3);
            add_action('woocommerce_save_product_variation', [$this, 'en_save_product_fields'], 120, 1);

            add_filter('en_ppp_request', array($this, 'en_ppp_request'), 10, 3);
            add_filter('en_ppp_existence', array($this, 'en_ppp_existence'), 10, 1);
        }

        // Addon existence
        public function en_ppp_existence($action)
        {
            return true;
        }

        /**
         * Get product rental
         */
        public function en_sao_pallet($product_object, $product_detail)
        {
            $post_id = (isset($product_object['variation_id']) && $product_object['variation_id'] > 0) ? $product_object['variation_id'] : $product_detail->get_id();
            return get_post_meta($post_id, '_en_sao_pallet', true);
        }

        /**
         * Get product markup
         */
        public function en_vrf_pallet($product_object, $product_detail)
        {
            $post_id = (isset($product_object['variation_id']) && $product_object['variation_id'] > 0) ? $product_object['variation_id'] : $product_detail->get_id();
            return get_post_meta($post_id, '_en_vrf_pallet', true);
        }

        /**
         * Arrange the pricing per product with WP plugins
         */
        public function en_ppp_request($values, $product_object, $product_detail)
        {
            $en_vrf_pallet = $this->en_vrf_pallet($product_object, $product_detail);
            $en_sao_pallet = $this->en_sao_pallet($product_object, $product_detail);
            $values['ppp'] = [
                'ship_as_own_pallet' => $en_sao_pallet,
                'vertical_rotation_for_pallet' => $en_vrf_pallet
            ];
            return $values;
        }

        /**
         * Show product fields in variation and simple product.
         * @param array $loop
         * @param array $variation_data
         * @param array $variation
         */
        public function en_show_product_fields($loop, $variation_data = [], $variation = [])
        {
            $postId = (isset($variation->ID)) ? $variation->ID : get_the_ID();
            $this->en_custom_product_fields($postId);
        }

        /**
         * Save the simple product fields.
         * @param int $postId
         */
        public function en_save_product_fields($postId)
        {
            if (isset($postId) && $postId > 0) {
                $en_product_fields = $this->en_product_fields_arr();
                foreach ($en_product_fields as $key => $custom_field) {
                    $custom_field = (isset($custom_field['id'])) ? $custom_field['id'] : '';
                    $en_updated_product = (isset($_POST[$custom_field][$postId])) ? sanitize_text_field($_POST[$custom_field][$postId]) : '';
                    $en_updated_product = esc_attr($en_updated_product);
                    update_post_meta($postId, $custom_field, $en_updated_product);
                }
            }
        }

        /**
         * Product Fields Array
         * @return array
         */
        public function en_product_fields_arr()
        {
            $en_product_fields = [
                [
                    'type' => 'title',
                    'id' => '_en_pallet',
                    'class' => '_en_pallet',
                    'line_item' => '_en_pallet',
                    'label' => 'LTL Pallet Packaging Settings'
                ],
                [
                    'type' => 'checkbox',
                    'id' => '_en_sao_pallet',
                    'class' => '_en_sao_pallet',
                    'line_item' => '_en_sao_pallet',
                    'label' => 'Ship as its own pallet',
                    'desc_tip' => 'true',
                    'description' => 'This item ships as its own pallet.'
                ],
                [
                    'type' => 'checkbox',
                    'id' => '_en_vrf_pallet',
                    'class' => '_en_vrf_pallet',
                    'line_item' => 'en_vrf_pallet',
                    'label' => 'Allow vertical rotation on the pallet',
                    'desc_tip' => 'true',
                    'description' => 'Allow item to be rotated vertically when placing it on a pallet.'
                ]
            ];

            // We can use hook for add new product field from other plugin add-on
            $en_product_fields = apply_filters('en_ppp', $en_product_fields);

            return $en_product_fields;
        }

        /**
         * Heading show on product detail page
         * @param array $custom_field
         * @param int $postId
         */
        public function en_product_title($custom_field, $postId)
        {
            echo "<h2>" . $custom_field['label'] . "</h2>";
        }

        /**
         * Show Product Fields
         * @param int $postId
         */
        public function en_custom_product_fields($postId)
        {
            $en_product_fields = $this->en_product_fields_arr();

            foreach ($en_product_fields as $key => $custom_field) {
                $en_field_type = (isset($custom_field['type'])) ? $custom_field['type'] : '';
                $en_action_function_name = 'en_product_' . $en_field_type;

                if (method_exists($this, $en_action_function_name)) {
                    $this->$en_action_function_name($custom_field, $postId);
                }
            }
        }

        /**
         * Dynamic checkbox field show on product detail page
         * @param array $custom_field
         * @param int $postId
         */
        public function en_product_checkbox($custom_field, $postId)
        {
            $custom_checkbox_field = [
                'id' => $custom_field['id'] . '[' . $postId . ']',
                'value' => get_post_meta($postId, $custom_field['id'], true),
                'label' => $custom_field['label'],
                'class' => $custom_field['class'],
            ];

            if (isset($custom_field['description'])) {
                $custom_checkbox_field['description'] = $custom_field['description'];
            }

            if (isset($custom_field['desc_tip'])) {
                $custom_checkbox_field['desc_tip'] = true;
            }

            woocommerce_wp_checkbox($custom_checkbox_field);
        }
    }

    new EnPalletProductDetail();
}
