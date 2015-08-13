<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor_tab.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */
 
global $DC_Product_Vendor, $product;
	$html = '';
	$vendors = get_dc_product_vendors( $product->id );
	if( $vendors ) {
		foreach( $vendors as $vendor ) {
			$html .= '<div class="product-vendor">';
			$html .= '<h2>' . $vendor->user_data->display_name . '</h2>';
			if( '' != $vendor->description ) {
					$html .= '<p>' . $vendor->description . '</p>';
			}
			$html .= '<p><a href="' . $vendor->permalink . '">' . sprintf( __( 'More products from %1$s', $DC_Product_Vendor->token ), $vendor->user_data->display_name ) . '</a></p>';
			$html .= '</div>';
		}
	}
	echo $html;
?>