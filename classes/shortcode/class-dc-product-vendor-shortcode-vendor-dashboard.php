<?php
class WC_Vendor_Dashboard_Shortcode {

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
		if ( ! defined( 'MNDASHBAOARD' ) ) define( 'MNDASHBAOARD', true );
		$user = wp_get_current_user();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if(isset($_POST['vendor_apply']) && $user ) {	
				if( isset( $_POST['pending_vendor'] ) && ( $_POST['pending_vendor'] == 'true' ) ) {
					$DC_Product_Vendor->user->vendor_registration($user->ID);
					$DC_Product_Vendor->user->dc_woocommerce_created_customer_notification();
				}
			}
		} 
		if(is_user_logged_in() )	{ 
			if(is_user_dc_vendor($user->ID)) {
				$DC_Product_Vendor->template->get_template( 'shortcode/vendor_dashboard.php' );
			} else {
				$DC_Product_Vendor->template->get_template( 'shortcode/non_vendor_dashboard.php' );
			}
		}
	}
}
