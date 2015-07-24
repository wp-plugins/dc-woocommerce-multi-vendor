<?php
/**
 * Main class
 *
 * @author  Your Inspiration Themes
 * @package DC WooCommerce Ajax Navigation
 * @version 1.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'DC_Woocommerce_Store_Location_Widget' ) ) {
    /**
     * DC_Woocommerce_Store_Location_Widget
     *
     * @author dualcube
     *
     * @since  1.0.0
     */
    class DC_Woocommerce_Store_Location_Widget extends WP_Widget {

        /**
         * Construct
         */
        function __construct() {
        		global $DC_Product_Vendor;
            $id_base        = 'dc-vendor-store-location';
            $name           = __( 'DC Vendor Store Location', $DC_Product_Vendor->text_domain );
            $widget_options = array(
                'description' => __( 'Display the vendor\'s store location in Google Maps', $DC_Product_Vendor->text_domain  )
            );

            parent::__construct( $id_base, $name, $widget_options );
        }

        /**
         * Echo the widget content.
         *
         * Subclasses should over-ride this function to generate their widget code.
         *
         * @param array $args     Display arguments including before_title, after_title,
         *                        before_widget, and after_widget.
         * @param array $instance The settings for the particular instance of the widget.
         *
         * @author dualcube
         */
        public function widget( $args, $instance ) {
            global $DC_Product_Vendor, $woocommerce;
						extract( $args, EXTR_SKIP );
						$vendor_id = false;
						$vendors = false;
						// Only show current vendor widget when showing a vendor's product(s)
						$show_widget = false;
						
						if( is_tax( 'dc_vendor_shop' ) ) {
							$show_widget = true;
						}
						
						if( $show_widget ) {
							$vendor_id = get_queried_object()->term_id;
							if( $vendor_id ) {
								$vendor = get_dc_vendor_by_term( $vendor_id );
							}
							$vendor_address_1 = get_user_meta($vendor->id, '_vendor_address_1', true);
							$vendor_address_2 = get_user_meta($vendor->id, '_vendor_address_2', true);
							$vendor_city =      get_user_meta($vendor->id, '_vendor_city', true);
							$vendor_state =     get_user_meta($vendor->id, '_vendor_state', true);
							$vendor_postcode =  get_user_meta($vendor->id, '_vendor_postcode', true);
							$vendor_country =   get_user_meta($vendor->id, '_vendor_country', true);
							$location = '';
							if($vendor_address_1) $location = $vendor_address_1.' ,';
							if($vendor_address_2) $location .= $vendor_address_2.' ,';
							if($vendor_city) $location .= $vendor_city.' ,';
							if($vendor_state) $location .= $vendor_state.' ,';
							if($vendor_postcode) $location .= $vendor_postcode.' ,';
							if($vendor_country) $location .= $vendor_country;
							
							$args = array(
									'instance'      => $instance,
									'gmaps_link'    => esc_url( add_query_arg( array( 'q' => urlencode( $location ) ),  '//maps.google.com/' ) ),
									'location'      => $location
							);
							$DC_Product_Vendor->template->get_template( 'widget/store-location.php', $args);
            }
        }

        /**
         * Output the settings update form.
         *
         * @param array $instance Current settings.
         *
         * @return string Default return is 'noform'.
         * @author dualcube
         */

        public function form( $instance ) {
        		global $DC_Product_Vendor, $woocommerce;
            $defaults = array(
                'title' => __( 'Store Location', $DC_Product_Vendor->text_domain ),
            );

            $instance = wp_parse_args( (array) $instance, $defaults );
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', $DC_Product_Vendor->text_domain ) ?>:
                    <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
                </label>
            </p>
        <?php
        }

        /**
         * Update a particular instance.
         *
         * This function should check that $new_instance is set correctly. The newly-calculated
         * value of `$instance` should be returned. If false is returned, the instance won't be
         * saved/updated.
         *
         * @param array $new_instance New settings for this instance as input by the user via.
         * @param array $old_instance Old settings for this instance.
         *
         * @return array Settings to save or bool false to cancel saving.
         * @author dualcube
         * @see    WP_Widget::form()
         */
        public function update( $new_instance, $old_instance ) {
            $instance          = $old_instance;
            $instance['title'] = strip_tags( $new_instance['title'] );
            return $instance;
        }
    }
}
