<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		DC_Product_Vendor_User
 * @version		1.0.0
 * @package		DC_Product_Vendor
 * @author 		DualCube
 */
class DC_Product_Vendor_User {
  
  private $post_type;
  
  public function __construct() {
  	
    $this->register_user_role();
    add_action( 'user_register',  array( &$this, 'vendor_registration' ), 10, 1 );
    add_filter( 'manage_users_columns', array( &$this,'column_register_product' ));
    add_filter( 'manage_users_custom_column', array( &$this, 'column_display_product' ), 10, 3 );
    add_filter(	'user_row_actions', array( &$this, 'vendor_action_links' ), 10, 2 );
    add_action( 'show_user_profile', array( &$this, 'additional_user_fields' ) );
    add_action( 'edit_user_profile', array( &$this, 'additional_user_fields') );
		add_action( 'user_profile_update_errors', array( &$this, 'validate_user_fields' ), 10, 3 );
    add_action( 'personal_options_update', array( &$this,'save_vendor_data') );
    add_action( 'edit_user_profile_update', array( &$this, 'save_vendor_data') );
    add_action( 'delete_user', array( &$this, 'delete_vendor') ); 
    //add_filter(	'woocommerce_customer_meta_fields', array($this , 'remove_woocommerce_address_field'), 60, 1);
    add_action( 'admin_head', array($this , 'profile_admin_buffer_start') );
    add_action( 'admin_footer', array($this , 'profile_admin_buffer_end') );
    add_action( 'woocommerce_register_form', array($this, 'dc_woocommerce_register_form'));
    add_action( 'woocommerce_created_customer_notification', array($this, 'dc_woocommerce_created_customer_notification'), 9, 3);
    add_filter( 'woocommerce_email_classes', array($this, 'dc_product_vendor_register_email_classes' ));
    add_action( 'set_user_role', array(&$this, 'set_user_role'), 30, 3 );
    add_action( 'woocommerce_before_my_account', array(&$this, 'woocommerce_before_my_account'));
  }
  
  /**
	 * function set_user_role
	 * @access public
	 * @return void
	 */
  public function woocommerce_before_my_account() {
  	$current_user = wp_get_current_user();
		if(is_user_dc_pending_vendor($current_user)) {
			echo 'Congratulation !! You have successfully applied for Vendor Role, Please wait untill Admin approval.';
		}  
  }
  
  
  /**
	 * function set_user_role
	 * @access public
	 * @param user_id, new role, old role
	 * @return void
	 */
  public function set_user_role($user_id, $new_role, $old_role) {
  	global $DC_Product_Vendor;
  	$user = new WP_User( $user_id );
  	if($user_id && $new_role == 'dc_rejected_vendor') {
  		$user_dtl = get_userdata( absint( $user_id ) );
  		$email = WC()->mailer()->emails['WC_Email_Rejected_New_Vendor_Account'];
  		$email->trigger( $user_id, $user_dtl->user_pass );
  		if(in_array('dc_vendor', $old_role)) {
  			$vendor = get_dc_vendor($user_id);
  			if($vendor) wp_delete_term( $vendor->term_id, 'dc_vendor_shop' );
  		}
  		wp_delete_user($user_id); 
  	}
  	if($user_id && $new_role == 'dc_pending_vendor') {
  		if(in_array('dc_vendor', $old_role)) {
  			$caps = $this->get_vendor_caps( $user_id );
				foreach( $caps as $cap ) {
					$user->remove_cap( $cap );
				}
  		}
  		$user->remove_cap('manage_woocommerce');
  	}
  	if($user_id && $new_role == 'dc_vendor') {
  		$user->add_cap('assign_product_terms');
  		$user->add_cap('read_product');
  		if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_upload_files') ) {
  			$user->add_cap('upload_files');
  		}
  		$user->add_cap('manage_woocommerce');
  		$user->add_cap('read_product');
  		if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_product') ) {
  			$vendor_submit_products = get_user_meta($user_id, '_vendor_submit_product', true);
				if( $vendor_submit_products ) {
					$caps = array();
					$caps[] = "edit_product";
					$caps[] = "delete_product";
					$caps[] = "edit_products";
					$caps[] = "edit_others_products";
					$caps[] = "delete_published_products";
					$caps[] = "delete_products";
					$caps[] = "delete_others_products";
					$caps[] = "edit_published_products";
					foreach( $caps as $cap ) {
						$user->add_cap( $cap );
					} 
				}
			}
			
			if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_coupon') ) {
  			$vendor_submit_coupon = get_user_meta($user_id, '_vendor_submit_coupon', true);
				if( $vendor_submit_coupon ) {
					$caps = array();
					$caps[] = 'edit_shop_coupons';
					$caps[] = 'read_shop_coupons';
					$caps[] = 'delete_shop_coupons';
					$caps[] = 'publish_shop_coupons';
					$caps[] = 'edit_published_shop_coupons';
					$caps[] = 'delete_published_shop_coupons';
					$caps[] = 'edit_others_shop_coupons';
					$caps[] = 'delete_others_shop_coupons';
					foreach( $caps as $cap ) {
						$user->add_cap( $cap );
					} 
				}
			}
  	}
  }
  
	/**
	 * function register_user_role
	 * @access public
	 * @return void
	 */
  public function register_user_role() {
  	global $wp_roles, $DC_Product_Vendor;
    if ( class_exists('WP_Roles') ) if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();
    if ( is_object($wp_roles) ) {
    	//remove_role( 'dc_vendor' );    	remove_role( 'dc_pending_vendor' );    	remove_role( 'dc_rejected_vendor' );
    	
      // Vendor role
      add_role( 'dc_vendor', apply_filters('dc_vendor_role', __('Vendor', $DC_Product_Vendor->text_domain )), array(
        'read' 					=> true,
        'edit_posts' 		=> false,
        'delete_posts' 	=> false,
        'manage_woocommerce' => true,
      ) );
      // Pending Vendor role
      add_role( 'dc_pending_vendor', apply_filters('dc_pending_vendor_role', __('Pending Vendor', $DC_Product_Vendor->text_domain )), array(
        'read' 					=> true,
        'edit_posts' 		=> false,
        'delete_posts' 	=> false,
      ) );
      // Pending Vendor role
      add_role( 'dc_rejected_vendor', apply_filters('dc_rejected_vendor_role', __('Rejected Vendor', $DC_Product_Vendor->text_domain )), array(
        'read' 					=> true,
        'edit_posts' 		=> false,
        'delete_posts' 	=> false,
      ) );
    }
  }
  
	/**
	 * Set up array of vendor admin capabilities
	 * @access private
	 * @return arr Vendor capabilities
	 */
	private function get_vendor_caps( $user_id ) {
		global $DC_Product_Vendor;
		$caps = array();
		$caps[] = "assign_product_terms";
		if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_upload_files') ) {
			$caps[] = "upload_files" ;
		}
		if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_product') ) {
			$vendor_submit_products = get_user_meta($user_id, '_vendor_submit_product', true);
			if( $vendor_submit_products ) {
				$caps[] = "edit_product";
				$caps[] = "delete_product";
				$caps[] = "edit_products";
				$caps[] = "edit_others_products";
				$caps[] = "delete_published_products";
				$caps[] = "delete_products";
				$caps[] = "delete_others_products";
				$caps[] = "edit_published_products";
			}
		}
		$caps[] = "read_product";
		$caps[] = "read_shop_coupon";
		if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_coupon') ) {
			$vendor_submit_coupon = get_user_meta($user_id, '_vendor_submit_coupon', true);
			if( $vendor_submit_coupon ) {
				$caps[] = 'edit_shop_coupons';
				$caps[] = 'read_shop_coupons';
				$caps[] = 'delete_shop_coupons';
				$caps[] = 'publish_shop_coupons';
				$caps[] = 'edit_published_shop_coupons';
				$caps[] = 'delete_published_shop_coupons';
				$caps[] = 'edit_others_shop_coupons';
				$caps[] = 'delete_others_shop_coupons';
			}
		}
		return $caps;
	}
	
	/**
   * Add capabilities to vendor admins
   * @param int $user_id User ID of vendor admin
   */
  public function add_vendor_caps( $user_id = 0 ) {
    if( $user_id > 0 ) {
      $caps = $this->get_vendor_caps( $user_id );
      $user = new WP_User( $user_id );
      foreach( $caps as $cap ) {
      	//echo $cap;
        $user->add_cap( $cap );
      }
    }
    //die;
  }

  /**
	 * function get_vendor_fields
	 * @param $user_id
	 * @access public
	 * @return array
	 */
  public function get_vendor_fields($user_id) {
  	global $DC_Product_Vendor;
		
		$vendor = new DC_Vendor($user_id);
		
		$fields = apply_filters('dc_vendor_fields', array(
			"vendor_page_title" => array(
				'label' => __('Vendor Page Title', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Vendor page title', $DC_Product_Vendor->text_domain),
				'value' => $vendor->page_title,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_page_slug" => array(
				'label' => __('Vendor Page Slug', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Vendor page Slug', $DC_Product_Vendor->text_domain),
				'value' => $vendor->page_slug,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_description" => array(
				'label' => __('Biographical Info', $DC_Product_Vendor->text_domain),
				'type' => 'wpeditor',
				'value' => $vendor->description,
				'class'	=> "user-profile-fields"
			), //Wp Eeditor
			
			"vendor_hide_description" => array(
				'label' => __('Hide description in frontend', $DC_Product_Vendor->text_domain), 
				'type' => 'checkbox',
				'hints' => __('Hide Description on Vendor Shop Page.', $DC_Product_Vendor->text_domain),
				'dfvalue' => $vendor->hide_description,
				'value' => 'Enable',
				'class' => 'user-profile-fields'
			),
			
			"vendor_company" => array(
				'label' => __('Vendor Company', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->company,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_address_1" => array(
				'label' => __('Address 1', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->address_1,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_address_2" => array(
				'label' => __('Address 2', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->address_2,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_city" => array(
				'label' => __('City', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->city,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_postcode" => array(
				'label' => __('Postcode', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->postcode,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_state" => array(
				'label' => __('State', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->state,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_country" => array(
				'label' => __('Country', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->country,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_phone" => array(
				'label' => __('Phone', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->phone,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_paypal_email" => array(
				'label' => __('Paypal Email', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->paypal_email,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_fb_profile" => array(
				'label' => __('Facebook Profile Url', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Your Profile Url', $DC_Product_Vendor->text_domain),
				'value' => $vendor->fb_profile,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_twitter_profile" => array(
				'label' => __('Twitter Profile Url', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Your Profile Url', $DC_Product_Vendor->text_domain),
				'value' => $vendor->twitter_profile,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_google_plus_profile" => array(
				'label' => __('Google+ Profile Url', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Your Profile Url', $DC_Product_Vendor->text_domain),
				'value' => $vendor->google_plus_profile,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_linkdin_profile" => array(
				'label' => __('Linkdin Profile Url', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Your Profile Url', $DC_Product_Vendor->text_domain),
				'value' => $vendor->linkdin_profile,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_youtube" => array(
				'label' => __('Youtube Vedio Url', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Vedio Url', $DC_Product_Vendor->text_domain),
				'value' => $vendor->youtube,
				'class'	=> "user-profile-fields"
			), // Text
			"vendor_image" => array(
				'label' => __('Logo', $DC_Product_Vendor->text_domain),
				'type' => 'upload',
				'prwidth' => 125,
				'hints' => __('Your presentation.', $DC_Product_Vendor->text_domain),
				'value' => $vendor->image,
				'class'	=> "user-profile-fields"
			),// Upload
			"vendor_banner" => array(
				'label' => __('Banner', $DC_Product_Vendor->text_domain),
				'type' => 'upload',
				'prwidth' => 600,
				'hints' => __('Your presentation.', $DC_Product_Vendor->text_domain),
				'value' => $vendor->banner,
				'class'	=> "user-profile-fields"
			),// Upload			
		), $user_id);
		$user = wp_get_current_user();
		if( is_array( $user->roles ) && in_array( 'administrator', $user->roles )) {
			$fields['vendor_submit_product'] = array(
				'label' => __('Submit Products', $DC_Product_Vendor->text_domain), 
				'type' => 'checkbox',
				'hints' => __('Is Vendor can Submit Product.', $DC_Product_Vendor->text_domain),
				'dfvalue' => $vendor->submit_product,
				'value' => 'Enable',
				'class' => 'user-profile-fields'
			);
			$fields['vendor_commission'] = array(
				'label' => __('Commission (%)', $DC_Product_Vendor->text_domain),
				'type' => 'text',
				'hints' => __('Enter Default Commission', $DC_Product_Vendor->text_domain),
				'value' => $vendor->commission,
				'class'	=> "user-profile-fields"
			); // Text   
			$fields['vendor_submit_coupon'] = array(
				'label' => __('Submit Coupon', $DC_Product_Vendor->text_domain), 
				'type' => 'checkbox',
				'hints' => __('Is Vendor can Submit Coupon.', $DC_Product_Vendor->text_domain),
				'dfvalue' => $vendor->submit_coupon,
				'value' => 'Enable',
				'class' => 'user-profile-fields'
			);
		}
		
		if(! $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_product')  ) {
			unset($fields['vendor_submit_product']);
		}
		
		if(! $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_coupon')  ) {
			unset($fields['vendor_submit_coupon']);
		}
		
  	return $fields;
  }
  
	/**
	 * function vendor_registration
	 * @access public
	 * @param $user_id
	 */
  public function vendor_registration( $user_id ) {
  	global $DC_Product_Vendor;
  	$is_approve_manually = $DC_Product_Vendor->vendor_caps->vendor_general_settings('approve_vendor_manually');
  	if(isset($_POST['pending_vendor']) &&  ($_POST['pending_vendor'] == 'true')  &&  !is_user_dc_vendor( $user_id ) && $is_approve_manually ) {
  		$user = new WP_User( absint( $user_id ) );
  		$user->remove_role( 'customer' );           
  		$user->remove_role( 'Subscriber' );
  		$user->add_role( 'dc_pending_vendor' );
  	}
  	
  	if(isset($_POST['pending_vendor']) &&  ($_POST['pending_vendor'] == 'true')  &&  !is_user_dc_vendor( $user_id ) && ! $is_approve_manually ) {
  		$user = new WP_User( absint( $user_id ) );
  		$user->remove_role( 'customer' );           
  		$user->remove_role( 'Subscriber' );
  		$user->add_role( 'dc_vendor' );
  		update_user_meta($user_id, '_vendor_submit_product', 'Enable');
  		update_user_meta($user_id, '_vendor_submit_coupon', 'Enable');
  	}
  	
  	if ( is_user_dc_vendor( $user_id ) ) {
  		$this->add_vendor_caps( $user_id );
  		$vendor = get_dc_vendor( $user_id );
			$vendor->generate_term();
  	}
  }
  
  /**
	 * ADD commission column on user dashboard
	 *
	 * @access public
	 * @return array
	*/	
  function column_register_product( $columns ) {
		$columns['product'] = 'Products';
		return $columns;
	}
	
	/**
	 * Display commission column on user dashboard
	 *
	 * @access public
	 * @return string
	*/		
	function column_display_product( $empty, $column_name, $user_id ) {
		if ( 'product' != $column_name && ! is_user_dc_vendor( $user_id ) )                                                                     
			return $empty;
		$vendor = get_dc_vendor( $user_id );
		if ( $vendor )  {
			$product_count = count($vendor->get_products());
			return "<a href='edit.php?post_type=product&dc_vendor_shop=".$vendor->user_data->user_login."'><strong>{$product_count}</strong></a>" ;
		}
		else return "<strong></strong>";
	}
	
	/**
	 * Add vendor action link in user dashboard
	 *
	 * @access public
	 * @return array
	*/	
	function vendor_action_links( $actions, $user_object ) {
		global $DC_Product_Vendor;
		
		if ( is_user_dc_vendor( $user_object ) ) {
			$vendor = get_dc_vendor( $user_object->ID );
			if ($vendor) {
				$actions['view_vendor'] = "<a target=_blank class='view_vendor' href='" . $vendor->permalink . "'>" . __( 'View', $DC_Product_Vendor->text_domain ) . "</a>";
			}
		}
		
		if ( is_user_dc_pending_vendor( $user_object ) ) {
			$vendor = get_dc_vendor( $user_object->ID );
			$actions['activate'] = "<a class='activate_vendor' data-id='".$user_object->ID."'href=#>" . __( 'Activate', $DC_Product_Vendor->text_domain ) . "</a>";
			$actions['reject'] = "<a class='reject_vendor' data-id='".$user_object->ID."'href=#>" . __( 'Reject', $DC_Product_Vendor->text_domain ) . "</a>";
		}
		
		if ( is_user_dc_rejected_vendor( $user_object ) ) {
			$vendor = get_dc_vendor( $user_object->ID );
			$actions['activate'] = "<a class='activate_vendor' data-id='".$user_object->ID."'href=#>" . __( 'Activate', $DC_Product_Vendor->text_domain ) . "</a>";
		}
		
		
		return $actions;
	}
	
	/**
	 * function additional_user_fields
	 * @access private
	 * @param $user obj
	 * @return void
	 */
	function additional_user_fields( $user ) {
		global $DC_Product_Vendor;
		$vendor = get_dc_vendor( $user->ID );
		if ( $vendor ) { ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="View Vendor" > <?php _e('View Vendor', $DC_Product_Vendor->text_domain); ?></label>
						</th>
						<td>
							<a class="button-primary" target="_blank" href=<?php echo $vendor->permalink; ?>>View</a>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
			$DC_Product_Vendor->dc_wp_fields->dc_generate_form_field( $this->get_vendor_fields( $user->ID ) );
		}
	}
	
	function validate_user_fields( &$errors, $update, &$user ) {
		global $DC_Product_Vendor;
		if(!$update) {
			if ( term_exists( sanitize_title( $_POST['vendor_page_slug'] ), 'dc_vendor_shop' ) ) {
				$errors->add( 'vendor_slug_exists', __( 'Slug already exists', $DC_Product_Vendor->text_domain ) );
			}
		} else {
			$vendor = get_dc_vendor( $user->ID );
			$vendor_term = get_term($vendor->term_id, 'dc_vendor_shop');
			if($vendor_term->slug != $_POST['vendor_page_slug']) {
				if ( term_exists( sanitize_title( $_POST['vendor_page_slug'] ), 'dc_vendor_shop' ) ) {
					$errors->add( 'vendor_slug_exists', __( 'Slug already exists', $DC_Product_Vendor->text_domain ) );
				}
			}
		}
	}
	
	/**
	* Saves additional user fields to the database
	* function save_vendor_data
	* @access private
	* @param $user_id
	* @return void
	*/
	function save_vendor_data( $user_id ) {
		global $DC_Product_Vendor;
		$user = new WP_User($user_id);
		//print_r($_POST);
		// only saves if the current user can edit user profiles
		if ( ! current_user_can( 'edit_user', $user_id ) )	return false;
		$errors = new WP_Error();
		
		if(!is_user_dc_vendor($user_id) && $_POST['role'] == 'dc_vendor') {
			$user->add_role( 'dc_vendor' );
			update_user_meta($user_id, '_vendor_submit_product', 'Enable');
			update_user_meta($user_id, '_vendor_submit_coupon', 'Enable');
			$this->add_vendor_caps( $user_id );
			$vendor = get_dc_vendor( $user_id );
			$vendor->generate_term();
			$user_dtl = get_userdata( absint( $user_id ) );
			$email = WC()->mailer()->emails['WC_Email_Approved_New_Vendor_Account'];
			$email->trigger( $user_id, $user_dtl->user_pass );
		}
		
		$fields = $this->get_vendor_fields( $user_id );
		$vendor = get_dc_vendor( $user_id );
		foreach( $fields as $fieldkey => $value ) {
			if ( isset( $_POST[ $fieldkey ] ) ) {
				if ( $fieldkey == 'vendor_page_title' ) {
					if( ! $vendor->update_page_title( wc_clean( $_POST[$fieldkey] ) ) ) {
						$errors->add( 'vendor_title_exists', __( 'Title Update Error', $DC_Product_Vendor->text_domain ) );
					} else {
						wp_update_user( array( 'ID' => $user_id, 'display_name' => $_POST[ $fieldkey ]  ) );
					}
				} elseif ( $fieldkey == 'vendor_page_slug' ) {
					if ( ! $vendor->update_page_slug( wc_clean( $_POST[$fieldkey] ) ) ) {
						$errors->add( 'vendor_slug_exists', __( 'Slug already exists', $DC_Product_Vendor->text_domain ) );
					}
				} else {
					update_user_meta( $user_id, '_' . $fieldkey, wc_clean( $_POST[ $fieldkey ] ) );
				}
			}	else if( !isset( $_POST['vendor_submit_product'] ) && $fieldkey == 'vendor_submit_product' )  {
				delete_user_meta($user_id, '_vendor_submit_product');
			} else if(!isset( $_POST['vendor_submit_coupon'] ) && $fieldkey == 'vendor_submit_coupon') {
				delete_user_meta($user_id, '_vendor_submit_coupon');
			} else if(!isset( $_POST['vendor_hide_description'] ) && $fieldkey == 'vendor_hide_description') {
				delete_user_meta($user_id, '_vendor_hide_description');
			}
		}
		$this->user_change_cap( $user_id );
		
		if( is_user_dc_vendor($user_id) && isset($_POST['role']) && $_POST['role'] != 'dc_vendor' ) {
			$vendor = get_dc_vendor( $user_id );
			$user->remove_role( 'dc_vendor' );
			if( $_POST['role'] != 'dc_pending_vendor' ) {
				$user->remove_role( 'dc_pending_vendor' );
			}
			wp_delete_term( $vendor->term_id, 'dc_vendor_shop' );
		}		
	}
	
	/**
	* Delete vendor data on user delete
	* function delete_vendor
	* @access private
	* @param $user_id
	* @return void
	*/
	function delete_vendor( $user_id ) {
		global $DC_Product_Vendor;
		
  	if( is_user_dc_vendor( $user_id ) ) {
  		
  		$vendor = get_dc_vendor( $user_id );
			
			do_action( 'delete_dc_vendor', $vendor );
			
			if( isset( $_POST['reassign_user'] ) && ! empty( $_POST['reassign_user'] ) && ( $_POST['delete_option'] == 'reassign' ) ) {
				if( is_user_dc_vendor( absint( $_POST['reassign_user'] ) ) ) {
					if( $products = $vendor->get_products( array( 'fields' => 'ids' ) ) ) {
						foreach( $products as $product_id ) {
							$new_vendor = get_dc_vendor( absint( $_POST['reassign_user'] ) );
							wp_set_object_terms( $product_id, absint( $new_vendor->term_id ), $DC_Product_Vendor->taxonomy->taxonomy_name );
						}
					}
				} else {
					wp_die( __( 'Select a vendor.', $DC_Product_Vendor->text_domain ) );
				}
			}
			
			wp_delete_term( $vendor->term_id, $DC_Product_Vendor->taxonomy->taxonomy_name );
		}
	}
	
	/**
	 * change user capability
	 *
	 * @access public
	 * @return void
	*/	
	function user_change_cap( $user_id ) {
		global $DC_Product_Vendor;
		
		$user = new WP_User( $user_id );
		
		$product_caps = array("edit_product","delete_product","edit_products","edit_others_products","delete_published_products","delete_products","delete_others_products","edit_published_products");
		$is_submit_product = get_user_meta( $user_id, '_vendor_submit_product', true );
		if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_product') ) {
			if($is_submit_product) {
				foreach( $product_caps as $product_cap_add ) {
					$user->add_cap( $product_cap_add );
				}
			} 
		}
		if(empty($is_submit_product)) {
			foreach( $product_caps as $product_cap_remove ) {
				$user->remove_cap( $product_cap_remove );
			}
		}
		
		$coupon_caps = array("edit_shop_coupons", "delete_shop_coupons", "edit_shop_coupons", "edit_others_shop_coupons" , "delete_published_shop_coupons", "delete_shop_coupons", "delete_others_shop_coupons"	, "edit_published_shop_coupons");
		$is_submit_coupon = get_user_meta( $user_id, '_vendor_submit_coupon', true );
		if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_coupon') ) {
			if($is_submit_coupon) {
				foreach( $coupon_caps as $coupon_cap_add ) {
					$user->add_cap( $coupon_cap_add );
				}
			} 
		}
		if(empty($is_submit_coupon)) {
			foreach( $coupon_caps as $coupon_cap_remove ) {
				$user->remove_cap( $coupon_cap_remove );
			}
		}
	}
	
	/**
	 * woocommerce_address_field
	 *
	 * @access public
	 * @return $fields
	*/	
	function remove_woocommerce_address_field( $fields ) {
		$fields = array();
		return $fields;
	}
	
	function profile_admin_buffer_start() {
		ob_start( array( $this , 'remove_plain_bio' ) );
	}
	
	function profile_admin_buffer_end() {
		ob_end_flush();
	}
	
	/**
	 * remove_plain_bio
	 *
	 * @access public
	 * @return $buffer
	*/	
	function remove_plain_bio($buffer) {
		$titles = array('#<h3>About Yourself</h3>#','#<h3>About the user</h3>#');
		$buffer=preg_replace($titles,'<h3>Password</h3>',$buffer,1);
		$biotable='#<h3>Password</h3>.+?<table.+?/tr>#s';
		$buffer=preg_replace($biotable,'<h3>Password</h3> <table class="form-table">',$buffer,1);
		return $buffer;
	}
	
	/**
	 * Add vendor form in woocommece registration form
	 *
	 * @access public
	 * @return void
	*/	
	public function dc_woocommerce_register_form() {
		global $DC_Product_Vendor;
		$customer_can = $DC_Product_Vendor->vendor_caps->vendor_general_settings('enable_registration');
		if($customer_can) {
			?>
			<tr>
				<p class="form-row form-row-wide">
					<input type="checkbox" name="pending_vendor" value="true"> <?php _e('Apply to become a vendor ?', $DC_Product_Vendor->text_domain);?>
				</p>
			</tr>
		<?php }
	}
	
	/**
	 * Add vendor form in woocommece registration form
	 *
	 * @access public
	 * @return void
	*/	
	public function dc_woocommerce_add_vendor_form() {
		global $DC_Product_Vendor;
		$customer_can = $DC_Product_Vendor->vendor_caps->vendor_general_settings('enable_registration');
		if($customer_can) {
			?>
			<tr>
				<p class="form-row form-row-wide">
					<input type="checkbox" name="pending_vendor" value="true"> <?php _e('Apply to become a vendor ?', $DC_Product_Vendor->text_domain);?>
				</p>
			</tr>
				<tr><input type="submit" name="vendor_apply" value="Save"></tr>
		<?php }
	}
	
	/**
	 * created customer notification
	 *
	 * @access public
	 * @return void
	*/	
	function dc_woocommerce_created_customer_notification() {
		if(isset($_POST['pending_vendor']) && !empty($_POST['pending_vendor'])) {
			remove_action('woocommerce_created_customer_notification', array(WC()->mailer(), 'customer_new_account'), 10, 3);
			add_action( 'woocommerce_created_customer_notification', array($this, 'dc_customer_new_account'), 10, 3);
		}
	}
	
	/**
	 * Send mail on new vendor creation
	 *
	 * @access public
	 * @return void
	*/	
	function dc_customer_new_account( $customer_id, $new_customer_data = array(), $password_generated = false ) {
		if ( ! $customer_id )
			return;
		$user_pass = ! empty( $new_customer_data['user_pass'] ) ? $new_customer_data['user_pass'] : '';
		$email = WC()->mailer()->emails['WC_Email_Vendor_New_Account'];
		$email->trigger( $customer_id, $user_pass, $password_generated );
		$email_admin = WC()->mailer()->emails['WC_Email_Admin_New_Vendor_Account'];
		$email_admin->trigger( $customer_id, $user_pass, $password_generated );
	}
	
	/**
	 * Register all emails for vendor
	 *
	 * @access public
	 * @return array
	*/	
	function dc_product_vendor_register_email_classes($emails) {
		
		include( 'emails/class-wc-email-vendor-new-account.php' );
		$emails['WC_Email_Vendor_New_Account'] = new WC_Email_Vendor_New_Account();
		include( 'emails/class-wc-email-admin-new-vendor-account.php' );
		$emails['WC_Email_Admin_New_Vendor_Account'] = new WC_Email_Admin_New_Vendor_Account();
		include( 'emails/class-wc-email-approved-vendor-new-account.php' );
		$emails['WC_Email_Approved_New_Vendor_Account'] = new WC_Email_Approved_New_Vendor_Account();
		include( 'emails/class-wc-email-rejected-vendor-new-account.php' );
		$emails['WC_Email_Rejected_New_Vendor_Account'] = new WC_Email_Rejected_New_Vendor_Account();
		include( 'emails/class-wc-email-vendor-new-order.php' );
		$emails['WC_Email_Vendor_New_Order'] = new WC_Email_Vendor_New_Order();
		include( 'emails/class-wc-email-vendor-notify-shipped.php' );
		$emails['WC_Email_Notify_Shipped'] = new WC_Email_Notify_Shipped();
		include( 'emails/class-wc-email-vendor-new-product-added.php' );
		$emails['WC_Email_Vendor_New_Product_Added'] = new WC_Email_Vendor_New_Product_Added();
		include( 'emails/class-wc-email-admin-added-new-product-to-vendor.php' );
		$emails['WC_Email_Admin_Added_New_Product_to_Vendor'] = new WC_Email_Admin_Added_New_Product_to_Vendor();
		include( 'emails/class-wc-email-vendor-new-commission-paid.php' );
		$emails['WC_Email_Vendor_Commissions_Paid'] = new WC_Email_Vendor_Commissions_Paid();
		
		return $emails;
	}
}
?>