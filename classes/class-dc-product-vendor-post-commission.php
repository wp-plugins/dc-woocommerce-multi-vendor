<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		DC_Product_Vendor_Commission
 * @version		1.0.0
 * @package		DC_Product_Vendor
 * @author 		DualCube
 */
class DC_Product_Vendor_Commission {
  
  private $post_type;
  
  public function __construct() {
    $this->post_type = 'dc_commission';
    $this->register_post_type();
		if ( is_admin() ) {
			// Handle custom fields for post
			add_action( 'admin_menu', array( $this, 'meta_box_setup' ), 20 );
			add_action( 'save_post', array( $this, 'meta_box_save' ) );
			
			// Handle commission paid status
			add_action( 'post_submitbox_misc_actions', array( $this, 'custom_actions_content' ) );
			add_action( 'save_post', array( $this, 'custom_actions_save' ) );
			
			// Handle post columns
			add_filter( 'manage_edit-' . $this->post_type . '_columns', array( $this, 'dc_register_custom_column_headings' ), 10, 1 );
			add_action( 'manage_pages_custom_column', array( $this, 'dc_register_custom_columns' ), 10, 2 );
			
			// Add bulk actions to commissions table
			add_action( 'admin_footer-edit.php', array( $this, 'dc_add_bulk_action_options' ) );
			add_action( 'load-edit.php', array( $this, 'dc_generate_commissions_csv' ) );
			add_action( 'load-edit.php', array( $this, 'dc_mark_all_commissions_paid' ) );
			
			add_action( 'restrict_manage_posts', array($this, 'dc_woocommerce_restrict_manage_orders') );
			add_filter( 'request', array(&$this, 'dc_woocommerce_orders_by_customer_query') );
			
			add_filter('pre_get_posts', array(&$this, 'commission_post_types_admin_order'));
		}
  }
   
  /**
	 * register commission post type
	 * @access public
	 * @return void
	*/
  function register_post_type() {
		global $DC_Product_Vendor;
		if ( post_type_exists($this->post_type) ) return;
		$labels = array(
			'name' => _x( 'Commissions', 'post type general name' , $DC_Product_Vendor->text_domain ),
			'singular_name' => _x( 'Commission', 'post type singular name' , $DC_Product_Vendor->text_domain ),
			'add_new' => _x( 'Add New', $this->post_type , $DC_Product_Vendor->text_domain ),
			'add_new_item' => sprintf( __( 'Add New %s' , $DC_Product_Vendor->text_domain ), __( 'Commission' , $DC_Product_Vendor->text_domain ) ),
			'edit_item' => sprintf( __( 'Edit %s' , $DC_Product_Vendor->text_domain ), __( 'Commission' , $DC_Product_Vendor->text_domain) ),
			'new_item' => sprintf( __( 'New %s' , $DC_Product_Vendor->text_domain ), __( 'Commission' , $DC_Product_Vendor->text_domain) ),
			'all_items' => sprintf( __( 'All %s' , $DC_Product_Vendor->text_domain ), __( 'Commissions' , $DC_Product_Vendor->text_domain ) ),
			'view_item' => sprintf( __( 'View %s' , $DC_Product_Vendor->text_domain ), __( 'Commission' , $DC_Product_Vendor->text_domain ) ),
			'search_items' => sprintf( __( 'Search %a' , $DC_Product_Vendor->text_domain ), __( 'Commissions' , $DC_Product_Vendor->text_domain ) ),
			'not_found' =>  sprintf( __( 'No %s Found' , $DC_Product_Vendor->text_domain ), __( 'Commissions' , $DC_Product_Vendor->text_domain ) ),
			'not_found_in_trash' => sprintf( __( 'No %s Found In Trash' , $DC_Product_Vendor->text_domain ), __( 'Commissions' , $DC_Product_Vendor->text_domain ) ),
			'parent_item_colon' => '',
			'all_items' => __( 'Commissions' , $DC_Product_Vendor->text_domain ),
			'menu_name' => __( 'Commissions' , $DC_Product_Vendor->text_domain )
		);
		
		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_ui' => true,
			'show_in_menu' => current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true,
			'show_in_nav_menus' => false,
			'query_var' => false,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => true,
			'supports' => array( 'title' ),
			'menu_position' => 57,
			'menu_icon' => $DC_Product_Vendor->plugin_url.'/assets/images/dualcube.png'
		);
		
		register_post_type( $this->post_type, $args );
	}
	
	/**
	 * Add meta box to commission posts
	 * @return void
	*/
	public function meta_box_setup() {
		global $DC_Product_Vendor;
		add_meta_box( 'dc-commission-data', __( 'Commission Details' , $DC_Product_Vendor->text_domain ), array( &$this, 'dc_meta_box_content' ), $this->post_type, 'normal', 'high' );
	}
	
	/**
	 * Add content to meta box to commission posts
	 * @return void
	*/
	public function dc_meta_box_content() {
		global $post_id, $woocommerce, $DC_Product_Vendor;
		$fields = get_post_custom( $post_id );
		$field_data = $this->get_custom_fields_settings();

		$html = '';

		$html .= '<input type="hidden" name="' . $this->post_type . '_nonce" id="' . $this->post_type . '_nonce" value="' . wp_create_nonce( plugin_basename( $this->dir ) ) . '" />';

		if ( 0 < count( $field_data ) ) {
			$html .= '<table class="form-table">' . "\n";
			$html .= '<tbody>' . "\n";

			foreach ( $field_data as $k => $v ) {
				$data = $v['default'];

				if ( isset( $fields[$k] ) && isset( $fields[$k][0] ) ) {
					$data = $fields[$k][0];
				}
				if( $k == '_commission_product' ) {
					
					$option = '<option value=""></option>';
					if( $data && strlen( $data ) > 0 ) {
						if( function_exists( 'get_product' ) ) {
							$product = get_product( $data );
						} else {
							$product = new WC_Product( $data );
						}
						if($product->get_formatted_name()) {
							$option = '<option value="' . $data . '" selected="selected">' . $product->get_formatted_name() . '</option>';
						} else {
							$option = '<option value="' . $data . '" selected="selected">' . $product->get_title() . '</option>';
						}
					}
					$html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label></th><td><select name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="ajax_chosen_select_products_and_variations" data-placeholder="Search for product&hellip;" style="min-width:300px;">' . $option . '</select>' . "\n";
					$html .= '<p class="description">' . $v['description'] . '</p>' . "\n";
					$html .= '</td><tr/>' . "\n";
				
				} elseif( $k == '_commission_vendor' ) {
					$vendor = get_dc_vendor_by_term( $data );
					$option = '<option value=""></option>';
					if( $data && strlen( $data ) > 0 ) {
						$option = '<option value="' . $vendor->term_id . '" selected="selected">' . $vendor->user_data->user_login . '</option>';
					}

					$html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label></th><td><select name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="ajax_chosen_select_vendor" data-placeholder="Search for vendor&hellip;" style="min-width:300px;">' . $option . '</select>' . "\n";
					$html .= '<p class="description">' . $v['description'] . '</p>' . "\n";
					$html .= '</td><tr/>' . "\n";

				} else {

					if( $v['type'] == 'checkbox' ) {
						$html .= '<tr valign="top"><th scope="row">' . $v['name'] . '</th><td><input name="' . esc_attr( $k ) . '" type="checkbox" id="' . esc_attr( $k ) . '" ' . checked( 'on' , $data , false ) . ' /> <label for="' . esc_attr( $k ) . '"><span class="description">' . $v['description'] . '</span></label>' . "\n";
						$html .= '</td><tr/>' . "\n";
					} else {
						$html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label></th><td><input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" />' . "\n";
						$html .= '<p class="description">' . $v['description'] . '</p>' . "\n";
						$html .= '</td><tr/>' . "\n";
					}

				}

			}

			$html .= '</tbody>' . "\n";
			$html .= '</table>' . "\n";
		}

		echo $html;
	}
	
	/**
	 * Add custom field to commission posts
	 * @return arr Array of custom fields
	 */
	public function get_custom_fields_settings() {
		global $DC_Product_Vendor;
		$fields = array();
		
		$fields['_commission_order_id'] = array(
	    'name' => __( 'Order ID:' , $DC_Product_Vendor->text_domain ),
	    'description' => __( 'The order id of commission (' . get_woocommerce_currency_symbol() . ').' , $DC_Product_Vendor->text_domain ),
	    'type' => 'text',
	    'default' => '',
	    'section' => 'dc-commission-data'
		);
		
		$fields['_commission_product'] = array(
	    'name' => __( 'Product:' , $DC_Product_Vendor->text_domain ),
	    'description' => __( 'The product purchased that generated this commission.' , $DC_Product_Vendor->text_domain ),
	    'type' => 'select',
	    'default' => '',
	    'section' => 'dc-commission-data'
		);

		$fields['_commission_vendor'] = array(
	    'name' => __( 'Vendor:' , $DC_Product_Vendor->text_domain ),
	    'description' => __( 'The vendor who receives this commission.' , $DC_Product_Vendor->text_domain ),
	    'type' => 'select',
	    'default' => '',
	    'section' => 'dc-commission-data'
		);
		
		$fields['_commission_amount'] = array(
	    'name' => __( 'Amount:' , $DC_Product_Vendor->text_domain ),
	    'description' => __( 'The total value of this commission (' . get_woocommerce_currency_symbol() . ').' , $DC_Product_Vendor->text_domain ),
	    'type' => 'text',
	    'default' => 0.00,
	    'section' => 'dc-commission-data'
		);

		return $fields;
	}
	
	/**
	 * Save meta box on commission posts
	 * @param  int $post_id Commission ID
	 * @return void
	 */
	public function meta_box_save($post_id) {
		global $post, $messages;
		
		// Verify nonce
		if ( ( get_post_type() != $this->post_type ) || ! wp_verify_nonce( $_POST[ $this->post_type . '_nonce'], plugin_basename( $this->dir ) ) ) {
			return $post_id;
		}

		// Verify user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Handle custom fields
		$field_data = $this->get_custom_fields_settings();
		$fields = array_keys( $field_data );
		foreach ( $fields as $f ) {

			if( isset( $_POST[$f] ) ) {
				${$f} = strip_tags( trim( $_POST[$f] ) );
			}

			// Escape the URLs.
			if ( 'url' == $field_data[$f]['type'] ) {
				${$f} = esc_url( ${$f} );
			}

			if ( ${$f} == '' ) {
				delete_post_meta( $post_id , $f , get_post_meta( $post_id , $f , true ) );
			} else {
				update_post_meta( $post_id , $f , ${$f} );
			}
		}
	}
	
	/**
	 * Add custom actions to commission posts
	 * @return void
	*/
	public function custom_actions_content() {
    global $post;
    if( get_post_type( $post ) == $this->post_type ) {
      echo '<div class="misc-pub-section misc-pub-section-last">';
      wp_nonce_field( plugin_basename( $this->file ), 'paid_status_nonce' );

      $status = get_post_meta( $post->ID, '_paid_status', true ) ? get_post_meta( $post->ID, '_paid_status', true ) : 'unpaid';

      echo '<input type="radio" name="_paid_status" id="_paid_status-unpaid" value="unpaid" ' . checked( $status, 'unpaid', false ) . ' /> <label for="_paid_status-unpaid" class="select-it">Unpaid</label>&nbsp;&nbsp;&nbsp;&nbsp;';
      echo '<input type="radio" name="_paid_status" id="_paid_status-paid" value="paid" ' . checked( $status, 'paid', false ) . '/> <label for="_paid_status-paid" class="select-it">Paid</label>';
      echo '</div>';
    }
	}

	/**
	* Save custom actions for commission posts
	* @param  int $post_id Commission ID
	* @return void
	*/
	public function custom_actions_save( $post_id ) {
		if( isset( $_POST['paid_status_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['paid_status_nonce'], plugin_basename( $this->file ) ) )
      	return $post_id;

      if( isset( $_POST['_paid_status'] ) ) {
      	$email_admin = WC()->mailer()->emails['WC_Email_Vendor_Commissions_Paid'];
      	$vendor_id = get_post_meta($post_id, '_commission_vendor', true);
      	$paid_status = get_post_meta( $post_id, '_paid_status', true);
      	if($paid_status && $paid_status == 'unpaid' && $_POST['_paid_status'] == 'paid') {
      		$email_admin->trigger( $post_id, $vendor_id );
      	}
      	if(!$paid_status &&  $_POST['_paid_status'] == 'paid') {
      		$email_admin->trigger( $post_id, $vendor_id );
      	}
      	update_post_meta( $post_id, '_paid_status', $_POST['_paid_status'] );
      }
    }
	}
	
	/**
	* Add columns to commissions list table
	* @param  arr $defaults Default columns
	* @return arr           New columns
	*/
	public function dc_register_custom_column_headings( $defaults ) {
		global $DC_Product_Vendor;
		$new_columns = array(
			'_commission_order_id' => __( 'Order ID' , $DC_Product_Vendor->text_domain ),
			'_commission_product' => __( 'Product' , $DC_Product_Vendor->text_domain ),
			'_commission_vendor' => __( 'Vendor' , $DC_Product_Vendor->text_domain ),
			'_commission_amount' => __( 'Amount' , $DC_Product_Vendor->text_domain ),
			'_paid_status' => __( 'Status' , $DC_Product_Vendor->text_domain ),
		);
		
		$last_item = '';	
		
		if ( count( $defaults ) > 2 ) {
			$last_item = array_slice( $defaults, -1 );
		
			array_pop( $defaults );
		}
		$defaults = array_merge( $defaults, $new_columns );
		
		if ( $last_item != '' ) {
			foreach ( $last_item as $k => $v ) {
				$defaults[$k] = $v;
				break;
			}
		}
		return $defaults;
	}

	/**
	 * Register new columns for commissions list table
	 * @param  str $column_name Name of column
	 * @param  int $id          ID of commission
	 * @return void
	 */
	public function dc_register_custom_columns( $column_name, $id ) {

		$data = get_post_meta( $id , $column_name , true );

		switch ( $column_name ) {

			case '_commission_product':
				if( $data && strlen( $data ) > 0 ) {
					if( function_exists( 'get_product' ) ) {
						$product = get_product( $data );
					} else {
						$product = new WC_Product( $data );
					}
					if($product->get_formatted_name()) {
						$edit_url = 'post.php?post=' . $product->id . '&action=edit';
						echo '<a href="' . esc_url( $edit_url ) . '">' . $product->get_formatted_name() . '</a>';
					} else {
						$edit_url = 'post.php?post=' . $data . '&action=edit';
						echo '<a href="' . esc_url( $edit_url ) . '">' . $product->get_title() . '</a>';
					}
				}
			break;
			
			case '_commission_order_id':
				if( $data && strlen( $data ) > 0 ) {
					$edit_url = 'post.php?post=' . $data . '&action=edit';
					echo '<a href="' . esc_url( $edit_url ) . '">#'.$data . '</a>';
				}
			break;

			case '_commission_vendor':
				if( $data && strlen( $data ) > 0 ) {
					$vendor_user_id = get_woocommerce_term_meta($data, '_vendor_user_id', true);
					if( $vendor_user_id ) {
						$vendor = get_dc_vendor($vendor_user_id);
						$edit_url = get_edit_user_link( $vendor_user_id );
						echo '<a href="' . esc_url( $edit_url ) . '">' . $vendor->user_data->user_login . '</a>';
					}
				}
			break;

			case '_commission_amount':
				echo get_woocommerce_currency_symbol() . number_format( $data, 2 );
			break;

			case '_paid_status':
				echo ucfirst( $data );
			break;

			default:
			break;
		}
	}
	
	/**
	 * Add bulk action options to commission list table
	 * @return void
	*/
	public function dc_add_bulk_action_options() {
		global $post_type, $DC_Product_Vendor;
	
	if( $post_type == $this->post_type ) { ?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('<option>').val('export').text('<?php _e('Export unpaid commissions (CSV)', $DC_Product_Vendor->text_domain ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('export').text('<?php _e('Export unpaid commissions (CSV)', $DC_Product_Vendor->text_domain ); ?>').appendTo("select[name='action2']");
				jQuery('<option>').val('mark_paid').text('<?php _e('Mark all commissions as paid', $DC_Product_Vendor->text_domain ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('mark_paid').text('<?php _e('Mark all commissions as paid', $DC_Product_Vendor->text_domain ); ?>').appendTo("select[name='action2']");
				jQuery('<option>').val('mark_unpaid').text('<?php _e('Mark selected commissions as unpaid', $DC_Product_Vendor->text_domain ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('mark_unpaid').text('<?php _e('Mark selected commissions as unpaid', $DC_Product_Vendor->text_domain ); ?>').appendTo("select[name='action2']");
				jQuery('<option>').val('mark_selected_paid').text('<?php _e('Mark selected commissions as paid', $DC_Product_Vendor->text_domain ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('mark_selected_paid').text('<?php _e('Mark selected commissions as paid', $DC_Product_Vendor->text_domain ); ?>').appendTo("select[name='action2']");
			});
		</script>
	<?php }
	}
	
	/**
	 * Create export CSV for unpaid commissions
	 * @return void
	*/
	public function dc_generate_commissions_csv() {
		global $DC_Product_Vendor;
		$screen = get_current_screen();
		if (in_array( $screen->id, array( 'edit-dc_commission' ))) {
			// Confirm list table action
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action = $wp_list_table->current_action();
			if( $action != 'export' ) return;
			// Security check
			check_admin_referer( 'bulk-posts' );
			// Set filename
			$date = date( 'd-m-Y H:i:s' );
			$filename = 'Commissions ' . $date . '.csv';
	
			// Set page headers to force download of CSV
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename={$filename}");
			header("Content-Transfer-Encoding: binary");
	
			// Set CSV headers
			$headers = array(
				'Recipient',
				'Payment',
				'Currency',
				'Customer ID',
				'Note'
			);
	
			// Get data for CSV
			$args = array(
				'post_type' => $this->post_type,
				'post_status' => array( 'publish', 'private' ),
				'meta_key' => '_paid_status',
				'meta_value' => 'unpaid',
				'posts_per_page' => -1
			);
			$commissions = get_posts( $args );
			//print_R($commissions);
			// Get total commissions for each vendor
			$commission_totals = array();
			foreach( $commissions as $commission ) {
				// Get commission data
				$commission_data = $this->get_commission( $commission->ID );
				$commission_totals[][ $commission_data->vendor->term_id ] = $commission_data->amount;
			}
		
			// Set info for all payouts
			$currency = get_woocommerce_currency();
			$payout_note = sprintf( __( 'Total commissions earned from %1$s as at %2$s on %3$s', $DC_Product_Vendor->text_domain ), get_bloginfo( 'name' ), date( 'H:i:s' ), date( 'd-m-Y' ) );
			// Set up data for CSV
			$commissions_data = array();
			foreach( $commission_totals as $key => $totals ) {
				foreach($totals as $vendor_id => $total) {
					// Get vendor data
					$vendor = get_dc_vendor_by_term( $vendor_id );
					$vendor_user_id = get_woocommerce_term_meta($vendor_id, '_vendor_user_id', true);
					$vendor_paypal_email = get_user_meta($vendor_user_id, '_vendor_paypal_email', true);
					// Set vendor recipient field
					if( isset( $vendor_paypal_email ) && strlen( $vendor_paypal_email ) > 0 ) {
						$recipient = $vendor_paypal_email;
					} else {
						$recipient = $vendor->user_data->display_name;
					}
					
					$commissions_data[] = array(
						$recipient,
						$total,
						$currency,
						$vendor_id,
						$payout_note
					);
				}
			}
			// Initiate output buffer and open file
				ob_start();
				$file = fopen( "php://output", 'w' );
		
				// Add headers to file
				fputcsv( $file, $headers );
				// Add data to file
			foreach ( $commissions_data as $commission ) {
				fputcsv( $file, $commission );
			}
		
			// Close file and get data from output buffer
			fclose( $file );
			$csv = ob_get_clean();
		
			// Send CSV to browser for download
			echo $csv;
			die();
		}
	}
	
	/**
	 * Mark all unpaid commissions as paid
	 * @return void
	*/
	public function dc_mark_all_commissions_paid() {
		global $DC_Product_Vendor;
		$screen = get_current_screen();
		if (in_array( $screen->id, array( 'edit-dc_commission' ))) {
			// Confirm list table action
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action = $wp_list_table->current_action();
			// Get all unpaid commissions
			$args = array(
				'post_type' => $this->post_type,
				'post_status' => array( 'publish', 'private' ),
				'meta_key' => '_paid_status',
				'meta_value' => 'unpaid',
				'posts_per_page' => -1
			);
			$commissions = get_posts( $args );
			$email_admin = WC()->mailer()->emails['WC_Email_Vendor_Commissions_Paid'];
			switch ( $action ) {
				case 'mark_paid':
					$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );
					foreach( $commissions as $commission ) {
						$vendor_id = get_post_meta($commission->ID, '_commission_vendor', true);
						$email_admin->trigger( $commission->ID, $vendor_id );
						update_post_meta( $commission->ID, '_paid_status', 'paid', 'unpaid' );
					}
					$redirect = add_query_arg( 'message', 'paid', $_REQUEST['_wp_http_referer'] );	
					break;
				case 'mark_unpaid':
					$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );
					foreach( $post_ids as $commission ) {
						update_post_meta( $commission, '_paid_status', 'unpaid', 'paid' );
					}
					$redirect = add_query_arg( 'message', 'unpaid', $_REQUEST['_wp_http_referer'] );			
					break;
				case 'mark_selected_paid':
					$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );
					foreach( $post_ids as $commission ) {
						$vendor_id = get_post_meta($commission, '_commission_vendor', true);
						$email_admin->trigger( $commission, $vendor_id );
						update_post_meta( $commission, '_paid_status', 'paid', 'unpaid' );
					}
					$redirect = add_query_arg( 'message', 'paid', $_REQUEST['_wp_http_referer'] );			
					break;
				default:
					return;
			}
			wp_safe_redirect( $redirect );
			exit;
		}
	}
	
	/**
	* Get commission details
	* @param  int $commission_id Commission ID
	* @return obj                Commission object
	*/
	function get_commission( $commission_id = 0 ) {
		$commission = false;
		
		if( $commission_id > 0 ) {
			// Get post data
			$commission = get_post( $commission_id );
		
			// Get meta data
			$commission->product = get_post_meta( $commission_id, '_commission_product', true );
			$commission->vendor = get_dc_vendor_by_term( get_post_meta( $commission_id, '_commission_vendor', true ) );
			$commission->amount = get_post_meta( $commission_id, '_commission_amount', true );
			$commission->paid_status = get_post_meta( $commission_id, '_paid_status', true );
		}
	
		return $commission;
	}
	
	/**
	* Show custom filters to filter orders by status/customer.
	*
	* @access public
	* @return void
	*/
	function dc_woocommerce_restrict_manage_orders() {
		global $woocommerce, $typenow, $wp_query, $DC_Product_Vendor;
		
		if ( $typenow != $this->post_type )
		return;
		
		// Commission Satus
		?>
		<select name='commission_status' id='dropdown_commission_status'>
			<option value=""><?php _e( 'Show Commission Status', $DC_Product_Vendor->text_domain ); ?></option>
			<option value="paid"><?php _e( 'Paid', $DC_Product_Vendor->text_domain ); ?></option>
			<option value="unpaid"><?php _e( 'UnPaid', $DC_Product_Vendor->text_domain ); ?></option>
		</select>
		<?php
	}
	
	/**
	* Filter the orders by the posted customer.
	*
	* @access public
	* @param mixed $vars
	* @return array
	*/
	function dc_woocommerce_orders_by_customer_query( $vars ) {
		global $typenow, $wp_query;
		if ( $typenow == $this->post_type && isset( $_GET['commission_status'] ) ) {
			$vars['meta_key'] = '_paid_status';
			$vars['meta_value'] = $_GET['commission_status'];
		}
		return $vars;
	}
	
	/**
	* get vendor commission by date
	*
	* @access public
	* @param mixed $vars
	* @return array
	*/
	public function get_vendor_commissions( $vendor_id = 0, $year = false, $month = false, $day = false ) {
		$commissions = false;
		if( $vendor_id > 0 ) {
			$args = array(
				'post_type' =>  $this->post_type,
				'post_status' => array( 'publish', 'private' ),
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => '_commission_vendor',
						'value' => absint($vendor_id),
						'compare' => '='
					)
				)
			);
	
			// Add date parameters if specified
			if( $year ) $args['year'] = $year;
			if( $month ) $args['monthnum'] = $month;
			if( $day ) $args['day'] = $day;
			$commissions = get_posts( $args );
		}
		return $commissions;
	}
	
	function commission_post_types_admin_order( $wp_query ) {
		if (is_admin()) {
			// Get the post type from the query
			$post_type = $wp_query->query['post_type'];
			if ( $post_type == $this->post_type ) {
				$wp_query->set('orderby', 'ID');
				$wp_query->set('order', 'DESC');
			}
		}
	}
}
