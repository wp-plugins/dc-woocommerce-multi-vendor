<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class DC_Product_Vendor {

	public $plugin_url;

	public $plugin_path;

	public $version;

	public $token;
	
	public $text_domain;
	
	public $library;

	public $shortcode;

	public $admin;

	public $frontend;

	public $template;

	public $ajax;
	
	public $taxonomy;
	
	public $product;

	private $file;
	
	public $settings;
	
	public $dc_wp_fields;
	
	public $user;
	
	public $vendor_caps;
	
	public $vendor_dashboard;

	public function __construct( $file ) {

		$this->file = $file;
		$this->plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
		$this->plugin_path = trailingslashit( dirname( $file ) );
		$this->token = PLUGIN_TOKEN;
		$this->text_domain = TEXT_DOMAIN;
		$this->version = PLUGIN_VERSION;
		$this->init_custom_widgets();
		$this->init_masspay_cron();
		add_action( 'init', array( &$this, 'init' ) );
	}
	
	/**
	 * initilize plugin on WP init
	*/
	function init() {
		
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		
		// Init library
		$this->load_class( 'library' );
		$this->library = new DC_Product_Vendor_Library();

		// Init ajax
		if( defined('DOING_AJAX') ) {
			$this->load_class( 'ajax' );
			$this->ajax = new  DC_Product_Vendor_Ajax();
		}
		
		if ( is_admin() ) {
			$this->load_class( 'admin' );
			$this->admin = new DC_Product_Vendor_Admin();
		}
		

		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			$this->load_class( 'frontend' );
			$this->frontend = new DC_Product_Vendor_Frontend();
			
			// init shortcode
			$this->load_class( 'shortcode' );
			$this->shortcode = new DC_Product_Vendor_Shortcode();

			// init templates
			$this->load_class( 'template' );
			$this->template = new DC_Product_Vendor_Template();
		}
		
		// calculate commission
		$this->load_class( 'calculate-commission' );
		new DC_Product_Vendor_Calculate_Commission();
		
		//include the vendor class
		$this->load_class( 'details' );
		
		//product function
		$this->load_class('product');
		$this->product = new DC_Product_Vendor_Product();

		// DC Wp Fields
		$this->dc_wp_fields = $this->library->load_wp_fields();
		
		// Init user roles
    $this->init_user_roles();
    
    // Init product vendor taxonomies
    $this->init_taxonomy();
    
    // Init product vendor custom post types
    $this->init_custom_post();
    
    //init custom reports
    $this->init_custom_reports();
    
    //init custom capabilities
    $this->init_custom_capabilities();
    
    //init paypal masspay
    $this->init_paypal_masspay();
    
    //init vendor dashboard
    $this->init_vendor_dashboard();
    
    //init vendor coupon
    $this->init_vendor_coupon();
    
	}
	
	/**
   * Load Localisation files.
   *
   * Note: the first-loaded translation file overrides any following ones if the same translation is present
   *
   * @access public
   * @return void
   */
  public function load_plugin_textdomain() {
    $locale = apply_filters( 'plugin_locale', get_locale(), $this->token );

    load_textdomain( $this->text_domain, WP_LANG_DIR . "/dc-product-vendor/dc-product-vendor-$locale.mo" );
    load_textdomain( $this->text_domain, $this->plugin_path . "/languages/dc-product-vendor-$locale.mo" );
  }

	public function load_class($class_name = '') {
		if ( '' != $class_name && '' != $this->token ) {
			require_once ( 'class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php' );
		} // End If Statement
	}// End load_class()
	
	/** Cache Helpers *********************************************************/

	/**
	 * Sets a constant preventing some caching plugins from caching a page. Used on dynamic pages
	 *
	 * @access public
	 * @return void
	 */
	function nocache() {
		if (!defined('DONOTCACHEPAGE'))
			define("DONOTCACHEPAGE", "true");
		// WP Super Cache constant
	}
	
	/**
   * On activation, include the installer and run it.
   *
   * @access public
   * @return void
   */
  function activate_dc_product_vendor_plugin() {
    global $DC_Product_Vendor;
    $DC_Product_Vendor->load_class('install');
    new DC_Product_Vendor_Install();
    update_option( 'dc_product_vendor_plugin_installed', 1 );
  }
  
  
	/**
   * Init demo_plugin user capabilities.
   *
   * @access public
   * @return void
   */
  function init_user_roles() {
  	global $wpdb, $DC_Product_Vendor;
    $this->load_class( 'user' );
    $this->user = new DC_Product_Vendor_User();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
  }

   /**
   * Init DC product vendor taxonomy.
   *
   * @access public
   * @return void
   */
  function init_taxonomy() {
    global $wpdb, $DC_Product_Vendor;
    
    $this->load_class( 'taxonomy' );
    $this->taxonomy = new DC_Product_Vendor_Taxonomy();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
  }
  
  /**
   * Init DC product vendor post type.
   *
   * @access public
   * @return void
   */
  function init_custom_post() {
    global $wpdb, $DC_Product_Vendor;
    
    $this->load_class( 'post-commission' );
    new  DC_Product_Vendor_Commission();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
  }
  
  /**
   * Init DC product vendor reports.
   *
   * @access public
   * @return void
   */
  function init_custom_reports() {
  	global $wpdb, $DC_Product_Vendor;
  	
  	$this->load_class( 'post-reports' );
    new  DC_Product_Vendor_Plugin_Post_Reports( __FILE__ );
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
  }
  
  /**
   * Init DC product vendor widgets.
   *
   * @access public
   * @return void
   */
  function init_custom_widgets() {
  	global $wpdb, $DC_Product_Vendor;
  	
  	$this->load_class( 'widget-init' );
    new  DC_Product_Vendor_Widget_Init();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
  }
  
   
  /**
   * Init DC product vendor capabilities.
   *
   * @access public
   * @return void
   */
  function init_custom_capabilities() {
  	global $wpdb, $DC_Product_Vendor;
  	
  	$this->load_class('capabilities');
    $this->vendor_caps = new  DC_Product_Vendor_Capabilities();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
  }
  
  /**
   * Init DC product vendor MassPay.
   *
   * @access public
   * @return void
   */
  function init_paypal_masspay() {
  	global $wpdb, $DC_Product_Vendor;
  	
  	$this->load_class('paypal-masspay');
    $this->paypal_masspay = new  DC_Product_Vendor_Paypal_Masspay();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
  }
  
  
  /**
	 * Init Vendor Dashboard Function
	 *
	 * @access public
	 * @return void
	*/	
  function init_vendor_dashboard() {
  	global $wpdb, $DC_Product_Vendor;
  	
  	$this->load_class('vendor-dashboard');
    $this->vendor_dashboard = new  DC_Vendor_Admin_Dashboard();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
  }
  
  
  /**
	 * Product Vendor Action Links Function
	 *
	 * @access public
	 * @param plugin links
	 * @return plugin links
	*/	
  function dc_product_vendor_action_links($links) {
		global $DC_Product_Vendor;
		$plugin_links = array(
    '<a href="' . admin_url( 'admin.php?page=dc-product-vendor-setting-admin' ) . '">' . __( 'Settings', $DC_Product_Vendor->text_domain ) . '</a>'  );
    return array_merge( $plugin_links, $links );
	}
	
	
	/**
	 * Init Masspay Cron
	 *
	 * @access public
	 * @return void
	*/	
	function init_masspay_cron() {
		global $DC_Product_Vendor;
		add_filter( 'cron_schedules', array($this, 'cron_add_weekly') );
		$abc = wp_get_schedules();
  	$this->load_class('masspay-cron');
    $this->masspay_cron = new  DC_Product_Vendor_MassPay_Cron();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
	}
	
	
	/**
	 * Init Vendor Coupon
	 *
	 * @access public
	 * @return void
	*/	
	function init_vendor_coupon() {
		global $wpdb, $DC_Product_Vendor;
  	
  	$this->load_class('coupon');
    new  DC_Product_Vendor_Coupon();
    
    register_activation_hook( __FILE__, 'flush_rewrite_rules' );
	}
	
	
	/**
	 * add weekly and monthly corn schedule
	 *
	 * @access public
	 * @param schedules array
	 * @return schedules array
	*/	
	function cron_add_weekly($schedule) {
		$schedules['weekly'] = array(
		'interval' => 604800,
		'display' => __('Every 7 days')
		);
		$schedules['monthly'] = array(
			'interval' => 2592000,
			'display' => __('Every 1 month')
		);
		return $schedules;
	}
}
