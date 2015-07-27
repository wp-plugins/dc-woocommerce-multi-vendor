<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/shop_settings.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $DC_Product_Vendor;
$user = wp_get_current_user();
$vendor = get_dc_vendor($user->ID);
if($vendor) {
$vendor_hide_description = get_user_meta($user->ID, '_vendor_hide_description', true);
?>

<div class="shop_settings_shortcode">
	<form method="post" name="shop_settings_form" >
	
		<p class="vendor_page_title">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Shop Title', $DC_Product_Vendor->text_domain) ?></strong></label>
			<input type="text" id="vendor_page_title" name="vendor_page_title" class="user-profile-fields" value="<?php echo $vendor_page_title['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_page_slug">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Shop Slug', $DC_Product_Vendor->text_domain) ?></strong></label>
			<input type="text" id="vendor_page_slug" name="vendor_page_slug" class="user-profile-fields" readonly value="<?php echo $vendor_page_slug['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_description">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Biographical Info', $DC_Product_Vendor->text_domain) ?></strong>
			<?php wp_editor( $vendor_description['value'], 'listingeditor', array('textarea_name' => vendor_description, 'textarea_rows' => 5) ); ?>
		</p>
		
		<p class="vendor_hide_description">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Hide description in Vendor Shop', $DC_Product_Vendor->text_domain) ?></strong></label>
			<input type="checkbox" id="vendor_hide_description" name="vendor_hide_description" <?php if($vendor_hide_description == 'Enable') echo 'checked=checked'; ?> class="user-profile-fields" value="Enable">
		</p>
		
		<p class="vendor_company">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Company', $DC_Product_Vendor->text_domain) ?></strong></label>
			<input type="text" id="vendor_company" name="vendor_company" class="user-profile-fields" value="<?php echo $vendor_company['value']; ?> " placeholder=""  />
		</p>
		
		<p class="vendor_address_1">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Address 1', $DC_Product_Vendor->text_domain) ?></strong></label>
		  <input type="text" id="vendor_address_1" name="vendor_address_1" class="user-profile-fields" value="<?php echo $vendor_address_1['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_address_2">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Address 2', $DC_Product_Vendor->text_domain) ?></strong></label>
		  <input type="text" id="vendor_address_2" name="vendor_address_2" class="user-profile-fields" value="<?php echo $vendor_address_2['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_city">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('City', $DC_Product_Vendor->text_domain) ?></strong></label>
			<input type="text" id="vendor_city" name="vendor_city" class="user-profile-fields" value="<?php echo $vendor_city['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_postcode">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Postcode', $DC_Product_Vendor->text_domain) ?></strong></label>
		  <input type="text" id="vendor_postcode" name="vendor_postcode" class="user-profile-fields" value="<?php echo $vendor_postcode['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_state">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('State', $DC_Product_Vendor->text_domain) ?></strong></label>
			<input type="text" id="vendor_state" name="vendor_state" class="user-profile-fields" value="<?php echo $vendor_state['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_country">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Country', $DC_Product_Vendor->text_domain) ?></strong></label>
			<input type="text" id="vendor_country" name="vendor_country" class="user-profile-fields" value="<?php echo $vendor_country['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_phone">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Phone', $DC_Product_Vendor->text_domain) ?></strong></label>
			<input type="text" id="vendor_phone" name="vendor_phone" class="user-profile-fields" value="<?php echo $vendor_phone['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_paypal_email">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Paypal Email', $DC_Product_Vendor->text_domain) ?>Paypal Email</strong></label>
			<input type="text" id="vendor_paypal_email" name="vendor_paypal_email" class="user-profile-fields" value="<?php echo $vendor_paypal_email['value']; ?>" placeholder=""  />
		</p>
		
		<p class="vendor_image">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Logo', $DC_Product_Vendor->text_domain) ?></strong></label>
			<span class="dc-wp-fields-uploader">
				<img id="vendor_image_display" src="<?php echo $vendor_image['value']; ?>" width="200" class="placeHolder" />
				<input type="text" name="vendor_image" id="vendor_image" style="display: none;" class="user-profile-fields" readonly value="<?php echo $vendor_image['value']; ?>"  />
				<input type="button" class="upload_button button button-secondary" name="vendor_image_button" id="vendor_image_button" value="Upload" />
				<input type="button" class="remove_button button button-secondary" name="vendor_image_remove_button" id="vendor_image_remove_button" value="Remove" />
			</span>
		</p>
		
		<p class="vendor_banner">
			<label style="width: 160px; display: inline-block;"><strong><?php _e('Banner', $DC_Product_Vendor->text_domain) ?></strong></label>
			<span class="dc-wp-fields-uploader">
				<img id="vendor_banner_display" src="<?php echo $vendor_banner['value']; ?>" class="placeHolder" />
				<input type="text" name="vendor_banner" id="vendor_banner" style="display: none;" class="user-profile-fields" readonly value="<?php echo $vendor_banner['value']; ?>"  />
				<input type="button" class="upload_button button button-secondary" name="vendor_banner_button" id="vendor_banner_button" value="Upload" />
				<input type="button" class="remove_button button button-secondary" name="vendor_banner_remove_button" id="vendor_banner_remove_button" value="Remove" />
			</span>
		</p>
		<br><br><p><input name="store_save" type="submit" value="<?php _e('Save', $DC_Product_Vendor->text_domain) ?>" /></p>
	</form>
<div>
<?php
}
?>