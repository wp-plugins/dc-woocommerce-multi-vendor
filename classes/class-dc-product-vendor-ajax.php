<?php
class DC_Product_Vendor_Ajax {

	public function __construct() {
		add_action('wp_ajax_woocommerce_json_search_vendors', array($this, 'woocommerce_json_search_vendors'));
		add_action('wp_ajax_activate_pending_vendor', array($this, 'activate_pending_vendor'));
		add_action('wp_ajax_reject_pending_vendor', array($this, 'reject_pending_vendor'));
	  add_action( 'wp_ajax_send_report_abuse', array( $this, 'send_report_abuse' ) );
    add_action( 'wp_ajax_nopriv_send_report_abuse', array( $this, 'send_report_abuse' ) );
	}

	/**
	 * Search vendors via AJAX
	 *
	 * @return void
	 */
	function woocommerce_json_search_vendors() {
		global $DC_Product_Vendor;
	
		//check_ajax_referer( 'search-vendors', 'security' );
	
		header( 'Content-Type: application/json; charset=utf-8' );
	
		$term = urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );
		
		if ( empty( $term ) )
			die();
	
		$found_vendors = array();
	
		$args = array(
			'search' => '*'.$term.'*',
			'search_columns' => array( 'user_login' )
		);
		
		$vendors = get_dc_vendors( $args );
	
		if ( $vendors ) {
			foreach ( $vendors as $vendor ) {
				$found_vendors[ $vendor->term_id ] = $vendor->user_data->user_login;
			}
		}
	
		echo json_encode( $found_vendors );
		die();
	}
	
	/**
	 * Activate Pending Vendor via AJAX
	 *
	 * @return void
	 */
	
	function activate_pending_vendor() {
		global $DC_Product_Vendor;
		$user_id = $_POST['user_id'];
		$user = new WP_User( absint( $user_id ) );
		$user->remove_role( 'dc_pending_vendor' );
		$user->remove_role( 'dc_rejected_vendor' );
		update_user_meta($user_id, '_vendor_submit_product', 'Enable');
		update_user_meta($user_id, '_vendor_submit_coupon', 'Enable');
		$user->add_role( 'dc_vendor' );
		$DC_Product_Vendor->user->add_vendor_caps( $user_id );
  	$vendor = get_dc_vendor( $user_id );
		$vendor->generate_term();
		$user_dtl = get_userdata( absint( $user_id ) );
		$email = WC()->mailer()->emails['WC_Email_Approved_New_Vendor_Account'];
		$email->trigger( $user_id, $user_dtl->user_pass );
		die();
	}
	
	/**
	 * Reject Pending Vendor via AJAX
	 *
	 * @return void
	 */
	 
	function reject_pending_vendor() {
		global $DC_Product_Vendor;
		$user_id = $_POST['user_id'];
		$user = new WP_User( absint( $user_id ) );
		if(is_array( $user->roles ) && in_array( 'dc_pending_vendor', $user->roles )) {
			$user->remove_role( 'dc_pending_vendor' );
		}
		$user->add_role( 'dc_rejected_vendor' );
		$user_dtl = get_userdata( absint( $user_id ) );
		$email = WC()->mailer()->emails['WC_Email_Rejected_New_Vendor_Account'];
		$email->trigger( $user_id, $user_dtl->user_pass );
		
		if(in_array('dc_vendor', $old_role)) {
			$vendor = get_dc_vendor($user_id);
			if($vendor) wp_delete_term( $vendor->term_id, 'dc_vendor_shop' );
		}
		wp_delete_user($user_id);
		die();
	}
	
	/**
	 * Report Abuse Vendor via AJAX
	 *
	 * @return void
	 */
	
	function send_report_abuse()  {
		global $DC_Product_Vendor;
		$check = false;
		$name           = sanitize_text_field( $_POST['name'] );
		$from_email     = sanitize_email( $_POST['email'] );
		$user_message   = sanitize_text_field( $_POST['msg'] );
		$product_id     = sanitize_text_field( $_POST['product_id'] );

		$check = ! empty( $name ) && ! empty( $from_email ) && ! empty( $user_message );

		if( $check ) {
			$product = get_post( absint($product_id) );
			$vendors = get_dc_product_vendors( $product_id );
			$vendor = $vendors[0];
 
			$subject    = 'Report an abuse for product'.get_the_title($product_id);
			
			$to         = sanitize_email( get_option( 'admin_email' ) );
			$from_email = sanitize_email( $from_email );
			$headers = "From: {$name} <{$from_email}>" . "\r\n";

			$message = sprintf( __( "User %s (%s) is reporting an abuse on the following product: \n", $DC_Product_Vendor->text_domain ), $name, $from_email );
			$message .= sprintf( __( "Product details: %s (ID: #%s) \n", $DC_Product_Vendor->text_domain ), $product->post_title, $product->ID );

			$message .= sprintf( __( "Vendor shop: %s \n", $DC_Product_Vendor->text_domain ), $vendor->user_data->display_name  );

			$message .= sprintf( __( "Message: %s\n", $DC_Product_Vendor->text_domain ), $user_message  );
			$message .= "\n\n\n";

			$message .= sprintf( __( "Product page:: %s\n", $DC_Product_Vendor->text_domain ), get_the_permalink( $product->ID ) );

			/* === Send Mail === */
			$response = wp_mail( $to, $subject, $message, $headers );
		}
		die();
	}
}
