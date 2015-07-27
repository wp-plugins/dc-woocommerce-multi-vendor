<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/archive_vendor_info.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
global $DC_Product_Vendor;
?>
<div class="vendor_description_background" style="background: url(<?php echo $banner; ?>) no-repeat; width: 100%; height: 230px; color: white; margin-bottom: 10px; background-size: 100% 100%;">
	<div class="vendor_description">
		<div class="vendor_img_add">
			<div class="img_div"><img height="400" width="200" src=<?php echo $profile;?> /></div>
			<div class="vendor_address">
				<p><img height="25" width="25" src=<?php echo $DC_Product_Vendor->plugin_url . 'assets/images/location_pin.png';?> /><label><?php echo $location; ?></label></p>
				<p><img height="25" width="25" src=<?php echo $DC_Product_Vendor->plugin_url . 'assets/images/mobile-phone.png';?> /><label><?php echo $mobile; ?></label></p>
				<p><img height="25" width="25" src=<?php echo $DC_Product_Vendor->plugin_url . 'assets/images/email_envelope_message.png';?> /><label><?php echo $email; ?></label></p>
			</div>
		</div>
		<div class="description">
			<div class="social_profile">
				<a href="<?php echo get_user_meta($vendor_id,'_vendor_fb_profile', true);?>"><img src="<?php echo $DC_Product_Vendor->plugin_url . 'assets/images/facebook.png';?>" alt="facebook" height="20" width="20" ></a>
				<a href="<?php echo get_user_meta($vendor_id,'_vendor_twitter_profile', true);?>"><img src="<?php echo $DC_Product_Vendor->plugin_url . 'assets/images/twitter.png';?>" alt="twitter" height="20" width="20" ></a>
				<a href="<?php echo get_user_meta($vendor_id,'_vendor_linkdin_profile', true);?>"><img src="<?php echo $DC_Product_Vendor->plugin_url . 'assets/images/linkedin.png';?>" alt="linkedin" height="20" width="20" ></a>
				<a href="<?php echo get_user_meta($vendor_id,'_vendor_google_plus_profile', true);?>"><img src="<?php echo $DC_Product_Vendor->plugin_url . 'assets/images/google-plus.png';?>" alt="google_plus" height="20" width="20" ></a>
				<a href="<?php echo get_user_meta($vendor_id,'_vendor_youtube', true);?>"><img src="<?php echo $DC_Product_Vendor->plugin_url . 'assets/images/youtube.png';?>" alt="youtube" height="20" width="20" ></a>
			</div>
		</div>
	</div>
</div>	
<div class="description_data">
	<?php
		$string = strip_tags($description);
	?>
	<table>
		<tbody>
			<tr>
				<td>
					<label><strong>Desciption</strong></label>
				</td>
				<td style="padding: 15px;">
					<i><?php echo stripslashes($string); ?></i>
				</td>
			</tr>
		</tbody>
	</table>
</div>
