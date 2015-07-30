<?php
class DC_Product_Vendor_Admin {
  
  public $settings;

	public function __construct() {
		
		//admin script and style
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'), 30);		
		add_action('dualcube_admin_footer', array(&$this, 'dualcube_admin_footer_for_dc_product_vendor'));
		add_action('admin_bar_menu', array(&$this, 'add_toolbar_items_dc'), 100);
		add_action('admin_head', array( &$this, 'admin_header' ) );
		
		add_action( 'current_screen', array( $this, 'conditonal_includes' ) );
		
		$this->load_class('settings');
		$this->settings = new DC_Product_Vendor_Settings();
	}
	
	function conditonal_includes() {
		$screen = get_current_screen();
		
		if (in_array( $screen->id, array( 'options-permalink' ))) {
			$this->permalink_settings_init();
			$this->permalink_settings_save();
		}
	}
	
	function permalink_settings_init() {
		global $DC_Product_Vendor;
		// Add our settings
		add_settings_field(
			'dc_product_vendor_taxonomy_slug',            // id
			__( 'Vendor Shop base', $DC_Product_Vendor->text_domain ),   // setting title
			array( &$this, 'dc_product_vendor_taxonomy_slug_input' ),  // display callback
			'permalink',                                    // settings page
			'optional'                                      // settings section
		);
	}
	
	function dc_product_vendor_taxonomy_slug_input() {
		global $DC_Product_Vendor;
		$permalinks = get_option( 'dc_vendors_permalinks' );
		?>
		<input name="dc_product_vendor_taxonomy_slug" type="text" class="regular-text code" value="<?php if ( isset( $permalinks['vendor_shop_base'] ) ) echo esc_attr( $permalinks['vendor_shop_base'] ); ?>" placeholder="<?php echo _x('vendor', 'slug', $DC_Product_Vendor->text_domain) ?>" />
		<?php
	}
	
	function permalink_settings_save() {
		if ( ! is_admin() ) {
			return;
		}
		// We need to save the options ourselves; settings api does not trigger save for the permalinks page
		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['dc_product_vendor_taxonomy_slug'] ) ) {
			
			// Cat and tag bases
			$dc_product_vendor_taxonomy_slug  = wc_clean( $_POST['dc_product_vendor_taxonomy_slug'] );
			$permalinks = get_option( 'dc_vendors_permalinks' );

			if ( ! $permalinks ) {
				$permalinks = array();
			}

			$permalinks['vendor_shop_base']    = untrailingslashit( $dc_product_vendor_taxonomy_slug );
			update_option( 'dc_vendors_permalinks', $permalinks );
			
		}
	}

	/**
	 * Add Toolbar for vendor user 
	 *
	 * @access public
	 * @param admin bar
	 * @return void
	*/	
	function add_toolbar_items_dc($admin_bar) {
		$plugin_pages = get_option('dc_pages_settings_name');
		$user = wp_get_current_user();
  	if(is_user_dc_vendor($user)) {
			$admin_bar->add_menu( 
				array(
					'id'    => 'vendor_pages',
					'title' => 'Vendor',
					'href'  => '#',
					'meta'  => array(
						'title' => __('Vendor'),            
					),
				)
			);
			$admin_bar->add_menu( 
				array(
					'id'    => 'vendor_dashboard',
					'parent' => 'vendor_pages',
					'title' => 'Vendor Dashboard',
					'href'  => get_permalink($plugin_pages['vendor_dashboard']),
					'meta'  => array(
						'title' => __('Vendor Dashboard'),
						'target' => '_blank',
						'class' => 'shop-settings'
					),
				)
			);
			$admin_bar->add_menu( 
				array(
					'id'    => 'shop_settings',
					'parent' => 'vendor_pages',
					'title' => 'Shop Settings',
					'href'  => get_permalink($plugin_pages['shop_settings']),
					'meta'  => array(
						'title' => __('Shop Settings'),
						'target' => '_blank',
						'class' => 'shop-settings'
					),
				)
			);
    }
	}
	

  
	function load_class($class_name = '') {
	  global $DC_Product_Vendor;
		if ('' != $class_name) {
			require_once ($DC_Product_Vendor->plugin_path . '/admin/class-' . esc_attr($DC_Product_Vendor->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}// End load_class()
	
	
	/**
	 * Add dualcube footer text on plugin settings page
	 *
	 * @access public
	 * @param admin bar
	 * @return void
	*/		
	
	function dualcube_admin_footer_for_dc_product_vendor() {
    global $DC_Product_Vendor;
    ?>
    <div style="clear: both"></div>
    <div id="dc_admin_footer">
      <?php _e('Powered by', $DC_Product_Vendor->text_domain); ?> <a href="http://dualcube.com" target="_blank"><img src="<?php echo $DC_Product_Vendor->plugin_url.'/assets/images/dualcube.png'; ?>"></a><?php _e('Dualcube', $DC_Product_Vendor->text_domain); ?> &copy; <?php echo date('Y');?>
    </div>
    <?php
	}
	
	
	
	/**
	 * Add css on admin header
	 *
	 * @access public
	 * @return void
	*/
	function admin_header() {
		global $DC_Product_Vendor;
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$user = new WP_User($user_id);
			if ( !empty( $user->roles ) && is_array( $user->roles )  && in_array( 'dc_vendor', $user->roles )) {
				if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_coupon') ) {
					$vendor_submit_coupon = get_user_meta($user_id, '_vendor_submit_coupon', true);
					if( $vendor_submit_coupon ) {
						echo '<style type="text/css">';
						echo '#toplevel_page_woocommerce ul li, #menu-posts, #menu-posts-dc_commission{ 
										display : none;
									}';	
						echo '#toplevel_page_woocommerce ul li.wp-first-item {
										display : block;
									}';									
						echo '</style>';
					}
					echo '<style type="text/css">';
					echo '#menu-tools, #menu-comments, #menu-appearance{ 
										display : none;
									}';	
					echo '</style>';
				} else {
					echo '<style type="text/css">';
					echo '#toplevel_page_woocommerce { 
										display : none;
									}';
					echo '</style>';				
				}
			}
		}
	}
	

	/**
	 * Admin Scripts
	*/

	public function enqueue_admin_script() {
		global $DC_Product_Vendor, $woocommerce;
		$screen = get_current_screen();
		
		// Enqueue admin script and stylesheet from here
		if (in_array( $screen->id, array( 'woocommerce_page_dc-product-vendor-setting-admin' ))) :   
		  $DC_Product_Vendor->library->load_qtip_lib();
		  $DC_Product_Vendor->library->load_upload_lib();
		  $DC_Product_Vendor->library->load_colorpicker_lib();
		  $DC_Product_Vendor->library->load_datepicker_lib();
		  wp_enqueue_script('admin_js', $DC_Product_Vendor->plugin_url.'assets/admin/js/admin.js', array('jquery'), $DC_Product_Vendor->version, true);
		  wp_enqueue_style('admin_css',  $DC_Product_Vendor->plugin_url.'assets/admin/css/admin.css', array(), $DC_Product_Vendor->version);
	  endif;
	  
	  if (in_array( $screen->id, array( 'dc_commission' ))) :
		  $DC_Product_Vendor->library->load_qtip_lib();
		  wp_enqueue_script('admin_js', $DC_Product_Vendor->plugin_url.'assets/admin/js/admin.js', array('jquery'), $DC_Product_Vendor->version, true);
		  wp_enqueue_style('admin_css',  $DC_Product_Vendor->plugin_url.'assets/admin/css/admin.css', array(), $DC_Product_Vendor->version);
			if( ! wp_style_is( 'woocommerce_chosen_styles', 'queue' ) ) {
				wp_enqueue_style( 'woocommerce_chosen_styles', $woocommerce->plugin_url() . '/assets/css/chosen.css' );
			}
			// Load Chosen JS
			wp_enqueue_script( 'ajax-chosen' );
			wp_enqueue_script( 'chosen' );
			wp_enqueue_script('commission_js', $DC_Product_Vendor->plugin_url.'assets/admin/js/commission.js', array('jquery'), $DC_Product_Vendor->version, true);
			wp_localize_script('commission_js', 'dc_vendor_object', array('security' => wp_create_nonce("search-products")));
	  endif;
	  	  
	  if (in_array( $screen->id, array( 'product' ))) :
		  $DC_Product_Vendor->library->load_qtip_lib();
		  wp_enqueue_script('admin_js', $DC_Product_Vendor->plugin_url.'assets/admin/js/admin.js', array('jquery'), $DC_Product_Vendor->version, true);
		  wp_enqueue_style('admin_css',  $DC_Product_Vendor->plugin_url.'assets/admin/css/admin.css', array(), $DC_Product_Vendor->version);
			if( ! wp_style_is( 'woocommerce_chosen_styles', 'queue' ) ) {
				wp_enqueue_style( 'woocommerce_chosen_styles', $woocommerce->plugin_url() . '/assets/css/chosen.css' );
			}
			// Load Chosen JS
			wp_enqueue_script( 'ajax-chosen' );
			wp_enqueue_script( 'chosen' );
			wp_enqueue_script('commission_js', $DC_Product_Vendor->plugin_url.'assets/admin/js/product.js', array('jquery'), $DC_Product_Vendor->version, true);
	  endif;
	  
		if (in_array( $screen->id, array( 'user-edit', 'profile'))) :
			$DC_Product_Vendor->library->load_qtip_lib();
			$DC_Product_Vendor->library->load_upload_lib();
			wp_enqueue_script('admin_js', $DC_Product_Vendor->plugin_url.'assets/admin/js/admin.js', array('jquery'), $DC_Product_Vendor->version, true);
			wp_enqueue_style('admin_user',  $DC_Product_Vendor->plugin_url.'assets/admin/css/admin-user.css', array(), $DC_Product_Vendor->version);
		endif;
	  
		if (in_array( $screen->id, array( 'users' ))) :
			wp_enqueue_script('dc_users_js', $DC_Product_Vendor->plugin_url.'assets/admin/js/user.js', array('jquery'), $DC_Product_Vendor->version, true);
		endif;
		
		if (in_array( $screen->id, array( 'woocommerce_page_wc-reports' ))) :
			wp_enqueue_script( 'ajax-chosen' );
			wp_enqueue_script( 'chosen' );
			wp_enqueue_script('product_js', $DC_Product_Vendor->plugin_url.'assets/admin/js/product.js', array('jquery'), $DC_Product_Vendor->version, true);
			wp_localize_script('product_js', 'dc_vendor_object', array('security' => wp_create_nonce("search-products")));
		endif;
	}
	
}