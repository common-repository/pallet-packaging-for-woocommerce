<?php

/**
 * Includes Ajax Request class
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnWooPalletAddonsCurlReqIncludes")) {

    class EnWooPalletAddonsCurlReqIncludes extends EnPackagingTab
    {
        public $plugin_standards;
        public $plugin_license_key;

        /**
         * Smart street curl api response from server
         * @param type $action
         * @return type
         */
        public function en_smart_street_api_curl_request($action, $plugin_name = "", $selected_plan = "")
        {
            if (isset($plugin_name) && (!empty($plugin_name))) {
                $this->plugin_standards = array("plugin_name" => $plugin_name);
            }
            $plugin_standards = $this->plugin_standards;
            $plugin_dependies = $this->en_plugins_dependencies();
            $plugin_dependies = $plugin_dependies[$plugin_standards['plugin_name']];

            $this->plugin_license_key = get_option($this->en_get_arr_filterd_val($plugin_dependies['license_key']));

            $postArr = array(
                'platform' => 'wordpress',
                'request_key' => md5(microtime() . rand()),
                'action' => $action,
                'package' => (isset($selected_plan) && (!empty($selected_plan))) ? $selected_plan : "",
                'domain_name' => en_pallet_get_domain(),
                'license_key' => $this->plugin_license_key,
            );

            /* Check if URL contains folder */
            if ($this->en_check_url_contains_folder()) {
                $postArr['webHookUrl'] = get_site_url();
            }

            $field_string = wp_json_encode($postArr);
            $url = esc_url('https://ws049.eniture.com/addon/standard-packaging/index.php');
            if (!empty($url) && !empty($field_string)) {
                // Set response
                $response = wp_remote_post($url, array(
                        'method' => 'POST',
                        'timeout' => 60,
                        'redirection' => 5,
                        'blocking' => true,
                        'body' => $field_string,
                    )
                );

                // Get response
                $output = wp_remote_retrieve_body($response);
                return $output;
            }
        }

        /**
         * Function detect site contains folder.
         */
        public function en_check_url_contains_folder()
        {
            $url = get_site_url();
            $url = preg_replace('#^https?://#', '', $url);
            $urlArr = explode("/", $url);
            if (isset($urlArr[1]) && !empty($urlArr[1])) {
                return true;
            }
            return false;
        }

    }

    new EnWooPalletAddonsCurlReqIncludes();
}
