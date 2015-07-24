<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/vendor-new-account.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global  $DC_Product_Vendor;

echo $email_heading . "\n\n";

echo sprintf( __( "Thanks for creating an account on %s.We take your application for vendor.We will verify your informations and inform you by mail.Your username is <strong>%s</strong>.",  $DC_Product_Vendor->text_domain ), $blogname, $user_login ) . "\n\n";

if ( get_option( 'woocommerce_registration_generate_password' ) === 'yes' && $password_generated )
	echo sprintf( __( "Your password is <strong>%s</strong>.",  $DC_Product_Vendor->text_domain ), $user_pass ) . "\n\n";

echo sprintf( __( 'You can access your account area to view your orders and change your password here: %s.',  $DC_Product_Vendor->text_domain ), get_permalink( wc_get_page_id( 'myaccount' ) ) ) . "\n\n";

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );