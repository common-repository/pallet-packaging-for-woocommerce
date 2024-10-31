jQuery(document).ready(function () {
    jQuery("#en_pallet_sizing_plugin_name").closest('tr').addClass('en_pallet_sizing_plugin_name_style');
    jQuery("#en_pallet_sizing_subscription_status").closest('tr').addClass('en_pallet_sizing_subscription_status_style');
    jQuery("#en_pallet_sizing_current_subscription").closest('tr').addClass('en_pallet_sizing_current_subscription');
    jQuery("#en_pallet_sizing_current_usage").closest('tr').addClass('en_pallet_sizing_current_usage');

    /**
     * When user switch from disable to plan popup hide
     * @returns {jQuery}
     */
    var en_woo_pallet_addons_popup_notifi_disabl_to_plan_hide = function () {
        return jQuery(".en_notification_disable_to_plan_overlay_pallet").css({
            display: 'none',
            visibility: "hidden",
            opacity: "0"
        });
    };

    /**
     * when user switch from disable to plan popup show
     * @returns {jQuery}
     */
    var en_woo_addons_popup_notifi_disabl_to_plan_show_pallet = function () {
        var selected_plan = jQuery("#en_pallet_packaging_options_plans").find("option:selected").text();
        jQuery(".en_notification_disable_to_plan_overlay_pallet").last().find("#selected_plan_popup_pallet").text(selected_plan);
        return jQuery(".en_notification_disable_to_plan_overlay_pallet").css({
            display: 'block',
            visibility: "visible",
            opacity: "1"
        });
    };

    /**
     * When user from disable to plan popup actions.
     * @returns {undefined}
     */
    jQuery(".cancel_plan").on('click', function () {
        en_woo_pallet_addons_popup_notifi_disabl_to_plan_hide();
        jQuery('#en_pallet_packaging_options_plans').prop('selectedIndex', 0);
        return false;
    });

    /**
     * Confirm click function.
     */
    jQuery(".confirm_plan").on('click', function () {
        var params = "";
        en_woo_pallet_addons_popup_notifi_disabl_to_plan_hide();
        var monthly_pckg = jQuery("#en_pallet_packaging_options_plans").val();
        var plugin_name = jQuery("#en_pallet_sizing_plugin_name").attr("placeholder");

        var data = {
            plugin_name: plugin_name,
            selected_plan: monthly_pckg,
            action: 'en_woo_pallet_addons_upgrade_plan_submit'
        };
        params = {
            loading_id: "en_pallet_packaging_options_plans",
            message_id: "plan_to_disable_message",
            disabled_id: "en_pallet_packaging_options_plans",
            message_ph: " Success! Your choice of plans has been updated. "

        };

        pallet_ajax_request(params, data, pallet_monthly_packg_response);
        return false;
    });

    /**
     * Plan subscription.
     */
    var pallet_monthly_packg_response = function (params, response) {
        var pars_response = JSON.parse(response);
        if (pars_response.severity == "SUCCESS") {
            if (pars_response.subscription_packages_response_for_pallet == "yes") {

                jQuery(".en_pallet_sizing_current_subscription .description").html(pars_response.current_subscription);
                jQuery(".en_pallet_sizing_current_usage .description").html(pars_response.current_usage);
                jQuery("#en_pallet_sizing_subscription_status").attr("placeholder", "yes");
            }

            if (typeof params.message_ph != 'undefined' && params.message_ph.length > 0) {
                jQuery("#pallet_sizing_description-description p:nth-child(1)").first().after('<div class="alert-plan-messages alert-success pallet_package_msg">  ' + params.message_ph + '   </div>');
            }

            en_suspend_automatic_detection_pallet();

        } else {
            jQuery("#pallet_sizing_description-description").after(' <div class="notice notice-error is-dismissible pallet_package_msg" ><p>  ' + pars_response.Message + '  </p></div>');

            jQuery(".pallet-detail p:nth-child(1)").first().after(' <div class="alert-plan-messages alert-danger pallet_package_msg" >  ' + pars_response.Message + '  </div>');

            jQuery('#en_pallet_packaging_options_plans').prop('selectedIndex', 0);
        }
        setTimeout(function () {
           jQuery('.pallet_package_msg').fadeOut('fast');
        }, 3000);
    };


    /**
     * Monthly package select actions.
     * @param string monthly_pckg
     * @returns boolean
     */
    var en_woo_addons_monthly_packg_box = function (monthly_pckg) {
        jQuery(".pallet_package_msg").remove();
        var plugin_name = jQuery("#en_pallet_sizing_plugin_name").attr("placeholder");
        var data = {
            plugin_name: plugin_name,
            selected_plan: monthly_pckg,
            action: 'en_woo_pallet_addons_upgrade_plan_submit'
        };
        var params = "";

        if (window.existing_plan_box == "disable") {
            en_woo_addons_popup_notifi_disabl_to_plan_show_pallet();
            return false;
        } else if (monthly_pckg == "disable") {

            params = {
                loading_id: "en_pallet_packaging_options_plans",
                disabled_id: "en_pallet_packaging_options_plans",
                message_ph: "You have disabled the Standard Packaging plugin. The plugin will stop working when the current plan is depleted or expires."
            };
        } else {
            params = {
                loading_id: "en_pallet_packaging_options_plans",
                disabled_id: "en_pallet_packaging_options_plans",
                message_ph: " Success! Your choice of plans has been updated. "
            };
        }

        pallet_ajax_request(params, data, pallet_monthly_packg_response);
    };

    /**
     * Suspend plan.
     */
    var en_suspend_automatic_detection_pallet = function (params, response) {
        var selected_plan = jQuery("#en_pallet_packaging_options_plans").val();
        window.existing_plan_box = selected_plan;
        var suspend_automatic = jQuery("#suspend_automatic_detection_of_pallets").prop("checked");
        var subscription_status = jQuery("#en_pallet_sizing_subscription_status").attr("placeholder");

        if (subscription_status == "yes") {

            jQuery("#suspend_automatic_detection_of_pallets").prop('disabled', false);
            jQuery("label[for='en_pallet_packaging_options_plans']").text("Auto-renew");
            if (suspend_automatic) {

                jQuery(".en_add_box .add_box_packaging_click").addClass("disable");
            } else {
                jQuery(".en_add_box .add_box_packaging_click").removeClass("disable");

            }
        } else {
            jQuery(".en_add_box .add_box_packaging_click").addClass("disable");
            jQuery("label[for='en_pallet_packaging_options_plans']").text("Select a plan");
            jQuery("#suspend_automatic_detection_of_pallets").prop({checked: false, disabled: true});
        }
    };

    /**
     * existing user plan for box sizing.
     * @param {type} params
     * @param {type} data
     * @param {type} call_back_function
     * @returns {undefined}
     */
    en_suspend_automatic_detection_pallet();

    /**
     * Ajax common class for pallet addon.
     */
    function pallet_ajax_request(params, data, call_back_function) {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                (typeof params.loading_id != 'undefined' && params.loading_id.length > 0) ? jQuery("#" + params.loading_id).css('background', 'rgba(255, 255, 255, 1) url(' + sp_plugins_url + '/pallet-packaging-for-woocommerce/assets/images/processing.gif) no-repeat scroll 50% 50%') : "";
                (typeof params.disabled_id != 'undefined' && params.disabled_id.length > 0) ? jQuery("#" + params.disabled_id).prop({disabled: true}) : "";
                (typeof params.loading_msg != 'undefined' && params.loading_msg.length > 0 && typeof params.disabled_id != 'undefined' && params.disabled_id.length > 0) ? jQuery("#" + params.disabled_id).after(params.loading_msg) : "";
            },
            success: function (response) {
                console.log(response);
                jQuery('.notice-dismiss-bin-php').remove();
                (typeof params.loading_id != 'undefined' && params.loading_id.length > 0) ? jQuery("#" + params.loading_id).focus() : "";
                (typeof params.disabled_id != 'undefined' && params.disabled_id.length > 0) ? jQuery("#" + params.disabled_id).prop({disabled: false}) : "";
                (typeof params.loading_msg != 'undefined' && params.loading_msg.length > 0 && typeof params.disabled_id != 'undefined' && params.disabled_id.length > 0) ? jQuery("#" + params.disabled_id).next('.suspend-loading').remove() : "";
                (typeof params.loading_id != 'undefined' && params.loading_id.length > 0) ? jQuery("#" + params.loading_id).css('background', '#fff url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E") no-repeat right 5px top 55%') : "";
                return call_back_function(params, response);
            },
            error: function () {
                console.log('error');
            }
        });
    }

    /**
     * plan change function for pallet packaging.
     */
    jQuery("#en_pallet_packaging_options_plans").on('change', function () {
        en_woo_addons_monthly_packg_box(jQuery(this).val());
        return false;
    });

    /**
     * plan change loading message.
     */
    var suspend_automatic_detection_params = function () {
        return {
            loading_msg: " <span class='suspend-loading'>Loading ...</span>",
            disabled_id: "suspend_automatic_detection_of_pallets",
        };
    };

    /**
     * plan suspend.
     */
    var suspend_automatic_detection_anable = function () {
        return {
            suspend_automatic_detection_of_pallets: "yes",
            action: "en_suspend_automatic_detection_pallet",
        };
    };

    /**
     * plan disabled.
     */
    var suspend_automatic_detection_disabled = function () {
        var always_include_threed = jQuery(".en_woo_pallet_addons_always_include_threed_fee").attr("id");
        return {
            suspend_automatic_detection_of_pallets: "no",
            action: "en_suspend_automatic_detection_pallet"
        };
    };

    jQuery("#suspend_automatic_detection_of_pallets").on('click', function () {
        var data = "";
        var params = "";
        if (this.checked) {
            data = suspend_automatic_detection_anable();
            params = suspend_automatic_detection_params();
        } else {
            data = suspend_automatic_detection_disabled();
            params = suspend_automatic_detection_params();
        }
        pallet_ajax_request(params, data, en_suspend_automatic_detection_pallet);
    });

});