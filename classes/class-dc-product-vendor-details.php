<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		DC_Vendor
 * @version		1.0.0
 * @package		DC_Product_Vendor
 * @author 		DualCube
 */
class DC_Vendor {
	
	public $id;
	
	public $taxonomy;
	
	public $term;
	
	public $user_data;
	
	/**
	 * Get the vendor if UserID is passed, otherwise the vendor is new and empty.
	 *
	 * @access public
	 * @param string $id (default: '')
	 * @return void
	 */
	public function __construct($id = '') {
		
		$this->taxonomy = 'dc_vendor_shop';
		
		$this->term = false;
		
		if ( $id > 0 ) {
			$this->get_vendor( $id );
		}
	}
	
	/**
	 * Gets an Vendor User from the database.
	 *
	 * @access public
	 * @param int $id (default: 0)
	 * @return bool
	 */
	public function get_vendor( $id = 0 ) {
		if ( ! $id ) {
			return false;
		}
		
		if ( ! is_user_dc_vendor($id) ) {
			return false;
		}
		
		if ( $result = get_userdata( $id ) ) {
			$this->populate( $result );
			return true;
		}
		return false;
	}
	
	/**
	 * Populates an Vendor from the loaded user data.
	 *
	 * @access public
	 * @param mixed $result
	 * @return void
	 */
	public function populate( $result ) {
		
		$this->id = $result->ID;
		$this->user_data = $result;
	}
	
	/**
	 * __isset function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return bool
	 */
	public function __isset( $key ) {
		global $DC_Product_Vendor;
		
		if ( ! $this->id ) {
			return false;
		}
		
		if ( in_array( $key, array( 'term_id', 'page_title', 'page_slug', 'link' ) ) ) {
			if ( $term_id = get_user_meta( $this->id, '_vendor_term_id', true ) ) {
				return term_exists( absint( $term_id ), $DC_Product_Vendor->taxonomy->taxonomy_name );
			} else {
				return false;
			}
		}
		
		return metadata_exists( 'user', $this->id, '_' . $key );
	}
	
	/**
	 * __get function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( ! $this->id ) {
			return false;
		}
		
		if ( $key == 'page_title' ) {
			
			$value = $this->get_page_title();
			
		} elseif( $key == 'page_slug' ) {
			
			$value = $this->get_page_slug();
			
		} elseif( $key == 'permalink' ) {
			
			$value = $this->get_permalink();
			
		} else {
			// Get values or default if not set
			$value = get_user_meta( $this->id, '_vendor_' . $key, true );
		}
		
 		return $value;
	}
	
	/**
	 * generate_term function
	 * @access public
	 * @return void
	 */
	public function generate_term() {
		global $DC_Product_Vendor;
		
		if ( ! isset( $this->term_id ) ) {
			$term = wp_insert_term( $this->user_data->user_login, $DC_Product_Vendor->taxonomy->taxonomy_name );
			if ( ! is_wp_error( $term ) ) {
	      update_user_meta( $this->id, '_vendor_term_id', $term['term_id'] );
	      update_woocommerce_term_meta( $term['term_id'], '_vendor_user_id', $this->id ) ;
      }
		}
	}
	
	/**
	 * update_page_title function
	 * @access public
	 * @param $title
	 * @return boolean
	 */
	public function update_page_title( $title = '' ) {
		global $DC_Product_Vendor;
		
		if ( ! empty( $title ) && isset( $this->term_id ) ) {
			if( ! is_wp_error( wp_update_term( $this->term_id, $DC_Product_Vendor->taxonomy->taxonomy_name, array( 'name' => $title ) ) ) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * update_page_slug function
	 * @access public
	 * @param $slug
	 * @return boolean
	 */
	public function update_page_slug( $slug = '' ) {
		global $DC_Product_Vendor;
		
		if ( ! empty( $slug ) && isset( $this->term_id ) ) {
			if ( ! is_wp_error( wp_update_term( $this->term_id, $DC_Product_Vendor->taxonomy->taxonomy_name, array( 'slug' => $slug ) ) ) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * set_term_data function
	 * @access public
	 * @return void
	 */
	public function set_term_data() {
		global $DC_Product_Vendor;
		//return if term is already set
		if( $this->term ) return;
		 
		if ( isset( $this->term_id ) ) {
			$term = get_term( $this->term_id, $DC_Product_Vendor->taxonomy->taxonomy_name );
			if ( ! is_wp_error( $term ) ) {
				$this->term = $term;
			}
		}
	}
	
	/**
	 * get_page_title function
	 * @access public
	 * @return string
	*/
	public function get_page_title() {
		$this->set_term_data();
		if ( $this->term ) {
			return $this->term->name;
		} else {
			return '';
		}
	}
	
	/**
	 * get_page_slug function
	 * @access public
	 * @return string
	 */
	public function get_page_slug() {
		$this->set_term_data();
		if ( $this->term ) {
			return $this->term->slug;
		} else {
			return '';
		}
	}
	
	/**
	 * get_permalink function
	 * @access public
	 * @return string
	*/
	public function get_permalink() {
		global $DC_Product_Vendor;
		
		$link = '';
		if( isset( $this->term_id ) ) {
			$link = get_term_link( absint( $this->term_id ), $DC_Product_Vendor->taxonomy->taxonomy_name );
		}
		
		return $link;
	}
	
	/**
	 * Get all products belonging to vendor
	 * @param  $args (default=array())
	 * @return arr Array of product post objects
	*/
	public function get_products( $args = array() ) {
		global $DC_Product_Vendor;
		$products = false;
		
		$default = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => $DC_Product_Vendor->taxonomy->taxonomy_name,
					'field' => 'id',
					'terms' => absint( $this->term_id )
				)
			)
		);
		
		$args = wp_parse_args( $args, $default );
		
		
		$products = get_posts( $args );
		
		
		return $products;
	}
	
	/**
	 * get_orders function
	 * @access public
	 * @return array with order id
	*/
	public function get_orders($no_of = false) {
		if(!$no_of) $no_of = -1;
		$vendor_id = $this->term_id;
		$commissions = false;
		$order_id = null;
		if( $vendor_id > 0 ) {
			$args = array(
				'post_type' => 'dc_commission',
				'post_status' => array( 'publish', 'private' ),
				'posts_per_page' => (int)$no_of,
				'meta_query' => array(
					array(
						'key' => '_commission_vendor',
						'value' => absint($vendor_id),
						'compare' => '='
					)
				)
			);
			$commissions = get_posts( $args );
		}
		if( $commissions ) {
			$order_id = array();
			foreach( $commissions as $commission  ) {
				$order_id[] = get_post_meta( $commission->ID, '_commission_order_id', true );
			}
		}
		return $order_id;
	}
	
	/**
	 * get_vendor_items_order function get items of a order belongs to a vendor
	 * @access public
	 * @param order_id , vendor term id 
	 * @return array with order item detail
	*/	
	public function get_vendor_items_order( $order_id, $term_id ) {
		$item_dtl = array();
		$order =  new WC_Order( $order_id );
		if( $order ) {
			$items = $order->get_items( 'line_item' );
			if( $items ) {
				foreach( $items as $item_id => $item ) {
					$product_id = $order->get_item_meta( $item_id, '_product_id', true );
					$variation_id = $order->get_item_meta( $item_id, '_variation_id', true );
					if(!empty($variation_id)) {
						$product_id = $variation_id;
					}
					if( $product_id ) {
						$commissions = false;
						if( $term_id > 0 ) {
							$args = array(
								'post_type' =>  'dc_commission',
								'post_status' => array( 'publish', 'private' ),
								'posts_per_page' => -1,
								'meta_query' => array(
									array(
										'key' => '_commission_vendor',
										'value' => absint($term_id),
										'compare' => '='
									),
									array(
										'key' => '_commission_product',
										'value' => absint($product_id),
										'compare' => '='
									),
									array(
										'key' => '_commission_order_id',
										'value' => absint($order_id),
										'compare' => '='
									),
								),
							);
							$commissions = get_posts( $args );
							$product_vendors = wp_get_post_terms( $product_id, 'dc_vendor_shop', array("fields" => "ids"));
							if(!empty($commissions)) { 
								$item_dtl[$item_id] = $item;
							}
						}
					}
				}
			}
		}
		return $item_dtl;
	}
	
	/**
	 * get_vendor_orders_by_product function to get orders belongs to a vendor and a product
	 * @access public
	 * @param product id , vendor term id 
	 * @return array with order id
	*/	
	
	public function get_vendor_orders_by_product( $vendor_term_id, $product_id ) {
		$order_dtl = array();
		if( $product_id && $vendor_term_id ) {
			$commissions = false;
			$args = array(
				'post_type' =>  'dc_commission',
				'post_status' => array( 'publish', 'private' ),
				'posts_per_page' => -1,
				'order' => 'asc',
				'meta_query' => array(
					array(
						'key' => '_commission_vendor',
						'value' => absint($vendor_term_id),
						'compare' => '='
					),
					array(
						'key' => '_commission_product',
						'value' => absint($product_id),
						'compare' => '='
					),
				),
			);
			$commissions = get_posts( $args );
			if(!empty($commissions)) { 
				foreach($commissions as $commission) {
					$order_dtl[] = get_post_meta($commission->ID, '_commission_order_id', true);
				}
			}
		}
		return $order_dtl;
	}
	
	/**
	 * vendor_order_item_table function to get the html of item table of a vendor.
	 * @access public
	 * @param order id , vendor term id 
	*/	
	public function vendor_order_item_table( $order, $vendor_id ) {
		global $DC_Product_Vendor;
		$vendor_items = $this->get_vendor_items_order($order->id, $vendor_id);
		foreach( $vendor_items as $item ) {
			$_product     = apply_filters( 'dc_woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
			$item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );
			?>
			<tr class="">
				<td scope="col" style="text-align:left; border: 1px solid #eee;" class="product-name">
					<?php
						if ( $_product && ! $_product->is_visible() )
							echo apply_filters( 'dc_woocommerce_order_item_name', $item['name'], $item );
						else
							echo apply_filters( 'woocommerce_order_item_name', sprintf( '<a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ), $item );
							$item_meta->display();
						?>
				</td>
				<td scope="col" style="text-align:left; border: 1px solid #eee;">	
					<?php
						echo $item['qty'];
						?>
				</td>
				<td scope="col" style="text-align:left; border: 1px solid #eee;">
					<?php echo $order->get_formatted_line_subtotal( $item ); ?>
				</td>
			</tr>
			<?php
		}
	}
	
	/**
	 * plain_vendor_order_item_table function to get the plain html of item table of a vendor.
	 * @access public
	 * @param order id , vendor term id 
	*/	
	
	public function plain_vendor_order_item_table( $order, $vendor_id ) {
		global $DC_Product_Vendor;
		
		$vendor_items = $this->get_vendor_items_order($order->id, $vendor_id);
		foreach( $vendor_items as $item ) {
		$_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
		$item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );
		
		// Title
		echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );
		
		
		// Variation
		echo $item_meta->meta ? "\n" . $item_meta->display( true, true ) : '';
		
		// Quantity
		echo "\n" . sprintf( __( 'Quantity: %s', $DC_Product_Vendor->text_domain ), $item['qty'] );
		
		// Cost
		echo "\n" . sprintf( __( 'Cost: %s', $DC_Product_Vendor->text_domain ), $order->get_formatted_line_subtotal( $item ) );
		echo "\n\n";

		}
	}
	
	
	/**
	 * format_order_details function
	 * @access public
	 * @param order id , product_id
	 * @return array of order details
	*/	
	public function format_order_details( $orders, $product_id ) {
		$body    = $items = array();
		$product = get_product( $product_id )->get_title();
		foreach ( array_unique($orders) as $order ) {
			$i          = $order;
			$order      = new WC_Order ( $i );
			$body[ $i ] = array(
				'order_number' => $order->get_order_number(),
				'product'      => $product,
				'name'         => $order->shipping_first_name . ' ' . $order->shipping_last_name,
				'address'      => $order->shipping_address_1,
				'city'         => $order->shipping_city,
				'state'        => $order->shipping_state,
				'zip'          => $order->shipping_postcode,
				'email'        => $order->billing_email,
				'date'         => $order->order_date,
				'comments'     => wptexturize( $order->customer_note ),
			);

			$items[ $i ][ 'total_qty' ] = 0;
			foreach ( $order->get_items() as $line_id => $item ) {

				if ( $item[ 'product_id' ] != $product_id && $item[ 'variation_id' ] != $product_id ) continue;

				$items[ $i ][ 'items' ][ ] = $item;
				$items[ $i ][ 'total_qty' ] += $item[ 'qty' ];
			}
		}

		return array( 'body' => $body, 'items' => $items, 'product_id' => $product_id );
	}
	
	/**
	 * get_commision_per_order function
	 * @access public
	 * @param order id , vendor term id
	 * @return string
	*/	
	public function get_commision_per_order($order_id, $vendor_term_id) {
		if( $order_id && $vendor_term_id ) {
			$commission_total = 0;
			$commissions = false;
			$args = array(
				'post_type' =>  'dc_commission',
				'post_status' => array( 'publish', 'private' ),
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => '_commission_vendor',
						'value' => absint($vendor_term_id),
						'compare' => '='
					),
					array(
						'key' => '_commission_order_id',
						'value' => absint($order_id),
						'compare' => '='
					),
				),
			);
			$commissions = get_posts( $args );
			if(!empty($commissions)) { 
				foreach($commissions as $commission) {
					$commission_total = $commission_total + get_post_meta($commission->ID, '_commission_amount', true);
				}
			}
		}
		return $commission_total;		
	}
	
}
?>