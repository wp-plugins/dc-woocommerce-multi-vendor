<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/vendor-commissions-paid.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $DC_Product_Vendor;


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

?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php _e( 'The commission has been credited successfully.', $DC_Product_Vendor->text_domain ) ?></p>

<h2><?php printf( __( 'Commission #%s detail', $DC_Product_Vendor->text_domain), $commission ); ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Status', $DC_Product_Vendor->text_domain ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo get_post_meta($commission, '_paid_status', true);?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Date', $DC_Product_Vendor->text_domain ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo get_the_date('Y-m-d', $commission); ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Amount', $DC_Product_Vendor->text_domain ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo get_post_meta($commission, '_commission_amount', true); ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Paypal email', $DC_Product_Vendor->text_domain ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $vendor->paypal_email; ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Vendor', $DC_Product_Vendor->text_domain ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $vendor->user_data->display_name; ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Order number', $DC_Product_Vendor->text_domain ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo get_post_meta($commission, '_commission_order_id', true); ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Product', $DC_Product_Vendor->text_domain ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $title; ?></td>
		</tr>
	</tbody>
</table>
<?php do_action( 'woocommerce_email_footer' ); ?>
