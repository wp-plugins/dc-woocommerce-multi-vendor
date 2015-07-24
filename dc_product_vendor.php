<?php
/*
Plugin Name: DC WooCommerce Multi Vendor
Plugin URI: http://dualcube.com
Description: Set up vendor market place that allows vendor to manage their own products and earn commission easily.
Author: Dualcube, Mousumi Saha
Version: 1.0.0
Author URI: http://dualcube.com
*/


if ( ! class_exists( 'WC_Dependencies_Product_Vendor' ) ) require_once 'includes/class-dc-dependencies.php';
require_once 'includes/dc-product-vendor-core-functions.php';
require_once 'config.php';
if(!defined('ABSPATH')) exit; // Exit if accessed directly
if(!defined('PLUGIN_TOKEN')) exit;
if(!defined('TEXT_DOMAIN')) exit;

if(!class_exists('DC_Product_Vendor') && WC_Dependencies_Product_Vendor::is_woocommerce_active() ) {
	require_once( 'classes/class-dc-product-vendor.php' );
	global $DC_Product_Vendor;
	$DC_Product_Vendor = new DC_Product_Vendor( __FILE__ );
	$GLOBALS['DC_Product_Vendor'] = $DC_Product_Vendor;
	// Activation Hooks
	register_activation_hook( __FILE__, array('DC_Product_Vendor', 'activate_dc_product_vendor_plugin') );
	register_activation_hook( __FILE__, 'flush_rewrite_rules' );
	if( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array('DC_Product_Vendor', 'dc_product_vendor_action_links') );
	}
} else {
	add_action( 'admin_notices', 'dc_admin_notice' );
	function dc_admin_notice() {
		?>
    <div class="error">
        <p><?php _e( 'This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugins to be active!', TEXT_DOMAIN ); ?></p>
    </div>
    <?php
	}
}
?>
