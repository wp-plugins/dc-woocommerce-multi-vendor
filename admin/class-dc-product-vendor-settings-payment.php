<?php
class DC_Product_Vendor_Settings_Payment {
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
                                                      "default_settings_section" => array("title" =>  __('MassPay- ', $DC_Product_Vendor->text_domain), // Section one
                                                                                         "fields" => array("is_mass_pay" => array('title' => __('Activate MassPay', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_mass_pay', 'label_for' => 'is_mass_pay', 'name' => 'is_mass_pay', 'value' => 'Enable'), // Checkbox
                                                                                                           "payment_schedule" => array('title' => __('Set Schedule', $DC_Product_Vendor->text_domain), 'type' => 'radio', 'id' => 'payment_schedule', 'label_for' => 'payment_schedule', 'name' => 'payment_schedule', 'dfvalue' => 'daily', 'options' => array('weekly' => 'Weekly', 'daily' => 'Daily', 'monthly' => 'Monthly', 'hourly' => 'Hourly'), 'hints' => __('Choose your preferred Shedule for Paypal Masspay ...', $DC_Product_Vendor->text_domain)), // Radio
                                                                                                           "api_username" => array('title' => __('Api Username', $DC_Product_Vendor->text_domain), 'type' => 'text', 'id' => 'api_username', 'label_for' => 'api_username', 'name' => 'api_username', 'hints' => __('Give your Paypal API User Name', $DC_Product_Vendor->text_domain), 'desc' => __('Give your Paypal API User Name', $DC_Product_Vendor->text_domain)),
                                                                                                           "api_pass" => array('title' => __('Api Password', $DC_Product_Vendor->text_domain), 'type' => 'text', 'id' => 'api_pass', 'label_for' => 'api_pass', 'name' => 'api_pass', 'hints' => __('Give your Paypal API Password', $DC_Product_Vendor->text_domain), 'desc' => __('Give your Paypal API Password', $DC_Product_Vendor->text_domain)),
                                                                                                           "api_signature" => array('title' => __('Api Signature', $DC_Product_Vendor->text_domain), 'type' => 'text', 'id' => 'api_signature', 'label_for' => 'api_signature', 'name' => 'api_signature', 'hints' => __('Give your Paypal API Signature', $DC_Product_Vendor->text_domain), 'desc' => __('Give your Paypal API Signature', $DC_Product_Vendor->text_domain)),
                                                                                                           "is_testmode" => array('title' => __('Enable Test Mode', $DC_Product_Vendor->text_domain), 'type' => 'checkbox', 'id' => 'is_testmode', 'label_for' => 'is_testmode', 'name' => 'is_testmode', 'value' => 'Enable'), // Checkbox
                                                                                                           )							
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
  public function dc_payment_settings_sanitize( $input ) {
    global $DC_Product_Vendor;
    $new_input = array();
    
    $hasError = false;
		if( isset( $input['is_mass_pay'] ) )
			$new_input['is_mass_pay'] = sanitize_text_field( $input['is_mass_pay'] );
		
		if( isset( $input['is_testmode'] ) )
			$new_input['is_testmode'] = sanitize_text_field( $input['is_testmode'] );
		
		if( isset( $input['payment_schedule'] ) )
			$new_input['payment_schedule'] = $input['payment_schedule'];
		
		if( isset( $input['payment_schedule'] )  &&  isset( $input['is_mass_pay']) ) {
			$schedule = wp_get_schedule( 'paypal_masspay_cron_start' );
			if($schedule != $input['payment_schedule']) {
				if(wp_next_scheduled( 'paypal_masspay_cron_start' ) ) {
					$timestamp = wp_next_scheduled( 'paypal_masspay_cron_start' );
					wp_unschedule_event($timestamp, 'paypal_masspay_cron_start' );
				}
				wp_schedule_event( time(), $input['payment_schedule'], 'paypal_masspay_cron_start');
			} else {
				wp_schedule_event( time(), $input['payment_schedule'], 'paypal_masspay_cron_start');
			}
		} else {
    	if(wp_next_scheduled( 'paypal_masspay_cron_start' ) ) {
				$timestamp = wp_next_scheduled( 'paypal_masspay_cron_start' );
				wp_unschedule_event($timestamp, 'paypal_masspay_cron_start' );
			}
    }
		
    if( isset( $input['api_username'] ) ) {
      $new_input['api_username'] = trim($input['api_username']);
    } else {
      add_settings_error(
        "dc_{$this->tab}_settings_name",
        esc_attr( "dc_{$this->tab}_settings_admin_error" ),
        __('Set Api User Name', $DC_Product_Vendor->text_domain),
        'error'
      );
      $hasError = true;
    }
    
    if( isset( $input['api_pass'] ) ) {
      $new_input['api_pass'] = trim($input['api_pass']);
    } else {
      add_settings_error(
        "dc_{$this->tab}_settings_name",
        esc_attr( "dc_{$this->tab}_settings_admin_error" ),
        __('Set Api Password', $DC_Product_Vendor->text_domain),
        'error'
      );
      $hasError = true;
    }
      
    if( isset( $input['api_signature'] ) ) {
      $new_input['api_signature'] = trim($input['api_signature']);
    } else {
      add_settings_error(
        "dc_{$this->tab}_settings_name",
        esc_attr( "dc_{$this->tab}_settings_admin_error" ),
        __('Set Api Signature', $DC_Product_Vendor->text_domain),
        'error'
      );
      $hasError = true;
    }
    return $new_input;
  }

  /** 
   * Print the Section text
   */
  public function default_settings_section_info() {
    global $DC_Product_Vendor;
   	_e('Payment can be done only if Vendors have valid Paypal Email Id in their profile.You can add from Users->Edit Users->Paypal Email', $DC_Product_Vendor->text_domain);
  }
}