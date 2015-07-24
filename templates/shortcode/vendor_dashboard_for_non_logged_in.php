<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_dashboard_for_non_logged_in.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $woocommerce, $DC_Product_Vendor;
?>

<div class="vendor_apply">
<form method="post">
	<table class="vendor_apply" >
		<tbody>
			<tr><?php _e('Your Account is not Vendor Capable',  $DC_Product_Vendor->text_domain ) ?></tr>
			<tr> 
				<?php echo $DC_Product_Vendor->user->dc_woocommerce_register_form(); ?>
			</tr>
			<tr><input type="submit" name="vendor_apply" value="Save"></tr>
		</tbody>
	</table>
</form>
</div>