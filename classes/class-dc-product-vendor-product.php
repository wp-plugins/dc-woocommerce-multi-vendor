<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		DC_Product_Vendor_Product
 * @version		1.0.0
 * @package		DC_Product_Vendor
 * @author 		DualCube
*/
class DC_Product_Vendor_Product {

	public function __construct() {
		if( is_admin() ) {
			add_action(	'woocommerce_product_write_panel_tabs', array( &$this, 'add_vendor_tab' ), 30);
			add_action(	'woocommerce_product_write_panels', array( &$this, 'output_vendor_tab'), 30);
			add_action(	'save_post', array( &$this, 'process_vendor_data' ) );
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_variation_settings' ), 10, 3 );
			//add_filter( 'wp_insert_post_data' , array( $this, 'dont_publish_product') , '99', 2);
			add_filter('pre_get_posts', array( $this, 'convert_business_id_to_taxonomy_term_in_query'));
			add_action( 'transition_post_status',  array( $this, 'on_all_status_transitions'), 10, 3 );
		}
		add_action( 'woocommerce_product_thumbnails', array( $this, 'add_report_abuse_link' ), 30 );
		add_filter( 'woocommerce_product_tabs', array( $this, 'product_vendor_tab' ) );
		
		add_filter( 'wp_count_posts', array( &$this, 'vendor_count_products' ), 10, 3 );
	}
	
	public function filter_products_list( $request ) {
		global $typenow;

		$current_user = wp_get_current_user();

		if ( is_admin() && is_user_dc_vendor($current_user) && 'product' == $typenow ) {
				$request[ 'author' ] = $current_user->ID;
				$term_id = get_user_meta($current_user->ID, '_vendor_term_id', true);
				$taxquery = array(
						array(
								'taxonomy' => 'dc_vendor_shop',
								'field' => 'id',
								'terms' => array( $term_id ),
								'operator'=> 'IN'
						)
				);
	
			$request['tax_query'] = $taxquery;
		}

		return $request;
	}

        
	public function vendor_count_products( $counts, $type, $perm ) {
		$current_user = wp_get_current_user();

		if ( is_user_dc_vendor($current_user) && 'product' == $type ) {
			$term_id = get_user_meta($current_user->ID, '_vendor_term_id', true);
			
			$args = array(
				'post_type' => $type,
				'tax_query' => array(
					array(
							'taxonomy' => 'dc_vendor_shop',
							'field' => 'id',
							'terms' => array( $term_id ),
							'operator'=> 'IN'
					),
				),
			);

			/**
			 * Get a list of post statuses.
			 */
			$stati = get_post_stati();

			// Update count object
			foreach ( $stati as $status ) {
					$args['post_status'] = $status;
					$query = new WP_Query( $args );
					$posts = $query->get_posts();
					$counts->$status     = count( $posts );
			}
		}

		return $counts;
	}
	
	/**
	* notify admin on publish product by vendor
	* @return void
	*/
	function on_all_status_transitions( $new_status, $old_status, $post ) {
		if ( $new_status != $old_status && $post->post_status == 'pending') {
			$current_user = get_current_user_id();
			if($current_user) $current_user_is_vendor = is_user_dc_vendor($current_user);  
			if($current_user_is_vendor) {
				//send mails to admin for new vendor product
				$vendor = get_dc_vendor_by_term(get_user_meta( $current_user, '_vendor_term_id', true ));
				$email_admin = WC()->mailer()->emails['WC_Email_Vendor_New_Product_Added'];
				$email_admin->trigger( $post->post_id, $post, $vendor );
			}
		} else if( $new_status != $old_status &&  $post->post_status == 'publish' ) {
			$current_user = get_current_user_id();
			if($current_user) $current_user_is_vendor = is_user_dc_vendor($current_user);  
			if($current_user_is_vendor) {
				//send mails to admin for new vendor product
				$vendor = get_dc_vendor_by_term(get_user_meta( $current_user, '_vendor_term_id', true ));
				$email_admin = WC()->mailer()->emails['WC_Email_Vendor_New_Product_Added'];
				$email_admin->trigger( $post->post_id, $post, $vendor );
			}
		} 
		if( current_user_can('administrator') && $new_status != $old_status &&  $post->post_status == 'publish') { 
			if( isset($_POST['choose_vendor'] ) && !empty($_POST['choose_vendor'])) {
				$term = get_term( $_POST['choose_vendor'] , 'dc_vendor_shop' );
				$vendor = get_dc_vendor_by_term( $term->term_id );
				$email_admin = WC()->mailer()->emails['WC_Email_Admin_Added_New_Product_to_Vendor'];
				$email_admin->trigger( $post->post_id, $post, $vendor );
			}
		}
	}

	/**
	* Add Vendor tab in single product page 
	* @return void
	*/
	function add_vendor_tab() { ?>
		<li class="vendor_icon vendor_icons"><a href="#choose_vendor"><?php _e( 'Choose Vendor', 'woocommerce' ); ?></a></li>
	<?php }
	
	/**
	* Output of Vendor tab in single product page 
	* @return void
	*/
	function output_vendor_tab() { 
		global $post, $woocommerce;
		$data = wp_get_post_terms( $post->ID, 'dc_vendor_shop', array("fields" => "ids"));
		$commission_per_poduct = get_post_meta($post->ID, '_commission_per_product', true);
		$current_user = get_current_user_id();
		if($current_user) $current_user_is_vendor = is_user_dc_vendor($current_user);  
		$html .= '<div class="options_group" > <table class="form-field form-table">' ;
		$html .= '<tbody>';
		if( $data && isset($data) && !empty($data) ) {
			$vendor = get_dc_vendor_by_term( $data[0] );
			$option = '<option value="' . $vendor->term_id . '" selected="selected">' . $vendor->user_data->user_login . '</option>';
		} else if($current_user_is_vendor) {
			$vendor = get_dc_vendor_by_term(get_user_meta( $current_user, '_vendor_term_id', true ));
			$option = '<option value="' . $vendor->term_id . '" selected="selected">' . $vendor->user_data->user_login . '</option>';
		} else {
			$option = '<option>choose a vendor</option>';
		}
		$html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for="' . esc_attr( 'vendor' ) . '">' . 'Vendor' . '</label></td><td><select name="' . esc_attr( 'choose_vendor' ) . '" id="' . esc_attr( 'choose_vendor_ajax' ) . '" class="ajax_chosen_select_vendor" data-placeholder="Search for vendor&hellip;" style="width:300px;" >' . $option . '</select>' ;
		$html .= '<p class="description">' . 'choose vendor' . '</p>' ;
		$html .= '</td><tr/>' ;
		if(!$current_user_is_vendor) {
			$html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission"> Commission </label></td><td><input class="input-commision" type="text" name="commision" value="';
			if(isset($commission_per_poduct)) {
				$html .= ''.$commission_per_poduct.'';
			} else {
				$html .= '';
			}
			$html .= '"/>%<td></tr>';	
		}
		$html .= '</tbody>' ;
		$html .= '</table>';
		$html .= '</div>' ;
		?>
		<div id="choose_vendor" class="panel woocommerce_options_panel">
			<?php echo	$html; ?>
		</div>
	<?php }

	/**
	* save vendor related data
	* @return void
	*/
	function process_vendor_data( $post_id ) {
		$post = get_post( $post_id );
		
		if( $post->post_type == 'product' ) {
			if(isset($_POST['commision']) && !empty($_POST['commision'])) {
				update_post_meta( $post_id, '_commission_per_product', $_POST['commision'] );
			}
			if( isset($_POST['choose_vendor'] ) && !empty($_POST['choose_vendor'])) {
				$term = get_term( $_POST['choose_vendor'] , 'dc_vendor_shop' );
				wp_delete_object_term_relationships( $post_id, 'dc_vendor_shop' );
				wp_set_post_terms( $post_id, $term->name , 'dc_vendor_shop', true );
			}
			if(isset( $_POST['variable_post_id'] ) && !empty( $_POST['variable_post_id'] )) {
				foreach( $_POST['variable_post_id'] as $post_key => $value ) {
					$commission = $_POST['variable_product_vendors_commission'][$post_key];
					update_post_meta( $value , '_product_vendors_commission' , $commission );
				}
			}
		}
	}
	
	/**
	* save vendor related data for variation
	* @return void
	*/
	public function add_variation_settings( $loop, $variation_data, $variation ) {
		
		$commission = get_post_meta($variation->ID, '_product_vendors_commission', true );
		
		$html = '<tr>
								<td>
									<div class="_product_vendors_commission">
										<label for="_product_vendors_commission_' . $loop . '">' . __( 'Commission (%) ', 'wc_product_vendors' ) . ':</label>
										<input size="4" type="text" name="variable_product_vendors_commission[' . $loop . ']" id="_product_vendors_commission_' . $loop . '" value="' . $commission . '" />
									</div>
								</td>
							</tr>';
		
		echo $html;
	}
	
	/**
	* add vendor tab on single product page
	* @return void
	*/
	function product_vendor_tab( $tabs ) {
		global $product, $DC_Product_Vendor;
		$vendors = get_dc_product_vendors( $product->id );
		if( $vendors ) {
			if( count( $vendors ) > 1 ) {
					$title = __( 'Vendors', $DC_Product_Vendor->token );
			} else {
					$title = __( 'Vendor', $DC_Product_Vendor->token );
			}
			$tabs['vendor'] = array(
					'title' => $title,
					'priority' => 20,
					'callback' => array($this, 'woocommerce_product_vendor_tab')
			);
		}
		return $tabs;
	}
	
	/**
	* add vendor tab html
	* @return void
	*/
	function woocommerce_product_vendor_tab() {
		global $woocommerce, $DC_Product_Vendor;
		woocommerce_get_template('templates/vendor_tab.php', array(),'', $DC_Product_Vendor->plugin_path );
	}
	
	function dont_publish_product( $data , $postarr ) {
		global $DC_Product_Vendor;
		$vendor = is_user_dc_vendor(get_current_user_id());
		if($vendor) {
			$vendor_can = $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_published_product');
			if($vendor_can) {
				if($data['post_type'] == 'product') {
					$data['post_status'] = 'draft';   
				}
			}
		}
		return $data;   
	}
	
	/**
	* add tax query on product page
	* @return void
	*/
	function convert_business_id_to_taxonomy_term_in_query($query) {
    global $pagenow;
    if($_GET['post_type'] == 'product' && $pagenow == 'edit.php') {
    	$current_user_id = get_current_user_id();
    	$current_user = get_user_by('id', $current_user_id );
			if(!in_array( 'dc_vendor', $current_user->roles )) return $query;
			$term_id = get_user_meta($current_user_id, '_vendor_term_id', true);
			$taxquery = array(
					array(
							'taxonomy' => 'dc_vendor_shop',
							'field' => 'id',
							'terms' => array( $term_id ),
							'operator'=> 'IN'
					)
			);
	
			$query->set( 'tax_query', $taxquery );
		}
		return $query;
  }
  
  function add_report_abuse_link() { 
  	global $product;
  	?>
  	<a href="#" id="report_abuse">Report Abuse</a>
  	<div id="report_abuse_form" class="simplePopup"> 
			<h3 class="dc-wpv-abuse-report-title">Report an abuse for product <?php the_title(); ?> </h3>
			<form action="#" method="post" id="report-abuse" class="report-abuse-form">
				<table>
					<tbody>
						<tr>
							<td>
								<input type="text" class="report_abuse_name" name="report_abuse[name]" value="" style="width: 100%;" placeholder="Name" required="">
							</td>
						</tr>
						<tr>
							<td>
								<input type="email" class="report_abuse_email" name="report_abuse[email]" value="" style="width: 100%;" placeholder="Email" required="">
							</td>
						</tr>
						<tr>
							<td>
								<textarea name="report_abuse[message]" class="report_abuse_msg" rows="5" style="width: 100%;" placeholder="Leave a message explaining the reasons for your abuse report" required=""></textarea>
							</td>
						</tr>
						<tr>
							<td>
								<input type="hidden" class="report_abuse_product_id" value="<?php echo $product->id; ?>">
								<input type="submit" class="submit-report-abuse submit" name="report_abuse[submit]" value="Report">
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div> 							
		<?php
  }
  
}