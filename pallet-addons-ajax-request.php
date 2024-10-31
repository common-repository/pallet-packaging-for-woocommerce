<?php

/**
 * Includes Ajax Request class
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnWooPalletAddonsAjaxReqIncludes")) {

    class EnWooPalletAddonsAjaxReqIncludes extends EnPackagingTab
    {

        public $plugin_standards;
        public $selected_plan;
        public $post_title;
        public $meta_key;
        public $post_meta;
        public $wp_post_id;
        public $success;
        public $postId;

        /**
         * Constructer
         */
        public function __construct()
        {
            /**
             * Pallet ajax request
             */
            add_action('wp_ajax_nopriv_en_woo_pallet_addons_upgrade_plan_submit', array($this, 'en_woo_pallet_addons_upgrade_plan_submit'));
            add_action('wp_ajax_en_woo_pallet_addons_upgrade_plan_submit', array($this, 'en_woo_pallet_addons_upgrade_plan_submit'));
            /**
             * Suspend automatic detection of box sizing.
             */
            add_action('wp_ajax_nopriv_en_suspend_automatic_detection_pallet', array($this, 'en_suspend_automatic_detection_pallet'));
            add_action('wp_ajax_en_suspend_automatic_detection_pallet', array($this, 'en_suspend_automatic_detection_pallet'));
        }

        /**
         * Auto detect box sizing ajax request.
         */
        public function en_woo_pallet_addons_upgrade_plan_submit()
        {
            $packgInd = (isset($_POST['selected_plan'])) ? sanitize_text_field($_POST['selected_plan']) : '';
            $plugin_name = (isset($_POST['plugin_name'])) ? sanitize_text_field($_POST['plugin_name']) : '';
            $this->plugin_standards = $plugin_name;
            $this->selected_plan = $packgInd;
            $action = isset($packgInd) && ($packgInd == "disable") ? "d" : "c";
            $EnWooPalletAddonsCurlReqIncludes = new EnWooPalletAddonsCurlReqIncludes();
            $status = $EnWooPalletAddonsCurlReqIncludes->en_smart_street_api_curl_request($action, $this->plugin_standards, $this->selected_plan);
            $status = json_decode($status, true);
            if (isset($status['severity']) && $status['severity'] == "SUCCESS") {
                if (!class_exists("EnPackagingTab")) {
                    include_once(addon_plugin_url . '/packaging-tab.php');
                }
                $EnPackagingTab = new EnPackagingTab();
                $status = $EnPackagingTab->en_pallet_subscription($status);
                $status['severity'] = "SUCCESS";
            }
            echo wp_json_encode($status);
            die();
        }

        /**
         * Update pallets indexes
         */
        public function en_suspend_automatic_detection_pallet()
        {
            $options = array();
            $suspend_automatic_detection_of_pallets = (isset($_POST['suspend_automatic_detection_of_pallets'])) ? sanitize_text_field($_POST['suspend_automatic_detection_of_pallets']) : '';
            (isset($suspend_automatic_detection_of_pallets) && (!empty($suspend_automatic_detection_of_pallets))) ?
                $options["suspend_automatic_detection_of_pallets"] = $suspend_automatic_detection_of_pallets : "";
            $this->en_update_db($options);
            echo wp_json_encode($options);
            die();
        }

        /**
         * Update options table.
         * @param array $options
         */
        public function en_update_db($options)
        {
            if (isset($options) && (is_array($options))) {
                foreach ($options as $options_name => $options_value) {
                    update_option($options_name, $options_value);
                }
            }
        }

    }

}
/* Initialize object */
new EnWooPalletAddonsAjaxReqIncludes();
