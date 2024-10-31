<?php

//  Not allowed to access directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnPackaging")) {

    class EnPackaging
    {
        public $order_id;
        public $pallet_nickname;
        public $standard_packaging;

        function __construct()
        {
            add_filter('en_pallet_identify', array($this, 'en_pallet_identify'), 10, 1);
            add_action('woocommerce_order_actions', array($this, 'en_create_meta_box_pallets'), 11, 2);
        }

        /**
         * Get the pallet details from meta data.
         */
        public function en_create_meta_box_pallets($actions, $order)
        {
            $this->order_id = $order->get_id();
            $order_data = $this->standard_packaging = [];
            $shipping_details = $order->get_items('shipping');
            foreach ($shipping_details as $item_id => $shipping_item_obj) {
                $order_data = $shipping_item_obj->get_formatted_meta_data();
            }

            $shipment = 'single';
            $en_data = false;
            foreach ($order_data as $key => $is_meta_data) {
                if (isset($is_meta_data->key) && ($is_meta_data->key === "en_data" || $is_meta_data->key === "min_prices")) {
                    $order_meta_data = json_decode($is_meta_data->value, true);
                    if (!empty($order_meta_data)) {
                        $en_data = true;
                        $shipment = 'multiple';
                        $order_data = $order_meta_data;
                    }
                }
            }

            if ($shipment == 'multiple') {
                if ($en_data) {
                    foreach ($order_data as $key => $meta_data) {
                        !isset($meta_data['standard_packaging']) && isset($meta_data['meta_data']['standard_packaging']) ? $meta_data = $meta_data['meta_data'] : '';
                        (isset($meta_data['standard_packaging']) && !empty($meta_data['standard_packaging'])) ? $this->standard_packaging[] = json_decode($meta_data['standard_packaging'], true) : '';
                    }
                }
            } else {
                foreach ($order_data as $key => $meta_data) {
                    (isset($meta_data->key) && $meta_data->key === 'standard_packaging') ? $this->standard_packaging[] = json_decode($meta_data->value, true) : '';
                }
            }

            add_meta_box('en_order_visual_bin_details', __('Pallet Details', 'woocommerce'), array($this, 'en_assign_pallet_details'), get_current_screen()->id, 'normal', 'core');
            return $actions;
        }

        /**
         * Get the pallet packed
         */
        public function en_get_pallets_detail_db()
        {
            $order = new WC_Order($this->order_id);
            $main_standard_packaging = array();
            foreach ($order->get_meta_data() as $key => $value) {
                $standard_packaging = $value->get_data();
                if (isset($standard_packaging['key']) && ($standard_packaging['key'] == "_standard_packaging")) {
                    $main_standard_packaging = json_decode($standard_packaging['value'], TRUE);
                }
            }

            return $main_standard_packaging;
        }

        /**
         * Pallet packed or not.
         */
        public function en_assign_pallet_details()
        {
            $standard_packaging = $this->en_get_pallets_detail_db();
            if (isset($this->standard_packaging) && !empty($this->standard_packaging)) {
                $standard_packaging = $this->standard_packaging;
            }

            if (!empty($standard_packaging)) {
                $this->en_get_pallet_packed($standard_packaging);
            } else {
                echo force_balance_tags('<p>No packaging found for this order.</p>');
            }
        }

        /**
         * Get the pallet packed.
         */
        public function en_get_pallet_packed($standard_packaging)
        {
            $count = 1;

            foreach ($standard_packaging as $key => $standard_packag) {
                foreach ($standard_packag as $key => $details) {
                    if (isset($details['bins_packed']) && (!empty($details['bins_packed']))) {
                        $details['pallets_packed'] = $details['bins_packed'];
                    }

                    if (isset($details['pallets_packed']) && (!empty($details['pallets_packed']))) {
                        $total_boxs_packet = count($details['pallets_packed']);
                        $total_items_packet = $this->en_total_items_count($details);
                        echo force_balance_tags("<div class='en-pallet-details'>");
                        echo force_balance_tags("<p>Number of Pallets :<span class='total_boxes_packed'><strong> " . esc_attr($total_boxs_packet) . "</strong></span></p>");
                        echo force_balance_tags("<p>Total items packed :<span class='total_boxes_packed'><strong> " . esc_attr($total_items_packet) . "</strong></span></p>");
                        _e('<hr>', 'eniture-technology');
                        $this->en_output_bins_packed($details);
                        echo force_balance_tags("</div>");
                        $count++;
                    }
                }
            }
        }

        /**
         * Bins packets ouput.
         * @param array $bin_details
         * @param string/int $zip
         */
        public function en_output_bins_packed($details)
        {
            $bin_count = 1;
            echo force_balance_tags("<div class='per-package'>");
            /* Pallet packed details */
            foreach ($details['pallets_packed'] as $bin_details) {

                if (isset($bin_details['bin_data'])) {
                    $bin_details['pallet_data'] = $bin_details['bin_data'];
                    $bin_details['pallet_data']['id'] = $this->en_get_pallet_nickname($bin_details);
                }

                $this->pallet_nickname = (isset($bin_details['pallet_data']['type'], $bin_details['pallet_data']['id']) && $bin_details['pallet_data']['type'] == "item") ? $this->en_get_product_name($bin_details['pallet_data']['id']) : ((isset($bin_details['pallet_data']['id'])) ? $bin_details['pallet_data']['id'] : "");
                $main_bin_img = $bin_details['image_complete'];
                echo '<div class="en-pallet-' . esc_attr($bin_count) . '">';
                echo '<div class="en-full-row">';
                echo '<div class="en-left before-steps-info">';
                echo '<p><b>Pallet ' . esc_attr($bin_count) . ' of ' . count($details['pallets_packed']) . '</b></p>';
                echo '<p class="box-prod-title">' . esc_attr($this->pallet_nickname) . '</p>';
                echo '<div class="package-dimensions">'
                    . '<p>' . esc_attr($bin_details['pallet_data']['d']) . ' x ' . esc_attr($bin_details['pallet_data']['w']) . ' x ' . esc_attr($bin_details['pallet_data']['h']) . '</p>'
                    . '</div>';
                echo '</div>';
                echo '<div class="package-complete-image">';
                echo '<img class="package-complete-image-tag" src="' . esc_url($main_bin_img) . '" />';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                $this->en_output_items_packed($bin_details);
                $bin_count++;
            }
            echo force_balance_tags("</div>");
        }

        /**
         * Get Product Name
         */
        public function en_get_product_name($product_id)
        {
            return esc_attr(get_the_title($product_id)) . " (Own Pallet)";
        }

        /**
         * Items packet details.
         * @param array $bin_details
         * @param string/int $zip
         */
        public function en_output_items_packed($bin_details)
        {
            echo force_balance_tags('<div class="package-steps-block">');
            echo force_balance_tags("<p><strong>Steps:</strong></p>");
            /* Items packed details */
            foreach ($bin_details['items'] as $item_details) {
                if((isset($bin_details['pallet_data']['type'], $bin_details['pallet_data']['id']) && $bin_details['pallet_data']['type'] == "item")){
                    $product_name = '';
                }else{
                    $product= wc_get_product($item_details['id']);
                    $product_name = (!empty($product) && !empty($product->get_name())) ? $product->get_name() : '';
                }
                echo '<div class="package-steps-product">';
                echo '<img class="en-prduct-steps-image" src="' . esc_url($item_details['image_sbs']) . '" />';
                echo '<div class="en-product-steps-details">';
                echo '<p class="en-prdouct-steps-name">' . esc_attr($product_name) . '</p>';
                echo '<p class="en-prdouct-steps-dimensions">' . esc_attr($item_details['d']) . ' x ' . esc_attr($item_details['w']) . ' x ' . esc_attr($item_details['h']) . '</p>';
                echo '</div>';
                echo '</div>';
            }
            /* Clear the float effect */
            echo '<div class="en-clear"></div>';
            echo force_balance_tags('</div>');
            _e('<hr>', 'eniture-technology');
        }

        /**
         *  Count the items.
         * @param array $details
         */
        public function en_total_items_count($details)
        {
            $items = 0;
            foreach ($details['pallets_packed'] as $d) {
                (isset($d['items'])) ? $items += count($d['items']) : "";
            }
            return $items;
        }

        function en_get_pallet_nickname($details)
        {
            $id = isset($details['pallet_data']['id']) ? $details['pallet_data']['id'] : "";
            if (empty($id)) return $id;

            global $wpdb;
            $en_table_name = $wpdb->prefix . 'en_pallets';
            $pallet_data = $wpdb->get_row("SELECT * FROM " . $en_table_name . " WHERE id = " . $id, ARRAY_A);
            $nickname = isset($pallet_data['nickname']) ? $pallet_data['nickname'] : $id;
            
            return $nickname;
        }

        /**
         *  Pallet packages.
         */
        public function en_pallet_identify($post_data)
        {
            $subscription_packages_falg = get_option('subscription_packages_response_for_pallet');
            $suspend_automatic_flag = get_option('suspend_automatic_detection_of_pallets');
            if ($subscription_packages_falg == "yes" && $suspend_automatic_flag != "yes") {

                $post_data['standard_packaging'] = 1;
                $post_data['standardPackaging'] = 1;

                $custom_plugin = isset($post_data['plugin_name']) && $post_data['plugin_name'] == "wweLtl";

                $pallets = [];
                $en_enp_list = \EnPpfwPallethouse\EnPpfwPallethouse::get_data();
                foreach ($en_enp_list as $key => $enp) {
                    $id = $nickname = $length = $width = $max_height = $pallet_height = $max_weight = $pallet_weight = $available = $pallet_fee =$w_max_height =$w_max_weight='';
                    extract($enp);
                    if ($available == 'on') {
                        $pallets[] = [
                            'w' => $width, // width
                            'd' => $length, // length
                            'h' => $max_height, // max allowed height of product on pallet
                            'id' => $custom_plugin ? $id : $nickname, // unique id
                            'wg' => $max_weight, // max allowed weight on pallet
                            'pallet_wg' => $pallet_weight, // weight of empty pallet
                            'pallet_h' => $pallet_height, // pallet thickness
                            'pallet_fee' => $pallet_fee, // pallet fee
                            'w_max_height'=>$w_max_height,
                            'w_max_weight'=>$w_max_weight,
                            'nickname' => $nickname,
                        ];
                    }
                }

                if ($custom_plugin && !empty($pallets)) {
                    $post_data['newApiForPallets'] = '1';
                    $pallets = array_combine(array_column($pallets, 'id'), $pallets);
                }

                $post_data[$custom_plugin ? 'pallet' : 'pallets'] = $pallets;
            }

            return $post_data;
        }

    }

    new EnPackaging();
}
