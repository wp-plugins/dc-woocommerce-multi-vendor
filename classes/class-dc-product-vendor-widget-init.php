<?php
class DC_Product_Vendor_Widget_Init{
  
	public function __construct() {
		add_action('widgets_init', array($this, 'product_vendor_register_widgets'));
		add_action( 'wp_dashboard_setup', array($this, 'pmg_rm_meta_boxes') );
	}
	
	/**
		 * Add vendor widgets
	*/
	function product_vendor_register_widgets() {
		global $DC_Product_Vendor;
		include_once ('widgets/class-dc-product-vendor-widget-vendor-info.php');
		require_once ('widgets/class-dc-product-vendor-widget-vendor-list.php');
		require_once ('widgets/class-dc-product-vendor-widget-vendor-quick-info.php');
		require_once ('widgets/class-dc-product-vendor-widget-vendor-location.php');
		register_widget('DC_Widget_Vendor_Info');
		register_widget('DC_Widget_Vendor_List');    
		register_widget('DC_Widget_Quick_Info_Widget');
		register_widget('DC_Woocommerce_Store_Location_Widget');
  }
  
  /**
		 * removing woocommerce widget from vendor dashboard
	*/
  function pmg_rm_meta_boxes() {
  	if ( is_user_dc_vendor( get_current_user_id() ) ) {
  		remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal' );
  	}
  }
}