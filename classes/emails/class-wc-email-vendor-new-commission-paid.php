<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email_Vendor_Commissions_Paid' ) ) :

/**
 * New Order Email
 *
 * An email sent to the admin when a new order is received/paid for.
 *
 * @class 		WC_Email_Vendor_Commissions_Paid
 * @version		2.0.0
 * @package		WooCommerce/Classes/Emails
 * @author 		WooThemes
 * @extends 	WC_Email
 *
 * @property DC_Commission $object
 */
class WC_Email_Vendor_Commissions_Paid extends WC_Email {

	/**
	 * Constructor
	 */
	function __construct() {
		global $DC_Product_Vendor;
		$this->id 				= 'vendor_commissions_paid';
		$this->title 			= __( 'Commission paid (for Vendor)', $DC_Product_Vendor->text_domain );
		$this->description		= __( 'New commissions have been credited to vendor', $DC_Product_Vendor->text_domain);

		$this->heading 			= __( 'Vendor\'s Commission paid', $DC_Product_Vendor->text_domain);
		$this->subject      	= __( '[{site_title}] Commission paid', $DC_Product_Vendor->text_domain);

		$this->template_base = $DC_Product_Vendor->plugin_path . 'templates/';
		$this->template_html 	= 'emails/vendor-commissions-paid.php';
		$this->template_plain 	= 'emails/plain/vendor-commissions-paid.php';


		// Call parent constructor
		parent::__construct();
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 *
	 * @param Commission $commission Commission paid
	 */
	function trigger( $commission, $vendor_id ) {
		
		if(!isset($commission) && !isset($vendor_id)) return;
		
		$this->vendor = get_dc_vendor_by_term($vendor_id);
		$this->commission = $commission;
    $this->recipient = $this->vendor->user_data->user_email;
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		global $DC_Product_Vendor;
		ob_start();
		wc_get_template( $this->template_html, array(
			'commission'    => $this->commission,
			'email_heading' => $this->get_heading(),
			'vendor' =>  $this->vendor,
			'sent_to_admin' => false,
			'plain_text'    => false
			), '', $this->template_base);
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		wc_get_template( $this->template_plain, array(
			'commission'    => $this->commission,
			'email_heading' => $this->get_heading(),
			'vendor' =>  $this->vendor,
			'sent_to_admin' => false,
			'plain_text'    => false
			), '', $this->template_base);
		return ob_get_clean();
	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
		global $DC_Product_Vendor;
		$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __( 'Enable/Disable', $DC_Product_Vendor->text_domain ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable notification for this email', $DC_Product_Vendor->text_domain ),
				'default' 		=> 'yes'
			),
			'subject' => array(
				'title' 		=> __( 'Subject', $DC_Product_Vendor->text_domain ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', $DC_Product_Vendor->text_domain ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'heading' => array(
				'title' 		=> __( 'Email Heading', $DC_Product_Vendor->text_domain ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the main heading contained in the email notification. Leave it blank to use the default heading: <code>%s</code>.', $DC_Product_Vendor->text_domain ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'email_type' => array(
				'title' 		=> __( 'Email type', $DC_Product_Vendor->text_domain ),
				'type' 			=> 'select',
				'description' 	=> __( 'Choose format for the email that will be sent.', $DC_Product_Vendor->text_domain ),
				'default' 		=> 'html',
				'class'			=> 'email_type wc-enhanced-select',
				'options'		=> $this->get_email_type_options()
			)
		);
	}
}

endif;

return new WC_Email_Vendor_Commissions_Paid();