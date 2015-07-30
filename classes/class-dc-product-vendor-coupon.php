<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		DC_Product_Vendor_Coupon
 * @version		1.0.0
 * @package		DC_Product_Vendor
 * @author 		DualCube
 */ 
class DC_Product_Vendor_Coupon {
	
	public function __construct() {
		
		/* Coupon Management */
		add_filter( 'woocommerce_coupon_discount_types', array( &$this, 'coupon_discount_types' ) );
		add_filter( 'woocommerce_json_search_found_products', array( &$this, 'json_filter_report_products' ) );
		
		/* Filter coupon list */
		add_action( 'request', array( &$this, 'filter_coupon_list' ) );
		add_filter( 'wp_count_posts', array( &$this, 'vendor_count_coupons' ), 10, 3 );

	}
	
	public function coupon_discount_types( $coupon_types ){
		$current_user = wp_get_current_user();
		if( is_user_dc_vendor($current_user) ){
			$to_unset = apply_filters( 'dc_multi_vendor_coupon_types', array( 'fixed_cart', 'percent' ) );
			foreach( $to_unset as $coupon_type_id ){
				unset( $coupon_types[ $coupon_type_id ] );
			}
		}
		return $coupon_types;
	}

        
	public function filter_coupon_list( $request ) {
		global $typenow;

		$current_user = wp_get_current_user();

		if ( is_admin() && is_user_dc_vendor($current_user) && 'shop_coupon' == $typenow ) {
				$request[ 'author' ] = $current_user->ID;
		}

		return $request;
	}

        
	public function vendor_count_coupons( $counts, $type, $perm ) {
		$current_user = wp_get_current_user();

		if ( is_user_dc_vendor($current_user) && 'shop_coupon' == $type ) {
				$args = array(
						'post_type'     => $type,
						'author'    => $current_user->ID
				);

				/**
				 * Get a list of post statuses.
				 */
				$stati = get_post_stati();

				// Update count object
				foreach ( $stati as $status ) {
						$args['post_status'] = $status;
						$posts               = get_posts( $args );
						$counts->$status     = count( $posts );
				}
		}

		return $counts;
	}
	
	
	/**
	 * Filter product search with vendor specific
	 *
	 * @access public
	 * @return void
	*/	
	function json_filter_report_products($products) {
		$current_userid = get_current_user_id();
		
		$filtered_product = array();

		if ( is_user_dc_vendor($current_userid) ) {
			$vendor = get_dc_vendor($current_userid);
				$vendor_products = $vendor->get_products();
				if(!empty($vendor_products)) {
					foreach( $vendor_products as $vendor_product ) {
						if( isset( $products[ $vendor_product->ID ] ) ){
								$filtered_product[ $vendor_product->ID ] = $products[ $vendor_product->ID ];
						}
					}
				}
				$products = $filtered_product;
		}
		
		return $products;
	}
	
}
?>