<?php
class DC_Product_Vendor_Settings_Product {
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
                                                      "default_settings_section_left_pnl" => array("title" =>  __('Left Side Pannel -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "is_inventory" => array('title' => __('Inventory', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_inventory', 'label_for' => 'is_inventory', 'name' => 'inventory', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_shipping" => array('title' => __('Shipping', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_shipping', 'label_for' => 'is_shipping', 'name' => 'shipping', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_linked_products" => array('title' => __('Linked Products', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_linked_products', 'label_for' => 'is_linked_products', 'name' => 'linked_products', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_attribute" => array('title' => __('Attributes', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_attribute', 'label_for' => 'is_attribute', 'name' => 'attribute', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_advanced" => array('title' => __('Advanced', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_advanced', 'label_for' => 'is_advanced', 'name' => 'advanced', 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ), 
                                                      "default_settings_section_types" => array("title" =>  __('Product types -' , $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "is_simple" => array('title' => __('Simple', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_simple', 'label_for' => 'is_simple', 'name' => 'simple', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_variable" => array('title' => __('Variable', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_variable', 'label_for' => 'is_variable', 'name' => 'variable', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_grouped" => array('title' => __('Grouped', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_grouped', 'label_for' => 'is_grouped', 'name' => 'grouped', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_external" => array('title' => __('External / Affilate', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_external', 'label_for' => 'is_external', 'name' => 'external', 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ), 
                                                      "default_settings_section_type_option" => array("title" =>  __('Type Options -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "is_virtual" => array('title' => __('Virtual', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_virtual', 'label_for' => 'is_virtual', 'name' => 'virtual', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_downloadable" => array('title' => __('Downloadable', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_downloadable', 'label_for' => 'is_downloadable', 'name' => 'downloadable', 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ), 
                                                      "default_settings_section_miscellaneous" => array("title" =>  __('Miscellaneous -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                           "is_taxes" => array('title' => __('Taxes', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_taxes', 'label_for' => 'is_taxes', 'name' => 'taxes', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_add_comment" => array('title' => __('Add Comment', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_add_comment', 'label_for' => 'is_add_comment', 'name' => 'add_comment', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_comment_box" => array('title' => __('Comment Box', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_comment_box', 'label_for' => 'is_comment_box', 'name' => 'comment_box', 'value' => 'Enable'), // Checkbox
                                                                                                           "is_sku" => array('title' => __('SKU', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_sku', 'label_for' => 'is_sku', 'name' => 'sku', 'value' => 'Enable'), // Checkbox
                                                                                                           )
                                                                                         ), 
                                                      "default_settings_section_style" => array("title" =>  __('Stylesheet -', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array(
                                                                                                          "stylesheet" => array('title' => '', 'type' => 'textarea', 'id' => 'stylesheet', 'label_for' => 'stylesheet', 'name' => 'stylesheet', 'rows' => 6, 'placeholder' => __('Style Sheet', $DC_Product_Vendor->text_domain), 'desc' => __('You can added css in the text area that will be load on the product page', $DC_Product_Vendor->text_domain)), // Textarea
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
  public function dc_product_settings_sanitize( $input ) {
    global $DC_Product_Vendor;
    $new_input = array();
    
    $hasError = false;
    
    
    if( isset( $input['stylesheet'] ) )
      $new_input['stylesheet'] = sanitize_text_field( $input['stylesheet'] );

    if( isset( $input['inventory'] ) )
      $new_input['inventory'] = sanitize_text_field( $input['inventory'] );
    
    if( isset( $input['shipping'] ) )
      $new_input['shipping'] = sanitize_text_field( $input['shipping'] );
    
    if( isset( $input['linked_products'] ) )
      $new_input['linked_products'] = sanitize_text_field( $input['linked_products'] );
    
    if( isset( $input['attribute'] ) )
      $new_input['attribute'] = sanitize_text_field( $input['attribute'] );
    
    if( isset( $input['advanced'] ) )
      $new_input['advanced'] = sanitize_text_field( $input['advanced'] ); 
    
    if( isset( $input['simple'] ) )
      $new_input['simple'] = sanitize_text_field( $input['simple'] );
    
		if( isset( $input['variable'] ) )
			$new_input['variable'] = sanitize_text_field( $input['variable'] );
    
    if( isset( $input['grouped'] ) )
      $new_input['grouped'] = sanitize_text_field( $input['grouped'] );
    
    if( isset( $input['virtual'] ) )
      $new_input['virtual'] = sanitize_text_field( $input['virtual'] );
    
    if( isset( $input['external'] ) )
      $new_input['external'] = sanitize_text_field( $input['external'] );
    
    if( isset( $input['downloadable'] ) )
      $new_input['downloadable'] = sanitize_text_field( $input['downloadable'] );
    
    if( isset( $input['taxes'] ) )
      $new_input['taxes'] = sanitize_text_field( $input['taxes'] );
    
    if( isset( $input['add_comment'] ) )
      $new_input['add_comment'] = sanitize_text_field( $input['add_comment'] );
    
    if( isset( $input['comment_box'] ) )
      $new_input['comment_box'] = sanitize_text_field( $input['comment_box'] );
    
    if( isset( $input['sku'] ) )
      $new_input['sku'] = sanitize_text_field( $input['sku'] );
    
    return $new_input;
  }

  /** 
   * Print the Section text
   */
  public function default_settings_section_left_pnl_info() {
    global $DC_Product_Vendor;
    _e('Show/Hide Left Pannel Info sections in Edit Product Page', $DC_Product_Vendor->text_domain);
  }
  
  /** 
   * Print the Section text
   */
  public function default_settings_section_types_info() {
    global $DC_Product_Vendor;
    _e('Show/Hide Product Types sections in Edit Product Page', $DC_Product_Vendor->text_domain);
  }
  /** 
   * Print the Section text
   */
  public function default_settings_section_type_option_info() {
    global $DC_Product_Vendor;
    _e('Show/Hide Product Type option sections in Edit Product Page', $DC_Product_Vendor->text_domain);
  }
  /** 
   * Print the Section text
   */
  public function default_settings_section_miscellaneous_info() {
    global $DC_Product_Vendor;
    _e('Show/Hide Miscellaneous sections in Edit Product Page', $DC_Product_Vendor->text_domain);
  }
 
  /** 
   * Print the Section text
   */
  public function default_settings_section_style_info() {
    global $DC_Product_Vendor;
    _e('Add extra style options in Edit Product Page', $DC_Product_Vendor->text_domain);
  }
  
}