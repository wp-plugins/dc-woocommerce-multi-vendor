<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_order_export.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */

global $DC_Product_Vendor;
?>
<form method="post" name="export_orders">
	<input type="submit"
		   class="btn btn-primary btn-small"
		   style="float:right;margin-bottom:10px;"
		   name="export_orders"
		   value="<?php _e( 'Export orders',  $DC_Product_Vendor->text_domain); ?>"
	>
</form>