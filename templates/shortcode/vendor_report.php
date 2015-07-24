<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_report.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */
 
global $DC_Product_Vendor;

$user = wp_get_current_user();
if( is_user_logged_in()  && is_user_dc_vendor($user->ID) )	{
	$DC_Product_Vendor_Plugin_Post_Reports = new DC_Product_Vendor_Plugin_Post_Reports();
	$vendor_sales_report = $DC_Product_Vendor_Plugin_Post_Reports->vendor_total_earnings_report();
	$vendor_month_earnings = $DC_Product_Vendor_Plugin_Post_Reports->vendor_month_earnings(); ?>
	<div class="vendor_dashboard_shortcode">
		<table class="vendor_dash">
			<tbody>
				<tr>
					<th style="width: 30%;"><h3><?php _e('SALES REPORT', $DC_Product_Vendor->text_domain) ?></h3></th>
				</tr>
			</tbody>
		</table>
		<div class="vendor_month_report">
			<?php echo $vendor_month_earnings; ?>
		</div>
		<br>
		<div class="vendor_sales_report">
			<h4> <?php _e('Vendor Total Sales Report : ', $DC_Product_Vendor->text_domain) ?></h4>
		</div>
		<br>
		<div>
		<?php echo $vendor_sales_report; ?>
	</div> 
	<?php 
}
if(in_array( 'dc_pending_vendor', $user->roles )) { ?>
	<div class="vendor_dashboard_shortcode">
		<table class="vendor_dash">
			<tbody>
				<tr>
					<th style="width: 30%;"><label for="pending_vendor"><?php _e('SALES REPORT', $DC_Product_Vendor->text_domain) ?></label></th>
				</tr>
				<tr>
					<td>
						<label for="pending_vendor">
							<?php _e('There are no sale report as you have already applied for vendor, Please wait untill admin approval. ', $DC_Product_Vendor->text_domain) ?>
						</label>
					</td>
				</tr>
			</tbody>
		</table>
	<div>
	<?php 
} ?>