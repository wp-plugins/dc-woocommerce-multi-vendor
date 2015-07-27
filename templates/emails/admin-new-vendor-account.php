<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/admin-new-vendor-account.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
global $DC_Product_Vendor;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>
<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( "A New User has applied for vendor on %s. His/her Email is <strong>%s</strong>.", $DC_Product_Vendor->text_domain ), esc_html( $blogname ), esc_html( $user_email ) ); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>