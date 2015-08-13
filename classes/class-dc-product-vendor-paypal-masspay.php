<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		DC_Product_Vendor_User
 * @version		1.0.0
 * @package		DC_Product_Vendor
 * @author 		DualCube
 */ 
class DC_Product_Vendor_Paypal_Masspay {
	public $is_masspay_enable;
	public $payment_schedule;
	public $api_username;
	public $api_pass;
	public $api_signature;	
	public $test_mode;
	
	public function __construct() {
		$masspay_admin_settings = get_option("dc_payment_settings_name");
		if($masspay_admin_settings  && array_key_exists('is_mass_pay', $masspay_admin_settings)) {
			$this->is_masspay_enable = true;
			$this->payment_schedule = $masspay_admin_settings['payment_schedule'];
			$this->api_username = $masspay_admin_settings['api_username'];
			$this->api_pass = $masspay_admin_settings['api_pass'];
			$this->api_signature = $masspay_admin_settings['api_signature'];
			if(array_key_exists('is_testmode', $masspay_admin_settings)) {
				$this->test_mode = true;
			}
		}								
	}
	
	public function call_masspay_api() {
		global $DC_Product_Vendor;
		require_once($DC_Product_Vendor->plugin_path.'lib/paypal/CallerService.php');
		session_start();
		$emailSubject = urlencode('You have money!');
		$receiverType = urlencode('EmailAddress');
		$currency = urlencode(get_woocommerce_currency());
		$receiver_information = $this->get_receiver_information();
		$nvpstr;
		if($receiver_information) {
			foreach($receiver_information as $receiver) {
				$j = 0;
				$receiverEmail = urlencode($receiver['recipient']);
				$amount = urlencode($receiver['total']);
				$uniqueID = urlencode($receiver['vendor_id']);
				$note = urlencode($receiver['payout_note']);
				$nvpstr.="&L_EMAIL$j=$receiverEmail&L_Amt$j=$amount&L_UNIQUEID$j=$uniqueID&L_NOTE$j=$note";
				$j++;
			}
			$nvpstr.="&EMAILSUBJECT=$emailSubject&RECEIVERTYPE=$receiverType&CURRENCYCODE=$currency" ;
			
			doProductVendorLOG($nvpstr);
			
			$resArray=hash_call("MassPay",$nvpstr);
			
			$ack = strtoupper($resArray["ACK"]);
			if($ack == "SUCCESS" ||  $ack == "SuccessWithWarning" ){
				doProductVendorLOG(json_encode($resArray));
				$commissions = $this->get_query_commission();
				foreach($commissions as $commission) {
					$email_admin = WC()->mailer()->emails['WC_Email_Vendor_Commissions_Paid'];
					$vendor_id = get_post_meta( $commission->ID , '_commission_vendor', true);
					$email_admin->trigger( $commission->ID, $vendor_id );
					update_post_meta($commission->ID, '_paid_status', 'paid');
				}
			} else {
				doProductVendorLOG(json_encode($resArray));
			}
		}
	}

	public function get_receiver_information() {
		global $DC_Product_Vendor;
		$commissions = $this->get_query_commission();
		$commission_data = $commission_totals = $commissions_data = array();
		if($commissions) {
			foreach($commissions as $commission) {
				$DC_Product_Vendor_Commission = new DC_Product_Vendor_Commission();
				$commission_data = $DC_Product_Vendor_Commission->get_commission( $commission->ID );
				$commission_totals[][ $commission_data->vendor->term_id ] = $commission_data->amount;
			}
			// Set info for all payouts
			$currency = get_woocommerce_currency();
			$payout_note = sprintf( __( 'Total commissions earned from %1$s as at %2$s on %3$s', $DC_Product_Vendor->text_domain ), get_bloginfo( 'name' ), date( 'H:i:s' ), date( 'd-m-Y' ) );
			// Set up data for CSV
			$commissions_data = array();
			foreach( $commission_totals as $key => $totals ) {
				foreach($totals as $vendor_id => $total) {
					// Get vendor data
					$vendor = get_dc_vendor_by_term( $vendor_id );
					$vendor_user_id = get_woocommerce_term_meta($vendor_id, '_vendor_user_id', true);
					$vendor_paypal_email = get_user_meta($vendor_user_id, '_vendor_paypal_email', true);
					// Set vendor recipient field
					if( isset( $vendor_paypal_email ) && strlen( $vendor_paypal_email ) > 0 ) {
						$recipient = $vendor_paypal_email;
						$commissions_data[] = array( 
								'recipient' => $recipient,
								'total' => $total,
								'currency' => $currency,
								'vendor_id' =>$vendor_id,
								'payout_note' =>$payout_note
						);
					}
				}
			}
		}
		return $commissions_data;
	}
	
	public function get_query_commission() {
		$args = array(
			'post_type' => 'dc_commission',
			'post_status' => array( 'publish', 'private' ),
			'meta_key' => '_paid_status',
			'meta_value' => 'unpaid',
			'posts_per_page' => 250
		);
		$commissions = get_posts( $args );
		return $commissions;
	}
}
?>