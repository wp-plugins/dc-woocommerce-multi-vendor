<?php

/**
 * Demo plugin Install
 *
 * Plugin install script which adds default pages, taxonomies, and database tables to WordPress. Runs on activation and upgrade.
 *
 * @author 		Dualcube
 * @package 	dc_demo_plugin/Admin/Install
 * @version    0.0.1
 */
class DC_Product_Vendor_Install {
  
  public function __construct() {
  	
    global $DC_Product_Vendor;
	
    if(!get_option( "dc_product_vendor_plugin_page_install")) $this->dc_product_vendor_plugin_create_pages();
    
    update_option( "dc_product_vendor_plugin_db_version", $DC_Demo_Plugin->version );
    
    update_option( "dc_product_vendor_plugin_page_install", 1 );
    
    $this->save_default_plugin_settings();
  }
  
  /**
   * Create a page
   *
   * @access public
   * @param mixed $slug Slug for the new page
   * @param mixed $option Option name to store the page's ID
   * @param string $page_title (default: '') Title for the new page
   * @param string $page_content (default: '') Content for the new page
   * @param int $post_parent (default: 0) Parent for the new page
   * @return void
   */
  function dc_product_vendor_plugin_create_page( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
    global $wpdb;
    $option_value = get_option( $option );
    if ( $option_value > 0 && get_post( $option_value ) )
      return;
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '$slug' LIMIT 1;");
    if ( $page_found ) :
      if ( ! $option_value )
        update_option( $option, $page_found );
      return;
    endif;
    $page_data = array(
          'post_status' 		=> 'publish',
          'post_type' 		=> 'page',
          'post_author' 		=> 1,
          'post_name' 		=> $slug,
          'post_title' 		=> $page_title,
          'post_content' 		=> $page_content,
          'post_parent' 		=> $post_parent,
          'comment_status' 	=> 'closed'
      );
      $page_id = wp_insert_post( $page_data );
      update_option( $option, $page_id );
  }
  
  /**
   * Create pages that the plugin relies on, storing page id's in variables.
   *
   * @access public
   * @return void
   */
  function dc_product_vendor_plugin_create_pages() {
    global $DC_Product_Vendor;
    // Dc_demo_plugins test page
    $this->dc_product_vendor_plugin_create_page( esc_sql( _x('vendor_dashboard', 'page_slug', $DC_Product_Vendor->text_domain) ), 'dc_product_vendor_vendor_dashboard_page_id', __('Vendor Dashboard', $DC_Product_Vendor->text_domain), '[vendor_dashboard][vendor_report][vendor_orders]' );
    $this->dc_product_vendor_plugin_create_page( esc_sql( _x('shop_settings', 'page_slug', $DC_Product_Vendor->text_domain) ), 'dc_product_vendor_shop_settings_page_id', __('Shop Settings', $DC_Product_Vendor->text_domain), '[shop_settings]' );
    $this->dc_product_vendor_plugin_create_page( esc_sql( _x('vendor_orders', 'page_slug', $DC_Product_Vendor->text_domain) ), 'dc_product_vendor_vendor_orders_page_id', __('Vendor Orders', $DC_Product_Vendor->text_domain), '[vendor_orders_by_product]' );
    $this->dc_product_vendor_plugin_create_page( esc_sql( _x('vendor_order_detail', 'page_slug', $DC_Product_Vendor->text_domain) ), 'dc_product_vendor_vendor_order_detail_page_id', __('Vendor Order Details', $DC_Product_Vendor->text_domain), '[vendor_order_detail]' );
		$array_pages = array();
		$dc_product_vendor_vendor_dashboard_page_id = get_option('dc_product_vendor_vendor_dashboard_page_id');
		$dc_product_vendor_shop_settings_page_id = get_option('dc_product_vendor_shop_settings_page_id');
		$dc_product_vendor_vendor_orders_page_id = get_option('dc_product_vendor_vendor_orders_page_id');
		$dc_product_vendor_vendor_order_detail_page_id = get_option('dc_product_vendor_vendor_order_detail_page_id');
		$array_pages['vendor_dashboard'] = $dc_product_vendor_vendor_dashboard_page_id;
		$array_pages['shop_settings'] = $dc_product_vendor_shop_settings_page_id;
		$array_pages['view_order'] = $dc_product_vendor_vendor_orders_page_id;
		$array_pages['vendor_order_detail'] = $dc_product_vendor_vendor_order_detail_page_id;
		update_option('dc_pages_settings_name', $array_pages);
  }
  
  /**
   * save default product vendor plugin settings
   *
   * @access public
   * @return void
   */
  function save_default_plugin_settings() {
		$general_settings = get_option('dc_general_settings_name');
		if(empty($general_settings)) {
			$general_settings = array (
				'enable_registration' => 'Enable',
				'approve_vendor_manually' => 'Enable',
				'sold_by_cart_and_checkout' => 'Enable',
				'catalog_colorpicker' => '#000000',
				'catalog_hover_colorpicker' => '#000000',
				'commission_type' => 'percent',
			);
			update_option('dc_general_settings_name', $general_settings);
		}
		$product_settings = get_option('dc_product_settings_name');
		if(empty($product_settings)) {
			$product_settings = array (
				'inventory' => 'Enable',
				'shipping' => 'Enable',
				'linked_products' => 'Enable',
				'attribute' => 'Enable',
				'advanced' => 'Enable',
				'simple' => 'Enable',
				'variable' => 'Enable',
				'grouped' => 'Enable',
				'virtual' => 'Enable',
				'external' => 'Enable',
				'downloadable' => 'Enable',
				'taxes' => 'Enable',
				'add_comment' => 'Enable',
				'comment_box' => 'Enable',
				'sku' => 'Enable',
			);
			update_option('dc_product_settings_name', $product_settings);
		}
		$capabilities_settings = get_option('dc_capabilities_settings_name');
		if(empty($capabilities_settings)) {
			$capabilities_settings = array (
				'is_upload_files' => 'Enable',
				'is_submit_product' => 'Enable',
				'is_order_csv_export' => 'Enable',
				'is_show_email' => 'Enable',
				'is_vendor_view_comment' => 'Enable',
				'show_cust_billing_add' => 'Enable',
				'show_cust_shipping_add' => 'Enable',
				'show_cust_order_calulations' => 'Enable',
				'show_customer_dtl' => 'Enable',
				'show_customer_billing' => 'Enable',
				'show_customer_shipping' => 'Enable',
				'show_cust_add' => 'Enable',
			);
			update_option('dc_capabilities_settings_name', $capabilities_settings);
		}
	}
}
?>
