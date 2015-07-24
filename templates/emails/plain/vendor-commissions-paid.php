<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/vendor-commissions-paid.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $DC_Product_Vendor;

$vendor = get_dc_vendor(get_post_meta($commission, '_commission_vendor', true));

if( function_exists( 'get_product' ) ) {
	$product = get_product( get_post_meta($commission, '_commission_product', true) );
} else {
	$product = new WC_Product( get_post_meta($commission, '_commission_product', true) );
}
if($product->get_formatted_name()) {
	$title = $product->get_formatted_name();
} else {
	$title = $product->get_title();
}

echo "= " . $email_heading . " =\n\n";

echo __( 'The commission has been credited successfully.', $DC_Product_Vendor->text_domain ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";


echo strtoupper( sprintf( __( 'Commission number: %s', $DC_Product_Vendor->text_domain ), $commission ) ) . "\n";
echo date_i18n( __( 'jS F Y', $DC_Product_Vendor->text_domain ), strtotime( get_the_date('Y-m-d', $commission) ) ) . "\n";

echo __( 'Status', $DC_Product_Vendor->text_domain ) . ':';
echo get_post_meta($commission, '_paid_status', true) . " \n\n";

echo __( 'Date', $DC_Product_Vendor->text_domain ) . ':';
echo get_the_date('Y-m-d', $commission) . " \n\n";

echo __( 'Amount', $DC_Product_Vendor->text_domain ) . ':';
echo get_post_meta($commission, '_commission_amount', true) . " \n\n";

echo __( 'PayPal email', $DC_Product_Vendor->text_domain ) . ':';
echo $vendor->paypal_email . " \n\n";

echo __( 'Vendor', $DC_Product_Vendor->text_domain ) . ':';
echo $vendor->user_date->display_name . " \n\n";

echo __( 'Order number', $DC_Product_Vendor->text_domain ) . ':';
echo get_post_meta($commission, '_commission_order_id', true) . " \n\n";

echo __( 'Product', $DC_Product_Vendor->text_domain ) . ':';
echo $title . " \n\n";


echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );