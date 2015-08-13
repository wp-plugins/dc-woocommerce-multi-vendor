<?php
if(!function_exists('get_product_vendor_settings')) {
	/**
		* get plugin settings
		* @return array
	*/
	function get_product_vendor_settings($name = '', $tab = '') {
		if(empty($tab) && empty($name)) return '';
		if(empty($tab)) return get_option($name);
		if(empty($name)) return get_option("dc_{$tab}_settings_name");
		$settings = get_option("dc_{$tab}_settings_name");
		if(!isset($settings[$name])) return '';
		return $settings[$name];
	}
}

if( ! function_exists( 'is_user_dc_pending_vendor' ) ) {
	/**
	 * check if user is vendor
	 * @param userid or WP_User object
	 * @return array
	 */
	function is_user_dc_pending_vendor( $user ) {
		
		if( ! is_object( $user ) ) {
			$user = new WP_User( absint( $user ) );
		}
		return ( is_array( $user->roles ) && in_array( 'dc_pending_vendor', $user->roles ) );
	}
}


if( ! function_exists( 'is_user_dc_rejected_vendor' ) ) {
	/**
	 * check if user is vendor
	 * @param userid or WP_User object
	 * @return array
	 */
	function is_user_dc_rejected_vendor( $user ) {
		
		if( ! is_object( $user ) ) {
			$user = new WP_User( absint( $user ) );
		}
		return ( is_array( $user->roles ) && in_array( 'dc_rejected_vendor', $user->roles ) );
	}
}

if( ! function_exists( 'is_user_dc_vendor' ) ) {
	/**
	 * check if user is vendor
	 * @param userid or WP_User object
	 * @return boolean
	 */
	function is_user_dc_vendor( $user ) {
		
		if( ! is_object( $user ) ) {
			$user = new WP_User( absint( $user ) );
		}
		return ( is_array( $user->roles ) && in_array( 'dc_vendor', $user->roles ) );
	}
}

if( ! function_exists( 'get_dc_vendors' ) ) {
	/**
	 * Get all vendors
	 * @return arr Array of vendors
	 */
	function get_dc_vendors( $args = array() ) {
		global $DC_Product_Vendor;
		
		$vendors_array = false;
		
		$args = wp_parse_args( $args, array( 'role' => 'dc_vendor', 'fields' => 'ids', 'orderby' => 'registered', 'order' => 'ASC' ) );
		$user_query = new WP_User_Query( $args );
		
		if ( ! empty( $user_query->results ) ) {
			
			foreach( $user_query->results as $vendor_id ) {
				$vendors_array[] = get_dc_vendor( $vendor_id );
			}
		}
	
		return $vendors_array;
	}
}

if( ! function_exists( 'get_dc_vendor' ) ) {
	/**
	* Get individual vendor info by ID
	* @param  int $vendor_id ID of vendor
	* @return obj            Vendor object
	*/
	function get_dc_vendor( $vendor_id = 0 ) {
		global $DC_Product_Vendor;
	
		$vendor = false;
		
		if( is_user_dc_vendor( $vendor_id ) ) {
			$vendor = new DC_Vendor( absint( $vendor_id ) );
		}
	
		return $vendor;
	}
}

if( ! function_exists( 'get_dc_vendor_by_term' ) ) {
	/**
	 * Get individual vendor info by term id
	 * @param $term_id ID of term
	 */
	function get_dc_vendor_by_term( $term_id ) {
		$vendor = false;
		if ( $user_id = get_woocommerce_term_meta( $term_id, '_vendor_user_id' ) ) {
			if ( is_user_dc_vendor( $user_id ) ) {
				$vendor = get_dc_vendor( $user_id );
			}
		}
		return $vendor;
	}
}

if( ! function_exists( 'get_dc_product_vendors' ) ) {

	/**
	 * Get vendors for product
	 * @param  int $product_id Product ID
	 * @return arr             Array of product vendors
	 */
	function get_dc_product_vendors( $product_id = 0 ) {
		global $DC_Product_Vendor;
		$vendors = false;
	
		if( $product_id > 0 ) {
			$vendors_data = wp_get_post_terms( $product_id, $DC_Product_Vendor->taxonomy->taxonomy_name );
			foreach( $vendors_data as $vendor_data ) {
				$vendor = get_dc_vendor_by_term( $vendor_data->term_id );
				if( $vendor ) {
					$vendors[] = $vendor;
				}
			}
		}
	
		return $vendors;
	}
}

if( ! function_exists( 'doProductVendorLOG' ) ) {
	/**
	* Write to log file
	*/
	function doProductVendorLOG($str) {
		global $DC_Product_Vendor;
		$file = $DC_Product_Vendor->plugin_path.'log/product_vendor.log';
		if(file_exists($file)) {
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new content to the file
			$current .= "$str" . "\r\n";
			$current .= "-------------------------------------\r\n";
		} else {
			$current = "$str" . "\r\n";
			$current .= "-------------------------------------\r\n";
		}
		// Write the contents back to the file
		file_put_contents($file, $current);
	}
}

if( ! function_exists( 'is_vendor_dashboard' ) ) {

	/**
		* check if vendor dashboard page
		* @return boolean
	*/
	function is_vendor_dashboard() {
		$pages = get_option("dc_pages_settings_name");
		if(isset($pages['vendor_dashboard'])) {
			return is_page( $pages['vendor_dashboard'] ) ? true : false;
		}
		return false;
	}
}

if( ! function_exists( 'is_shop_settings' ) ) {

	/**
		* check if shop settings page
		* @return boolean
	*/
	function is_shop_settings() {
		$pages = get_option("dc_pages_settings_name");
		if(isset($pages['shop_settings'])) {
			return is_page( $pages['shop_settings'] ) ? true : false;
		}
		return false;
	}
}

if( ! function_exists( 'change_cap_existing_users' ) ) {
	
	/**
		* Change Capability in existing users
		* @return void
	*/
	function change_cap_existing_users( $user_cap ) {
		$product_caps = array("edit_product","delete_product","edit_products","edit_others_products","delete_published_products","delete_products","delete_others_products","edit_published_products");
		$coupon_caps = array("edit_shop_coupons", "delete_shop_coupons", "edit_shop_coupons", "edit_others_shop_coupons" , "delete_published_shop_coupons", "delete_shop_coupons", "delete_others_shop_coupons"	, "edit_published_shop_coupons");
		$get_dc_vendors = get_dc_vendors();
		if($get_dc_vendors) {
			foreach($get_dc_vendors as $get_dc_vendor) {
				$user =  new WP_User( $get_dc_vendor->id );
				if($user) {
					if( $user_cap == 'is_upload_files' ) $user->remove_cap('upload_files');
					if( $user_cap == 'is_submit_product' ) {
						foreach( $product_caps as $product_cap ) {
							 $user->remove_cap($product_cap);
						}
					}
					if($user_cap == 'is_submit_coupon') {
						foreach( $coupon_caps as $coupon_cap ) {
							 $user->remove_cap($coupon_cap);
						}
					}
					if( $user_cap == 'is_published_product' ) $user->remove_cap('publish_products');
				}
			}
		}
	}
}

if( ! function_exists( 'add_cap_existing_users' ) ) {
	/**
	* Add Capability in existing users
	* @return void
	*/
	function add_cap_existing_users( $user_cap ) {
		$get_dc_vendors = get_dc_vendors();
		if($get_dc_vendors) {
			foreach($get_dc_vendors as $get_dc_vendor) {
				$caps = array();
				$user =  new WP_User( $get_dc_vendor->id );
				if($user) {
					if( $user_cap == 'is_submit_product')  {
						$vendor_submit_products = get_user_meta($user->ID, '_vendor_submit_product', true);
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
						$caps[] = "read_product";
						foreach( $caps as $cap ) {
							$user->add_cap( $cap );
						}
					} else if( $user_cap == 'is_submit_coupon'){
						$vendor_submit_products = get_user_meta($user->ID, '_vendor_submit_coupon', true);
						if( $vendor_submit_products ) {
							$caps[] = 'edit_shop_coupon';
							$caps[] = 'delete_shop_coupon';
							$caps[] = 'edit_shop_coupons';
							$caps[] = 'read_shop_coupons';
							$caps[] = 'delete_shop_coupons';
							$caps[] = 'publish_shop_coupons';
							$caps[] = 'edit_published_shop_coupons';
							$caps[] = 'delete_published_shop_coupons';
							$caps[] = 'edit_others_shop_coupons';
							$caps[] = 'delete_others_shop_coupons';
						}
						$caps[] = "edit_posts";
						$caps[] = "read_shop_coupon";
						foreach( $caps as $cap ) {
							$user->add_cap( $cap );
						}
					} else {
						$user->add_cap($cap);
					}
				}
			}
		}
	}
}


if( ! function_exists( 'get_vendor_from_an_order' ) ) {
	/**
		* Get vendor from a order
		* @return array
	*/
	function get_vendor_from_an_order($order_id) {
		$vendors = array();
		$order = new WC_Order( $order_id );
		$items = $order->get_items( 'line_item' );
		foreach( $items as $item_id => $item ) {
			$product_id = $order->get_item_meta( $item_id, '_product_id', true );
			if( $product_id ) {
				$product_vendors = wp_get_post_terms( $product_id, 'dc_vendor_shop', array("fields" => "ids"));
				if( $product_vendors ) {
					$vendors[] = $product_vendors[0];
				}
			}
		}
		return $vendors;
	}
}

if( ! function_exists( 'is_vendor_page' ) ) {
	/**
		* check if vendor pages
		* @return boolean
	*/
	function is_vendor_page() {
		$pages = get_option("dc_pages_settings_name");
		return ( is_page( absint ( $pages['shop_settings'] ) ) || is_page( absint( $pages['vendor_dashboard'] ) ) ||  is_page( absint ( $pages['view_order'] ) ) ); 
	}    
}

if( ! function_exists( 'is_vendor_order_by_product_page' ) ) {
	/**
		* check if vendor order page
		* @return boolean
	*/
	function is_vendor_order_by_product_page() {
		$pages = get_option("dc_pages_settings_name");
		return ( is_page( absint ( $pages['view_order'] ) )  ); 
	}    
}


if( ! function_exists( 'get_vendor_coupon_amount' ) ) {
	/**
		* get vendor coupon from order.
		* @return boolean
	*/
	function get_vendor_coupon_amount($item_product_id, $order_id, $vendor) {
		$order = new WC_Order ($order_id);
		$coupons = $order->get_used_coupons();
		$coupon_used = array();
		if(!empty($coupons)) {
			foreach($coupons as $coupon_code) {
				$coupon = new WC_Coupon( $coupon_code );
				$coupon_post = get_post($coupon->id);
				$author_id = $coupon_post->post_author;
				if(get_current_user_id() != $author_id) {
					continue;
				} else {
					$coupon_product_ids = $coupon->product_ids;
					if(!in_array($item_product_id, $coupon_product_ids)) {
						continue;
					} else {
						$coupon_used[] = $coupon_code;
					}
				}
			}
			if(!empty($coupon_used)) {
				$return_coupon = ' ,   Copoun Used : ';
				$no_of_coupon_use = false;
				foreach($coupon_used as $coupon_use) {
					if(!$no_of_coupon_use)	$return_coupon .= '"'. $coupon_use . '"';
					else $return_coupon .= ', "' . $coupon_use .'"';
					$no_of_coupon_use = true;
				}
				return $return_coupon;
			} else {
				return null;
			}
		}
	}
}
if( ! function_exists( 'dc_product_vendor_action_links' ) ) {

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
}
?>
