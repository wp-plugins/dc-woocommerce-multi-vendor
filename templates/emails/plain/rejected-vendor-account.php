<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/rejected-vendor-account.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $DC_Product_Vendor;

echo $email_heading . "\n\n";

echo sprintf( __( "Thanks for creating an account as Pending Vendor on %s.But Your request is  rejected due to some reason.",  $DC_Product_Vendor->text_domain ), $blogname ) . "\n\n";

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );