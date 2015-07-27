<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/vendor-notify-shipped.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
global  $DC_Product_Vendor;
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php _e( 'A vendor has marked part of your order as shipped. The items that are shipped are as follows:',  $DC_Product_Vendor->text_domain ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', $order, true ); ?>

<h2><?php printf( __( 'Order: %s',  $DC_Product_Vendor->text_domain ), $order->get_order_number() ); ?> (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order->order_date ) ), date_i18n( woocommerce_date_format(), strtotime( $order->order_date ) ) ); ?>)</h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product',  $DC_Product_Vendor->text_domain ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity',  $DC_Product_Vendor->text_domain ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Price',  $DC_Product_Vendor->text_domain ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php 
		 	$vendor = get_dc_vendor_by_term($vendor_id);
			$vendor_items_dtl = $vendor->vendor_order_item_table($order, $vendor_id); 
			echo $vendor_items_dtl;
		?>
	</tbody>
</table>
<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<?php
		if ( $totals = $order->get_order_item_totals() ) {
			$i = 0;
			foreach ( $totals as $total ) {
				$i++;
				?><tr>
					<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['label']; ?></th>
					<td style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; ?></td>
				</tr><?php
			}
		}
	?>
</table>
<?php do_action('woocommerce_email_after_order_table', $order, true); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, true ); ?>

<h2><?php _e( 'Customer details',  $DC_Product_Vendor->text_domain ); ?></h2>

<?php if ( $order->billing_email ) : ?>
	<p><strong><?php _e( 'Email:',  $DC_Product_Vendor->text_domain ); ?></strong> <?php echo $order->billing_email; ?></p>
<?php endif; ?>
<?php if ( $order->billing_phone ) : ?>
	<p><strong><?php _e( 'Tel:',  $DC_Product_Vendor->text_domain ); ?></strong> <?php echo $order->billing_phone; ?></p>
<?php endif; ?>

<?php wc_get_template( 'emails/email-addresses.php', array( 'order' => $order ) ); ?>

<?php do_action( 'woocommerce_email_footer' ); ?>