<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		DC_Product_Vendor_User
 * @version		1.0.0
 * @package		DC_Product_Vendor
 * @author 		DualCube
 */ 
class DC_Product_Vendor_Capabilities {
	
	public $capability;
	
	public $general_cap;
	
	public $vendor_cap;
	
	public function __construct() {
		
		$this->capability = get_option("dc_product_settings_name");
		$this->general_cap = get_option("dc_general_settings_name");
		$this->vendor_cap = get_option("dc_capabilities_settings_name");
		add_filter('product_type_selector', array(&$this, 'dc_product_type_selector'), 10, 1);
		add_filter('product_type_options', array(&$this, 'dc_product_type_options'), 10);
		add_filter('wc_product_sku_enabled', array(&$this, 'dc_wc_product_sku_enabled'), 30);
		add_filter('woocommerce_product_data_tabs', array(&$this, 'dc_woocommerce_product_data_tabs'), 30);
		add_action('admin_print_styles', array(&$this, 'output_capability_css'));
		add_action('woocommerce_get_item_data', array(&$this, 'add_sold_by_text_cart'), 30, 2);
		add_action('woocommerce_order_status_completed', array(&$this, 'payment_complete_vendor_mail' ), 10, 1 );
		add_action('woocommerce_add_order_item_meta', array(&$this, 'order_item_meta_2'), 20, 2);
		add_action('woocommerce_after_shop_loop_item_title', array($this, 'dc_after_add_to_cart_form'),30);
		
	}
	
	/**
	 * Vendor Capability from Product Settings 
	 * @param capability
	 * @return boolean 
	*/
	public function vendor_can($cap) {
		if(is_array($this->capability) && array_key_exists($cap, $this->capability)) {
			return true;
		} else return false;
	}
	
	/**
	 * Vendor Capability from General Settings 
	 * @param capability
	 * @return boolean 
	*/
	public function vendor_general_settings($cap) {
		if(is_array($this->general_cap) && array_key_exists($cap, $this->general_cap)) {
			return true;
		} else return false;
	}
	
	
	/**
	 * Vendor Capability from Capability Settings 
	 * @param capability
	 * @return boolean 
	*/
	public function vendor_capabilities_settings($cap) {
		if(is_array($this->vendor_cap) && array_key_exists($cap, $this->vendor_cap)) {
			return true;
		} else return false;
	}
	
	/**
	 * Get Vendor Product Types
	 * @param product_types
	 * @return product_types 
	*/
	public function dc_product_type_selector($product_types) {
		$user = wp_get_current_user();
		if(is_user_dc_vendor($user) && $product_types) {
			foreach($product_types as $product_type => $value) {
				$vendor_can = $this->vendor_can($product_type);
				if(!$vendor_can) {
					unset($product_types[$product_type]);
				}
			}
		}
		return $product_types;
	}
	
	/**
	 * Get Vendor Product Types Options
	 * @param product_type_options
	 * @return product_type_options 
	*/
	public function dc_product_type_options($product_type_options) {
		$user = wp_get_current_user();
		if(is_user_dc_vendor($user) && $product_type_options) {
			foreach($product_type_options as $product_type_option => $value) {
				$vendor_can = $this->vendor_can($product_type_option);
				if(!$vendor_can) {
					unset($product_type_options[$product_type_option]);
				}
			}
		}
		return $product_type_options;
	}
	
	/**
	 * Check if Vendor Product SKU Enable
	 * @param state
	 * @return boolean 
	*/
	public function dc_wc_product_sku_enabled($state) {
		$user = wp_get_current_user();
		if(is_user_dc_vendor($user)) {
			$vendor_can = $this->vendor_can('sku');
			if($vendor_can) {
				return true;
			} else return false;
		}
		return true;
	}
	
	/**
	 * Set woocommerce product tab according settings
	 * @param panels
	 * @return panels 
	*/
	
	public function dc_woocommerce_product_data_tabs($panels) {
		$user = wp_get_current_user();
		if(is_user_dc_vendor($user)) {
			$vendor_can = $this->vendor_can('inventory');
			if(!$vendor_can) {
				unset($panels['inventory']);
			}
			$vendor_can = $this->vendor_can('shipping');
			if(!$vendor_can) {
				unset($panels['shipping']);
			}
			$vendor_can = $this->vendor_can('linked_product');
			if(!$vendor_can) {
				unset($panels['linked_product']);
			}
			$vendor_can = $this->vendor_can('attribute');
			if(!$vendor_can) {
				unset($panels['attribute']);
			}
			$vendor_can = $this->vendor_can('advanced');
			if(!$vendor_can) {
				unset($panels['advanced']);
			}
		}
		return $panels;
	}
	
	
	/**
	 * Set output capability css
	*/
	function output_capability_css() {
		$screen = get_current_screen();
		global $post;
		if (in_array( $screen->id, array( 'product' ))) {
			if(is_user_dc_vendor(get_current_user_id())) {
				if(!$this->vendor_can('tax')) {
					$custom_css .= '
					._tax_status_field, ._tax_class_field {
						display: none !important;
					}
					';
				}
				if(!$this->vendor_can('add_comment')) {
					$custom_css .= '
					.comments-box {
						display: none !important;
					}
					';
				}
				if(!$this->vendor_can('comment_box')) {
					$custom_css .= '
					#add-new-comment {
						display: none !important;
					}
					';
				}
				if($this->vendor_can('stylesheet')) {
					$custom_css .= $this->capability['stylesheet'];
				}
				
				$vendor_id = get_current_user_id();
				$vendor = get_dc_vendor($vendor_id);
				if($vendor && $post->post_author != $vendor_id) {
					$custom_css .= '.options_group.pricing.show_if_simple.show_if_external {
														display: none !important;
													}';
				}
				wp_add_inline_style('woocommerce_admin_styles', $custom_css);
			}
		}
	}
	
	/**
	 * Add Sold by Vendor text
	 * @param array, cart_item
	 * @return array 
	*/
	function add_sold_by_text_cart($array, $cart_item) {
		if($this->vendor_general_settings('sold_by_cart_and_checkout')) {
			$general_cap = $this->general_cap['sold_by_text'];
			if(!$general_cap) $general_cap = 'Sold By';
			$vendors = get_dc_product_vendors($cart_item['product_id']);
			if($vendors) {
				foreach($vendors as $vendor) {
					$array = array(array('name' => $general_cap , 'value' => $vendor->user_data->display_name));
				}
			}
		}
		return $array;
	}
	
	/**
	 * Add Sold by Vendor text
	 * @return void 
	*/
	function dc_after_add_to_cart_form() {
		global $post; 
		if($this->vendor_general_settings('sold_by_catalog')) {
			$vendors = get_dc_product_vendors($post->ID);
			if($vendors) {
				foreach($vendors as $vendor) {
					
					echo '<a class="by-vendor-name-link" style="display: block;" href="'.$vendor->permalink.'">by '. $vendor->user_data->display_name.'</a>';
				}
			}
		}
	}
	
	/**
	 * Send Payment Complete Mail
	 * @param order_id
	 * @return void 
	*/
	public function payment_complete_vendor_mail($order_id) {
		$vendors = get_vendor_from_an_order($order_id);
		if($vendors) {
			foreach($vendors as $vendor) {
				$vendor_obj = get_dc_vendor_by_term($vendor);
				$email_admin = WC()->mailer()->emails['WC_Email_Vendor_New_Order'];
				$email_admin->trigger( $order_id, $vendor_obj->user_data->user_email, $vendor_obj->term_id );
			}
		}
	}
	
		
	/**
	 * Save sold by text in database
	 * @param item_id, cart_item
	 * @return void 
	*/
	function order_item_meta_2($item_id, $cart_item) {
		global $DC_Product_Vendor;
		if($DC_Product_Vendor->vendor_caps->vendor_general_settings('sold_by_cart_and_checkout')) {
			$general_cap = $this->general_cap['sold_by_text'];
			if(!$general_cap) $general_cap = __('Sold By', $DC_Product_Vendor->text_domain);
			$vendors = get_dc_product_vendors($cart_item['product_id']);
			if($vendors) {
				foreach($vendors as $vendor) {
					woocommerce_add_order_item_meta($item_id, $general_cap, $vendor->user_data->display_name);
				}
			}
		}
	}
}
?>