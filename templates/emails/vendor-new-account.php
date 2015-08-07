<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/vendor-new-account.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
global  $DC_Product_Vendor;
?>
<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( "Thanks for creating an account as Pending Vendor on %s. Your username is <strong>%s</strong>.",  $DC_Product_Vendor->text_domain ), esc_html( $blogname ), esc_html( $user_login ) ); ?></p>
<p><?php printf( __( "We take your application for vendor.We will verify your informations and inform you by mail",   $DC_Product_Vendor->text_domain )); ?> </p>
<?php if ( get_option( 'woocommerce_registration_generate_password' ) == 'yes' && $password_generated ) : ?>
<p><?php printf( __( "Your password has been automatically generated: <strong>%s</strong>",  $DC_Product_Vendor->text_domain ), esc_html( $user_pass ) ); ?></p>
<?php endif; ?>
<p><?php printf( __( 'You can access your account area to view your orders and change your password here: %s.',  $DC_Product_Vendor->text_domain ), get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>