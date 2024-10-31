<?php
//  Not allowed to access directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnPackagingTab")) {

    class EnPackagingTab extends EnPalletWooAddonPluginDetail
    {
        public $subscriptionStatus;
        public $spackaging_recursive = '';
        public $settings;
        public $section;
        public $plugin_id;
        public $sections;
        public $en_plugins_dependencies;
        public $EnWooPalletAddonsCurlReqIncludes;
        public $plugin_name;
        public $status;
        public $subscription_packages_response_for_pallet;
        public $subscription_details;
        public $subscriptionInfo;
        public $lastUsageTime;
        public $subscribedPackage;
        public $subscribedPackageHitsStatus;
        public $nextSubcribedPackage;
        public $statusRequestTime;
        public $en_next_subcribed_package;

        function __construct()
        {
            add_filter('en_woo_pallet_addons_sections', array($this, 'en_woo_pallet_addons_sections'), 10, 2);
            add_filter('en_woo_pallet_addons_settings', array($this, 'en_woo_pallet_addons_settings'), 10, 3);
            add_action('woocommerce_settings_tabs_array', array($this, 'en_woo_addons_popup_notifi_disabl_to_plan_pallet'), 10);
            add_action('woocommerce_settings_wc_settings_quote_section_end_pallet_sizing_after', array($this, 'en_pallet_table'));
        }

        /**
         * Revoke SBS template for multiple times
         * @return type array
         */
        public function en_spackaging_recursive($spackaging_recursive)
        {
            $spackaging_recursive[] = $this->spackaging_recursive;
            return $spackaging_recursive;
        }

        /**
         * Plugins dependencies array merge
         * @return array
         */
        public function en_plugins_dependencies()
        {
            $plugins_dependies = array();
            $plugins_dependies_function_arr = array(
                'freightview_quotes_dependencies',
                'unishippers_freight_dependencies',
                'freightview_dependencies',
                'wwe_quests_dependencies',
                'cerasis_freights_dependencies',
                'odfl_quotes_dependencies',
                'freightquote_quests_dependencies',
                'saia_quests_dependencies',
                'rnl_quotes_dependencies',
                'sefl_quotes_dependencies',
                'abf_quotes_dependencies',
                'estes_ltl_quotes_dependencies',
                'xpo_quotes_dependencies',
                'fedex_freight_dependencies',
                'ups_freight_quotes_dependencies',
                'dayross_dependencies',
                'dayross_v2_dependencies',
                'yrc_quotes_dependencies',
                'tql_v2_dependencies',
                'echo_v2_dependencies',
                'daylight_v2_dependencies',
                'freightview_v2_dependencies',
                'priority_one_v2_dependencies'
            );

            foreach ($plugins_dependies_function_arr as $value) {
                $plugins_dependies = array_merge($plugins_dependies, $this->$value());
            }
            $plugins_dependies = apply_filters('en_woo_addons_plugin_dependencies_apply_filters', $plugins_dependies);

            return $plugins_dependies;
        }

        /**
         * Update web api settings array
         * @param array $settings
         * @param string $section
         * @param string $plugin_id
         * @return array
         */
        public function en_woo_pallet_addons_settings($settings, $section, $plugin_id)
        {
            $this->settings = $settings;
            $this->section = $section;
            $this->plugin_id = $plugin_id;

            $this->en_plugins_dependencies = $this->en_plugins_dependencies();

            if (isset($this->en_plugins_dependencies[$this->plugin_id])) {
                $plugin_detail = $this->en_plugins_dependencies[$this->plugin_id];
                $addons = $plugin_detail['addons'];
                if ($addons['pallet_packaging_addon']['active'] === true && $this->section == $addons['pallet_packaging_addon']['section']) {
                    $this->EnWooPalletAddonsCurlReqIncludes = new EnWooPalletAddonsCurlReqIncludes();
                    $this->settings = $this->en_woo_addons_box_sizing_fields_arr($this->plugin_id);
                }
            }

            return $this->settings;
        }

        /**
         * Smart street api response curl from server
         * @return array type
         */
        public function en_customer_subscription_status()
        {

            $this->en_trial_activation_bin();
            $status = $this->EnWooPalletAddonsCurlReqIncludes->en_smart_street_api_curl_request("s", $this->plugin_name);
            $this->en_check_plugin_status($status);
            $status = json_decode($status, true);
            return $status;
        }

        /**
         * Trial activation of 3dbin.
         */
        public function en_trial_activation_bin()
        {
            $trial_status = '';
            /* Trial activation code */
            $trial_status_3dbin = get_option('en_trial_activation_pallet');
            if (!$trial_status_3dbin) {
                $trial_status = $this->EnWooPalletAddonsCurlReqIncludes->en_smart_street_api_curl_request("c", $this->plugin_name, 'TR');
                $response_status = json_decode($trial_status);
                /* Trial Package activated succesfully */
                if (isset($response_status->severity) && $response_status->severity == "SUCCESS") {
                    update_option('en_trial_activation_pallet', 1);
                }
                /* Error response */
                if (isset($response_status->severity) && $response_status->severity == "ERROR") {
                    /* Do anthing in case of error */
                }
            }
        }

        /**
         * Check plugin status.
         */
        public function en_check_plugin_status($current_status)
        {
            $current_status = json_decode($current_status);
            if (
                isset($current_status->status->subscribedPackage->packageSCAC) &&
                $current_status->status->subscribedPackage->packageSCAC == 'TR'
            ) {
                $plugin_status = $this->EnWooPalletAddonsCurlReqIncludes->en_smart_street_api_curl_request("pluginType", $this->plugin_name, '');
                $decoded_plugin_status = json_decode($plugin_status);
                if ($decoded_plugin_status->severity == "SUCCESS") {
                    if ($decoded_plugin_status->pluginType == "trial") {
                        /* Plugin not activated notification */
                        echo '<div id="message" class="notice-dismiss-bin notice-dismiss-bin-php notice-warning notice is-dismissible"><p>The Freight Quotes plugin must have an active paid license to continue to use this feature.</p><button type="button" class="notice-dismiss notice-dismiss-bin"><span class="screen-reader-text notice-dismiss-bin">Dismiss this notice.</span></button></div>';
                    }
                }
            }
        }

        /**
         * Packages list.
         */
        public function en_packages_list($en_packages_list)
        {
            $en_packages_list_arr = array();
            if (isset($en_packages_list) && (!empty($en_packages_list))) {

                $en_packages_list_arr['options']['disable'] = 'Disable (default)';
                foreach ($en_packages_list as $key => $value) {
                    $value['pPeriod'] = (isset($value['pPeriod']) && ($value['pPeriod'] == "Month")) ? "mo" : $value['pPeriod'];
                    $value['pHits'] = is_numeric($value['pHits']) ? number_format($value['pHits']) : $value['pHits'];
                    $value['pCost'] = is_numeric($value['pCost']) ? number_format($value['pCost'], 2, '.', '') : $value['pCost'];
                    $trial = (isset($value['pSCAC']) && $value['pSCAC'] == "TR") ? "(Trial)" : "";
                    $en_packages_list_arr['options'][$value['pSCAC']] = esc_attr($value['pHits']) . "/" . esc_attr($value['pPeriod']) . " ($" . number_format(esc_attr($value['pCost'])) . ".00)" . " " . $trial;
                }
            }
            return $en_packages_list_arr;
        }

        /**
         * Ui display for next plan
         * @return string type
         */
        public function en_next_subcribed_package()
        {
            $this->en_next_subcribed_package = (isset($this->nextSubcribedPackage['nextToBeChargedStatus']) && $this->nextSubcribedPackage['nextToBeChargedStatus'] == 1) ? $this->nextSubcribedPackage['nextSubscriptionSCAC'] : "disable";
            return $this->en_next_subcribed_package;
        }

        /**
         * Plan details.
         */
        public function en_subscribed_package()
        {
            $en_subscribed_package = $this->subscribedPackage;
            $en_subscribed_package['packageDuration'] = (isset($en_subscribed_package['packageDuration']) && ($en_subscribed_package['packageDuration'] == "Month")) ? "mo" : $en_subscribed_package['packageDuration'];
            $en_subscribed_package['packageHits'] = is_numeric($en_subscribed_package['packageHits']) ? number_format($en_subscribed_package['packageHits']) : $en_subscribed_package['packageHits'];
            $en_subscribed_package['packageCost'] = is_numeric($en_subscribed_package['packageCost']) ? number_format($en_subscribed_package['packageCost'], 2, '.', '') : $en_subscribed_package['packageCost'];
            return $en_subscribed_package['packageHits'] . "/" . $en_subscribed_package['packageDuration'] . " ($" . number_format($en_subscribed_package['packageCost']) . ".00)";
        }

        /**
         * Response from smart street api and set in public attributes
         */
        function en_set_curl_res_attributes()
        {
            $this->subscriptionInfo = (isset($this->status['status']['subscriptionInfo'])) ? $this->status['status']['subscriptionInfo'] : "";
            $this->lastUsageTime = (isset($this->status['status']['lastUsageTime'])) ? $this->status['status']['lastUsageTime'] : "";
            $this->subscribedPackage = (isset($this->status['status']['subscribedPackage'])) ? $this->status['status']['subscribedPackage'] : "";
            $this->subscriptionStatus = (isset($this->status['status']['subscriptionInfo']['subscriptionStatus'])) ? ($this->status['status']['subscriptionInfo']['subscriptionStatus'] == 1) ? "yes" : "no" : "";
            $this->subscribedPackageHitsStatus = (isset($this->status['status']['subscribedPackageHitsStatus'])) ? $this->status['status']['subscribedPackageHitsStatus'] : "";
            $this->nextSubcribedPackage = (isset($this->status['status']['nextSubcribedPackage'])) ? $this->status['status']['nextSubcribedPackage'] : "";
            $this->statusRequestTime = (isset($this->status['statusRequestTime'])) ? $this->status['statusRequestTime'] : "";
        }

        /**
         * UI display Current Subscription & Current Usage
         * @param array type $status
         * @return array type
         */
        public function en_pallet_subscription($status = array())
        {
            if (isset($status) && (!empty($status)) && (is_array($status))) {
                $this->status = $status;
            } else { /* onload */
                $this->status = $this->en_customer_subscription_status();
                //          ============ All plans for 3dbin ===============
                $en_packages_list = $this->status['ListOfPackages']['Info'];
                if (isset($en_packages_list) && (!empty($en_packages_list)) && is_array($en_packages_list)) {
                    $en_packages_list = $this->en_packages_list($en_packages_list);
                } else {
                    $en_packages_list = array(
                        'options' => array(
                            'disable' => 'Disable (default)'
                        )
                    );
                }
            }
            if (isset($this->status['severity']) && ($this->status['severity'] == "SUCCESS")) {
                $this->en_set_curl_res_attributes();
                if ($this->lastUsageTime == '0000-00-00 00:00:00') {
                    $this->lastUsageTime = 'yyyy-mm-dd hrs-min-sec';
                }
                $subscription_time = (isset($this->subscriptionInfo) && (!empty($this->subscriptionInfo['subscriptionTime']))) ? "Start date: " . $this->en_formate_date_time($this->subscriptionInfo['subscriptionTime']) : "NA";
                $status_request_time = (isset($this->lastUsageTime) && (!empty($this->lastUsageTime))) ? '(' . $this->lastUsageTime . ')' : "NA";
                $expiry_time = (isset($this->subscriptionInfo) && (!empty($this->subscriptionInfo['expiryTime']))) ? "End date: " . $this->en_formate_date_time($this->subscriptionInfo['expiryTime']) : "NA";
                $en_subscribed_package = (isset($this->subscribedPackage) && (!empty($this->subscribedPackage))) ? $this->en_subscribed_package() : "NA";
                $consumedHits = (isset($this->subscribedPackageHitsStatus) && (!empty($this->subscribedPackageHitsStatus['consumedHits']))) ? $this->subscribedPackageHitsStatus['consumedHits'] . "/" : "0/";
                $consumed_hits_prcent = (isset($this->subscribedPackageHitsStatus) && (!empty($this->subscribedPackageHitsStatus['consumedHitsPrcent']))) ? $this->subscribedPackageHitsStatus['consumedHitsPrcent'] . "%" : "0%";
                $package_hits = (isset($this->subscribedPackageHitsStatus) && (!empty($this->subscribedPackageHitsStatus['packageHits']))) ? $this->subscribedPackageHitsStatus['packageHits'] : "/NA";
                $en_next_subcribed_package = (isset($this->nextSubcribedPackage) && (!empty($this->nextSubcribedPackage))) ? $this->en_next_subcribed_package() : "NA";
                if ($this->subscriptionStatus == "yes") {
                    $current_subscription = '<span id="en_subscribed_package">' . esc_attr($en_subscribed_package) . '</span>'
                        . '&nbsp;&nbsp;&nbsp; '
                        . '<span id="subscription_time">' . esc_attr($subscription_time) . '</span>'
                        . '&nbsp;&nbsp;&nbsp;'
                        . '<span id="expiry_time">' . esc_attr($expiry_time) . '</span>';
                    $current_usage = '<span id="en_subscribed_package_status">' . esc_attr($consumedHits) . esc_attr($package_hits) . '</span> '
                        . '&nbsp;&nbsp;&nbsp;'
                        . '<span id="consumed_hits_prcent">' . esc_attr($consumed_hits_prcent) . '</span>'
                        . '&nbsp;&nbsp;&nbsp;'
                        . '<span id="status_request_time">' . esc_attr($status_request_time) . '</span>';
                    $this->subscription_packages_response_for_pallet = "yes";
                    update_option("subscription_packages_response_for_pallet", $this->subscription_packages_response_for_pallet);
                } else {
                    $current_subscription = '<span id="en_subscribed_package">Your current subscription is expired.</span>';
                    $current_usage = 'Not available.';
                    $this->subscription_packages_response_for_pallet = "no";
                }
            } else {
                $current_subscription = '<span id="en_subscribed_package">Not subscribed.</span>';
                $current_usage = 'Not available.';
                //              ============== when no plan exist plan go to dislable =============
                $en_next_subcribed_package = "disable";
                $this->subscription_packages_response_for_pallet = "no";

                update_option("subscription_packages_response_for_pallet", $this->subscription_packages_response_for_pallet);
            }

            update_option("en_pallet_packaging_options_plans", $en_next_subcribed_package);

            $this->subscription_details = array('current_usage' => (isset($current_usage)) ? $current_usage : "",
                'current_subscription' => (isset($current_subscription)) ? $current_subscription : "",
                'en_next_subcribed_package' => (isset($en_next_subcribed_package)) ? $en_next_subcribed_package : "",
                'en_packages_list' => (isset($en_packages_list)) ? $en_packages_list : "",
                'subscription_packages_response_for_pallet' => (isset($this->subscription_packages_response_for_pallet)) ? $this->subscription_packages_response_for_pallet : "");

                return $this->subscription_details;
        }

        /**
         * new fields add for box sizing
         * @return array
         */
        function en_woo_addons_box_sizing_fields_arr($plugin_id)
        {
            $this->plugin_name = $plugin_id;
            $en_next_subcribed_package = $current_subscription = $current_usage = '';
            $en_packages_list = [];
            extract($this->en_pallet_subscription());
            $this->plugin_name = $plugin_id;
            
            if (!empty($this->status['statusRequestDomain']) && 'staging' == $this->status['statusRequestDomain']) {
                $auto_renew_desc = 'Plan change is only allowed on your production site. You are currently on the staging site. Kindly switch to your production environment to select a plan.';
            }else{
                $auto_renew_desc = '';
            }

            $settings = array(
                'services_quoted_en_woo_pallet_addons_packages' => array(
                    'title' => __('', 'woocommerce'),
                    'name' => __('', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'desc' => '',
                    'id' => 'woocommerce_services_quoted_en_woo_pallet_addons_packages',
                    'css' => '',
                    'default' => '',
                    'type' => 'title',
                ),
                'en_pallet_sizing_options_label_description' => array(
                    'name' => __('', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'type' => 'title',
                    'desc' => 'The Pallet Size feature calculates the optimal packaging solution based on your standard Pallet size. The solution is available graphically to assist order fulfillment. The next subscription begins when the current one expires or is depleted, which ever comes first. Refer to the <a target="_blank" href="https://eniture.com/woocommerce-pallet-packing/#documentation">User Guide</a> for more detailed information.',
                    'class' => 'hidden',
                    'id' => 'pallet_sizing_description'
                ),
                'en_pallet_packaging_options_plans' => array(
                    'name' => __('Auto-renew ', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'type' => 'select',
                    'default' => $en_next_subcribed_package,
                    'id' => 'en_pallet_packaging_options_plans',
                    'class' => 'en_pallet_packaging_options_plans',
                    'desc' => $auto_renew_desc,
                    'options' => $en_packages_list['options']
                ),
                'en_pallet_sizing_current_subscription' => array(
                    'name' => __('Current plan', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => $current_subscription,
                    'id' => "en_pallet_sizing_current_subscription"
                ),
                'en_pallet_sizing_current_usage' => array(
                    'name' => __('Current usage', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => $current_usage,
                    'id' => 'en_pallet_sizing_current_usage'
                ),
                'suspend_automatic_detection_of_pallets' => array(
                    'name' => __('Suspend use', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'type' => 'checkbox',
                    'id' => 'suspend_automatic_detection_of_pallets',
                    'desc' => __(' ', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'class' => 'suspend_automatic_detection_of_pallets'
                ),
                'en_pallet_sizing_plugin_name' => array(
                    'name' => __('', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'type' => 'text',
                    'class' => "hidden",
                    'placeholder' => $this->plugin_name,
                    'id' => "en_pallet_sizing_plugin_name",
                ),
                'en_pallet_sizing_subscription_status' => array(
                    'name' => __('', 'woocommerce-settings-en_woo_pallet_addons_packages_quotes'),
                    'type' => 'text',
                    'class' => "hidden",
                    'placeholder' => $this->subscriptionStatus,
                    'id' => "en_pallet_sizing_subscription_status",
                ),
                'section_end_quote' => array(
                    'type' => 'sectionend',
                    'id' => 'wc_settings_quote_section_end'
                ),
                'section_end_quote_box_sizing' => array(
                    'type' => 'sectionend',
                    'id' => 'wc_settings_quote_section_end_pallet_sizing'
                )
            );

            return $settings;
        }

        /**
         * Formate the given date time @param $datetime like in sow
         * @param datetime $datetime
         * @return string
         */
        public function en_formate_date_time($datetime)
        {
            $date = date_create($datetime);
            return date_format($date, "M. d, Y");
        }

        /**
         * en_get_arr_filterd_val function see for if @param $arr_val type is array reset value return
         * @param array or string type $arr_val
         * @return string type
         */
        public function en_get_arr_filterd_val($arr_val)
        {
            return (isset($arr_val) && (!empty($arr_val))) ? (is_array($arr_val)) ? reset($arr_val) : $arr_val : "";
        }

        /**
         * Popup notification for using notification show during disable to plan through using jquery
         * @return html
         */
        public function en_woo_addons_popup_notifi_disabl_to_plan_pallet()
        {
            ?>
            <div id="plan_confirmation_popup" class="en_notification_disable_to_plan_overlay_pallet"
                 style="display: none;">
                <div class="en_pallet_notifi_disabl_to_plan_pallet">
                    <h2 class="del_hdng">
                        <?php _e('Note!', 'eniture-technology'); ?>
                    </h2>
                    <p class="confirmation_p">
                        <?php _e('Note! You have elected to enable the Standard Pallet feature. By confirming this election you
                        will be charged for the <span id="selected_plan_popup_pallet">[plan]</span> plan. To ensure
                        service continuity the plan will automatically renew each month, or when the plan is depleted,
                        whichever comes first. You can change which plan is put into effect on the next renewal date by
                        updating the selection on this page at anytime.', 'eniture-technology');
                        ?>
                    </p>
                    <div class="confirmation_btns">
                        <a style="cursor: pointer" class="cancel_plan"><?php _e('Cancel', 'eniture-technology'); ?></a>
                        <a style="cursor: pointer" class="confirm_plan"><?php _e('OK', 'eniture-technology'); ?></a>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Array merge after specific index
         * @param array $array
         * @param index of array $key
         * @param array $new
         * @return array
         */
        public function en_addon_array_insert_after(array $array, $key, array $new)
        {
            if (isset($key) && in_array($key, array_keys($array))) {
                $keys = array_keys($array);
                $index = array_search($key, $keys);
                $pos = false === $index ? count($array) : $index + 1;
                $array = array_merge(array_slice($array, 0, $pos), $new, array_slice($array, $pos));
            }
            return $array;
        }

        /**
         * Append pallet tab settings.
         */
        public function en_woo_pallet_addons_sections($sections, $plugin_id)
        {
            $this->sections = $sections;
            $this->en_plugins_dependencies = $this->en_plugins_dependencies();

            if (isset($this->en_plugins_dependencies[$plugin_id])) {
                $plugin_detail = $this->en_plugins_dependencies[$plugin_id];
                $addons = $plugin_detail['addons'];
                if ($addons['pallet_packaging_addon']['active'] === true) {
                    $wh_tab_key = '';
                    foreach ($this->sections as $key => $value) {
                        if (strpos($value, 'Warehouses') !== false) {
                            $wh_tab_key = $key;
                            break;
                        }
                    }

                    $key = in_array('shipping-rules', array_keys($this->sections)) ? 'shipping-rules' : (!empty($wh_tab_key) ? $wh_tab_key : key(array_slice($this->sections, -2, 1)));
                    $new = array('section-pallet' => 'Pallets');
                    $this->sections = $this->en_addon_array_insert_after($this->sections, $key, $new);
                }
            }
            return $this->sections;
        }

        /**
         * box sizing table html
         * @return type
         */
        public function en_pallet_table()
        {
            $this->spackaging_recursive = 'spackaging_template';
            $spackaging_recursive = apply_filters('en_spackaging_recursive', []);
            if (!empty($spackaging_recursive) && in_array($this->spackaging_recursive, $spackaging_recursive)) {
                return;
            }

            add_filter('en_spackaging_recursive', [$this, 'en_spackaging_recursive'], 10, 1);

            EnEnp::en_load();
        }
    }

    new EnPackagingTab();
}


