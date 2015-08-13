<?php
class WC_Vendor_Coupon_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the demo shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	public static function output( $attr ) {
		global $DC_Product_Vendor;
		$DC_Product_Vendor->nocache();
		$coupon_arr = array();
		if ( ! defined( 'MNDASHBAOARD' ) ) define( 'MNDASHBAOARD', true );
		if(is_user_logged_in() )	{ 
			$user = wp_get_current_user();
			if(is_user_dc_vendor($user->ID)) {
				$vendor = get_dc_vendor($user->ID);
				if($vendor) {
					$args = array(
							'posts_per_page' => -1,
							'post_type'     => 'shop_coupon',
							'author'    => $user->ID,
							'post_status' => 'any'
					);
					$coupons = get_posts( $args );
					if(!empty($coupons)) {
						foreach ( $coupons as $coupon ) {
							$coupon_arr[] += $coupon->ID;
						}
					}
				}
				$DC_Product_Vendor->template->get_template( 'shortcode/vendor_coupon.php', array('coupons' => $coupon_arr) );
			}
		}
	}
}