jQuery(document).ready(function () {
    jQuery('#en_enable_pallet').closest('.en_popup_enp_input_field').addClass('en_enable_pallet');
    jQuery('.en_popup_enp_input_field').find('.err').remove();
    jQuery('.en_pallet_sizing_current_subscription').find('.err').remove();
    jQuery('.en_pallet_sizing_current_usage').find('.err').remove();
    jQuery('.en_close_popup_enp').on('click', function () {
        en_popup_enp_overly_hide();
    });
    // negative value not allowed
    jQuery("#en_pallet_length,#en_pallet_width,#en_pallet_max_height,#en_pallet_height,#en_pallet_max_weight,#en_pallet_weight").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[0-9\d\.\s]+$/i)) return false;
    });
    jQuery('.en_popup_enp_form .en_enp_input_field').each(function () {
        this.style.setProperty('width', '100%', 'important');
    });
    jQuery('#en_enable_pallet').each(function () {
        this.style.setProperty('width', '1rem', 'important');
    });
    //change height function
    jQuery('#en_pallet_max_height').on('change', function () {
        var max_height= jQuery(this).val() ? parseFloat(jQuery(this).val()):0;
        var pallet_height= jQuery('#en_pallet_height').val() ? parseFloat(jQuery('#en_pallet_height').val()):0;
        var total_height= max_height+pallet_height;
        jQuery('#en_pallet_w_max_height').val(total_height);
    });
    jQuery('#en_pallet_height').on('change', function () {
        var max_height=  jQuery('#en_pallet_max_height').val() ? parseFloat(jQuery('#en_pallet_max_height').val()):0;
        var pallet_height= jQuery(this).val() ? parseFloat(jQuery(this).val()):0;
        var total_height= max_height+pallet_height;
        jQuery('#en_pallet_w_max_height').val(total_height);
    });
    //change weight function
    jQuery('#en_pallet_max_weight').on('change', function () {
        var max_height= jQuery(this).val() ? parseFloat(jQuery(this).val()):0;
        var pallet_height= jQuery('#en_pallet_weight').val() ? parseFloat(jQuery('#en_pallet_weight').val()):0;
        var total_height= max_height+pallet_height;
        jQuery('#en_pallet_w_max_weight').val(total_height);
    });
    jQuery('#en_pallet_weight').on('change', function () {
        var max_height=  jQuery('#en_pallet_max_weight').val() ? parseFloat(jQuery('#en_pallet_max_weight').val()):0;
        var pallet_height= jQuery(this).val() ? parseFloat(jQuery(this).val()):0;
        var total_height= max_height+pallet_height;
        jQuery('#en_pallet_w_max_weight').val(total_height);
    });
    jQuery('.en_ppfw_enp_btn').on('click', function (e) {
        e.preventDefault();

        var validate = en_ppfw_validate_input('.en_popup_enp_form');
        if (validate === false) {
            jQuery('.en_popup_enp_form').delay(200).animate({scrollTop: 0}, 300);
            return false;
        }

        var tab = get_parameter_by_ppfw('tab');
        var en_post_data = {
            'tab': tab,
            'action': 'en_ppfw_enp_save_form_data',
            'en_post_data': jQuery(".en_popup_enp_form input").serialize()
        };

        var en_params = {
            en_ajax_loading_msg_btn: '.en_ppfw_enp_btn',
        };

        en_ajax_request(en_params, en_post_data, en_ppfw_enp_save_form_data);
        jQuery('html, body').animate({
            scrollTop: jQuery(".subsubsub").offset().top
        }, 2000);
    });
});

/**
 * Eniture Validation Form JS
 */
if (typeof en_ppfw_validate_input != 'function') {
    function en_ppfw_validate_input(form_id) {
        var has_err = true;
        jQuery(form_id + " input[type='text']").each(function () {

            var input = jQuery(this).val();
            var response = en_validate_string(input);
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');

            var en_error_element = jQuery(this).parent().find('.en_enp_error,.en_connection_error');
            jQuery(en_error_element).html('');

            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';

            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(en_error_element).html(errorText);
            }
            has_err = (response != true && optional == 0) ? false : has_err;
        });
        return has_err;
    }
}

/**
 * Validate Input String
 */
if (typeof en_enp_available != 'function') {
    function en_enp_available(obj) {
        var last_text = jQuery(obj).text();
        var next_text = '';
        var db_text = '';
        if (last_text == 'Yes') {
            next_text = 'No';
            db_text = 'off';
        } else {
            next_text = 'Yes';
            db_text = 'on';
        }
        jQuery(obj).text(next_text);

        var enp_id = jQuery(obj).attr('data-available_id');
        var en_post_data = {
            'action': 'en_ppfw_enp_available_updated',
            'db_text': db_text,
            'en_enp_id': enp_id
        };

        var en_params = {};

        en_ajax_request(en_params, en_post_data, en_ppfw_enp_available_updated);
    }
}

/**
 * Validate Input String
 */
if (typeof en_validate_string != 'function') {
    function en_validate_string(string) {
        if (string == '')
            return 'empty';
        else
            return true;

    }
}

/**
 * Variable exist
 */
if (typeof en_is_var_exist != 'function') {
    function en_is_var_exist(index, item) {
        return typeof item[index] != 'undefined' ? true : false;
    }
}

/**
 * Only alpha allow
 */
if (typeof en_alpha_only != 'function') {
    function en_alpha_only(event) {
        var key = event.keyCode;
        return ((key >= 65 && key <= 90) || key == 8);
    }
}

/**
 * Round integer two number after decimal
 */
if (typeof en_round_two_digits_after_decimal != 'function') {
    function en_round_two_digits_after_decimal(el) {
        var v = parseFloat(el.value);
        el.value = (isNaN(v)) ? '' : v.toFixed(2);
    }
}

/**
 * Available updated
 */
if (typeof en_ppfw_enp_available_updated != 'function') {
    function en_ppfw_enp_available_updated(params, response) {
        var data = JSON.parse(response);
        en_enp_notification(data);
    }
}

/**
 * Enp data save in to db
 */
if (typeof en_ppfw_enp_save_form_data != 'function') {
    function en_ppfw_enp_save_form_data(params, response) {

        var data = JSON.parse(response);

        if (en_is_var_exist('severity', data) && data['severity'] == 'success') {
            jQuery(data['target_enp']).replaceWith(data['html']);
            en_popup_enp_overly_hide();
            en_enp_notification(data);
        } else if (en_is_var_exist('severity', data) && data['severity'] == 'error') {
            jQuery('.en_popup_enp_form').delay(200).animate({scrollTop: 0}, 300);
            jQuery('.en_enp_error_message span').text(data['message']);
            en_show_errors('.en_enp_error_message');
        }
    }
}

/**
 * Enp popup enp form reset
 * @param enClassId
 */
if (typeof en_popup_enp_reset != 'function') {
    function en_popup_enp_reset() {
        jQuery('.en_enp_error').text('');
        jQuery('#en_enp_form_reset_me')[0].reset();
        jQuery(jQuery(".bootstrap-tagsinput").find("span[data-role=remove]")).trigger("click");
        jQuery('#en_enp_city').closest('div').show();
        jQuery('.en_multi_city_change').closest('div').hide();
        jQuery('.en_popup_enp_form').delay(200).animate({scrollTop: 0}, 300);
        jQuery('.en_enp_error_message').hide();
    }
}

/**
 * Show errors when we get adresss on change zip code in pallethouses tab
 * @param enClassId
 */
if (typeof en_show_errors != 'function') {
    function en_show_errors(en_class_id) {
        jQuery(en_class_id).show('slow');
        setTimeout(function () {
            jQuery(en_class_id).hide('slow');
        }, 5000);
    }
}

/**
 * Filter City option
 */
if (typeof en_save_city != 'function') {
    function en_save_city(e) {
        var city = jQuery(e).val();
        jQuery('#en_enp_city').val(city);
    }
}

/**
 * When enp row deleted
 */
if (typeof en_action_enp_deleted != 'function') {
    function en_action_enp_deleted(params, response) {
        var data = JSON.parse(response);
        jQuery(data['target_enp']).html(data['html']);
        en_enp_notification(data);
        en_popup_confirmation_enp_delete_hide();
    }
}

/**
 * Enp add btn click
 */
if (typeof en_show_popup_enp != 'function') {
    function en_show_popup_enp(e) {
        e.preventDefault();
        // First reset the enp popup form
        en_popup_enp_reset();
        jQuery('#en_enp_id').val('');
        jQuery('#en_enp_type').val('pship');
        jQuery('#en_popup_enp_heading').text('Pallet Properties');

        en_popup_enp_overly_show();
    }
}

/**
 * Enp edit btn click
 */
if (typeof en_ppfw_enp_edit != 'function') {
    function en_ppfw_enp_edit(e, data, en_enp_type) {
        e.preventDefault();
        en_show_popup_enp(e);

        var en_enable_pallet = jQuery(data).closest('tr').find('.en_available_link').text();
        var en_enp_db_data = jQuery(data).closest('tr').find('.en_enp_db_data').text();
        var en_enp_db_data_parsed = JSON.parse(en_enp_db_data);

        var en_enp_custom_data = jQuery(data).closest('tr').find('.en_enp_custom_data').text();
        var en_enp_custom_data_parsed = JSON.parse(en_enp_custom_data);

        jQuery.each(en_enp_custom_data_parsed, function (index, item) {
            var en_item_id = typeof item['id'] !== undefined ? item['id'] : '';
            var en_item_name = typeof item['name'] !== undefined ? item['name'] : '';
            var en_item_type = typeof item['type'] !== undefined ? item['type'] : '';
            var en_item_get_value = typeof en_enp_db_data_parsed[en_item_name] !== undefined ? en_enp_db_data_parsed[en_item_name] : '';

            switch (en_item_type) {
                case "en_input_field":
                    if (index == 'en_local_delivery_postal_code' || index == 'en_in_store_pickup_postal_code') {
                        jQuery("#" + en_item_id).tagsinput('add', en_item_get_value);
                    } else {
                        jQuery("#" + en_item_id).val(en_item_get_value);
                    }
                    break;
                case "en_input_hidden":
                    jQuery("#" + en_item_id).val(en_item_get_value);
                    break;
                case "en_checkbox":
                    en_enable_pallet == 'Yes' ?
                        jQuery("#" + en_item_id).prop("checked", true) :
                        jQuery("#" + en_item_id).prop("checked", false);

                    break;
            }
        });
    }
}

/**
 * Get url detail
 */
if (typeof get_parameter_by_ppfw != 'function') {
    function get_parameter_by_ppfw(name) {
        name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }
}

/**
 * Enp row delete confirmation
 */
if (typeof en_ppfw_enp_delete != 'function') {
    function en_ppfw_enp_delete(e, data, en_enp_type, en_enp_id) {
        e.preventDefault();
        en_popup_confirmation_enp_delete_show();

        jQuery('.en_close_popup_enp').on('click', function () {
            en_enp_id = false;
        });

        jQuery('.en_enp_cancel_delete').on('click', function () {
            en_enp_id = false;
            en_popup_confirmation_enp_delete_hide();
        });

        jQuery('.en_enp_confirm_delete').on('click', function () {
            if (en_enp_id != false) {
                en_enp_confirm_delete(data, en_enp_type, en_enp_id);
            }
        });
    }
}

/**
 * Enp row delete
 */
if (typeof en_enp_confirm_delete != 'function') {
    function en_enp_confirm_delete(data, en_enp_type, en_enp_id) {

        var tab = get_parameter_by_ppfw('tab');
        var en_post_data = {
            'tab': tab,
            'action': 'en_ppfw_enp_delete_row',
            'en_enp_id': en_enp_id,
            'en_enp_type': (en_enp_type) ? 'pallethouse' : 'pship'
        };

        var en_params = {
            en_ajax_loading_msg_ok_btn: '.en_enp_confirm_delete',
        };

        en_ajax_request(en_params, en_post_data, en_action_enp_deleted);
    }
}
/**
 * Enp notification
 */
if (typeof en_enp_notification != 'function') {
    function en_enp_notification(data) {
        if (data['message'].length > 0) {
            jQuery('.en_popup_enp_form').delay(200).animate({scrollTop: 0}, 300);
            jQuery('.en_enp_success_message span').text(data['message']);
            en_show_errors('.en_enp_success_message');
        }
    }
}

/**
 * Enp popup hide
 */
if (typeof en_popup_enp_overly_hide != 'function') {
    function en_popup_enp_overly_hide() {
        jQuery('.en_popup_enp_overly').css({'opacity': 0, 'visibility': 'hidden'});
    }
}

/**
 * Enp popup show
 */
if (typeof en_popup_enp_overly_show != 'function') {
    function en_popup_enp_overly_show() {
        jQuery('.en_popup_enp_overly').css({'opacity': 1, 'visibility': 'visible'});
    }
}

/**
 * Enp popup hide
 */
if (typeof en_popup_confirmation_enp_delete_hide != 'function') {
    function en_popup_confirmation_enp_delete_hide() {
        jQuery('.confirmation_enp_delete').css({'opacity': 0, 'visibility': 'hidden'});
    }
}

/**
 * Enp popup show
 */
if (typeof en_popup_confirmation_enp_delete_show != 'function') {
    function en_popup_confirmation_enp_delete_show() {
        jQuery('.confirmation_enp_delete').css({'opacity': 1, 'visibility': 'visible'});
    }
}

/**
 * Ajax common resource
 * @param params.en_ajax_loading_id The loading Path Id
 * @param params.en_ajax_disabled_id The disabled Path Id
 * @param params.en_ajax_loading_msg_btn The message show on button during load
 */
if (typeof en_ajax_request != 'function') {
    function en_ajax_request(params, data, call_back_function) {

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            beforeSend: function () {
            },
            success: function (response) {
                return call_back_function(params, response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
}
