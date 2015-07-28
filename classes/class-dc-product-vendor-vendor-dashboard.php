<?php 
/**
 *  DC Vendor Admin Dashboard - Vendor WP-Admin Dashboard Pages
 * 
 * @author dualcube
 * @package DCVendors
 */

Class DC_Vendor_Admin_Dashboard { 
	
	function __construct() { 
		
		// Add Shop Settings page 
		add_action( 'admin_menu', array( $this, 'vendor_dashboard_pages') ); 
		
		//init export functions
    $this->export_csv();
    
    //init submit comment
    $this->submit_comment();
	}
	
	/**
	 * Export CSV from vendor dasboard page
	 *
	 * @access public
	 * @return void
	*/	
	function export_csv() {
		global $DC_Product_Vendor;
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_order_csv_export') && !empty( $_POST[ 'export_orders' ] ) ) {
				$order_data = array();
				$date = date( 'd-m-Y H:i:s' );
				$filename = 'SalesReport ' . $date . '.csv';
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment;filename={$filename}");
				header("Content-Transfer-Encoding: binary");
				
				$headers = array(
					'order'   => __( 'Order', $DC_Product_Vendor->text_domain),
					'product' => __( 'Product Title', $DC_Product_Vendor->text_domain ),
					'name'    => __( 'Full name', $DC_Product_Vendor->text_domain ),
					'address' => __( 'Address', $DC_Product_Vendor->text_domain ),
					'city'    => __( 'City', $DC_Product_Vendor->text_domain ),
					'state'   => __( 'State', $DC_Product_Vendor->text_domain ),
					'zip'     => __( 'Zip', $DC_Product_Vendor->text_domain ),
					'email'   => __( 'Email address', $DC_Product_Vendor->text_domain ),
					'date'    => __( 'Date', $DC_Product_Vendor->text_domain ),
					'extra_data'  => __( 'Extra Data', $DC_Product_Vendor->text_domain ),
				);

				if ( ! $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_order_show_email') ) {
					unset( $headers[ 'email' ] );
				}
							
				$user = wp_get_current_user();
				$vendor = get_dc_vendor($user->ID);
				if($vendor) {
					$customer_orders = array_unique($vendor->get_orders());
					if(!empty($customer_orders)) {
						foreach ( $customer_orders as $customer_order ) {
							$order = new WC_Order($customer_order);
							$vendor_items = $vendor->get_vendor_items_order($customer_order, $vendor->term_id);
							if ( sizeof( $vendor_items ) > 0 ) {
								foreach( $vendor_items as $item ) {
									$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
									$order_formated_data = $item_meta->get_formatted();
									$extra_datas = '';
									foreach($order_formated_data as $formated_data) {
										$extra_datas .= $formated_data['label'] . ' -> '. $formated_data['value'] .', ';
									}
									$order_datas[] = array(
										'order' => '#'. $customer_order,
										'product' => $item['name'],
										'name' => $order->shipping_first_name .' '. $order->shipping_last_name,
										'address' => $order->shipping_address_1,
										'city' => $order->shipping_city,
										'state' => $order->shipping_state,
										'zip' => $order->shipping_postcode,
										'email' => $order->billing_email,
										'date' => $order->order_date,
										'extra_data'  => $extra_datas
									); 
								}
							}
						}
					}
				}
				
				// Initiate output buffer and open file
				ob_start();
				$file = fopen( "php://output", 'w' );
		
				// Add headers to file
				fputcsv( $file, $headers );
				// Add data to file
				foreach ( $order_datas as $order_data ) {
					if ( ! $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_order_show_email') ) {
						unset( $order_data[ 'email' ] );
					}
					fputcsv( $file, $order_data );
				}
			
				// Close file and get data from output buffer
				fclose( $file );
				$csv = ob_get_clean();
			
				// Send CSV to browser for download
				echo $csv;
				die();
			}
			
			if( $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_order_csv_export') && !empty( $_POST[ 'export_orders_by_product' ] ) ) {
				$product_id = $_POST['product_id'];
				$order_data = array();
				$date = date( 'd-m-Y H:i:s' );
				$filename = 'SalesReportByProduct ' . $date . '.csv';
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment;filename={$filename}");
				header("Content-Transfer-Encoding: binary");
				
				$headers = array(
					'order'   => __( 'Order', $DC_Product_Vendor->text_domain),
					'product' => __( 'Product Title', $DC_Product_Vendor->text_domain ),
					'name'    => __( 'Full name', $DC_Product_Vendor->text_domain ),
					'address' => __( 'Address', $DC_Product_Vendor->text_domain ),
					'city'    => __( 'City', $DC_Product_Vendor->text_domain ),
					'state'   => __( 'State', $DC_Product_Vendor->text_domain ),
					'zip'     => __( 'Zip', $DC_Product_Vendor->text_domain ),
					'email'   => __( 'Email address', $DC_Product_Vendor->text_domain ),
					'date'    => __( 'Date', $DC_Product_Vendor->text_domain ),
					'extra_data'  => __( 'Extra Data', $DC_Product_Vendor->text_domain ),
				);

				if ( ! $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_order_show_email') ) {
					unset( $headers[ 'email' ] );
				}
							
				$user = wp_get_current_user();
				$vendor = get_dc_vendor($user->ID);
				
				if($vendor) {
					$customer_orders = array();
					$product = get_product($product_id);
					if($product->is_type('variable')) {
						$variations = $product->get_children();
						if(!empty($variations)) {
							foreach($variations as $variation) {
								$customer_order = $vendor->get_vendor_orders_by_product($vendor->term_id, $variation);
								$customer_orders = array_merge($customer_orders, $customer_order);
							}
						}
					$customer_orders = array_unique($customer_orders);
					} else {
						$customer_orders = $vendor->get_vendor_orders_by_product($vendor->term_id, $product_id);
					}
					$all = $vendor->format_order_details( $customer_orders, $product_id );
					if(!empty($customer_orders)) {
						foreach ( $customer_orders as $customer_order ) {
							$order = new WC_Order($customer_order);
							$vendor_items = !empty( $all[ 'items' ][ $customer_order ][ 'items' ] ) ? $all[ 'items' ][ $customer_order ][ 'items' ] : array();
							if ( sizeof( $vendor_items ) > 0 ) {
								foreach( $vendor_items as $item ) {
									$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
									$order_formated_data = $item_meta->get_formatted();
									$extra_datas = '';
									foreach($order_formated_data as $formated_data) {
										$extra_datas .= $formated_data['label'] . ' -> '. $formated_data['value'] .', ';
									}
									$order_datass[] = array(
										'order' => '#'. $customer_order,
										'product' => $item['name'],
										'name' => $order->shipping_first_name .' '. $order->shipping_last_name,
										'address' => $order->shipping_address_1,
										'city' => $order->shipping_city,
										'state' => $order->shipping_state,
										'zip' => $order->shipping_postcode,
										'email' => $order->billing_email,
										'date' => $order->order_date,
										'extra_data' => $extra_datas,
									); 
								}
							}
						}
					}
				}
				
				// Initiate output buffer and open file
				ob_start();
				$file = fopen( "php://output", 'w' );
		
				// Add headers to file
				fputcsv( $file, $headers );
				// Add data to file
				foreach ( $order_datass as $order_data ) {
					if ( ! $DC_Product_Vendor->vendor_caps->vendor_capabilities_settings('is_order_show_email') ) {
						unset( $order_data[ 'email' ] );
					}
					fputcsv( $file, $order_data );
				}
			
				// Close file and get data from output buffer
				fclose( $file );
				$csv = ob_get_clean();
			
				// Send CSV to browser for download
				echo $csv;
				die();
			}
			
		}
	}
	
	
	/**
	 * Submit Comment 
	 *
	 * @access public
	 * @return void
	*/	
	function submit_comment() {
		global $DC_Product_Vendor;
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ( !empty( $_POST[ 'dc_submit_comment' ] ) ) {
				
				$user = wp_get_current_user();
				$user = $user->ID;
				
				// Don't submit empty comments
				if ( empty( $_POST[ 'comment_text' ] ) ) {
					return false;
				}

				// Only submit if the order has the product belonging to this vendor
				$order = new WC_Order ( $_POST['order_id'] );
				$comment = esc_textarea( $_POST[ 'comment_text' ] );
				$order->add_order_note( $comment, 1 );
			}
		}
	}
	
	function vendor_dashboard_pages() {
		global $DC_Product_Vendor;
		$user = wp_get_current_user();
		$vendor = get_dc_vendor($user->ID);
		if($vendor) {
			$hook = add_menu_page( __( 'Orders', $DC_Product_Vendor->text_domain ), __( 'Orders', $DC_Product_Vendor->text_domain ), 'read', 'dc-vendor-orders', array( $this, 'orders_page' ) );
			add_action( "load-$hook", array( $this, 'add_options' ) );
		}
	}
	
	/**
	 *
	 *
	 * @param unknown $status
	 * @param unknown $option
	 * @param unknown $value
	 *
	 * @return unknown
	 */
	public static function set_table_option( $status, $option, $value )
	{
		if ( $option == 'orders_per_page' ) {
			return $value;
		}
	}


	/**
	 * add_options
	 */
	public static function add_options()
	{
		global $DC_Vendor_Order_Page;
		$args = array(
			'label'   => 'Rows',
			'default' => 10,
			'option'  => 'orders_per_page'
		);
		add_screen_option( 'per_page', $args );

		$DC_Vendor_Order_Page = new DC_Vendor_Order_Page();

	}


	/**
	 * HTML setup for the Orders Page 
	 */
	public static function orders_page()
	{
		global $woocommerce, $DC_Vendor_Order_Page, $DC_Product_Vendor;
		$DC_Vendor_Order_Page->prepare_items();

		?>
		<div class="wrap">

			<div id="icon-woocommerce" class="icon32 icon32-woocommerce-reports"><br/></div>
			<h2><?php _e( 'Orders', $DC_Product_Vendor->text_domain ); ?></h2>

			<form id="posts-filter" method="get">

				<input type="hidden" name="page" value="dc-vendor-orders"/>
				<?php $DC_Vendor_Order_Page->display() ?>

			</form>
			<div id="ajax-response"></div>
			<br class="clear"/>
		</div>

<?php }
}

if ( !class_exists( 'WP_List_Table' ) ) require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * WCV Vendor Order Page 
 * 
 * @author dualcube
 * @package DCVendors 
 * @extends WP_List_Table
 */
class DC_Vendor_Order_Page extends WP_List_Table {

	public $index;


	/**
	 * __construct function.
	 *
	 * @access public
	 */
	function __construct()
	{
		global $status, $page;

		$this->index = 0;

		//Set parent defaults
		parent::__construct( array(
								  'singular' => 'order',
								  'plural'   => 'orders',
								  'ajax'     => false
							 ) );
	}


	/**
	 * column_default function.
	 *
	 * @access public
	 *
	 * @param unknown $item
	 * @param mixed   $column_name
	 *
	 * @return unknown
	 */
	function column_default( $item, $column_name )
	{
		global $wpdb;

		switch ( $column_name ) {
			case 'order_id' :
				return $item->order_id;
			case 'customer' : 
				return $item->customer; 
			case 'products' :
				return $item->products; 
			case 'total' : 
				return $item->total; 
			case 'date' : 
				return $item->date; 
			case 'status' : 
				return $item->status;
		}
	}


	/**
	 * column_cb function.
	 *
	 * @access public
	 *
	 * @param mixed $item
	 *
	 * @return unknown
	 */
	function column_cb( $item )
	{
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			'order_id',
			/*$2%s*/
			$item->order_id
		);
	}


	/**
	 * get_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	function get_columns()	{
		global $DC_Product_Vendor;
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'order_id'  => __( 'Order ID', $DC_Product_Vendor->text_domain ),
			'customer'  => __( 'Customer', $DC_Product_Vendor->text_domain ),
			'products'  => __( 'Products', $DC_Product_Vendor->text_domain ),
			'total' 	=> __( 'Total', $DC_Product_Vendor->text_domain ),
			'date'      => __( 'Date', $DC_Product_Vendor->text_domain ),
			'status'    => __( 'Shipped', $DC_Product_Vendor->text_domain ),
		);

		return $columns;
	}


	/**
	 * get_sortable_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	function get_sortable_columns()
	{
		$sortable_columns = array(
			'order_id'  	=> array( 'order_id', false ),
			'total'  		=> array( 'total', false ),
			'status'     	=> array( 'status', false ),
		);

		return $sortable_columns;
	}


	/**
	 * Get bulk actions
	 *
	 * @return unknown
	 */
	function get_bulk_actions()	{
		global $DC_Product_Vendor;
		$actions = array(
			'mark_shipped'     => __( 'Mark shipped', $DC_Product_Vendor->text_domain ),
		);

		return $actions;
	}


	/**
	 * Process bulk actions
	 *
	 * @return unknown
	 */
	function process_bulk_action(){
		global $DC_Product_Vendor;
		if ( !isset( $_GET[ 'order_id' ] ) ) return;

		$items = array_map( 'intval', $_GET[ 'order_id' ] );

		switch ( $this->current_action() ) {
			case 'mark_shipped':

				$result = $this->mark_shipped( $items );

				if ( $result )
					echo '<div class="updated"><p>' . __( 'Orders marked shipped.', $DC_Product_Vendor->text_domain ) . '</p></div>';
				break;

			default:
				// code...
				break;
		}

	}


	/**
	 *  Mark orders as shipped 
	 *
	 * @param unknown $ids (optional)
	 *
	 * @return unknown
	 */
	public function mark_shipped( $ids = array() ) {
		global $woocommerce, $DC_Product_Vendor;

		$user_id = get_current_user_id();
		$vendor = get_dc_vendor($user_id);

		if ( !empty( $ids ) ) {
			foreach ($ids as $order_id ) {
				$shippers = (array) get_post_meta( $order_id, 'dc_pv_shipped', true );
				if(!in_array($user_id, $shippers)) {
					$shippers[] = $user_id;
					$mails = WC()->mailer()->emails['WC_Email_Notify_Shipped'];
					if ( !empty( $mails ) ) {
						$customer_email = get_post_meta($order_id, '_billing_email', true);
						$mails->trigger( $order_id, $customer_email, $vendor->term_id );
					}
					do_action('dc_vendors_vendor_ship', $order_id, $vendor->term_id);
					update_post_meta( $order_id, 'dc_pv_shipped', $shippers );
				}
				$order = new WC_Order( $order_id );
				$order->add_order_note('Vendor '.$vendor->user_data->display_name .' has shipped his part of order to customer.');
			}
			return true; 
		}
		return false; 
	}

	/**
	 *  get orders
	 *
	 * @return array
	 */
	
	function get_orders() { 

		$user_id = get_current_user_id(); 
		
		$vendor = get_dc_vendor($user_id);
		
		$orders = array(); 

		$_orders = $vendor->get_orders();
		
		if (!empty( $_orders ) ) { 
			foreach ( $_orders as $order_id ) {

				$order = new WC_Order( $order_id );

				$valid_items = $vendor->get_vendor_items_order( $order->id, $vendor->term_id ); 

				$products = ''; 

				foreach ($valid_items as $key => $item) { 
					$item_meta = new WC_Order_Item_Meta( $item[ 'item_meta' ] );
					// $item_meta = $item_meta->display( false, true ); 
					$item_meta = $item_meta->get_formatted( ); 
					$products .= '<strong>'. $item['qty'] . ' x ' . $item['name'] . '</strong><br />'; 
					foreach ($item_meta as $key => $meta) {
						// Remove the sold by meta key for display 
						if (strtolower($key) != 'sold by' ) $products .= $meta[ 'label' ] .' : ' . $meta[ 'value' ]. '<br />'; 
					}
				}

				$shippers = (array) get_post_meta( $order->id, 'dc_pv_shipped', true );
				$shipped = in_array($user_id, $shippers) ? 'Yes' : 'No' ; 

				$total = $vendor->get_commision_per_order($order->id, $vendor->term_id);

				$order_items = array(); 
				$order_items[ 'order_id' ] 	= $order->id;
				$order_items[ 'customer' ] 	= $order->get_formatted_shipping_address();
				$order_items[ 'products' ] 	= $products; 
				$order_items[ 'total' ] 	= woocommerce_price( $total );
				$order_items[ 'date' ] 		= date_i18n( wc_date_format(), strtotime( $order->order_date ) ); 
				$order_items[ 'status' ] 	= $shipped;

				$orders[] = (object) $order_items; 
			}
		}
		return $orders; 

	}



	/**
	 * prepare_items function.
	 *
	 * @access public
	 */
	function prepare_items()
	{

		
		/**
		 * Init column headers
		 */
		$this->_column_headers = $this->get_column_info();


		/**
		 * Process bulk actions
		 */
		$this->process_bulk_action();

		/**
		 * Get items
		 */
		 
		$per_page = 10;
		$current_page = $this->get_pagenum();
		$total_items = count( $this->get_orders() );
		// only ncessary because we have sample data
		$found_data = array_slice( $this->get_orders(), ( ( $current_page - 1 ) * $per_page ), $per_page);
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page                     //WE have to determine how many items to show on a page
		) );
		$this->items = $found_data;
		
	}

}
?>