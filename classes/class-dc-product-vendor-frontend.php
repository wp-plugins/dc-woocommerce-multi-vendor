<?php
class DC_Product_Vendor_Frontend {

	public function __construct() {
		//enqueue scripts
		add_action('wp_enqueue_scripts', array(&$this, 'frontend_scripts'));
		//enqueue styles
		add_action('wp_enqueue_scripts', array(&$this, 'frontend_styles'));
		add_action( 'woocommerce_archive_description', array(&$this, 'product_archive_vendor_info' ), 10);
		add_action( 'template_redirect', array( &$this, 'load_product_archive_template' ) );
		add_filter( 'body_class', array( &$this, 'set_product_archive_class' ) );
		add_action( 'template_redirect', array(&$this, 'template_redirect' ));

	}

	/**
	 * Add frontend scripts
	 * @return void
	 */
	function frontend_scripts() {
		global $DC_Product_Vendor;
		$frontend_script_path = $DC_Product_Vendor->plugin_url . 'assets/frontend/js/';
		
		// Enqueue your frontend javascript from here
		if(is_shop_settings()) {
			$DC_Product_Vendor->library->load_upload_lib();
		}
		if(is_vendor_order_by_product_page()) {
			wp_enqueue_script('vendor_order_by_product_js', $frontend_script_path. 'vendor_order_by_product.js', array('jquery'), $DC_Product_Vendor->version, true);
		}
		
		if(is_single()) {
			wp_enqueue_script('simplepopup_js', $frontend_script_path. 'simplepopup.js', array('jquery'), $DC_Product_Vendor->version, true);
			wp_enqueue_script('frontend_js', $frontend_script_path. 'frontend.js', array('jquery'), $DC_Product_Vendor->version, true);
		}
		
		wp_register_script( 'gmaps-api', '//maps.google.com/maps/api/js?sensor=false&amp;language=en', array( 'jquery' ) );
    wp_register_script( 'gmap3', $frontend_script_path . 'gmap3.min.js', array( 'jquery', 'gmaps-api' ), '6.0.0', false );
		if( is_tax( 'dc_vendor_shop' ) ) {
			wp_enqueue_script( 'gmap3' );
		}
	}

  /**
	 * Add frontend styles
	 * @return void
	*/
	function frontend_styles() {
		global $DC_Product_Vendor;
		$frontend_style_path = $DC_Product_Vendor->plugin_url . 'assets/frontend/css/';
		
		if( is_tax( 'dc_vendor_shop' ) ) {
			wp_enqueue_style('frontend_css',  $frontend_style_path .'frontend.css', array(), $DC_Product_Vendor->version);
		}
		
		wp_enqueue_style('product_css',  $frontend_style_path .'product.css', array(), $DC_Product_Vendor->version);
		
		if(is_vendor_order_by_product_page()) {
			wp_enqueue_style('vendor_order_by_product_css',  $frontend_style_path .'vendor_order_by_product.css', array(), $DC_Product_Vendor->version);
		}
		
    $link_color = $DC_Product_Vendor->vendor_caps->general_cap['catalog_colorpicker'];
    $hover_link_color = $DC_Product_Vendor->vendor_caps->general_cap['catalog_hover_colorpicker'];
    
    $custom_css = "
                .by-vendor-name-link:hover{
                        color: {$hover_link_color} !important;
                }
                .by-vendor-name-link{
                        color: {$link_color} !important;
                }";
    wp_add_inline_style( 'product_css', $custom_css );
	}
	
	/**
	 * Add html for vendor taxnomy page
	 * @return void
	*/
	 
	function product_archive_vendor_info() {
		global $DC_Product_Vendor;
		if( is_tax( 'dc_vendor_shop' ) ) {
			// Get vendor ID
			$vendor_id = get_queried_object()->term_id;
			// Get vendor info
			$vendor = get_dc_vendor_by_term( $vendor_id );
			$image 	= '';
			$image 	= $vendor->image;
			if(!$image) $image = $DC_Product_Vendor->plugin_url . 'assets/images/WP-stdavatar.png';
			$description = $vendor->description;
			if($vendor->city) {
				$address = $vendor->city .', ';
			}
			if($vendor->state) {
				$address .= $vendor->state .', ';
			}
			if($vendor->country) {
				$address .= $vendor->country;
			}
			$DC_Product_Vendor->template->get_template( 'archive_vendor_info.php', array('vendor_id' => $vendor->id, 'banner' => $vendor->banner, 'profile' => $image, 'description' => stripslashes($description), 'mobile' => $vendor->phone, 'location' => $address, 'email' => $vendor->user_data->user_email ) );
		}
  }
	
	/**
	 * Load product archive template for vendor pages
	 * @return void
	*/
	public function load_product_archive_template() {
		if( is_tax( 'dc_vendor_shop' ) ) {
			woocommerce_get_template( 'archive-product.php' );
			exit;
		}
	}
	
	/**
	 * Add 'woocommerce' class to body tag for vendor pages
	 * @param  arr $classes Existing classes
	 * @return arr          Modified classes
	*/
	public function set_product_archive_class( $classes ) {
		if( is_tax( 'dc_vendor_shop' ) ) {

			// Add generic classes
			$classes[] = 'woocommerce';
			$classes[] = 'product-vendor';

			// Get vendor ID
			$vendor_id = get_queried_object()->term_id;

			// Get vendor info
			$vendor = get_dc_vendor_by_term( $vendor_id );

			// Add vendor slug as class
			if( '' != $vendor->slug ) {
					$classes[] = $vendor->slug;
			}
		}
		return $classes;
	}
	
	
	/**
	 * template redirect function
	 * @return void
	*/
	function template_redirect() {
		$pages = get_option("dc_pages_settings_name");
		
		//rediect to shop page when a non vendor loggedin user is on vendor pages but not in vendor dashboard page
    if( is_user_logged_in() && is_vendor_page() && ! is_user_dc_vendor( get_current_user_id() ) ) {
    	if(!is_page($pages['vendor_dashboard'])) {
				wp_safe_redirect( get_permalink( woocommerce_get_page_id( 'shop' ) ) );
				exit();
			}
    } 
    
    //rediect to myaccount page when a non loggedin user is on vendor pages
    if( !is_user_logged_in() && is_vendor_page() && ! is_page( woocommerce_get_page_id( 'myaccount' ) ) ) {
      wp_safe_redirect( get_permalink( woocommerce_get_page_id( 'myaccount' ) ) );
      exit();
    }
    
    //rediect to vendor dashboard page when a  loggedin user is on vendor_order_detail page but order id query argument is not sent in url
		if(is_page( absint( $pages['vendor_order_detail'] ) ) && is_user_logged_in() ) {
			if(!isset($_GET['order_id']) && empty($_GET['order_id'])) {
				wp_safe_redirect( get_permalink($pages['vendor_dashboard'] ) );
				exit();
			}
		}
		
		//rediect to vendor dashboard page when a  loggedin user is on view_order page but orders_for_product query argument is not sent in url
		if(is_page( absint( $pages['view_order'] ) ) && is_user_logged_in() ) {
			if(!isset($_GET['orders_for_product']) && empty($_GET['orders_for_product'])) {
				wp_safe_redirect( get_permalink($pages['vendor_dashboard'] ) );
				exit();
			}
		}
		
		//rediect to myaccount page when a non logged in user is on vendor_order_detail
		if( !is_user_logged_in() && is_page( absint( $pages['vendor_order_detail'] ) ) && ! is_page( woocommerce_get_page_id( 'myaccount' ) ) ) {
			wp_safe_redirect( get_permalink( woocommerce_get_page_id( 'myaccount' ) ) );
      exit();
		}
	}
}
