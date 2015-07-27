<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/widget/vendor-list.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */

global $DC_Product_Vendor;

$vendor_count = count($vendors);
if($vendor_count > 5 )	{ ?>
	<div style=" height: 191px;overflow-y: scroll;width: 226px;" >
<?php } else {?>
<div style=" height: 191px; width: 226px;" >
<?php }
if($vendors) {
	foreach($vendors as $vendors_key => $vendor) { ?>
		<h4 style="margin: 10px 0;">
			<a href="<?php echo esc_attr( $vendor->permalink ); ?>">
				<?php echo $vendor->user_data->display_name; ?>
			</a>
		</h4>
	<?php } 
}?>
</div>