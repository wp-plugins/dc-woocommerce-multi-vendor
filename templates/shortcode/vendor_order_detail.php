<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_order_detail.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $DC_Product_Vendor;
$user = wp_get_current_user();
$vendor = get_dc_vendor($user->ID);
$order_id = $_GET['order_id'];
if( $vendor && $order_id ) {
	$vendor_items = $vendor->get_vendor_items_order($order_id, $vendor->term_id);
	$order = new WC_Order( $order_id );
	//print_r($order);
	if($order) {
		?>
		<h2><?php _e( 'Order Details', $DC_Product_Vendor->text_domain ); ?></h2>
			<table class="customer_order_dtl"> 
				<tbody>
					<th><label for="product_name"><?php _e('Product Title', $DC_Product_Vendor->text_domain) ?></label></th>
					<th><label for="product_qty"><?php _e('Product Quantity', $DC_Product_Vendor->text_domain) ?></label></th>
					<th><label for="product_total"><?php _e('Line Subtotal', $DC_Product_Vendor->text_domain) ?></label></th>
					<?php if ( in_array( $order->status, array( 'processing', 'completed' ) ) && ( $purchase_note = get_post_meta( $_product->id, '_purchase_note', true ) ) ) { ?>
						<th><label for="product_note"><?php _e('Purchase Note', $DC_Product_Vendor->text_domain) ?></label></th>
					<?php }?>
					<?php
					if ( sizeof( $order->get_items() ) > 0 ) {
						foreach( $vendor_items as $item ) {
							$_product     = apply_filters( 'dc_woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
							$item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );
			
							?>
								<tr class="">
									
									<td class="product-name">
										<?php
											if ( $_product && ! $_product->is_visible() )
												echo apply_filters( 'dc_woocommerce_order_item_name', $item['name'], $item );
											else
												echo apply_filters( 'woocommerce_order_item_name', sprintf( '<a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ), $item );
												$item_meta->display();
											?>
									</td>
									<td>	
										<?php
											echo $item['qty'];
											?>
									</td>
									<td>
										<?php echo $order->get_formatted_line_subtotal( $item ); ?>
									</td>
							<?php
							if ( in_array( $order->status, array( 'processing', 'completed' ) ) && ( $purchase_note = get_post_meta( $_product->id, '_purchase_note', true ) ) ) {
								?>
									<td colspan="3"><?php echo apply_filters( 'the_content', $purchase_note ); ?></td>
								<?php
							}
						}
					}
					?>
					</tr>
				</tbody>
			</table>
			<?php
			$coupons = $order->get_used_coupons();
			if(!empty($coupons)) {
				?>
				<h2><?php _e( 'Coupon Used :', $DC_Product_Vendor->text_domain ); ?></h2>
				<table class="coupon_used"> 
					<tbody>
					  <tr>
							<?php
							$coupon_used = false;
							foreach($coupons as $coupon_code) {
								$coupon = new WC_Coupon( $coupon_code );
								$coupon_post = get_post($coupon->id);
								$author_id = $coupon_post->post_author;
								if(get_current_user_id() == $author_id) {
									$coupon_used = true;
									echo '<td>"'.$coupon_code.'"</td>';
								}
							}
							if(!$coupon_used) echo '<td>Sorry No Coupons of yours is used.</td>'
							?>
						</tr>
					</tbody>
				</table>
			<?php
			}
			?>
			<?php $customer_note = $order->customer_note;
				?>
				<h2><?php _e( 'Customer Note', $DC_Product_Vendor->text_domain ); ?></h2>
				<table class="customer_note"> 
					<tbody>
					  <tr>
					  	<td colspan="100%">
								<p>
									<?php echo $customer_note ? $customer_note : __( 'No customer note.', $DC_Product_Vendor->text_domain ); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
			<?php 
			if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_vendor_view_comment') ) { 
				$vendor_comments = $order->get_customer_order_notes();
				if($vendor_comments) { ?>
				<h2><?php _e( 'Comments', $DC_Product_Vendor->text_domain ); ?></h2>
				<?php
					foreach ( $vendor_comments as $comment ) {
						$last_added = human_time_diff( strtotime( $comment->comment_date_gmt ), current_time( 'timestamp', 1 ) );	?>
						<p>
							<?php printf( __( 'added %s ago', $DC_Product_Vendor->text_domain ), $last_added ); ?>
							</br>
							<?php echo $comment->comment_content; ?>
						</p>
					<?php } ?>
					<?php 
				}
			} ?>
			
			<?php if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_vendor_submit_comment') ) { ?>
				<h2><?php _e( 'Add Comment', $DC_Product_Vendor->text_domain ); ?></h2>
				<form method="post" name="add_comment" id="add-comment_<?php echo $order_id; ?>">
					<?php wp_nonce_field( 'dc-add-comment' ); ?>
					<textarea name="comment_text" style="width:97%; margin-bottom: 10px;"></textarea>
					<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
					<input class="btn btn-large btn-block" type="submit" name="dc_submit_comment" value="<?php _e( 'Add comment', $DC_Product_Vendor->text_domain ); ?>">
				</form>
			<?php } ?>
			
			<?php if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('show_customer_dtl') ) { ?>
			<header>
				<h2><?php _e( 'Customer details', $DC_Product_Vendor->text_domain ); ?></h2>
			</header>
			<dl class="customer_details">
			<?php
			$name =  $order->billing_first_name . ' ' . $order->billing_last_name ;
				echo '<dt>' . __( 'Name:', $DC_Product_Vendor->text_domain ) . '</dt><dd>'.$name.'</dd>';
				if ( $order->billing_email ) echo '<dt>' . __( 'Email:', $DC_Product_Vendor->text_domain ) . '</dt><dd>' . $order->billing_email . '</dd>';
				if ( $order->billing_phone ) echo '<dt>' . __( 'Telephone:', $DC_Product_Vendor->text_domain ) . '</dt><dd>' . $order->billing_phone . '</dd>';
			
				// Additional customer details hook
				do_action( 'dc_woocommerce_order_details_after_customer_details', $order );
			?>
			</dl>
			<?php } ?>
			
			
			<?php if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('show_customer_billing') ) { ?>
				<div class="col-1">
					<header class="title">
						<h3><?php _e( 'Billing Address', $DC_Product_Vendor->text_domain ); ?></h3>
					</header>
					<address><p>
						<?php
							if ( ! $order->get_formatted_billing_address() ) _e( 'N/A', $DC_Product_Vendor->text_domain ); else echo $order->get_formatted_billing_address();
						?>
					</p></address>
				</div><!-- /.col-1 -->
			<?php } 
			if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('show_customer_shipping') ) {
			?>
			<?php if ( ! wc_ship_to_billing_address_only() && get_option( 'woocommerce_calc_shipping' ) !== 'no' ) { ?>
				<div class="col-2">
					<header class="title">
						<h3><?php _e( 'Shipping Address', $DC_Product_Vendor->text_domain ); ?></h3>
					</header>
					<address><p>
						<?php
							if ( ! $order->get_formatted_shipping_address() ) _e( 'N/A', $DC_Product_Vendor->text_domain ); else echo $order->get_formatted_shipping_address();
						?>
					</p></address>
			
				</div><!-- /.col-2 -->
			
			</div><!-- /.col2-set -->
			<?php
			}
		}
	}
}?>
<div class="clear"></div>