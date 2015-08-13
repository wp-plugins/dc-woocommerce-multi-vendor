<?php
class DC_Product_Vendor_Settings_Gneral {
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
                                                      "default_settings_section" => array("title" =>  __('General Options -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array("default_commission" => array('title' => __('Default commission', $DC_Product_Vendor->text_domain), 'type' => 'text', 'id' => 'default_commission', 'label_for' => 'default_commission', 'name' => 'default_commission', 'desc' => __('This will be the default commssion(in percentage or fixed) paid to vendors if product and vendor specific commission is not set. ', $DC_Product_Vendor->text_domain)), // Text
                                                                                         	 								 "commission_type" => array('title' => __('Commission Type', $DC_Product_Vendor->text_domain), 'type' => 'select', 'id' => 'commission_type', 'label_for' => 'commission_type', 'name' => 'commission_type', 'options' => array('' => 'Choose Commission Type', 'fixed' => 'Fixed Amount', 'percent' => 'Percentage Amount'), 'hints' => __('Choose your preferred type of commission type.It will effect all commisssion calculations.', $DC_Product_Vendor->text_domain)), // Select
                                                                                         	 								 "commission_include_coupon" => array('title' => __('Include coupons in commission calculations', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'commission_include_coupon', 'label_for' => 'commission_include_coupon', 'hints' => __('Decide whether vendor commissions have to be calculated including coupon value or not.', $DC_Product_Vendor->text_domain), 'name' => 'commission_include_coupon', 'value' => 'Enable'), // Checkbox
                                                                                                           "enable_registration" => array('title' => __('Allow user to become a vendor', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'enable_registration', 'label_for' => 'enable_registration', 'hints' => __('To enable registration option for a vendor, go to WooCommerce > Settings > Accounts and check the Enable registration on the My Account page.', $DC_Product_Vendor->text_domain), 'name' => 'enable_registration', 'value' => 'Enable'), // Checkbox
                                                                                                           "approve_vendor_manually" => array('title' => __('Approve vendors manually', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'approve_vendor_manually', 'label_for' => 'approve_vendor_manually', 'hints' => __('Checking this option will stop automatic registrations for vendors. The admin will have to manually approve each vendor registration application.', $DC_Product_Vendor->text_domain), 'name' => 'approve_vendor_manually', 'value' => 'Enable'), // Checkbox
                                                                                                           "sold_by_catalog" => array('title' => __(' Mention vendor name in product catalog pages', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'sold_by_catalog', 'label_for' => 'sold_by_catalog', 'hints' => __('Checking this box will add the text - Sold by [vendor name] in product catalog pages.', $DC_Product_Vendor->text_domain), 'name' => 'sold_by_catalog', 'value' => 'Enable'), // Checkbox
                                                                                                           "catalog_colorpicker" => array('title' => __('Vendor Name label color', $DC_Product_Vendor->text_domain), 'type' => 'colorpicker', 'id' => 'catalog_colorpicker', 'label_for' => 'catalog_colorpicker', 'name' => 'catalog_colorpicker', 'default' => '000000', 'hints' => __('Vendor Name label color in shop page and single product page', $DC_Product_Vendor->text_domain)), // Colorpicker
                                                                                                           "catalog_hover_colorpicker" => array('title' => __('Vendor Name label color(on hover)', $DC_Product_Vendor->text_domain), 'type' => 'colorpicker', 'id' => 'catalog_hover_colorpicker', 'label_for' => 'catalog_hover_colorpicker', 'name' => 'catalog_hover_colorpicker', 'default' => '000000', 'hints' => __('Vendor Name label color on hover in shop page and single product page', $DC_Product_Vendor->text_domain)), // Colorpicker
                                                                                                           "sold_by_cart_and_checkout_email" => array('title' => __('Mention vendor name in cart, checkout page, and emails.', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'sold_by_cart_and_checkout', 'label_for' => 'sold_by_cart_and_checkout', 'hints' => __('Checking this box will add the text - Sold by [vendor name] in cart, checkout page and emails', $DC_Product_Vendor->text_domain), 'name' => 'sold_by_cart_and_checkout', 'value' => 'Enable'), // Checkbox
                                                                                                           "sold_by_text" => array('title' => __('Alternative to the text -Sold By', $DC_Product_Vendor->text_domain), 'type' => 'text', 'id' => 'sold_by_text', 'label_for' => 'sold_by_text', 'name' => 'sold_by_text', 'hints' => __('modify the default text', $DC_Product_Vendor->text_domain)), // Text
                                                                                                           "notify_configure_vendor_store" => array('title' => __('Add Vendor Notify section', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'notify_configure_vendor_store', 'label_for' => 'notify_configure_vendor_store', 'hints' => __('Add a section in the vendor dashboard to notify vendors if they have not configured their stores properly', $DC_Product_Vendor->text_domain), 'name' => 'notify_configure_vendor_store', 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ), 
                                                      )
                                  );
    
    $DC_Product_Vendor->admin->settings->settings_field_init(apply_filters("settings_{$this->tab}_tab_options", $settings_tab_options));
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function dc_general_settings_sanitize( $input ) {
    global $DC_Product_Vendor;
    $new_input = array();
    
    $hasError = false;
    
    if( isset( $input['default_commission'] ) && $input['default_commission'] != 0 ) {
      $new_input['default_commission'] = $input['default_commission'];
    }

    if( isset( $input['enable_registration'] ) )
      $new_input['enable_registration'] = sanitize_text_field( $input['enable_registration'] );
    
    if( isset( $input['commission_type'] ) )
      $new_input['commission_type'] = sanitize_text_field( $input['commission_type'] );
    
    
    if( isset( $input['notify_configure_vendor_store'] ) )
      $new_input['notify_configure_vendor_store'] = sanitize_text_field( $input['notify_configure_vendor_store'] );               
    
    if( isset( $input['commission_include_coupon'] ) )
      $new_input['commission_include_coupon'] = sanitize_text_field( $input['commission_include_coupon'] );
    
    
    if( isset( $input['approve_vendor_manually'] ) )
      $new_input['approve_vendor_manually'] = sanitize_text_field( $input['approve_vendor_manually'] );
    
    if( isset( $input['enable_html_shop_desc'] ) )
      $new_input['enable_html_shop_desc'] = sanitize_text_field( $input['enable_html_shop_desc'] );
    
    if( isset( $input['vendor_shop_page'] ) )
      $new_input['vendor_shop_page'] = sanitize_text_field( $input['vendor_shop_page'] );
    
    if( isset( $input['sold_by_cart_and_checkout'] ) )
      $new_input['sold_by_cart_and_checkout'] = sanitize_text_field( $input['sold_by_cart_and_checkout'] );
    
    if( isset( $input['add_vendor_enquiry'] ) )
      $new_input['add_vendor_enquiry'] = sanitize_text_field( $input['add_vendor_enquiry'] );
    
    if( isset( $input['sold_by_text'] ) )
      $new_input['sold_by_text'] = sanitize_text_field( $input['sold_by_text'] );
    
    if( isset( $input['sold_by_catalog'] ) )
      $new_input['sold_by_catalog'] = sanitize_text_field( $input['sold_by_catalog'] );
    
    if( isset( $input['catalog_colorpicker'] ) )
      $new_input['catalog_colorpicker'] = sanitize_text_field( $input['catalog_colorpicker'] );
    
    if( isset( $input['catalog_hover_colorpicker'] ) )
      $new_input['catalog_hover_colorpicker'] = sanitize_text_field( $input['catalog_hover_colorpicker'] );

    
    return $new_input;
  }

  /** 
   * Print the Section text
   */
  public function default_settings_section_info() {
    global $DC_Product_Vendor;
  }
  
  /** 
   * Print the Section text
   */
  public function custom_settings_section_info() {
    global $DC_Product_Vendor;
  }
  
}