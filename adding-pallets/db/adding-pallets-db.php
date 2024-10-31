<?php

/**
 * Handle table.
 */

namespace EnPpfwPallethouse;

/**
 * Generic class to handle pallethouse data.
 * Class EnPpfwPallethouse
 * @package EnPpfwPallethouse
 */
if (!class_exists('EnPpfwPallethouse')) {

    class EnPpfwPallethouse
    {

        /**
         * Hook for call.
         * EnPpfwPallethouse constructor.
         */
        public function __construct()
        {
            add_action('admin_init', array($this, 'en_pallet_compatability'));
        }

        /**
         * get pallet option data
         */
        public function en_pallet_compatability()
        {
            global $wpdb;
            $pallet_option_data = get_option('pallet');
            $en_pallet_table = $wpdb->prefix . 'en_pallets';

            if ($wpdb->query("SHOW TABLES LIKE '" . $en_pallet_table . "'") != 0) {
                if (isset($pallet_option_data) && is_string($pallet_option_data) && strlen($pallet_option_data) > 0) {
                    $pallet_options = json_decode($pallet_option_data, true);
                    $en_pallet_nickname = $en_pallet_sizing_length = $en_pallet_width = $en_pallet_sizing_height = $en_pallet_sizing_max_weight = $en_pallet_sizing_weight = $en_pallet_box_sizing_fee = $en_pallet_w_max_height = $en_pallet_w_max_weight =  $en_pallet_available  = '';
                    extract($pallet_options);
                    $pallet_data = [
                        'nickname' => $en_pallet_nickname,
                        'length' => $en_pallet_sizing_length,
                        'width' => $en_pallet_width,
                        'max_height' => $en_pallet_sizing_height,
                        'max_weight' => $en_pallet_sizing_max_weight,
                        'pallet_weight' => $en_pallet_sizing_weight,
                        'pallet_fee' => $en_pallet_box_sizing_fee,
                        'w_max_height'=>$en_pallet_w_max_height,
                        'w_max_weight'=>$en_pallet_w_max_weight,
                        'available' => $en_pallet_available
                    ];

                    $get_data = self::get_data(['nickname' => $en_pallet_nickname]);
                    if (empty($get_data)) {
                        $wpdb->insert($en_pallet_table, $pallet_data);
                        delete_option('pallet');
                    }
                }
            }
        }

        /**
         * Get pship list
         * @param array $en_enp_details
         * @return array|object|null
         */
        public static function get_data($en_enp_details = [])
        {
            global $wpdb;

            if (isset($en_enp_details['enp'])) {
                unset($en_enp_details['enp']);
            }
            $en_where_clause_str = '';
            $en_where_clause_param = [];
            if (isset($en_enp_details) && !empty($en_enp_details)) {

                foreach ($en_enp_details as $index => $value) {
                    $en_where_clause_str .= (strlen($en_where_clause_str) > 0) ? ' AND ' : '';
                    $en_where_clause_str .= $index . ' = %s ';
                    $en_where_clause_param[] = $value;
                }
                $en_where_clause_str = (strlen($en_where_clause_str) > 0) ? ' WHERE ' . $en_where_clause_str : '';
            }

            $en_table_name = $wpdb->prefix . 'en_pallets';
            if (!empty($en_where_clause_str) && !empty($en_where_clause_param)) {
                $sql = $wpdb->prepare("SELECT * FROM $en_table_name $en_where_clause_str", $en_where_clause_param);
                return (array)$wpdb->get_results($sql, ARRAY_A);
            } else {
                return (array)$wpdb->get_results("SELECT * FROM $en_table_name", ARRAY_A);
            }

        }

    }

}