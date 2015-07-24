<?php
class DC_Product_Vendor_Settings {
  
  private $tabs = array();
  
  private $options;
  
  /**
   * Start up
   */
  public function __construct() {
    // Admin menu
    add_action( 'admin_menu', array( $this, 'add_settings_page' ), 100 );
    add_action( 'admin_init', array( $this, 'settings_page_init' ) );
    
    // Settings tabs
    add_action('settings_page_general_tab_init', array(&$this, 'general_tab_init'), 10, 1);
    add_action('settings_page_product_tab_init', array(&$this, 'product_tab_init'), 10, 1);
    add_action('settings_page_capabilities_tab_init', array(&$this, 'capabilites_tab_init'), 10, 1);
    add_action('settings_page_pages_tab_init', array(&$this, 'pages_tab_init'), 10, 1);
    add_action('settings_page_payment_tab_init', array(&$this, 'payment_tab_init'), 10, 1);
  }
  
  /**
   * Add options page
   */
  public function add_settings_page() {
    global $DC_Product_Vendor;
    
    add_submenu_page(
        'woocommerce', 
        __('DC Vendors', $DC_Product_Vendor->text_domain), 
        __('DC Vendors', $DC_Product_Vendor->text_domain), 
        'manage_woocommerce', 
        'dc-product-vendor-setting-admin', 
        array( $this, 'create_dc_product_vendor_settings' ),
        $DC_Product_Vendor->plugin_url . 'assets/images/dualcube.png'
    );
    
    $this->tabs = $this->get_dc_settings_tabs();
  }
  
  function get_dc_settings_tabs() {
    global $DC_Product_Vendor;
    $tabs = apply_filters('dc-product-vendor_tabs', array(
        'general' => __('General', $DC_Product_Vendor->text_domain),
        'product' =>  __('Products', $DC_Product_Vendor->text_domain),
        'capabilities' =>  __('Capabilities', $DC_Product_Vendor->text_domain),
        'pages' =>  __('Pages', $DC_Product_Vendor->text_domain),
        'payment' =>  __('Payment', $DC_Product_Vendor->text_domain),
    ));
    return $tabs;
  }
  
  function dc_settings_tabs( $current = 'general' ) {
  	 global $DC_Product_Vendor;
    if ( isset ( $_GET['tab'] ) ) :
      $current = $_GET['tab'];
    else:
      $current = 'general';
    endif;
    
    $links = array();
    foreach( $this->tabs as $tab => $name ) :
      if ( $tab == $current ) :
        $links[] = "<a class='nav-tab nav-tab-active' href='?page=dc-product-vendor-setting-admin&tab=$tab'>$name</a>";
      else :
        $links[] = "<a class='nav-tab' href='?page=dc-product-vendor-setting-admin&tab=$tab'>$name</a>";
      endif;
    endforeach;
    echo '<div class="icon32" id="dualcube_menu_ico"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $links as $link )
      echo $link;
    echo '</h2>';
    
    foreach( $this->tabs as $tab => $name ) :
      if ( $tab == $current ) :
        printf( __( "<h2>%s Settings</h2>", $DC_Product_Vendor->text_domain) , $name);
      endif;
    endforeach;
  }

  /**
   * Options page callback
   */
  public function create_dc_product_vendor_settings() {
    global $DC_Product_Vendor;
    ?>
    <div class="wrap">
      <?php $this->dc_settings_tabs(); ?>
      <?php
      $tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'general' );
      $this->options = get_option( "dc_{$tab}_settings_name" );
      //print_r($this->options);
      
      // This prints out all hidden setting errors
      settings_errors("dc_{$tab}_settings_name");
      ?>
      <form method="post" action="options.php">
      <?php
        // This prints out all hidden setting fields
        settings_fields( "dc_{$tab}_settings_group" );   
        do_settings_sections( "dc-{$tab}-settings-admin" );
        submit_button(); 
      ?>
      </form>
      <?php
      if( $_GET['tab'] == 'payment') {
      	if(wp_next_scheduled( 'paypal_masspay_cron_start' )) {
      		_e('<br><b>MassPay Sync</b><br>', $DC_Product_Vendor->text_domain);
					printf( __('Next 3PL backorders cron @ %s', $DC_Product_Vendor->text_domain),  date('d/m/Y g:i:s A', wp_next_scheduled( 'paypal_masspay_cron_start' ))) ;
					printf( __('<br>Now the time is %s', $DC_Product_Vendor->text_domain), date('d/m/Y g:i:s A', time()));
				}
			} 
			?>
    </div>
    <?php
    do_action('dualcube_admin_footer');
  }

  /**
   * Register and add settings
   */
  public function settings_page_init() { 
    do_action('befor_settings_page_init');
    
    // Register each tab settings
    foreach( $this->tabs as $tab => $name ) :
      do_action("settings_page_{$tab}_tab_init", $tab);
    endforeach;
    
    do_action('after_settings_page_init');
  }
  
  /**
   * Register and add settings fields
   */
  public function settings_field_init($tab_options) {
    global $DC_Product_Vendor;
    
    if(!empty($tab_options) && isset($tab_options['tab']) && isset($tab_options['ref']) && isset($tab_options['sections'])) {
      // Register tab options
      register_setting(
        "dc_{$tab_options['tab']}_settings_group", // Option group
        "dc_{$tab_options['tab']}_settings_name", // Option name
        array( $tab_options['ref'], "dc_{$tab_options['tab']}_settings_sanitize" ) // Sanitize
      );
      
      foreach($tab_options['sections'] as $sectionID => $section) {
        // Register section
        add_settings_section(
          $sectionID, // ID
          $section['title'], // Title
          array( $tab_options['ref'], "{$sectionID}_info" ), // Callback
          "dc-{$tab_options['tab']}-settings-admin" // Page
        );
        
        // Register fields
        if(isset($section['fields'])) {
          foreach($section['fields'] as $fieldID => $field) {
            if(isset($field['type'])) {
              $field['tab'] = $tab_options['tab'];
              $callbak = $this->get_field_callback_type($field['type']);
              if(!empty($callbak)) {
                add_settings_field(
                  $fieldID,
                  $field['title'],
                  array( $this, $callbak ),
                  "dc-{$tab_options['tab']}-settings-admin",
                  $sectionID,
                  $this->process_fields_args($field, $fieldID)
                );
              }
            }
          }
        }
      }
    }
  }

	/**
   * function process_fields_args
   * @param $fields
   * @param $fieldId
   * @return Array
   */
  function process_fields_args( $field, $fieldID ) {

    if( !isset($field['id'] ) ) {
      $field['id'] = $fieldID;
    }

    if( !isset($field['label_for'] ) ) {
      $field['label_for'] = $fieldID;
    }

    if( !isset($field['name'] ) ) {
      $field['name'] = $fieldID;
    }

    return $field;
  }
  
  function general_tab_init($tab) {
    global $DC_Product_Vendor;
    $DC_Product_Vendor->admin->load_class("settings-{$tab}", $DC_Product_Vendor->plugin_path, $DC_Product_Vendor->token);
    new DC_Product_Vendor_Settings_Gneral($tab);
  }
  
  function product_tab_init($tab) {
  	global $DC_Product_Vendor;
    $DC_Product_Vendor->admin->load_class("settings-{$tab}", $DC_Product_Vendor->plugin_path, $DC_Product_Vendor->token);
    new DC_Product_Vendor_Settings_Product($tab);
  }
  
	function capabilites_tab_init($tab) {
		global $DC_Product_Vendor;
		$DC_Product_Vendor->admin->load_class("settings-{$tab}", $DC_Product_Vendor->plugin_path, $DC_Product_Vendor->token);
		new DC_Product_Vendor_Settings_Capabilities($tab);
	}
	
	function pages_tab_init($tab) {
		global $DC_Product_Vendor;
		$DC_Product_Vendor->admin->load_class("settings-{$tab}", $DC_Product_Vendor->plugin_path, $DC_Product_Vendor->token);
		new DC_Product_Vendor_Settings_Pages($tab);
	}
	
	function payment_tab_init($tab) {
		global $DC_Product_Vendor;
		$DC_Product_Vendor->admin->load_class("settings-{$tab}", $DC_Product_Vendor->plugin_path, $DC_Product_Vendor->token);
		new DC_Product_Vendor_Settings_Payment($tab);
	}
  
  function get_field_callback_type($fieldType) {
    $callBack = '';
    switch($fieldType) {
      case 'input':
      case 'text':
      case 'email':
      case 'url':
        $callBack = 'text_field_callback';
        break;
        
      case 'hidden':
        $callBack = 'hidden_field_callback';
        break;
        
      case 'textarea':
        $callBack = 'textarea_field_callback';
        break;
        
      case 'wpeditor':
        $callBack = 'wpeditor_field_callback';
        break;
        
      case 'checkbox':
        $callBack = 'checkbox_field_callback';
        break;
        
      case 'radio':
        $callBack = 'radio_field_callback';
        break;
        
      case 'select':
        $callBack = 'select_field_callback';
        break;
        
      case 'upload':
        $callBack = 'upload_field_callback';
        break;
        
      case 'colorpicker':
        $callBack = 'colorpicker_field_callback';
        break;
        
      case 'datepicker':
        $callBack = 'datepicker_field_callback';
        break;
        
      case 'multiinput':
        $callBack = 'multiinput_callback';
        break;
        
      default:
        $callBack = '';
        break;
    }
    
    return $callBack;
  }
  
  /** 
   * Get the hidden field display
   */
  public function hidden_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_attr( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->hidden_input($field);
  }
  
  /** 
   * Get the text field display
   */
  public function text_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_attr( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->text_input($field);
  }
  
  /** 
   * Get the text area display
   */
  public function textarea_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_textarea( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_textarea( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->textarea_input($field);
  }
  
  /** 
   * Get the wpeditor display
   */
  public function wpeditor_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? ( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? ( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->wpeditor_input($field);
  }
  
  /** 
   * Get the checkbox field display
   */
  public function checkbox_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_attr( $this->options[$field['name']] ) : $field['value'];
    $field['dfvalue'] = isset( $this->options[$field['name']] ) ? esc_attr( $this->options[$field['name']] ) : '';
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->checkbox_input($field);
  }
  
  /** 
   * Get the checkbox field display
   */
  public function radio_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_attr( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->radio_input($field);
  }
  
  /** 
   * Get the select field display
   */
  public function select_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_textarea( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_textarea( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->select_input($field);
  }
  
  /** 
   * Get the upload field display
   */
  public function upload_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_attr( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->upload_input($field);
  }
  
  /** 
   * Get the multiinput field display
   */
  public function multiinput_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? $field['value'] : array();
    $field['value'] = isset( $this->options[$field['name']] ) ? $this->options[$field['name']] : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->multi_input($field);
  }
  
  /** 
   * Get the colorpicker field display
   */
  public function colorpicker_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_attr( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->colorpicker_input($field);
  }
  
  /** 
   * Get the datepicker field display
   */
  public function datepicker_field_callback($field) {
    global $DC_Product_Vendor;
    $field['value'] = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
    $field['value'] = isset( $this->options[$field['name']] ) ? esc_attr( $this->options[$field['name']] ) : $field['value'];
    $field['name'] = "dc_{$field['tab']}_settings_name[{$field['name']}]";
    $DC_Product_Vendor->dc_wp_fields->datepicker_input($field);
  }
  
}