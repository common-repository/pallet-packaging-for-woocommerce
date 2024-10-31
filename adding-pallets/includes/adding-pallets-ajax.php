<?php

namespace EnPpfwEnpAjax;

use EnPpfwDistance\EnPpfwDistance;
use EnPpfwDropshipTemplate\EnPpfwDropshipTemplate;
use EnPpfwPallethouse\EnPpfwPallethouse;

//use EnPpfwPallethouseTemplate\EnPpfwPallethouseTemplate;

if (!class_exists('EnPpfwEnpAjax')) {

    class EnPpfwEnpAjax
    {

        public function __construct()
        {
            add_action('wp_ajax_nopriv_en_ppfw_enp_save_form_data', [$this, 'en_ppfw_enp_save_form_data']);
            add_action('wp_ajax_en_ppfw_enp_save_form_data', [$this, 'en_ppfw_enp_save_form_data']);

            add_action('wp_ajax_nopriv_en_ppfw_enp_available_updated', [$this, 'en_ppfw_enp_available_updated']);
            add_action('wp_ajax_en_ppfw_enp_available_updated', [$this, 'en_ppfw_enp_available_updated']);

            add_action('wp_ajax_nopriv_en_ppfw_enp_delete_row', [$this, 'en_ppfw_enp_delete_row']);
            add_action('wp_ajax_en_ppfw_enp_delete_row', [$this, 'en_ppfw_enp_delete_row']);
        }

        /**
         * Available updated
         */
        public function en_ppfw_enp_available_updated()
        {
            global $wpdb;
            $en_table = $wpdb->prefix . 'en_pallets';
            $en_enp_id = (isset($_POST['en_enp_id'])) ? sanitize_text_field($_POST['en_enp_id']) : "";
            $db_text = (isset($_POST['db_text'])) ? sanitize_text_field($_POST['db_text']) : "";
            $post_data = [
                'available' => $db_text
            ];
            $enp_step = 'Pallet';
            $message = $enp_step . ' updated successfully.';
            $action = 'update';
            $wpdb->update($en_table, $post_data, array('id' => $en_enp_id));
            $severity = 'success';
            echo wp_json_encode([
                'severity' => $severity,
                'message' => $message,
                'action' => $action
            ]);
            exit;
        }

        /**
         * Delete row from enp tab
         */
        public function en_ppfw_enp_delete_row()
        {
            global $wpdb;
            $en_table = $wpdb->prefix . 'en_pallets';
            $en_enp_id = (isset($_POST['en_enp_id'])) ? sanitize_text_field($_POST['en_enp_id']) : "";
            $enp = (isset($_POST['en_enp_type'])) ? sanitize_text_field($_POST['en_enp_type']) : "";
            $enp = 'Pallet';
            $wpdb->delete($en_table, array('id' => $en_enp_id));
            $en_enp_html = EnPpfwDropshipTemplate::en_load();
            $en_target_enp = '.en_enp_pship_main_div';
            $message = ucwords($enp) . ' deleted successfully.';

            echo wp_json_encode([
                'message' => $message,
                'enp' => $enp,
                'enp_id' => $en_enp_id,
                'target_enp' => $en_target_enp,
                'html' => $en_enp_html,
            ]);
            exit;
        }

        /**
         * Enp btn clicked
         */
        public function en_ppfw_enp_save_form_data()
        {
            global $wpdb;
            $post_data = [];
            $action = $enp = $message = $en_enp_html = $severity = $enp_id = $en_target_enp = '';
            $en_table = $wpdb->prefix . 'en_pallets';

            parse_str($_POST['en_post_data'], $post_data);

            $duplicate_post_data = $post_data;

            if (isset($post_data['id'], $post_data['enp'])) {
                unset($post_data['id']);

                $enp = $post_data['enp'];
                $available = (isset($post_data['available'])) ? $post_data['available'] : 'off';

                $enp_step = 'Pallet';
                $en_enp_template_obj = new EnPpfwDropshipTemplate();
                $en_target_enp = '.en_enp_pship_main_div';
                $validate = ['nickname', 'length', 'width', 'max_height', 'pallet_height', 'max_weight', 'pallet_weight','w_max_height','w_max_weight'];

                $en_flipped_data = array_flip($validate);
                $en_intersected_data = array_intersect_key($post_data, $en_flipped_data);

                $en_enp_data = EnPpfwPallethouse::get_data($en_intersected_data);

                $severity = 'success';
                $enp_id = $duplicate_post_data['id'];

                if (isset($post_data['enp'])) {
                    unset($post_data['enp']);
                }

                $en_pallet_nickname = (isset($post_data['nickname'])) ? $post_data['nickname'] : '';
                $get_data = EnPpfwPallethouse::get_data(['nickname' => $en_pallet_nickname]);
                if (!empty($get_data)) {
                    $get_data = reset($get_data);
                }

                $post_data['available'] = $available;
                if (isset($get_data['id']) && $get_data['id'] != $enp_id) {
                    $message = 'Nickname already exists.';
                    $severity = 'error';
                } else {
                    if (strlen($duplicate_post_data['id']) > 0 &&
                        (empty($en_enp_data) ||
                            (!empty($en_enp_data) &&
                                reset($en_enp_data)['id'] === $enp_id))) {
                        $message = $enp_step . ' updated successfully.';
                        $action = 'update';
                        $wpdb->update($en_table, $post_data, array('id' => $enp_id));
                    } elseif (empty($en_enp_data)) {
                        $message = $enp_step . ' added successfully.';
                        $action = 'insert';
                        $wpdb->insert($en_table, $post_data);
                    } else {
                        $message = 'There is something wrong.';
                        $severity = 'error';
                    }
                }

                $en_enp_html = $en_enp_template_obj::en_load();
            }

            echo wp_json_encode([
                'severity' => $severity,
                'message' => $message,
                'action' => $action,
                'enp' => $enp,
                'enp_id' => $enp_id,
                'target_enp' => $en_target_enp,
                'html' => $en_enp_html,
            ]);

            exit;
        }

    }

}