<?php
class DC_Product_Vendor_Settings_Pages {
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
    $pages = get_pages(); 
    $woocommerce_pages = array ( woocommerce_get_page_id('shop'), woocommerce_get_page_id('cart'), woocommerce_get_page_id('checkout'), woocommerce_get_page_id('myaccount'));
    foreach ( $pages as $page ) {
    	if(!in_array($page->ID, $woocommerce_pages)) {
    		$pages_array[$page->ID] = $page->post_title;
    	}
    }
    $settings_tab_options = array("tab" => "{$this->tab}",
                                  "ref" => &$this,
                                  "sections" => array(
                                                      "default_settings_section" => array("title" =>  '', // Section one
                                                                                         "fields" => array(
                                                                                                           "vendor_dashboard" => array('title' => __('Vendor Dashboard', $DC_Product_Vendor->text_domain), 'type' => 'select', 'id' => 'vendor_dashboard', 'label_for' => 'vendor_dashboard', 'name' => 'vendor_dashboard', 'options' => $pages_array, 'hints' => __('Choose your preferred page for vendor dashboard.', $DC_Product_Vendor->text_domain)), // Select
                                                                                                           "shop_settings" => array('title' => __('Shop Settings', $DC_Product_Vendor->text_domain), 'type' => 'select', 'id' => 'shop_settings', 'label_for' => 'shop_settings', 'name' => 'shop_settings', 'options' => $pages_array, 'hints' => __('Choose your preferred page for vendor shop settings', $DC_Product_Vendor->text_domain)), // Select
                                                                                                           "view_order" => array('title' => __('View Vendor Orders', $DC_Product_Vendor->text_domain), 'type' => 'select', 'id' => 'view_order', 'label_for' => 'view_order', 'name' => 'view_order', 'options' => $pages_array, 'hints' => __('Choose your preferred page for vendor view order', $DC_Product_Vendor->text_domain)), // Select
                                                                                                           "vendor_order_detail" => array('title' => __('Vendor Order Detail Page', $DC_Product_Vendor->text_domain), 'type' => 'select', 'id' => 'vendor_order_detail', 'label_for' => 'vendor_order_detail', 'name' => 'vendor_order_detail', 'options' => $pages_array, 'hints' => __('Choose your preferred page for vendor order details', $DC_Product_Vendor->text_domain)), // Select
                                                                                         ), 
                                                                                         )
                                                      
                                                      )
                                  );
    
    $DC_Product_Vendor->admin->settings->settings_field_init(apply_filters("settings_{$this->tab}_tab_options", $settings_tab_options));
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function dc_pages_settings_sanitize( $input ) {
    global $DC_Product_Vendor;
    $new_input = array();
    
    $hasError = false;
    
   
    if( isset( $input['vendor_dashboard'] ) )
      $new_input['vendor_dashboard'] = sanitize_text_field( $input['vendor_dashboard'] );
    
    if( isset( $input['shop_settings'] ) )
      $new_input['shop_settings'] = sanitize_text_field( $input['shop_settings'] );
    
		if( isset( $input['view_order'] ) )
		$new_input['view_order'] = sanitize_text_field( $input['view_order'] );
    
    if( isset( $input['vendor_order_detail'] ) )
      $new_input['vendor_order_detail'] = sanitize_text_field( $input['vendor_order_detail'] );
    
    return $new_input;
  }

  /** 
   * Print the Section text
   */
  public function default_settings_section_info() {
    global $DC_Product_Vendor;
  }
 
  
}