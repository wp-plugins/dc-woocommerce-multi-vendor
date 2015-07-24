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

if ( ! class_exists( 'DC_Widget_Quick_Info_Widget' ) ) {
    /**
     * DC_Widget_Quick_Info_Widget
     *
     * @author dualcube
     *
     * @since  1.0.0
     */
    class DC_Widget_Quick_Info_Widget extends WP_Widget {

        public $response = array();
        /**
         * Construct
         */
        public function __construct() {
        		global $DC_Product_Vendor;
            $this->response = array(
                0 => array(
                    'message' => __( 'Unable to send email. Please try again', $DC_Product_Vendor->text_domain ),
                    'class'   => 'error'
                ),
                1 => array(
                    'message' => __( 'Email sent successfully', $DC_Product_Vendor->text_domain ),
                    'class'   => 'message'
                ),
            );

            add_action( 'init', array( $this, 'send_mail' ), 20 );

            $id_base        = 'dc-vendor-quick-info';
            $name           = __( 'DC Vendor Quick Info', $DC_Product_Vendor->text_domain );
            $widget_options = array(
                'description' => __( 'Add a quick info contact form in vendor\'s store page', $DC_Product_Vendor->text_domain )
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
					$show_widget = true;
					if( is_singular( 'product' ) ) {
						$show_widget = false;
					}
					if( is_archive() && ! is_tax( 'dc_vendor_shop' ) ) {
						$show_widget = false;
					}
					
					$hide_from_guests = ! empty( $instance['hide_from_guests'] ) ? true : false;
					if( $hide_from_guests ){
							$show_widget = is_user_logged_in();
					}
						
					if( $show_widget ) {
						if( is_tax( 'dc_vendor_shop' ) ) {
							$vendor_id = get_queried_object()->term_id;
							if( $vendor_id ) {
								$vendor = get_dc_vendor_by_term( $vendor_id );
							}
							$args = array(
									'instance'      => $instance,
									'vendor'        => $vendor,
									'current_user'  => wp_get_current_user(),
									'widget'        => $this
							);
							$DC_Product_Vendor->template->get_template( 'widget/quick-info.php', $args);
						}
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
        		global $DC_Product_Vendor;
            $defaults = array(
                'title'             => __( 'Quick Info', $DC_Product_Vendor->text_domain ),
                'description'       => __( 'Do you need more information? Write to us!', $DC_Product_Vendor->text_domain ),
                'hide_from_guests'  => '',
                'submit_label'      => __( 'Submit', $DC_Product_Vendor->text_domain ),
            );

            $instance = wp_parse_args( (array) $instance, $defaults );
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', $DC_Product_Vendor->text_domain ) ?>:
                    <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Description', $DC_Product_Vendor->text_domain ) ?>:
                    <input type="text" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" value="<?php echo $instance['description']; ?>" class="widefat" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'submit_label' ); ?>"><?php _e( 'Submit vutton label text', $DC_Product_Vendor->text_domain ) ?>:
                    <input type="text" id="<?php echo $this->get_field_id( 'submit_label' ); ?>" name="<?php echo $this->get_field_name( 'submit_label' ); ?>" value="<?php echo $instance['submit_label']; ?>" class="widefat" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'hide_from_guests' ); ?>"><?php _e( 'Hide from guests', $DC_Product_Vendor->text_domain ) ?>:
                    <input type="checkbox" id="<?php echo $this->get_field_id( 'hide_from_guests' ); ?>" name="<?php echo $this->get_field_name( 'hide_from_guests' ); ?>" value="1" <?php checked( $instance['hide_from_guests'], 1, true )?> class="widefat" />
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
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @see    WP_Widget::form()
         */
        public function update( $new_instance, $old_instance ) {
            $instance                       = $old_instance;
            $instance['title']              = strip_tags( $new_instance['title'] );
            $instance['description']        = strip_tags( $new_instance['description'] );
            $instance['hide_from_guests']   = strip_tags( $new_instance['hide_from_guests'] );
            $instance['submit_label']       = strip_tags( $new_instance['submit_label'] );
            return $instance;
        }

        /**
         * Send the quick info form mail
         *
         * @since 1.0
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function send_mail() {
            if ( $this->check_form() ) {
            
                /* === Sanitize Form Value === */
                $vendor     = get_dc_vendor( $_POST['quick_info']['vendor_id'] );
                $to         = sanitize_email( $vendor->user_data->user_email );
                $subject    = sanitize_text_field( $_POST['quick_info']['subject'] );
                $message    = sanitize_text_field( $_POST['quick_info']['message'] );
                $from       = sanitize_text_field( $_POST['quick_info']['name'] );
                $from_email = sanitize_email( $_POST['quick_info']['email'] );
                $admin_email = get_option('admin_email');
                $headers[]= "From: {$from} <{$from_email}>";
                $headers[]= "Cc: Admin <{$admin_email}>";
                
                /* === Send Mail === */
                $check = wp_mail( $to, $subject, $message, $headers );

                /* === Prevent resubmit form === */
                unset( $_POST );
                $redirect = esc_url( add_query_arg( array( 'message' => $check ? 1 : 0 ), $vendor->permalink ) );
                wp_redirect( $redirect );
                exit;
            }
        }

        /**
         * Check form information
         *
         * @since  1.0
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return bool
         */
        public function check_form(){
            return
                    ! empty( $_POST['dc_vendor_quick_info_submitted'] ) &&
                    wp_verify_nonce( $_POST['dc_vendor_quick_info_submitted'], 'dc_vendor_quick_info_submitted' ) &&
                    ! empty( $_POST['quick_info'] ) &&
                    ! empty( $_POST['quick_info']['name'] ) &&
                    ! empty( $_POST['quick_info']['subject'] ) &&
                    ! empty( $_POST['quick_info']['email'] ) &&
                    ! empty( $_POST['quick_info']['message'] ) &&
                    ! empty( $_POST['quick_info']['vendor_id'] ) &&
                      empty( $_POST['quick_info']['spam'] );
        }
    }
}
