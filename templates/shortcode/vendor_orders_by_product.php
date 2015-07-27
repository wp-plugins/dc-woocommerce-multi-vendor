<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_orders_by_product.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $woocommerce, $DC_Product_Vendor;

$user = wp_get_current_user();
$vendor = get_dc_vendor($user->ID);
if($vendor && isset($_GET['orders_for_product']) && !empty($_GET['orders_for_product'])) { 
	?>
	
	<h2><?php printf( 'Orders for %s', get_product( $_GET['orders_for_product'] )->get_title() ); ?></h2>
	<?php 
	if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_order_csv_export') ) { ?>
		<form method="post" name="export_orders">
			<input type="submit"
					 class="btn btn-primary btn-small"
					 style="float:right;margin-bottom:10px;"
					 name="export_orders_by_product"
					 value="<?php _e( 'Export orders', $DC_Product_Vendor->text_domain ); ?>"
				>
				<input type="hidden" name="product_id" value="<?php echo $_GET['orders_for_product'] ?>">
		</form>
	<?php
	}
	?>
	<?php
		$headers = array(
					'order'   => __( 'Order', $DC_Product_Vendor->text_domain),
					'product' => __( 'Product Title', $DC_Product_Vendor->text_domain ),
					'name'    => __( 'Full name', $DC_Product_Vendor->text_domain ),
					'address' => __( 'Address', $DC_Product_Vendor->text_domain ),
					'city'    => __( 'City', $DC_Product_Vendor->text_domain ),
					'state'   => __( 'State', $DC_Product_Vendor->text_domain ),
					'zip'     => __( 'Zip', $DC_Product_Vendor->text_domain ),
					'email'   => __( 'Email address', $DC_Product_Vendor->text_domain ),
					'date'    => __( 'Date', $DC_Product_Vendor->text_domain ),
		);
	?>
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<?php foreach ( $headers as $header ) { ?>
				<th class="<?php echo sanitize_title( $header ); ?>"><?php echo $header; ?></th>
			<?php } ?>
		</tr>
		</thead>
		<tbody>
			<?php 
				$product = get_product($_GET['orders_for_product']);
				$orders = array();
				if($product->is_type('variable')) {
					$variations = $product->get_children();
					if(!empty($variations)) {
						foreach($variations as $variation) {
							$childorders = $vendor->get_vendor_orders_by_product($vendor->term_id, $variation);
							$orders = array_merge($orders, $childorders);
						}
					}
				} else {
					$orders = $vendor->get_vendor_orders_by_product($vendor->term_id, $_GET['orders_for_product']);
				}
				$all = $vendor->format_order_details( $orders, $_GET['orders_for_product'] );
				foreach ( $all['body'] as $order_id => $order ) {
					$order_obj = new WC_Order ($order_id);
					$order_items = !empty( $all[ 'items' ][ $order_id ][ 'items' ] ) ? $all[ 'items' ][ $order_id ][ 'items' ] : array();
					$count  = count( $order_items ); 
					?>
					<tr>
						<?php
						$order_keys = array_keys( $order );
						$first_index = array_shift( $order_keys );
						$last_index = end( $order_keys );
						foreach ( $order as $detail_key => $detail ) { if ( $detail_key == $last_index ) continue; ?>
							<?php if ( $detail_key == $first_index ) { ?>
			
								<td class="<?php echo $detail_key; ?>"
									rowspan="<?php echo $count == 1 ? 3 : ( $count + 3 ); ?>"><?php echo $detail; ?></td>
			
							<?php } else { ?>
			
								<td class="<?php echo $detail_key; ?>"><?php echo $detail; ?></td>
			
							<?php } ?>
						<?php } ?>
					</tr>
					<tr>
						<?php foreach ( $order_items as $item ) {
							$item_meta = new WC_Order_Item_Meta( $item[ 'item_meta' ] );
							$item_meta = $item_meta->display( false, true );
							if ($count > 1) { ?>
								<tr>
									<?php } ?>
									<?php if (!empty( $item_meta ) && $item_meta != '<dl class="variation"></dl>') { ?>
										<td colspan="5">
											<?php echo $item_meta; ?>
										</td>
										<td colspan="2">
											<?php } else { ?>
											<td colspan="100%">
												<?php } ?>
												<?php printf( __( 'Quantity: %d', $DC_Product_Vendor->text_domain ), $item[ 'qty' ] ); ?>
											</td>
											<?php if ($count > 1) { ?>
								</tr>
								<?php }
						}	
						$customer_note = $order_obj->customer_note;
						?>
						<tr>
							<td colspan="100%">
								<h5>
									<?php _e( 'Customer note', $DC_Product_Vendor->text_domain ); ?>
								</h5>
						
								<p>
									<?php echo $customer_note ? $customer_note : __( 'No customer note.', $DC_Product_Vendor->text_domain ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<td colspan="100%">
							<?php
								if ($DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_vendor_view_comment') || $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_vendor_show_comment')) {
									$comments = array();
	
								if ( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_vendor_view_comment') ) {
									$comments = $order_obj->get_customer_order_notes();
								}
								?>
								<a href="#" class="order-comments-link">
									<p>
										<?php printf( __( 'Comments (%s)', 'wcvendors' ), count( $comments ) ); ?>
									</p>
								</a>

								<div class="order-comments">
									<?php
				
									}
				
									if ( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_vendor_view_comment') && !empty( $comments ) ) {
										foreach ( $comments as $comment ) {
											$last_added = human_time_diff( strtotime( $comment->comment_date_gmt ), current_time( 'timestamp', 1 ) );
											?>
											<p>
												<?php printf( __( 'added %s ago', 'wcvendors' ), $last_added ); ?>
												</br>
												<?php echo $comment->comment_content; ?>
											</p>
	
										<?php }
									}
				
									if ( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_vendor_submit_comment') ) { ?>
										<form method="post" name="add_comment" id="add-comment_<?php echo $order_id; ?>">
											<?php wp_nonce_field( 'add-comment' ); ?>
										
											<textarea name="comment_text" style="width:97%"></textarea>
										
											<input type="hidden" name="product_id" value="<?php echo $product_id ?>">
											<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
										
											<input class="btn btn-large btn-block" type="submit" name="dc_submit_comment" value="<?php _e( 'Add comment', 'wcvendors' ); ?>">
										
										</form>
									<?php	} ?>
							</div>
						</td>
					</tr>
				</tr>

	<?php } ?>

	</tbody>
</table>
	
<?php } else {
	_e("You havenot selected a product/'s orders to view! Please go back to the Vendor Dashboard and click Show Orders on the product you'd like to view.", $DC_Product_Vendor->text_domain);
}