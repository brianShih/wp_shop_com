<?php
/**
 * Plugin Name: amShop Connecter for woocommerce
 * Description: Connect the woocommerce module to shop.com system
 * Author:      Kun You Shih
 * Version:     0.01
 * Author URI:  https://www.breadcrumbs.tw
 * Plugin URI:  
 * Domain Path: /
 */

 
if(!defined( 'ABSPATH' )) exit;

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( defined( 'AMSHOP_CONNECTER_VERSION' ) ) {
    return;
}else{
    define( 'AMSHOP_CONNECTER_VERSION', '0.0.1' );
}

if ( defined('AMSHOP_OFFER_ID')) {
	return;
} else {
	define('AMSHOP_OFFER_ID', '12428');
}

if ( defined('AMSHOP_ADVERTISER_ID')) {
	return;
} else {
	define('AMSHOP_ADVERTISER_ID', '12192');
}

if ( ! defined( 'AMSHOP_CONNECTER_DIR' ) ) {
    define( 'AMSHOP_CONNECTER_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'AMSHOP_CONNECTER_INC' ) ) {
    define( 'AMSHOP_CONNECTER_INC', AMSHOP_CONNECTER_DIR . '/includes/' );
}

if ( ! defined( 'AMSHOP_CONNECTER_INIT' ) ) {
    define( 'AMSHOP_CONNECTER_INIT', plugin_basename( __FILE__ ) );
}

if ( !function_exists( 'amshop_connecter_install_woocommerce_admin_notice' ) ) {
	function amshop_connecter_install_woocommerce_admin_notice() {
		?>
        <div class="error">
            <p><?php _e( 'AM Shop connecter is enabled but not effective. It requires WooCommerce in order to work.', 'amshopConnecter' ); ?></p>
        </div>
		<?php
	}
}

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
	}
}

if ( ! function_exists( 'amshop_connecter_install' ) ) {
    function amshop_connecter_install() {

	    do_action( 'amshop_connecter_init' );

        // check for update table
        if( function_exists( 'amshop_connecter_update_db_check' ) ) {
            amshop_connecter_update_db_check();
        }
    }

    add_action( 'plugins_loaded', 'amshop_connecter_install', 11 );
}

if(is_woocommerce_active()) {
	if ( ! function_exists( 'set_amshop_rid' ) ) {
		function set_amshop_rid() {
			if (isset($_GET["RID"])){
				setcookie("RID", $_GET["RID"],time()+3600,"/");///setcookie 時效3600秒
				setcookie("Click_ID", $_GET["Click_ID"],time()+3600,"/");
			}
		}
	}

	if ( ! function_exists( 'shipping_information_form' ) ) {
		function shipping_information_form( $checkout ) {
			if ( isset($_COOKIE["RID"]) && isset($_COOKIE["Click_ID"])  ) {
				$RID = $_COOKIE["RID"];
				$Click_ID = $_COOKIE["Click_ID"];
				echo '<div id="my_custom_checkout_field"><h3>'.__("歡迎，美安顧客").'</h3>';
				echo '<h4>'.__("RID").'  '.$RID.'</h4>';
				echo '<h4>'.__("Click_ID").'  '.$Click_ID.'</h4>';
				echo '</div>';
				/*
				woocommerce_form_field( "RID", array(
					"type" => "text",
					"class" => array("my-field-class form-row-wide"),
					"label" => __("美安顧客碼(請勿更改)"),
					"default" => $RID,
				), $checkout -> get_value("RID") );
				
				woocommerce_form_field( "Click_ID", array(
					"type" => "text",
					"class" => array("my-field-class form-row-wide"),
					"label" => __("美安認證碼(請勿更改)"),
					"default" => $Click_ID,
				), $checkout -> get_value("Click_ID") );
				*/
			}
		}
	}

	add_action( "init", "set_amshop_rid" );
	add_action( "woocommerce_after_order_notes", "shipping_information_form" );

	function amshop_connecter_constructor() {

	    // Woocommerce installation check _________________________
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'amshop_connecter_install_woocommerce_admin_notice' );
			return;
		}

		 // Load ywpar text domain ___________________________________
	    //load_plugin_textdomain( 'amshopConnecter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	    if( ! class_exists( 'WP_List_Table' ) ) {
	        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	    }

		require_once( AMSHOP_CONNECTER_INC . 'functions.amshop_connecter_debug_log.php' );
	    require_once( AMSHOP_CONNECTER_INC . 'functions.amshop_connecter_db_helper.php' );
	    require_once( AMSHOP_CONNECTER_INC . 'functions.amshop_connecter_api_service.php' );
	    require_once( AMSHOP_CONNECTER_INC . 'class.amshop_connecter_order_helper.php' );


	    amshop_connecter_order_helper();

	}
	add_action( 'amshop_connecter_init', 'amshop_connecter_constructor' );
}
?>