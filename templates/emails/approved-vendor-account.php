<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/approved-vendor-account.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
global $DC_Product_Vendor;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>
<?php do_action( 'woocommerce_email_header', $email_heading ); ?>
<p><?php printf( __( "Hi there. This is a notification about a vendor application on %s.", $DC_Product_Vendor->text_domain ), get_option( 'blogname' ) ); ?></p>
<p>
	<?php _e( "Application status: Approved",  $DC_Product_Vendor->text_domain ); ?><br/>
	<?php printf( __( "Applicant username: %s",  $DC_Product_Vendor->text_domain ), $user_login ); ?>
</p>
<p><?php _e('Congratulations !! First set up your store account and Start selling from now..', $DC_Product_Vendor->text_domain) ?> <p>
<?php do_action( 'woocommerce_email_footer' );?>