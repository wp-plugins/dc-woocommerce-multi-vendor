<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/widget/quick-info.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */

global $DC_Product_Vendor;
$submit_label = ! empty( $instance['submit_label'] ) ? $instance['submit_label'] : __( 'Submit', $DC_Product_Vendor->text_domain );
extract( $instance );
?>

<div class="clearfix widget dc-wpv-quick-info">
    <h3 class="widget-title"><?php echo $title ?></h3>
    <div class="dc-wpv-quick-info-wrapper">
        <?php
        if( isset( $_GET['message'] ) ) {
            $message = sanitize_text_field( $_GET['message'] );
            echo "<div class='woocommerce-{$widget->response[ $message ]['class']}'>" . $widget->response[ $message ]['message'] . "</div>";
        }

        else {
            echo '<p>' . $description . '</p>';
        }?>

        <form action="" method="post" id="respond">
            <input type="text" class="input-text " name="quick_info[name]" value="<?php echo $current_user->display_name ?>" placeholder="<?php _e( 'Name', $DC_Product_Vendor->text_domain ) ?>" required/>
            <input type="text" class="input-text " name="quick_info[subject]" value="" placeholder="<?php _e( 'Subject', $DC_Product_Vendor->text_domain ) ?>" required/>
            <input type="email" class="input-text " name="quick_info[email]" value="<?php echo $current_user->user_email  ?>" placeholder="<?php _e( 'Email', $DC_Product_Vendor->text_domain ) ?>" required/>
            <textarea name="quick_info[message]" rows="5" placeholder="<?php _e( 'Message', $DC_Product_Vendor->text_domain ) ?>" required></textarea>
            <input type="submit" class="submit" id="submit" name="quick_info[submit]" value="<?php echo $submit_label ?>" />
            <input type="hidden" name="quick_info[spam]" value="" />
            <input type="hidden" name="quick_info[vendor_id]" value="<?php echo $vendor->id ?>" />
            <?php wp_nonce_field( 'dc_vendor_quick_info_submitted', 'dc_vendor_quick_info_submitted' ); ?>
        </form>
    </div>
</div>