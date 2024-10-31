<?php
if (!class_exists('EnEnp')) {

    class EnEnp
    {
        static public $plan_required = '';
        static public $disabled_plan = '';

        /**
         * Enp fields
         * @return array
         */
        static public function en_enp_data()
        {
            // Load function for plans implementation of instore pickup and local delivery
            self::en_get_instore_pickup_plan_status();
            $plan_required = self::$plan_required;
            $disabled_plan = self::$disabled_plan;

            $en_enp_data = [
                'en_enp_id' => [
                    'type' => 'en_input_hidden',
                    'id' => 'en_enp_id',
                    'name' => 'id',
                    'append' => ' data-optional="1" ',
                ],
                'en_enp_type' => [
                    'type' => 'en_input_hidden',
                    'id' => 'en_enp_type',
                    'name' => 'enp',
                    'append' => ' data-optional="1" ',
                ],
                'nickname' => [
                    'type' => 'en_input_field',
                    'name' => 'nickname',
                    'placeholder' => '',
                    'id' => 'en_enp_nickname',
                    'label' => 'Nickname',
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    'position' => 10,
                    'append' => ' maxlength="30" ',
                    'append_after' => '<span class="en_enp_error"></span>',
                ],
                'length' => [
                    'type' => 'en_input_field',
                    'name' => 'length',
                    'placeholder' => '',
                    'id' => 'en_pallet_length',
                    'label' => 'Length (in)',
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    'position' => 20,
                    'append' => ' maxlength="7" ',
                    'append_after' => '<span class="en_enp_error"></span>',
                ],
                'width' => [
                    'type' => 'en_input_field',
                    'name' => 'width',
                    'placeholder' => '',
                    'id' => 'en_pallet_width',
                    'label' => 'Width (in)',
                    'class' => 'en_enp_input_field',
                    'append' => '',
                    'frontend' => 'show',
                    'position' => 30,
                    'append' => ' maxlength="7" ',
                    'append_after' => '<span class="en_enp_error"></span>',
                ],
                'max_height' => [
                    'type' => 'en_input_field',
                    'name' => 'max_height',
                    'placeholder' => '',
                    'id' => 'en_pallet_max_height',
                    'label' => 'Max Height (in)',
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    'position' => 40,
                    'append' => ' maxlength="7" ',
                    'append_after' => '<span class="en_enp_error"></span>',
                ],
                'pallet_height' => [
                    'type' => 'en_input_field',
                    'name' => 'pallet_height',
                    'placeholder' => '',
                    'id' => 'en_pallet_height',
                    'label' => 'Pallet Height (in)',
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    'position' => 50,
                    'append' => ' maxlength="7" ',
                    'append_after' => '<span class="en_enp_error"></span>',
                ],
                'max_weight' => [
                    'type' => 'en_input_field',
                    'name' => 'max_weight',
                    'placeholder' => '',
                    'id' => 'en_pallet_max_weight',
                    'label' => 'Max Weight (LBS)',
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    'position' => 60,
                    'append' => ' maxlength="7" ',
                    'append_after' => '<span class="en_enp_error"></span>',
                ],
                'pallet_weight' => [
                    'type' => 'en_input_field',
                    'name' => 'pallet_weight',
                    'placeholder' => '',
                    'id' => 'en_pallet_weight',
                    'label' => 'Pallet Weight (LBS)',
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    'position' => 70,
                    'append' => ' maxlength="7" ',
                    'append_after' => '<span class="en_enp_error"></span>',
                ],
                "en_pallet_box_sizing_fee" => [
                    "type" => "en_input_field",
                    "title" => "Pallet Fee (e.g 1.75)",
                    "label" => "Pallet Fee (e.g 1.75)",
                    'name' => 'pallet_fee',
                    "id" => "en_pallet_box_sizing_fee",
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    "placeholder" => "",
                    'position' => 90,
                    "data_type" => "number",
                    'append' => ' data-optional="1" ',
                ],
                "en_pallet_w_max_height" => [
                    "type" => "en_input_field",
                    "title" => "Max Height w/ Pallet",
                    "label" => "Max Height w/ Pallet",
                    'name' => 'w_max_height',
                    "id" => "en_pallet_w_max_height",
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    "placeholder" => "",
                    'position' => 92,
                    "data_type" => "number",
                    'append' => ' readonly= "true"  ',
                ],
                "en_pallet_w_max_weight" => [
                    "type" => "en_input_field",
                    "title" => "Max Weight w/ Pallet",
                    "label" => "Max Weight w/ Pallet",
                    'name' => 'w_max_weight',
                    "id" => "en_pallet_w_max_weight",
                    'class' => 'en_enp_input_field',
                    'frontend' => 'show',
                    "placeholder" => "",
                    'position' => 93,
                    "data_type" => "number",
                    'append' => ' readonly= "true"  ',
                ],
                'en_enable_pallet' => [
                    'type' => 'en_checkbox',
                    'name' => 'available',
                    'id' => 'en_enable_pallet',
                    'label' => 'Available',
                    'class' => 'en_enp_input_field',
                    'title' => 'Available',
                    'append' => ' data-optional="1" ',
                ]
            ];

//      We can use hook for add new enp field from other plugin add-on
            return apply_filters('en_ppfw_add_enp', $en_enp_data);
        }

        /**
         * Make a table row in enp frontend
         * @param array $en_enp_list
         * @param array $en_data
         * @return mixed
         */
        static public function en_arrange_enp_table_row($en_enp_list, $en_data, $enp_bol, $disabled_plan = '')
        {
            ob_start();
            foreach ($en_enp_list as $key => $enp) {
                global $wpdb;
                $en_pallet_table = $wpdb->prefix . 'en_pallets';
                $en_enp_id = (isset($enp['id'])) ? $enp['id'] : '';
                $w_max_height=$enp['max_height']+$enp['pallet_height'];
                $w_max_weight=$enp['max_weight']+$enp['pallet_weight'];
                if($enp['w_max_height']==0 || $enp['w_max_weight']==0)
                {
                    $data_update = array('w_max_height' => $w_max_height ,'w_max_weight' => $w_max_weight);
                    $data_where = array('ID' => $en_enp_id);
                    $wpdb->update($en_pallet_table , $data_update, $data_where);
                }

                $en_available = (isset($enp['available'])) ? $enp['available'] : '';
                $en_available_text = $en_available == 'on' ? 'Yes' : 'No';
                $en_flipped_data = array_flip($en_data);

                $en_intersected_data = array_intersect_key($enp, $en_flipped_data);
                $en_sorted_enp = array_merge($en_flipped_data, $en_intersected_data);
                $append_class = $key === 0 ? '' : $disabled_plan;

                echo '<tr class="' . esc_attr($append_class) . '" id="en_enp_row_id_' . $en_enp_id . '">';
                echo self::en_arrange_table_data('td', $en_sorted_enp);
                echo "<td class='en_available_link' data-available_id='" . $en_enp_id . "' onclick='en_enp_available(this)'>" . $en_available_text . "</td>";
                echo "<td class='en_enp_db_data'>" . wp_json_encode($enp) . "</td>";
                echo "<td class='en_enp_custom_data'>" . wp_json_encode(\EnEnp::en_enp_data()) . "</td>";

                echo '<td class="en_enp_icons">';
                echo '<a href="javascript(0)" onclick="return en_ppfw_enp_edit(event, this,' . $enp_bol . ')"> <img src = "' . EN_PPFW_DIR_FILE . '/adding-pallets/assets/images/edit.png" title = "Edit" ></a>';
                echo '<a href="javascript(0)" onclick="return en_ppfw_enp_delete(event, this,' . $enp_bol . ' , ' . $en_enp_id . ')"> <img  src = "' . EN_PPFW_DIR_FILE . '/adding-pallets/assets/images/delete.png" title = "Delete" ></a>';
                echo '</td>';

                echo '</tr>';
            }

            return ob_get_clean();
        }

        /**
         * Load html for enp popup
         */
        static public function en_load()
        {
            $en_enp_data = self::en_enp_data();
            ?>

            <!-- Confirmation message when you delete pship or pallethouse -->
            <div class="confirmation_enp_delete en_popup_enp_overly">
                <div class="en_popup_enp_form en_hide_popup_enp">
                    <a class="en_close_popup_enp" href="#">×</a>

                    <h2 class="en_confirmation_warning">
                        <?php _e('Warning!', 'eniture-technology'); ?>
                    </h2>
                    <p class="en_confirmation_message">
                        <?php _e('Are you sure you want to delete it?', 'eniture-technology'); ?>
                    </p>
                    <div class="en_confirmation_buttons">
                        <a href="#"
                           class="button-primary en_enp_cancel_delete"><?php _e('Cancel', 'eniture-technology'); ?></a>
                        <a href="#"
                           class="button-primary en_enp_confirm_delete"><?php _e('OK', 'eniture-technology'); ?></a>
                    </div>
                </div>
            </div>

            <?php
            echo '<div class="en_popup_enp_overly">';
            echo '<div class="en_popup_enp_form en_hide_popup_enp">';
            echo '<h2 id="en_popup_enp_heading">Pallet Properties</h2>';
            echo '<a class="en_close_popup_enp" href="#">×</a>';

            echo '</form>';

            // Popup form to show error messages class|div
            echo '<div class="en_enp_error_message"><strong>Error!</strong> <span> </span></div>';

            echo '<form method="post" id="en_enp_form_reset_me">';

            foreach ($en_enp_data as $key => $value) {
                $id = $placeholder = $type = $label = $class = $append = $append_after = $name = $title = '';
                extract($value);
                echo '<div class="en_popup_enp_input_field">';

                switch ($type) {
                    case 'en_input_field':
                        echo "<label class='pallet_label' for='" . esc_attr($id) . "'>" . esc_attr($label) . "</label>";
                        echo "<input type='text' $append title='" . esc_attr($label) . "' name='" . esc_attr($name) . "' placeholder='" . esc_attr($placeholder) . "' id='" . esc_attr($id) . "' class='" . esc_attr($class) . "'>";
                        echo $append_after;
                        break;

                    case 'en_input_hidden':
                        echo "<input type='hidden' $append name='" . esc_attr($name) . "' id='" . esc_attr($id) . "'>";
                        break;

                    case 'en_heading':
                        echo "<h2 class='" . esc_attr($class) . " en_float_left'>" . esc_attr($label) . "</h2>";
                        break;

                    case 'en_space':
                        echo "<label for='" . esc_attr($id) . "'>" . __($label) . "</label>";
                        break;

                    case 'en_checkbox':
                        echo "<label for='" . esc_attr($id) . "'>" . __($label) . "</label>";
                        echo "<input type='checkbox' $append name='" . esc_attr($name) . "' id='" . esc_attr($id) . "' title='" . esc_attr($title) . "'>";
                        break;
                }

                echo '</div>';
            }

            echo '<input type="submit" value="Save" class="en_ppfw_enp_btn button-primary">';
            echo '</form>';
            echo '</div>';
            echo '</div>';

            echo \EnPpfwDropshipTemplate\EnPpfwDropshipTemplate::en_load();
        }

        /**
         * Convert array to string for table using
         * @param string $index
         * @param array $data
         * @return string
         */
        static public function en_arrange_table_data($index, $data)
        {
            return "<$index> " . implode(" <$index> ", $data) . " </$index>";
        }

        /**
         * search detail for existance enp
         * @param string $en_enp_type
         * @return array
         */
        static public function en_enp_filter_data($en_enp_type)
        {
            $en_enp_data = \EnEnp::en_enp_data();
            $en_enp_filtered_data = [];
            foreach ($en_enp_data as $key => $fields) {
                if (isset(
                        $fields['frontend'], $fields['label'], $fields['name'], $fields['position']) &&
                    $fields['frontend'] === 'show' &&
                    (($key === 'nickname' && $en_enp_type === 'pship') ||
                        $key != 'nickname')) {
                    $en_enp_filtered_data['en_heading'][$fields['position']] = $fields['label'];
                    $en_enp_filtered_data['en_data'][$fields['position']] = $fields['name'];
                }
            }
            return $en_enp_filtered_data;
        }

        /**
         * Get plan for use multi pallethouse
         */
        static public function en_get_instore_pickup_plan_status()
        {
            if (isset($_REQUEST['tab'])) {
                $instore_pickup = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_suscription_and_features", 'instore_pickup_local_delivery');
                if (is_array($instore_pickup) && count($instore_pickup) > 0) {
                    self::$plan_required = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_notification_link", $instore_pickup);
                    self::$disabled_plan = 'en_disabled_plan';
                }
            }
        }
    }
}
