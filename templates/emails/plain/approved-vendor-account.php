<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/approved-vendor-account.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $DC_Product_Vendor;

echo "= " . $email_heading . " =\n\n";
echo sprintf( __("Hi there. This is a notification about a vendor application on %s.", $DC_Product_Vendor->text_domain ), get_option( 'blogname' ) );
echo '\n';
echo sprintf( __( "Application status: %s", $DC_Product_Vendor->text_domain ), 'Approved' );
echo '\n';
echo sprintf( __( "Applicant username: %s", $DC_Product_Vendor->text_domain ), $user_login ); 
echo '\n';
echo _e('Congratulations !! First set up your store account and Start selling from now..', $DC_Product_Vendor->text_domain);

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
do_action( 'woocommerce_email_footer' ); 

?>