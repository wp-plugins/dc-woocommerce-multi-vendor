<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_coupon.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $woocommerce, $DC_Product_Vendor;
$user = wp_get_current_user();
$vendor = get_dc_vendor($user->ID);
if($vendor) {
	echo  '<h3>Coupons</h3>';
	if($DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_submit_coupon') && get_user_meta($user->ID, '_vendor_submit_coupon' ,true)) { 
		if($coupons) {?> 
			<table>
				<tbody>
				<th>Coupon Code</th>
				<th>Usage Count</th>
				<?php
				foreach($coupons as $coupon) {
					$usage_count = get_post_meta($coupon, 'usage_count', true);
					if(!$usage_count) $usage_count = 0;
					$coupon_post = get_post($coupon);
					echo '<tr>';
					echo '<td>'.$coupon_post->post_title.'</td>';
					echo '<td>'.$usage_count.'</td>';
					echo '</tr>';
				}
				?>
				</tbody>
			</table>
		<?php		
		} else {
			echo 'Sorry! You have not created any coupon till now.You can create your product specific coupon from - <a class="shop_url button button-primary" target="_blank" href='.admin_url( 'edit.php?post_type=shop_coupon' ).'><strong>Submit a Coupon</strong></a>';
		}
	} else {
		echo 'Sorry ! You havenot the capability to add coupons.';
	}
}
?>