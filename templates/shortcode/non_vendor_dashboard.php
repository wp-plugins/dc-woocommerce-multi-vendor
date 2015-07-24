<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/non_vendor_dashboard.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $woocommerce, $DC_Product_Vendor;
$user = wp_get_current_user();
if($user && !in_array( 'dc_pending_vendor', $user->roles ) && !in_array( 'administrator', $user->roles )) {
?>

<div class="vendor_apply">
<form method="post">
	<table class="vendor_apply" >
		<tbody>
			<tr><?php _e('Your Account is not Vendor Capable',  $DC_Product_Vendor->text_domain ) ?></tr>
			<?php 
				echo $DC_Product_Vendor->user->dc_woocommerce_add_vendor_form(); 
			?>
		</tbody>
	</table>
</form>
</div>
<?php } 

if($user &&  in_array( 'administrator', $user->roles )) { ?>
  <div class="vendor_apply">
  	<p>
  		<?php _e('You have logged in as Administrator.Try to logged out and then view this page.' , $DC_Product_Vendor->text_domain); ?>
  	</p>
  </div>
<?php
 }
?>