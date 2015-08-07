<?php
class DC_Product_Vendor_Settings_Capabilities {
   /**
   * Holds the values to be used in the fields callbacks
   */
  private $options;
  
  private $tab;

  /**
   * Start up
   */
  public function __construct($tab) {
    $this->tab = $tab;
    $this->options = get_option( "dc_{$this->tab}_settings_name" );
    $this->settings_page_init();
  }
  
  /**
   * Register and add settings
   */
  public function settings_page_init() {
    global $DC_Product_Vendor;
    
    $settings_tab_options = array("tab" => "{$this->tab}",
                                  "ref" => &$this,
                                  "sections" => array(
                                  										"products_settings_section" => array("title" =>  __('Products -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "is_submit_product" => array('title' => __('Submit products', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_submit_product', 'label_for' => 'is_submit_product', 'desc' => __('Checking this box will turn on the global setting. If you want to remove this feature from an individual vendor, please uncheck the relevant box in the Edit Vendor tab. To visit the vendor tab, go to users>edit users.', $DC_Product_Vendor->text_domain), 'name' => 'is_submit_product', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_published_product" => array('title' => __('Vendor can publish products without admin permission ?', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_published_product', 'label_for' => 'is_published_product', 'name' => 'is_published_product', 'hints' => __('This helps vendors to publish products automatically without Admin Permissions',  $DC_Product_Vendor->text_domain), 'value' => 'Enable'), // Checkbox
                                                                                                           "is_upload_files" => array('title' => __('Upload media files', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_upload_files', 'label_for' => 'is_upload_files', 'name' => 'is_upload_files', 'hints' => __('This allows Vendors to upload Media',  $DC_Product_Vendor->text_domain),  'value' => 'Enable'), // Checkbox
                                                                                                           "is_submit_coupon" => array('title' => __('Submit Coupons', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_submit_coupon', 'label_for' => 'is_submit_coupon', 'name' => 'is_submit_coupon', 'desc' => __('Checking this box will turn on the global setting. If you want to remove this feature from an individual vendor, please uncheck the relevant box in the Edit Vendor tab. To visit the vendor tab, go to users>edit users.', $DC_Product_Vendor->text_domain),  'value' => 'Enable'), // Checkbox
                                                                                                           "give_tax" => array('title' => __('Give Taxes per product', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'give_tax', 'label_for' => 'give_tax', 'name' => 'give_tax',  'hint' => __('Give vendors any tax collected per-product', $DC_Product_Vendor->text_domain),  'value' => 'Enable'), // Checkbox
                                                                                                           "give_shipping" => array('title' => __('Give Shipping per product', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'give_shipping', 'label_for' => 'give_shipping', 'name' => 'give_shipping', 'desc' => __('Currently we have support on woocoomerce defalut shipping methods like local delivery and international delivery and make fee type as per product or per item.', $DC_Product_Vendor->text_domain), 'hint' => __(' Give vendors any shipping collected per-product ', $DC_Product_Vendor->text_domain),  'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ), 
                                                      "vendor_order_export" => array("title" =>  __('Order export -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "is_order_csv_export" => array('title' => __('Allow export order', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_order_csv_export', 'label_for' => 'is_order_csv_export', 'name' => 'is_order_csv_export', 'hints' => __('Give permission to export Orders', $DC_Product_Vendor->text_domain), 'value' => 'Enable'), // Checkbox
                                                                                                           "is_order_show_email" => array('title' => __('Show customer email in export data', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_show_email', 'label_for' => 'is_show_email', 'name' => 'is_show_email', 'hints' => __('Show customer email in exported Data', $DC_Product_Vendor->text_domain), 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ), 
                                                      "vendor_order_comment" => array("title" =>  __('Order note -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "is_vendor_view_comment" => array('title' => __('View comment', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_vendor_view_comment', 'label_for' => 'is_vendor_view_comment', 'name' => 'is_vendor_view_comment',  'hints' => __('Vendor can see order notes', $DC_Product_Vendor->text_domain), 'value' => 'Enable'), // Checkbox
                                                                                                           "is_vendor_submit_comment" => array('title' => __('Submit Comment', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_vendor_submit_comment', 'label_for' => 'is_vendor_submit_comment', 'name' => 'is_vendor_submit_comment', 'hints' => __('Vendor can add order notes', $DC_Product_Vendor->text_domain), 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ), 
                                                      "vendor_email_settings" => array("title" =>  __('Vendor Order Email Settings -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "show_cust_name" => array('title' => __('Show customer name, phone no. and email in mail', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'show_cust_add', 'label_for' => 'show_cust_add', 'name' => 'show_cust_add', 'value' => 'Enable'), // Checkbox
                                                                                                           "show_cust_billing_add" => array('title' => __('Show customer billing address', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'show_cust_billing_add', 'label_for' => 'show_cust_billing_add', 'name' => 'show_cust_billing_add', 'value' => 'Enable'), // Checkbox
                                                                                                           "show_cust_shipping_add" => array('title' => __('Show customer shipping address', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'show_cust_shipping_add', 'label_for' => 'show_cust_shipping_add', 'name' => 'show_cust_shipping_add', 'value' => 'Enable'), // Checkbox
                                                                                                           "show_cust_order_calulations" => array('title' => __('Show customer order calculations', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'show_cust_order_calulations', 'label_for' => 'show_cust_order_calulations', 'name' => 'show_cust_order_calulations', 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ),
                                                                            
                                                      "vendor_order_dtl" => array("title" =>  __('Frontend Vendor Order Details -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "show_customer_dtl" => array('title' => __('Show cutomer detail', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'show_customer_dtl', 'label_for' => 'show_customer_dtl', 'name' => 'show_customer_dtl', 'value' => 'Enable'), // Checkbox
                                                                                                           "show_customer_billing" => array('title' => __('Show customer billing address', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'show_customer_billing', 'label_for' => 'show_customer_billing', 'name' => 'show_customer_billing', 'value' => 'Enable'), // Checkbox
                                                                                                           "show_customer_shipping" => array('title' => __('Show customer shipping address', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'show_customer_shipping', 'label_for' => 'show_customer_shipping', 'name' => 'show_customer_shipping', 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         )
                                                      ),
                                  );
    
    $DC_Product_Vendor->admin->settings->settings_field_init(apply_filters("settings_{$this->tab}_tab_options", $settings_tab_options));
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function dc_capabilities_settings_sanitize( $input ) {
    global $DC_Product_Vendor;
    $new_input = array();
    
    $hasError = false;
    
    if( isset( $input['is_upload_files'] ) ) {
      $new_input['is_upload_files'] = sanitize_text_field( $input['is_upload_files'] );  
      add_cap_existing_users('upload_files');
    } else {
    	 change_cap_existing_users('is_upload_files');
    }
    
    if( isset( $input['is_published_product'] ) ) {
      $new_input['is_published_product'] = sanitize_text_field( $input['is_published_product'] );
      add_cap_existing_users('publish_products');
    } else {
    	change_cap_existing_users('is_published_product');
    }
    
    if( isset( $input['is_submit_product'] ) ) {
      $new_input['is_submit_product'] = sanitize_text_field( $input['is_submit_product'] );
      add_cap_existing_users('is_submit_product');
    } else {
    	 change_cap_existing_users('is_submit_product');
    	 if( isset( $input['is_published_product'] ) ) {
    	 	 unset( $new_input['is_published_product'] );
    	 }
    }
    
    if( isset( $input['is_submit_coupon'] ) ) {
      $new_input['is_submit_coupon'] = sanitize_text_field( $input['is_submit_coupon'] );
      add_cap_existing_users('is_submit_coupon');
    } else {
    	 change_cap_existing_users('is_submit_coupon');
    }
    
    if( isset( $input['give_tax'] ) )
      $new_input['give_tax'] = sanitize_text_field( $input['give_tax'] );  
    
    if( isset( $input['give_shipping'] ) )
      $new_input['give_shipping'] = sanitize_text_field( $input['give_shipping'] );  
    
    
    if( isset( $input['is_order_csv_export'] ) )
      $new_input['is_order_csv_export'] = sanitize_text_field( $input['is_order_csv_export'] );      
    
    if( isset( $input['is_show_email'] ) )
      $new_input['is_show_email'] = sanitize_text_field( $input['is_show_email'] );    
    
    if( isset( $input['is_vendor_submit_comment'] ) ) 
      $new_input['is_vendor_submit_comment'] = sanitize_text_field( $input['is_vendor_submit_comment'] );    
    
    if( isset( $input['is_vendor_view_comment'] ) ) {
      $new_input['is_vendor_view_comment'] = sanitize_text_field( $input['is_vendor_view_comment'] );      
    } else if( isset( $input['is_vendor_submit_comment'] ) ) {
    	unset( $new_input['is_vendor_submit_comment'] );
    }
    
    if( isset( $input['is_order_email'] ) )
      $new_input['is_order_email'] = sanitize_text_field( $input['is_order_email'] );       
    
    if( isset( $input['show_cust_billing_add'] ) )
      $new_input['show_cust_billing_add'] = sanitize_text_field( $input['show_cust_billing_add'] );
    
    if( isset( $input['show_cust_shipping_add'] ) )
      $new_input['show_cust_shipping_add'] = sanitize_text_field( $input['show_cust_shipping_add'] );
    
    if( isset( $input['show_cust_order_calulations'] ) )
      $new_input['show_cust_order_calulations'] = sanitize_text_field( $input['show_cust_order_calulations'] );
    
    if( isset( $input['show_customer_dtl'] ) )
      $new_input['show_customer_dtl'] = sanitize_text_field( $input['show_customer_dtl'] );
    
    if( isset( $input['show_customer_billing'] ) )
      $new_input['show_customer_billing'] = sanitize_text_field( $input['show_customer_billing'] );
    
    if( isset( $input['show_customer_shipping'] ) )
      $new_input['show_customer_shipping'] = sanitize_text_field( $input['show_customer_shipping'] );
    
		if( isset( $input['show_cust_add'] ) )
			$new_input['show_cust_add'] = sanitize_text_field( $input['show_cust_add'] );

    return $new_input;
  }

  /** 
   * Print the Section text
   */
  public function products_settings_section_info() {
    global $DC_Product_Vendor;
  }
  
  /** 
   * Print the Section text
   */
  public function vendor_email_settings_info() {
    global $DC_Product_Vendor;
    _e('Show/Hide Customer Details in order notification emails to vendors', $DC_Product_Vendor->text_domain);
  }
  
  /** 
   * Print the Section text
   */
  public function view_vendor_order_info() {
    global $DC_Product_Vendor;
  }
 
  /** 
   * Print the Section text
   */
  public function vendor_order_dtl_info() {
    global $DC_Product_Vendor;
    _e('Show/Hide customer details in order view page of a vendor', $DC_Product_Vendor->text_domain);
  }
  
  /** 
   * Print the Section text
   */
  public function vendor_order_export_info() {
    global $DC_Product_Vendor;
  }
  
  /** 
   * Print the Section text
   */
  public function vendor_order_comment_info() {
    global $DC_Product_Vendor;
  }
  
}