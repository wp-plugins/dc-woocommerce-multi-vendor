<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Email_Notify_Shipped' ) ) :

/**
 * Customer New Account
 *
 * An email sent to the customer when they create an account.
 *
 * @class 		WC_Email_Approved_New_Vendor_Account
 * @version		2.0.0
 * @package		WooCommerce/Classes/Emails
 * @author 		WooThemes
 * @extends 	WC_Email
 */
class WC_Email_Notify_Shipped extends WC_Email {

	var $vendor_id;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		global $DC_Product_Vendor;
		$this->id 				= 'notify_shipped';
		$this->title 			= __( 'Notify Shipped', $DC_Product_Vendor->text_domain );
		$this->description		= __( 'Confirm Customer that Vendor has shipped the Order', $DC_Product_Vendor->text_domain );

		$this->template_html 	= 'emails/vendor-notify-shipped.php';
		$this->template_plain 	= 'emails/plain/vendor-notify-shipped.php';

		$this->subject 			= __( 'Your Order on {site_title} has Shipped', $DC_Product_Vendor->text_domain);
		$this->heading      	= __( 'Welcome to {site_title}', $DC_Product_Vendor->text_domain);
		$this->template_base = $DC_Product_Vendor->plugin_path . 'templates/';
		// Call parent constuctor
		parent::__construct();
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $order_id, $customer_email, $vendor_id ) {

		if ( $order_id && $customer_email && $vendor_id) {
			$this->object 		= new WC_Order( $order_id );

			$this->find[] = '{order_date}';
			$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
			
			$this->find[] = '{order_number}';
			$this->replace[] = $this->object->get_order_number();
			$this->vendor_id = $vendor_id;
			
			$user = get_user_by( 'id', $user_id );
			
			$this->recipient = $customer_email;
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() )
			return;

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	
	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		wc_get_template( $this->template_html, array(
			'email_heading'      => $this->get_heading(),
			'vendor_id'         => $this->vendor_id,
			'order'						 => $this->object,
			'blogname'           => $this->get_blogname(),
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
			'email_heading'      => $this->get_heading(),
			'vendor_id'         =>  $this->vendor_id,
			'order' 						=> $this->object,
			'blogname'           => $this->get_blogname(),
			'sent_to_admin' => false,
			'plain_text'    => true
		) ,'', $this->template_base );
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
				'label' 		=> __( 'Enable this email notification', $DC_Product_Vendor->text_domain ),
				'default' 		=> 'yes'
			),
			'subject' => array(
				'title' 		=> __( 'Subject', $DC_Product_Vendor->text_domain ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $DC_Product_Vendor->text_domain ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'heading' => array(
				'title' 		=> __( 'Email Heading', $DC_Product_Vendor->text_domain ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', $DC_Product_Vendor->text_domain ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'email_type' => array(
				'title' 		=> __( 'Email type', $DC_Product_Vendor->text_domain ),
				'type' 			=> 'select',
				'description' 	=> __( 'Choose which format of email to send.', $DC_Product_Vendor->text_domain ),
				'default' 		=> 'html',
				'class'			=> 'email_type',
				'options'		=> array(
					'plain'		 	=> __( 'Plain text', $DC_Product_Vendor->text_domain ),
					'html' 			=> __( 'HTML', $DC_Product_Vendor->text_domain ),
					'multipart' 	=> __( 'Multipart', $DC_Product_Vendor->text_domain ),
				)
			)
		);
    }
}

endif;

return new WC_Email_Notify_Shipped();