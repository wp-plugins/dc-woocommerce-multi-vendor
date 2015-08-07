<?php

class DC_Product_Vendor_MassPay_Cron {

	public function __construct() {
		add_action('paypal_masspay_cron_start', array(&$this, 'do_paypal_mass_payment') );
	}	

	/**
	* start paypal masspay cron
	*/
	function do_paypal_mass_payment() {
		global $DC_Product_Vendor;

		$payment_admin_settings = get_option('dc_payment_settings_name');

		if(array_key_exists('is_mass_pay', $payment_admin_settings)) {
			doProductVendorLOG("Cron Run Start for array creatation @ " . date('d/m/Y g:i:s A', time()));
			update_option('paypal_masspay_cron_running', 1);
			for($i = 0; $i < 100; $i++) {
				$commisions = $DC_Product_Vendor->paypal_masspay->get_query_commission();
				if(!empty($commisions)) {
					$DC_Product_Vendor->paypal_masspay->call_masspay_api();
				} else {
					doProductVendorLOG("Going to break the cron loop @ " . date('d/m/Y g:i:s A', time()));
					break;
				}				
			}
			doProductVendorLOG("Cron Run Finish @ " . date('d/m/Y g:i:s A', time()));
			doProductVendorLOG("Next Payment import cron @ " . date('d/m/Y g:i:s A', wp_next_scheduled( 'paypal_masspay_cron_start' )) . "::". date('d/m/Y g:i:s A', time()));
		}
		delete_option( 'paypal_masspay_cron_running' );
	}
}