<?php
class DC_Product_Vendor_Calculate_Commission {
	
	public function __construct() {
    // Process commissions for order
    add_action( 'woocommerce_order_status_completed', array( $this, 'process_commissions' ), 10, 1 );
  }
	
  /**
	* Process commission
	* @param  int $order_id ID of order for commission
	* @return void
	*/
	
  public function process_commissions($order_id) {
  	global $DC_Product_Vendor;
		// Only process commissions once
			$processed = get_post_meta( $order_id, '_commissions_processed', $single = false );
			if( $processed && $processed == 'yes' ) return;
			$order = new WC_Order( $order_id );
		
			$items = $order->get_items( 'line_item' );
		
			foreach( $items as $item_id => $item ) {
					if (isset( $item['variation_id']) && !empty( $item['variation_id'])) {
						$variation_id = $item['variation_id'] ;
					} 
					
					$product_id = $item['product_id'];
					
					
					if(isset($DC_Product_Vendor->vendor_caps->general_cap['commission_include_coupon'])) $line_total = $order->get_item_total( $item, false, false ) * $item['qty'];
					
					else $line_total = $order->get_item_subtotal( $item, false, false ) * $item['qty'];
					
          
					if( $product_id && $line_total ) {
						$this->record_commission( $product_id, $line_total, $order_id, $variation_id );
					}
			}
		
			// Mark commissions as processed
			update_post_meta( $order_id, '_commissions_processed', 'yes' );
  }
  
	/**
	* Record individual commission
	* @param  int $product_id ID of product for commission
	* @param  int $line_total Line total of product
	* @return void
	*/
	public function record_commission( $product_id = 0, $line_total = 0, $order_id = 0, $variation_id = 0 ) {
		if( $product_id > 0 && $line_total > 0 ) {
			$vendor =  wp_get_post_terms( $product_id, 'dc_vendor_shop', array("fields" => "ids"));
			if( $vendor ) {
				$commission = $this->get_commission_percent( $product_id, $vendor[0], $variation_id );
				if( $commission && $commission > 0 ) {
						$amount = (float) $line_total * ( (float)$commission / 100 );
						$this->create_commission( $vendor[0], $product_id, $amount, $order_id, $variation_id);
				}
			}
		}
	}
	
	/**
	* Get assigned commission percentage
	* @param  int $product_id ID of product
	* @param  int $vendor_id  ID of vendor
	* @return int             Relevent commission percentage
	*/
	public function get_commission_percent( $product_id = 0, $vendor_id = 0, $variation_id = 0 ) {
		global $DC_Product_Vendor;
		
		$data = ''; 
		if($variation_id > 0) { 
			$data = get_post_meta( $variation_id, '_product_vendors_commission', true );
			if(!isset($data)) {
				$data = get_post_meta( $product_id, '_commission_per_product', true );
			}
		} else {
			$data = get_post_meta( $product_id, '_commission_per_product', true );
		}
		if( isset($data)  && $data > 0) {
			return $data; // Use product commission percentage first
		} else {
			$vendor_user_id = get_woocommerce_term_meta($vendor_id, 'vendor_user_id', true);
			$vendor_commission = get_user_meta($vendor_user_id, 'vendor_commission', true);
			if($vendor_commission) {
				return $vendor_commission ; // Use vendor user commission percentage 
			} else {
				return isset($DC_Product_Vendor->vendor_caps->general_cap['default_commission']) ? $DC_Product_Vendor->vendor_caps->general_cap['default_commission'] : false ; // Use default commission
			}
		}
	}
	
	/**
	 * Create new commission post
	 * @param  int $vendor_id  ID of vendor for commission
	 * @param  int $product_id ID of product for commission
	 * @param  int $amount     Commission total
	 * @return void
	 */
	public function create_commission( $vendor_id = 0, $product_id = 0, $amount = 0 , $order_id = 0, $variation_id = 0 ) {
		global $DC_Product_Vendor;
		$commission_data = array(
				'post_type'     => 'dc_commission',
				'post_title'    => sprintf( __( 'Commission - %s', $DC_Product_Vendor->text_domain ), strftime( _x( '%B %e, %Y @ %I:%M %p', 'Commission date parsed by strftime', $DC_Product_Vendor->text_domain ) ) ),
				'post_status'   => 'private',
				'ping_status'   => 'closed',
				'post_excerpt'  => '',
				'post_author'   => 1
		);
		$commission_id = wp_insert_post( $commission_data );
		// Add meta data
		if( $vendor_id > 0 ) { update_post_meta( $commission_id, '_commission_vendor', $vendor_id ); }
		if( $variation_id > 0 ) {
			update_post_meta( $commission_id, '_commission_product', $variation_id );
		} else {
			update_post_meta( $commission_id, '_commission_product', $product_id );
		}
		if( $amount > 0 ) { update_post_meta( $commission_id, '_commission_amount', $amount ); }
		if( $order_id > 0 ) { update_post_meta( $commission_id, '_commission_order_id', $order_id ); }
		// Mark commission as unpaid
		update_post_meta( $commission_id, '_paid_status', 'unpaid' );
	}
}
?>