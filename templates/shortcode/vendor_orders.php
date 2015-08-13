<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_orders.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $woocommerce, $DC_Product_Vendor;
$user = wp_get_current_user();
$vendor = get_dc_vendor($user->ID);
if($vendor) {
	$customer_orders = $vendor->get_orders(5);
	if($customer_orders) {
		$pages = get_option('dc_pages_settings_name');
		$vendor_detail_page = $pages['vendor_order_detail'];
	?>
	<h3><?php echo apply_filters( 'woocommerce_my_account_my_orders_title', __( 'Orders', $DC_Product_Vendor->text_domain ) ); ?></h3>
	<?php 
	if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_order_csv_export') ) {
		$DC_Product_Vendor->template->get_template( 'shortcode/vendor_order_export.php' );
	}
	?>
	<table class="shop_table my_account_orders">

		<thead>
			<tr>
				<th class="order-number"><span class="nobr"><?php _e( 'Order', $DC_Product_Vendor->text_domain ); ?></span></th>
				<th class="order-date"><span class="nobr"><?php _e( 'Date', $DC_Product_Vendor->text_domain ); ?></span></th>
				<th class="order-status"><span class="nobr"><?php _e( 'Status', $DC_Product_Vendor->text_domain ); ?></span></th>
				<th class="order-total"><span class="nobr"><?php _e( 'Total', $DC_Product_Vendor->text_domain ); ?></span></th>
				<th class="order-actions">&nbsp;</th>
			</tr>
		</thead>

		<tbody><?php
		$customer_orders = array_unique($customer_orders);
			foreach ( $customer_orders as $customer_order ) {
				$order = new WC_Order($customer_order);
				$status  = $order->get_status(); //get_term_by( 'slug', $order->post_status, 'shop_order_status' );
				$item_count = $order->get_item_count();

				?><tr class="order">
					<td class="order-number">
						<?php echo '#'.$customer_order; ?>
					</td>
					<td class="order-date">
						<time datetime="<?php echo date( 'Y-m-d', strtotime( $order->order_date ) ); ?>" title="<?php echo esc_attr( strtotime( $order->order_date ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?></time>
					</td>
					<td class="order-status" style="text-align:left; white-space:nowrap;">
						<?php echo ucfirst( __( $status, $DC_Product_Vendor->text_domain ) ); ?>
					</td>
					<td class="order-total">
						<?php echo sprintf( _n( '%s for %s item', '%s for %s items', $item_count, $DC_Product_Vendor->text_domain ), $order->get_formatted_order_total(), $item_count ); ?>
					</td>
					<td class="order-actions">
						<?php
							$actions = array();
							$actions['view'] = array(
								'url'  => esc_url( add_query_arg( array( 'order_id' => $customer_order ), get_permalink($vendor_detail_page))),
								'name' => __( 'View', $DC_Product_Vendor->text_domain )
							);

							$actions = apply_filters( 'woocommerce_my_account_my_orders_actions', $actions, $order );

							if ($actions) {
								foreach ( $actions as $key => $action ) {
									echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
								}
							}
						?>
					</td>
				</tr><?php
			}
		?></tbody>

	</table> <?php
	} else { ?>
		<table> 
			<tbody>
				<tr>
					<lable for="no_orders"> <?php _e('There are no order is marked as completed that are sold from you.',$DC_Product_Vendor->text_domain); ?> </lable>
				</tr>
			</tbody>
		</table>
	<?php }
}
?>