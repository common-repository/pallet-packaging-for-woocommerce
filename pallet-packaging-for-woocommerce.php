<?php
/*
  Plugin Name: Pallet Packaging for WooCommerce
  Plugin URI: https://eniture.com/products/
  Description: Identifies the optimal packaging solution using your standard pallet. For exclusive use with Eniture Technology&#x27;s LTL Freight Quotes plugins.
  Version: 1.1.14
  Author: Eniture Technology
  Author URI: http://eniture.com/
  Text Domain: eniture-technology
  WC requires at least: 6.4
  WC tested up to: 9.2.3
  License: GPL version 2 or later - http://www.eniture.com/
 */

//  Not allowed to access directly
use EnPpfwPallethouse\EnPpfwPallethouse;

if (!defined('ABSPATH')) {
    exit;
}


define('EN_PPFW_DIR_FILE', plugin_dir_url(__FILE__));

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
});

/**
 * Get Host
 * @param type $url
 * @return type
 */
if (!function_exists('en_get_host')) {

    function en_get_host($url)
    {
        $parseUrl = parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }

}

/**
 * Get Domain Name
 */
if (!function_exists('en_pallet_get_domain')) {

    function en_pallet_get_domain()
    {
        global $wp;
        $url = home_url($wp->request);
        return en_get_host($url);
    }
}
/**
 * pallet packaging version check
 * @param array type $upgrader_object
 * @param array type $options
 */
function en_pallet_packaging_version_check()
{
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $index = 'pallet-packaging-for-woocommerce/pallet-packaging-for-woocommerce.php';
    $plugin_info = get_plugins();
    $plugin_version = (isset($plugin_info[$index]['Version'])) ? $plugin_info[$index]['Version'] : '';
    $get_current_version= get_option('en_pallet_packaging_version');

    if($get_current_version!= $plugin_version)
    {
        $en_data = [];
        $en_pship_list = EnPpfwPallethouse::get_data(['enp' => 'pship']);
        add_pallet_fee();
        add_max_w_height();
        add_max_w_weight();
        \EnEnp::en_arrange_enp_table_row($en_pship_list, $en_data, 0);
        update_option('en_pallet_packaging_version', $plugin_version);
    }

}

add_action('init', 'en_pallet_packaging_version_check');

require_once('adding-pallets/includes/pallets-per-product.php');
require_once('adding-pallets/adding-pallets.php');
require_once 'adding-pallets/template/adding-pallets-template.php';
require_once 'adding-pallets/includes/adding-pallets-ajax.php';
new \EnPpfwEnpAjax\EnPpfwEnpAjax();
require_once('adding-pallets/db/adding-pallets-db.php');
new \EnPpfwPallethouse\EnPpfwPallethouse();
require_once('pallet-plugin-details.php');
require_once('pallet-packaging.php');
require_once('packaging-tab.php');
require_once('pallet-addons-curl-request.php');
require_once('pallet-addons-ajax-request.php');

/**
 * App install hook
 */
register_activation_hook(__FILE__, 'create_pallet_table');

function create_pallet_table($network_wide = null)
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            global $wpdb;
            $en_table_name = $wpdb->prefix . 'en_pallets';
            if ($wpdb->query("SHOW TABLES LIKE '" . $en_table_name . "'") === 0) {
                $en_created_table = 'CREATE TABLE ' . $en_table_name . '( 
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                nickname varchar(255) NOT NULL,
                length varchar(255) NOT NULL,
                width varchar(255) NOT NULL,
                max_height varchar(255) NOT NULL,
                pallet_height varchar(255) NOT NULL,
                max_weight varchar(255) NOT NULL,
                pallet_weight varchar(255) NOT NULL,
                available varchar(20) NOT NULL,
                PRIMARY KEY  (id)        
                )';
                dbDelta($en_created_table);
            }

            $row = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'pallet_fee'");
            if (!(isset($row->Field) && $row->Field == 'pallet_fee')) {
                $wpdb->query("ALTER TABLE " . $en_table_name . " ADD pallet_fee varchar(255) NOT NULL DEFAULT 0");

            }
            restore_current_blog();
        }

    } else {
        global $wpdb;
        $en_table_name = $wpdb->prefix . 'en_pallets';
        if ($wpdb->query("SHOW TABLES LIKE '" . $en_table_name . "'") === 0) {
            $en_created_table = 'CREATE TABLE ' . $en_table_name . '( 
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                nickname varchar(255) NOT NULL,
                length varchar(255) NOT NULL,
                width varchar(255) NOT NULL,
                max_height varchar(255) NOT NULL,
                pallet_height varchar(255) NOT NULL,
                max_weight varchar(255) NOT NULL,
                pallet_weight varchar(255) NOT NULL,
                available varchar(20) NOT NULL,
                PRIMARY KEY  (id)        
                )';
            dbDelta($en_created_table);
        }

        $row = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'pallet_fee'");
        if (!(isset($row->Field) && $row->Field == 'pallet_fee')) {
            $wpdb->query("ALTER TABLE " . $en_table_name . " ADD pallet_fee varchar(255) NOT NULL DEFAULT 0");
        }
    }

}


if (!function_exists('add_pallet_fee')) {

    function add_pallet_fee($network_wide = null)
    {
        if (is_multisite() && $network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;
                $en_table_name = $wpdb->prefix . 'en_pallets';
                $row = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'pallet_fee'");
                if (!(isset($row->Field) && $row->Field == 'pallet_fee')) {
                    $wpdb->query("ALTER TABLE " . $en_table_name . " ADD pallet_fee varchar(255) NOT NULL DEFAULT 0");
                }
                restore_current_blog();
            }
        } else {
            global $wpdb;
            $en_table_name = $wpdb->prefix . 'en_pallets';
            $row = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'pallet_fee'");
            if (!(isset($row->Field) && $row->Field == 'pallet_fee')) {
                $wpdb->query("ALTER TABLE " . $en_table_name . " ADD pallet_fee varchar(255) NOT NULL DEFAULT 0");
            }
        }
    }
}

if (!function_exists('add_max_w_height')) {

    function add_max_w_height($network_wide = null)
    {
        if (is_multisite() && $network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;
                $en_table_name = $wpdb->prefix . 'en_pallets';
                $row = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'w_max_height'");
                if (!(isset($row->Field) && $row->Field == 'w_max_height')) {
                    $wpdb->query("ALTER TABLE " . $en_table_name . " ADD w_max_height varchar(255) NOT NULL DEFAULT 0");
                }
                restore_current_blog();
            }
        } else {
            global $wpdb;
            $en_table_name = $wpdb->prefix . 'en_pallets';
            $row = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'w_max_height'");
            if (!(isset($row->Field) && $row->Field == 'w_max_height')) {
                $wpdb->query("ALTER TABLE " . $en_table_name . " ADD w_max_height varchar(255) NOT NULL DEFAULT 0");
            }
        }
    }
}

if (!function_exists('add_max_w_weight')) {

    function add_max_w_weight($network_wide = null)
    {
        if (is_multisite() && $network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;
                $en_table_name = $wpdb->prefix . 'en_pallets';
                $row = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'w_max_weight'");
                if (!(isset($row->Field) && $row->Field == 'w_max_weight')) {
                    $wpdb->query("ALTER TABLE " . $en_table_name . " ADD w_max_weight varchar(255) NOT NULL DEFAULT 0 ");
                }
                restore_current_blog();
            }
        } else {
            global $wpdb;
            $en_table_name = $wpdb->prefix . 'en_pallets';
            $row = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'w_max_weight'");
            if (!(isset($row->Field) && $row->Field == 'w_max_weight')) {
                $wpdb->query("ALTER TABLE " . $en_table_name . " ADD w_max_weight varchar(255) NOT NULL DEFAULT 0");
            }
        }
    }
}

/**
 * Load script
 */
if (!function_exists('en_pallet_script')) {

    function en_pallet_script()
    {
        wp_enqueue_script('en_pallet_script', plugin_dir_url(__FILE__) . '/assets/js/standard-packaging-script.js', array(), '1.0.3');
        wp_localize_script('en_pallet_script', 'script', array(
            'pluginsUrl' => plugins_url(),
        ));

        wp_enqueue_script('en_adding_pallets_script', plugin_dir_url(__FILE__) . '/adding-pallets/assets/js/adding-pallets.js', array(), '1.0.1');
        wp_localize_script('en_adding_pallets_script', 'script', array(
            'pluginsUrl' => plugins_url(),
        ));

        wp_register_style('en_pallet_style', plugin_dir_url(__FILE__) . '/assets/css/standard-packaging-style.css', false, '1.0.2');
        wp_enqueue_style('en_pallet_style');

        wp_register_style('en_adding_pallets_style', plugin_dir_url(__FILE__) . '/adding-pallets/assets/css/adding-pallets.css', false, '1.0.2');
        wp_enqueue_style('en_adding_pallets_style');
    }

    add_action('admin_enqueue_scripts', 'en_pallet_script');
}

/**
 * globally script variable
 */
if (!function_exists('pallet_admin_inline_js')) {

    function pallet_admin_inline_js()
    {
        ?>
        <script>
            var sp_plugins_url = "<?php echo plugins_url(); ?>";
        </script>
        <?php
    }

    add_action('admin_print_scripts', 'pallet_admin_inline_js');
}
/**
 * add pallet price
 */
add_filter('en_pallet_price', 'en_add_price_cost', 1);
function en_add_price_cost($rate) {
    global $wpdb;
    $pallet_cost = 0;
    $bin_data =  json_decode($rate['meta_data']['standard_packaging']);
    if(isset($bin_data->response->pallets_packed) && is_array($bin_data->response->pallets_packed)){
        foreach($bin_data->response->pallets_packed as $bin_key => $bin_val){
            $pallet_price = $wpdb->get_results("SELECT `pallet_fee` FROM `" . $wpdb->prefix . "en_pallets` WHERE nickname = '" . $bin_val->pallet_data->id . "'");
            if (!empty($pallet_price)) {
                $pallet_cost = $pallet_cost + $pallet_price[0]->pallet_fee;
            }
        }
    }
    $rate['cost'] += $pallet_cost;
    return $rate;
}
