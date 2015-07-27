<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/widget/store-location.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version     0.0.1
 */
extract( $instance );

?>

<div class="clearfix widget store-location">
    <h3 class="widget-title"><?php echo $title ?></h3>
    <div class="yith-wpv-store-location-wrapper">
        <div id="store-maps" class="gmap3" style="height: 300px;"></div>
        <a href="<?php echo $gmaps_link ?>" target="_blank"><?php _e( 'Show in Google Maps', 'yith_wc_product_vendors' ) ?></a>
    </div>
</div>

<script type="text/javascript">
(function ($) {
    $("#store-maps").gmap3({
        map   : {
            options: {
                zoom                     : 15,
                disableDefaultUI         : true,
                mapTypeControl           : false,
                panControl               : false,
                zoomControl              : false,
                scaleControl             : false,
                streetViewControl        : false,
                rotateControl            : false,
                rotateControlOptions     : false,
                overviewMapControl       : false,
                OverviewMapControlOptions: false
            },
            address: "<?php echo $location; ?>"
        },
        marker: {
            address: "<?php echo $location; ?>",
        }
    });
})(jQuery)
</script>