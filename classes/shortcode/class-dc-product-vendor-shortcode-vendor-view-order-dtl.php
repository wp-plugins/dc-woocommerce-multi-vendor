<?php
class WC_Vendor_Order_Detail_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the demo shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	public function output( $attr ) {
		global $DC_Product_Vendor;
		$DC_Product_Vendor->nocache();
		if ( ! defined( 'MNDASHBAOARD' ) ) define( 'MNDASHBAOARD', true );
		$DC_Product_Vendor->template->get_template( 'shortcode/vendor_order_detail.php' );
	}
}