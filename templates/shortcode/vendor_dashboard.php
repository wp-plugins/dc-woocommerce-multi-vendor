<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_dashboard.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $woocommerce, $DC_Product_Vendor;
$user = wp_get_current_user();
$vendor = get_dc_vendor($user->ID);
if($vendor) {
$pages = get_option("dc_pages_settings_name");
$address1 = apply_filters( 'woocommerce_my_account_my_address_formatted_address', array(
					'address_1'		=> $vendor->address_1,
					'address_2'		=> $vendor->address_2,
					'city'			=> $vendor->city,
					'state'			=> $vendor->state,
					'postcode'		=> $vendor->postcode,
					'country'		=> $vendor->country
				), $user->ID, 'billing' );

$formatted_address = $woocommerce->countries->get_formatted_address( $address1 );
$image = $vendor->image;
if(!$vendor->image) $vendor->image = $DC_Product_Vendor->plugin_url . 'assets/images/WP-stdavatar.png';
?>

<div class="vendor_dashboard_shortcode">
	<table class="vendor_dash">
		<tbody>
			<tr>
				<th style="width: 30%;"><h3 for="vendor_profile"><?php _e('Profile', $DC_Product_Vendor->text_domain) ?></h3></th>
			</tr>
			<tr>
				<td><img width="125" class="vendor_img" src=<?php echo $vendor->image ?> id="vendor_image_display"></td>
				<th><label for="vendor_desc"> <?php echo $vendor->user_data->display_name ?> </label></th>
			</tr>
			<tr>
				<th><label for="vendor_company"><?php _e('My Company', $DC_Product_Vendor->text_domain) ?></label></th>
				<td colspan="2" ><?php echo $vendor->company; ?></td>
			</tr>
			<tr>
				<th><label for="vendor_desc"><?php _e('Description', $DC_Product_Vendor->text_domain) ?></label></th>
				<td colspan="2" ><?php echo $vendor->description; ?></td>
			</tr>
			<tr>
				<th><label for="vendor_address"><?php _e('Address', $DC_Product_Vendor->text_domain) ?></label></th>
				<td colspan="2" ><?php echo $formatted_address; ?></td>
			</tr>
			<tr>
				<th><label for="vendor_ph_No"><?php _e('Phone No', $DC_Product_Vendor->text_domain) ?></label></th>
				<td colspan="2" ><?php echo $vendor->phone; ?></td>
			</tr>
			</tbody>
	</table>
	<?php 
		if( $DC_Product_Vendor->vendor_caps->general_cap['notify_configure_vendor_store'] ){
			if( !$image || !$vendor->description ) {
				?>
				<table class="vendor_dash">
					<tbody>
						<tr> 
							<td>
								<?php
									$pages = get_option("dc_pages_settings_name");
									_e('<h3>You havent configured your store logo, description or name!</h3>', $DC_Product_Vendor->text_domain);
									_e('<p><a href="'.get_permalink($pages['shop_settings']).'"> Click here to set your store settings</a>.</p>', $DC_Product_Vendor->text_domain);
								?>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
			}
		}
	?>
	<table class="vendor_dash">
		<tbody>
			<tr>
				<td><a class="shop_url button button-primary" target="_blank" href=<?php echo $vendor->permalink; ?>><strong><?php _e('Go to My Shop', $DC_Product_Vendor->text_domain);?></strong></a></td>
				<?php if($DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_product') && get_user_meta($user->ID, '_vendor_submit_product' ,true)) { ?>
					<td><a class="shop_url button button-primary" target="_blank" href=<?php echo admin_url( 'edit.php?post_type=product' ); ?>><strong><?php _e('Submit a Product', $DC_Product_Vendor->text_domain);?></strong></a></td>
				<?php } ?>
				<td><a class="shop_url button"  href=<?php echo get_permalink($pages['shop_settings']); ?>><strong><?php _e('Edit My Profile', $DC_Product_Vendor->text_domain);?></strong></a></td>
			</tr>
		</tbody>
	</table>
	<?php
	} else if(is_user_dc_pending_vendor($user->ID)) { ?>
		<table class="vendor_dash">
			<tbody>
				<tr>
					<th style="width: 30%;"><label for="vendor_profile"><?php _e('Profile', $DC_Product_Vendor->text_domain); ?></label></th>
				</tr>
				<tr>
					<td>
						<label for="vendor_profile">
							<?php
								_e('Sorry! Your Request for "Vendor" Capability is not approved till now..', $DC_Product_Vendor->text_domain);
							?>
						</label>
					</td>
				</tr>
			</tbody>
		</table>
	<?php }
	?>
<div>