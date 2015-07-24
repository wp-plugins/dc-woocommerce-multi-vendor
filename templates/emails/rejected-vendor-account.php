<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/rejected-vendor-account.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
global $DC_Product_Vendor;
?>
<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( "Thanks for creating an account as Pending Vendor on %s.But Your request is rejected due to some reason.",  $DC_Product_Vendor->text_domain ), esc_html( $blogname )); ?></p>
<p><?php printf( __( "You can contact with the site-admin at %s",  $DC_Product_Vendor->text_domain ), get_option('admin_email')); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>