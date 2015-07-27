<?php
class WC_Vendor_Orders_Shortcode {

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
		$template_obj = new DC_Product_Vendor_Template();
		$DC_Product_Vendor->template->get_template( 'shortcode/vendor_orders.php' );
	}
}