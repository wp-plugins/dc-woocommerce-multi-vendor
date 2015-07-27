<?php
class WC_Shop_Setting_Shortcode {

	public function __construct() {

	}

	/**
	 * Output the demo shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	public function output( $attr ) {
		global $DC_Product_Vendor;
		$DC_Product_Vendor->nocache();
		if ( ! defined( 'MNDASHBAOARD' ) ) define( 'MNDASHBAOARD', true );
		$user_id = get_current_user_id();
		$vendor = get_dc_vendor($user_id);
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if(isset($_POST['store_save'])) {	
				$fields = $DC_Product_Vendor->user->get_vendor_fields( get_current_user_id() );
				foreach( $fields as $fieldkey => $value ) {
					if ( isset( $_POST[ $fieldkey ] )  ) {
						if( $fieldkey == 'vendor_description' ) update_user_meta( $user_id, '_' . $fieldkey,  $_POST[ $fieldkey ]  );
						else update_user_meta( $user_id, '_' . $fieldkey, wc_clean( $_POST[ $fieldkey ] ) );
						if ( $fieldkey == 'vendor_page_title' ) {
							if( ! $vendor->update_page_title( wc_clean( $_POST[ $fieldkey ] ) ) ) {
								echo  _e( 'Shop Title Update Error', $DC_Product_Vendor->text_domain );
							} else {
								wp_update_user( array( 'ID' => $user_id, 'display_name' => $_POST[ $fieldkey ]  ) );
							}
						} 
					} else if(!isset( $_POST['vendor_hide_description'] ) && $fieldkey == 'vendor_hide_description') {
						delete_user_meta($user_id, '_vendor_hide_description');
					}
				} 
			}
		}
		$DC_Product_Vendor->template->get_template( 'shortcode/shop_settings.php', $DC_Product_Vendor->user->get_vendor_fields( get_current_user_id() ) );
	}
}
