<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/vendor-new-order.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $DC_Product_Vendor;
echo $email_heading . "\n\n";

echo sprintf( __( 'An Order has received and marked as completed from %s. Their order is as follows:',  $DC_Product_Vendor->text_domain ), $order->billing_first_name . ' ' . $order->billing_last_name ) . "\n\n";

echo "****************************************************\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text );

echo sprintf( __( 'Order number: %s',  $DC_Product_Vendor->text_domain), $order->get_order_number() ) . "\n";
echo sprintf( __( 'Order link: %s',  $DC_Product_Vendor->text_domain), admin_url( 'post.php?post=' . $order->id . '&action=edit' ) ) . "\n";
echo sprintf( __( 'Order date: %s',  $DC_Product_Vendor->text_domain), date_i18n( __( 'jS F Y',  $DC_Product_Vendor->text_domain ), strtotime( $order->order_date ) ) ) . "\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text );

$vendor = new DC_Vendor( absint( $vendor_id ) );
$vendor_items_dtl = $vendor->plain_vendor_order_item_table($order, $vendor_id); 
echo $vendor_items_dtl;

echo "----------\n\n";
if($DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('show_cust_order_calulations')) {
	
	if ( $totals = $vendor->dc_vendor_get_order_item_totals($order, $vendor_id) ) {
		foreach ( $totals as $total ) {
			echo $total['label'] . "\t " . $total['value'] . "\n";
		}
	}
}

echo "\n****************************************************\n\n";

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text );
$show_customer_detail = $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('show_cust_add');
if($show_customer_detail) {
	echo __( 'Customer details',  $DC_Product_Vendor->text_domain ) . "\n";

	if ( $order->billing_email )
		echo __( 'Email:',  $DC_Product_Vendor->text_domain ); echo $order->billing_email . "\n";

	if ( $order->billing_phone )
		echo __( 'Tel:',  $DC_Product_Vendor->text_domain ); ?> <?php echo $order->billing_phone . "\n";
}

$show_cust_billing_add =  $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('show_cust_billing_add');
$show_cust_shipping_add =  $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('show_cust_shipping_add');
if($show_cust_billing_add) {
	echo "\n" . __( 'Billing address',  $DC_Product_Vendor->text_domain ) . ":\n";
	echo $order->get_formatted_billing_address() . "\n\n";
}
if($show_cust_shipping_add) {
	if ( get_option( 'woocommerce_ship_to_billing_address_only' ) == 'no' && ( $shipping = $order->get_formatted_shipping_address() ) ) {
	
		echo __( 'Shipping address',  $DC_Product_Vendor->text_domain ) . ":\n";
	
		echo $shipping . "\n\n";
	
	}
}

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );