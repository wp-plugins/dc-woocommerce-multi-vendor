<?php

class DC_Product_Vendor_Plugin_Post_Reports {
	public function __construct() {
		 // Add reports to WP dashboard
     add_filter( 'woocommerce_reports_charts', array( &$this, 'admin_add_reports' ) );
	}
	
	/**
	* Add Product Vendor reports to WC reports
	* @param  arr $charts Existing reports
	* @return arr         Modified reports
	*/
	public function admin_add_reports( $charts ) {
		global $DC_Product_Vendor;
		$charts['dc_product_vendors'] = array(
			'title' => __( 'DC Vendors', $DC_Product_Vendor->text_domain ),
			'charts' => array(
				array(
						'title'       => __( 'Overview', $DC_Product_Vendor->text_domain ),
						'description' => '',
						'hide_title'  => true,
						'function'    => array(&$this, 'woocommerce_product_vendors_report_overview')
				),
				array(
						'title'       => __( 'Sales by Vendor', $DC_Product_Vendor->text_domain ),
						'description' => '',
						'hide_title'  => false,
						'function'    =>  array(&$this, 'woocommerce_product_vendors_report_vendor_sales')
				),
				array(
						'title'       => __( 'Vendor Sales by Product', $DC_Product_Vendor->text_domain ),
						'description' => '',
						'hide_title'  => false,
						'function'    =>  array(&$this, 'woocommerce_product_vendors_report_vendor_product_sales')
				)
			)
		);
		return $charts;
	}
	
	
	/**
	* product vendors report overview
	* @return void
	*/
	function woocommerce_product_vendors_report_overview() {
		global $DC_Product_Vendor, $start_date, $end_date, $woocommerce, $wp_locale;
		$args = array(
			'post_type' => 'shop_order',
			'post_status' => array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded','wc-failed'),
			'meta_query' => array(
				array(
					'key' => '_commissions_processed',
					'value' => 'yes',
					'compare' => '='
				)
			),
			'posts_per_page' => -1
		);
		
		$orders = get_posts( $args );
		$total_sales = $total_orders = $total_earnings = $total_vendor_earnings = 0;
		foreach( $orders as $order ) {
			$order_obj = new WC_Order( $order->ID );
			$items = $order_obj->get_items( 'line_item' );
			$comm_amount = 0;
			foreach( $items as $item_id => $item ) {
				$product_id = $order_obj->get_item_meta( $item_id, '_product_id', true );
				$variation_id = $order_obj->get_item_meta( $item_id, '_variation_id', true );
				$line_total = $order_obj->get_item_meta( $item_id, '_line_total', true );
				if( $product_id && $line_total ) {
					$product_vendors = wp_get_post_terms( $product_id, 'dc_vendor_shop', array("fields" => "ids"));
					if( $product_vendors ) {
						$commission_obj = new DC_Product_Vendor_Calculate_Commission();
						$total_sales += $line_total;
						$comm_percent = $commission_obj->get_commission_percent( $product_id, $product_vendors[0], $variation_id );
						if( $comm_percent && $comm_percent > 0 ) {
							$comm_amount = (int) $line_total * ( $comm_percent / 100 );
							$total_vendor_earnings += $comm_amount;
						}
						$earnings = ( $line_total - $comm_amount );
						$total_earnings += $earnings;
					}
				}
			}
			++$total_orders;
		}
		?>
		
		<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Total sales', $DC_Product_Vendor->text_domain ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_sales > 0 ) echo woocommerce_price( $total_sales ); else _e( 'n/a', $DC_Product_Vendor->text_domain ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total orders', $DC_Product_Vendor->text_domain ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_orders > 0 ) echo $total_orders; else _e( 'n/a', $DC_Product_Vendor->text_domain ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Average order total', $DC_Product_Vendor->text_domain ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_orders > 0 ) echo woocommerce_price( $total_sales / $total_orders ); else _e( 'n/a', $DC_Product_Vendor->text_domain ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total earned', $DC_Product_Vendor->text_domain ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_earnings > 0 ) echo woocommerce_price( $total_earnings ); else _e( 'n/a', $DC_Product_Vendor->text_domain ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total earned by vendors', $DC_Product_Vendor->text_domain ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_vendor_earnings > 0 ) echo woocommerce_price( $total_vendor_earnings ); else _e( 'n/a', $DC_Product_Vendor->text_domain ); ?></p>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-main">
			<div class="postbox">
				<h3><span><?php _e( 'This month\'s sales', $DC_Product_Vendor->text_domain ); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
					<div id="cart_legend"></div>
				</div>
			</div>
		</div>
		</div>
		<?php
		
		$chart_data = array();
		$start_date = strtotime( date('Ymd', strtotime( date('Ym', current_time('timestamp') ) . '01' ) ) );
		$end_date = strtotime( date('Ymd', current_time( 'timestamp' ) ) );
		
		for( $date = $start_date; $date <= $end_date; $date = strtotime( '+1 day', $date ) ) {
			$year = date( 'Y', $date );
			$month = date( 'n', $date );
			$day = date( 'j', $date );
			$total_vendor_earnings = $total_earnings = $order_count = $day_total_vendors = $day_total = 0;
			
			$args = array(
				'post_type' => 'shop_order',
				'posts_per_page' => -1,
				'post_status' => array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded','wc-failed'),
				'meta_query' => array(
					array(
						'key' => '_commissions_processed',
						'value' => 'yes',
						'compare' => '='
					)
				),
				'date_query' => array(
					array(
						'year'  => $year,
						'month' => $month,
						'day'   => $day,
					),
				)
			);
			//print_r($args);
			$qry = new WP_Query( $args );
			//print_r($qry);
			$total_vendor_earnings = $total_earnings = $total_sales = $line_total = $day_total = $day_total_vendors = $comm_amount = 0;
			if ( $qry->have_posts() ) {
				while ( $qry->have_posts() ) { 
					$qry->the_post();
					$order = new WC_Order( get_the_ID() );
					$items = $order->get_items( 'line_item' );
					foreach( $items as $item_id => $item ) {
						$product_id = $order->get_item_meta( $item_id, '_product_id', true );
						$line_total = $order->get_item_meta( $item_id, '_line_total', true );
						if( $product_id && $line_total ) {
							$product_vendors = wp_get_post_terms( $product_id, 'dc_vendor_shop', array("fields" => "ids"));
							if( $product_vendors ) {
								$commission_obj = new DC_Product_Vendor_Calculate_Commission();
								$total_sales += $line_total;
								$comm_percent = $commission_obj->get_commission_percent( $product_id, $product_vendors[0] );
								if( $comm_percent && $comm_percent > 0 ) {
									$comm_amount = (int) $line_total * ( $comm_percent / 100 );
									$total_vendor_earnings += $comm_amount;
								}
								$earnings = ( $line_total - $comm_amount );
								$total_earnings += $earnings;
							}
						}
					}
					++$order_count;
				}
			}
			wp_reset_postdata();
			
			$chart_data[ __( 'Total earned', $DC_Product_Vendor->text_domain ) ][] = array(
				$date . '000',
				$total_earnings
			);
			
			$chart_data[ __( 'Total earned by vendors', $DC_Product_Vendor->text_domain ) ][] = array(
				$date . '000',
				$total_vendor_earnings
			);
			
			$chart_data[ __( 'Number of orders', $DC_Product_Vendor->text_domain ) ][] = array(
				$date . '000',
				$order_count
			);
		}
		//print_r($chart_data);
		
		?>
		<script type="text/javascript">
			jQuery(function(){
		
				<?php
					// Variables
					foreach ( $chart_data as $name => $data ) {
						$varname = str_replace( '-', '_', sanitize_title( $name ) ) . '_data';
						echo 'var ' . $varname . ' = jQuery.parseJSON( \'' . json_encode( $data ) . '\' );';
					}
				?>
		
				var placeholder = jQuery("#placeholder");
		
				var plot = jQuery.plot(placeholder, [
					<?php
					$labels = array();
		
					foreach ( $chart_data as $name => $data ) {
						if( $name == 'Number of orders' ) {
							$labels[] = '{ label: "' . esc_js( $name ) . '", data: ' . str_replace( '-', '_', sanitize_title( $name ) ) . '_data, yaxis: 2 }';
						} else {
							$labels[] = '{ label: "' . esc_js( $name ) . '", data: ' . str_replace( '-', '_', sanitize_title( $name ) ) . '_data }';
						}
					}
		
					echo implode( ',', $labels );
					?>
				], {
					legend: {
						container: jQuery('#cart_legend'),
						noColumns: 2
					},
					series: {
						lines: { show: true, fill: true },
						points: { show: true }
					},
					grid: {
						show: true,
						aboveData: false,
						color: '#aaa',
						backgroundColor: '#fff',
						borderWidth: 2,
						borderColor: '#aaa',
						clickable: false,
						hoverable: true
					},
					xaxis: {
						mode: "time",
						timeformat: "%d %b %y",
						monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
						tickLength: 1,
						minTickSize: [1, "day"]
					},
					yaxes: [ { min: 0, tickSize: 10, tickDecimals: 2 }, { position: "right",tickSize: 1, min: 0, tickDecimals: 2 } ],
					colors: ["#000","#47A03E","#21759B"]
				});
		
				placeholder.resize();
		
				<?php $this->render_tooltip_js(); ?>
			});
		</script>
		<?php
	}
	
	/**
	* product vendors report vendor product_sales
	* @return void
	*/
	function woocommerce_product_vendors_report_vendor_product_sales() { 
		global $wpdb, $woocommerce, $DC_Product_Vendor;
		if(isset($_POST['search_product'])) {
			$is_variation = false;
			$product_id = $_POST['search_product'];
		
			$_product = get_product($product_id);
		
			if( $_product->is_type( 'variation' ) ) {
				$title = $_product->get_formatted_name();
				$is_variation = true;
			} else {
				$title = $_product->get_title();
			}
		}
		
		if( isset( $product_id )) {
			$option = '<option value="' .$product_id. '" selected="selected">' . $title . '</option>';
		} else {
			$option = '<option></option>';
		}
		?>
		<form name="search_product_form" method="post" action="">
			<p>
				<select id="search_product" name="search_product" class="ajax_chosen_select_products_and_variations" data-placeholder="Search for product&hellip;" style="min-width:400px;"><?php echo $option; ?></select> <input type="submit" style="vertical-align: top;" class="button" value="<?php _e( 'Show', $DC_Product_Vendor->text_domain ); ?>" />
			</p>
		</form>
		<?php
		
		if( isset( $product_id ) && !$is_variation) {
			$data = wp_get_post_terms( $product_id, 'dc_vendor_shop', array("fields" => "ids"));
			if( $data && isset($data) && !empty($data) ) {
				$vendor = get_dc_vendor_by_term( $data[0] );
			}
		} else if(isset( $product_id ) && $is_variation) {
				$variatin_parent = wp_get_post_parent_id($product_id);
				$data = wp_get_post_terms( $variatin_parent, 'dc_vendor_shop', array("fields" => "ids"));
				if( $data && isset($data) && !empty($data) ) {
					$vendor = get_dc_vendor_by_term( $data[0] );
				}
		}
		if($vendor) {
			$orders = array();
			if( $_product->is_type( 'variable' ) ) {
				$get_children = $_product->get_children();
				if(!empty($get_children)) {
					foreach($get_children as $child) {
						$orders = array_merge($orders, $vendor->get_vendor_orders_by_product($data[0], $child));
					}
					$orders = array_unique($orders);
				}
			} else {
				$orders = $vendor->get_vendor_orders_by_product($data[0], $product_id);
			}
		}
			
		if(!empty($orders)) {
			foreach($orders as $order_id) {
				$order = new WC_Order ( $order_id );
				$order_line_items = $order->get_items('line_item');
				if(!empty($order_line_items)) {
					foreach($order_line_items as $line_item) {
						if ( $line_item[ 'product_id' ] == $product_id || $line_item[ 'variation_id' ] == $product_id ) {
							if( $_product->is_type( 'variation' ) ) {
								$order_items_product_id = wp_get_post_parent_id($product_id);
								$order_items_variation_id = $product_id;
							} else {
								$order_items_product_id = $product_id;
								$order_items_variation_id = 0;
							}
							$order_items[] = array(
								 'product_id' => $order_items_product_id,
								 'variation_id' => $order_items_variation_id,
								 'line_total' => $line_item['line_total'],
								 'item_quantity' => $line_item['qty'],
								 'post_date' => $order->order_date,
							);
						}
					}
				}
			}
		}
		if ( $order_items ) {
			foreach ( $order_items as $order_item ) {

				if ( $order_item['line_total'] == 0 && $order_item['item_quantity'] == 0 )
					continue;

				// Get date
				$date 	= date( 'Ym', strtotime( $order_item['post_date'] ) );

				// Calculate vendor earnings from sale
				$commission_obj = new DC_Product_Vendor_Calculate_Commission();
				
				if( $order_item['variation_id'] != 0 ){
					$variation_id = $order_item['variation_id'];
				} else {
					$variation_id = 0;
				}
				$vendor_earnings = 0;
				$comm_percent = $commission_obj->get_commission_percent( $order_item['product_id'], $vendor_id, $variation_id );
				if($comm_percent) {
					$vendor_earnings = $order_item['line_total'] * ( $comm_percent / 100 );
				}
				// Set values
				$product_sales[ $date ] 	= isset( $product_sales[ $date ] ) ? $product_sales[ $date ] + $order_item['item_quantity'] : $order_item['item_quantity'];
				$product_totals[ $date ] 	= isset( $product_totals[ $date ] ) ? $product_totals[ $date ] + $vendor_earnings : $vendor_earnings;
				if ( $product_sales[ $date ] > $max_sales )
					$max_sales = $product_sales[ $date ];

				if ( $product_totals[ $date ] > $max_totals )
					$max_totals = $product_totals[ $date ];
			}
		}
		if( isset( $product_id )) {
			?>
			<h4><?php printf( __( 'Sales and earnings for %s:', $DC_Product_Vendor->text_domain ), $vendor->title ); ?></h4>
			<table class="bar_chart">
				<thead>
					<tr>
						<th><?php _e( 'Month', $DC_Product_Vendor->text_domain ); ?></th>
						<th colspan="2"><?php _e( 'Sales', $DC_Product_Vendor->text_domain ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if ( sizeof( $product_sales ) > 0 ) {
							foreach ( $product_sales as $date => $sales ) {
								$width = ($sales>0) ? (round($sales) / round($max_sales)) * 100 : 0;
								$width2 = ($product_totals[$date]>0) ? (round($product_totals[$date]) / round($max_totals)) * 100 : 0;
	
								//$orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=' . urlencode( implode( ' ', $chosen_product_titles ) ) . '&m=' . date( 'Ym', strtotime( $date . '01' ) ) . '&shop_order_status=' . implode( ",", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) );
								//$orders_link = apply_filters( 'woocommerce_reports_order_link', $orders_link, $chosen_product_ids, $chosen_product_titles );
	
								echo '<tr><th><a href="' . esc_url( $orders_link ) . '">' . date_i18n( 'F', strtotime( $date . '01' ) ) . '</a></th>
								<td width="1%"><span>' . esc_html( $sales ) . '</span><span class="alt">' . woocommerce_price( $product_totals[ $date ] ) . '</span></td>
								<td class="bars">
									<span style="width:' . esc_attr( $width ) . '%">&nbsp;</span>
									<span class="alt" style="width:' . esc_attr( $width2 ) . '%">&nbsp;</span>
								</td></tr>';
							}
						} else {
							echo '<tr><td colspan="3">' . __( 'No sales :(', $DC_Product_Vendor->text_domain ) . '</td></tr>';
						}
					?>
				</tbody>
			</table>
			<?php
		}
	}		
	
	
	/**
	 * Sales report for each vendor
	 * @return void
	 */
	function woocommerce_product_vendors_report_vendor_sales() {
		global $wpdb, $woocommerce, $DC_Product_Vendor;
	
		$chosen_product_ids = $vendor_id = $vendor = false;
		if( isset( $_POST['vendor'] ) ) {
			$vendor_id = $_POST['vendor'];
			$vendor = get_dc_vendor_by_term( $vendor_id );
			if($vendor) $products = $vendor->get_products();
			if(!empty($products)) {
				foreach( $products as $product ) {
					$chosen_product_ids[] = $product->ID;
				}
			}
		}
		
		if( $vendor_id && $vendor ) {
			$option = '<option value="' . $vendor_id. '" selected="selected">' . $vendor->user_data->display_name . '</option>';
		} else {
			$option = '<option></option>';
		}
		?>
		<form method="post" action="">
			<p><select id="vendor" name="vendor" class="ajax_chosen_select_vendor" data-placeholder="<?php _e( 'Search for a vendor&hellip;', $DC_Product_Vendor->text_domain ); ?>" style="width: 400px;"><?php echo $option; ?></select> <input type="submit" style="vertical-align: top;" class="button" value="<?php _e( 'Show', $DC_Product_Vendor->text_domain ); ?>" /></p>
		</form>
		<?php
		
		if($vendor_id && empty($products)) { ?>
			<h4><?php printf( __( 'Sales and earnings for %s:', $DC_Product_Vendor->text_domain ), $vendor->title ); ?></h4>
			<table class="bar_chart">
				<thead>
					<tr>
						<th><?php _e( 'Month', $DC_Product_Vendor->text_domain ); ?></th>
						<th colspan="2"><?php _e( 'Sales', $DC_Product_Vendor->text_domain ); ?></th>
					</tr>
				</thead>
				<tbody> 
					<?php
						echo '<tr><td colspan="3">' . __( 'No sales :(', $DC_Product_Vendor->text_domain ) . '</td></tr>';
					?>
				</tbody>
			</table>
			<?php
		}
	
		if ( $chosen_product_ids && is_array( $chosen_product_ids ) ) {
	
			$start_date = date( 'Ym', strtotime( '-12 MONTHS', current_time('timestamp') ) ) . '01';
			$end_date 	= date( 'Ymd', current_time( 'timestamp' ) );
	
			$max_sales = $max_totals = 0;
			$product_sales = $product_totals = array();
	
			// Get titles and ID's related to product
			$chosen_product_titles = array();
			$children_ids = array();
	
			foreach ( $chosen_product_ids as $product_id ) {
				$children = (array) get_posts( 'post_parent=' . $product_id . '&fields=ids&post_status=any&numberposts=-1' );
				$children_ids = $children_ids + $children;
				$chosen_product_titles[] = get_the_title( $product_id );
			}
			
				
			// Get order items
			$order_items = apply_filters( 'woocommerce_reports_product_sales_order_items', $wpdb->get_results( "
				SELECT order_item_meta_2.meta_value as product_id, order_item_meta_1.meta_value as variation_id, posts.post_date, SUM( order_item_meta.meta_value ) as item_quantity, SUM( order_item_meta_3.meta_value ) as line_total
				FROM {$wpdb->prefix}woocommerce_order_items as order_items
	
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_1 ON order_items.order_item_id = order_item_meta_1.order_item_id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_3 ON order_items.order_item_id = order_item_meta_3.order_item_id
				LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
	
				WHERE posts.post_type 	= 'shop_order'
				AND 	order_item_meta_2.meta_value IN ('" . implode( "','", array_merge( $chosen_product_ids, $children_ids ) ) . "')
				AND posts.post_status IN ('wc-pending','wc-processing','wc-on-hold','wc-completed','wc-cancelled','wc-refunded','wc-failed')
				AND 	order_items.order_item_type = 'line_item'
				AND 	order_item_meta.meta_key = '_qty'
				AND 	order_item_meta_2.meta_key = '_product_id'
				AND 	order_item_meta_1.meta_key = '_variation_id'
				AND 	order_item_meta_3.meta_key = '_line_total'
				GROUP BY order_items.order_id
				ORDER BY posts.post_date ASC
			" ), array_merge( $chosen_product_ids, $children_ids ) );
			
			$found_products = array();
			
			if ( $order_items ) {
				foreach ( $order_items as $order_item ) {
	
					if ( $order_item->line_total == 0 && $order_item->item_quantity == 0 )
						continue;
	
					// Get date
					$date 	= date( 'Ym', strtotime( $order_item->post_date ) );
	
					// Calculate vendor earnings from sale
					$commission_obj = new DC_Product_Vendor_Calculate_Commission();
					
					if( $order_item->variation_id != '0' ){
						$variation_id = $order_item->variation_id;
					} else {
						$variation_id = 0;
					}
					$vendor_earnings = 0;
					
					$comm_percent = $commission_obj->get_commission_percent( $order_item->product_id, $vendor_id, $variation_id );
					if($comm_percent) {
						$vendor_earnings = $order_item->line_total * ( $comm_percent / 100 );
					}
					// Set values
					$product_sales[ $date ] 	= isset( $product_sales[ $date ] ) ? $product_sales[ $date ] + $order_item->item_quantity : $order_item->item_quantity;
					$product_totals[ $date ] 	= isset( $product_totals[ $date ] ) ? $product_totals[ $date ] + $vendor_earnings : $vendor_earnings;
					if ( $product_sales[ $date ] > $max_sales )
						$max_sales = $product_sales[ $date ];
	
					if ( $product_totals[ $date ] > $max_totals )
						$max_totals = $product_totals[ $date ];
				}
			}
			?>
			<h4><?php printf( __( 'Sales and earnings for %s:', $DC_Product_Vendor->text_domain ), $vendor->title ); ?></h4>
			<table class="bar_chart">
				<thead>
					<tr>
						<th><?php _e( 'Month', $DC_Product_Vendor->text_domain ); ?></th>
						<th colspan="2"><?php _e( 'Sales', $DC_Product_Vendor->text_domain ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if ( sizeof( $product_sales ) > 0 ) {
							foreach ( $product_sales as $date => $sales ) {
								$width = ($sales>0) ? (round($sales) / round($max_sales)) * 100 : 0;
								$width2 = ($product_totals[$date]>0) ? (round($product_totals[$date]) / round($max_totals)) * 100 : 0;
	
								$orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=' . urlencode( implode( ' ', $chosen_product_titles ) ) . '&m=' . date( 'Ym', strtotime( $date . '01' ) ) . '&shop_order_status=' . implode( ",", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) );
								$orders_link = apply_filters( 'woocommerce_reports_order_link', $orders_link, $chosen_product_ids, $chosen_product_titles );
	
								echo '<tr><th><a href="' . esc_url( $orders_link ) . '">' . date_i18n( 'F', strtotime( $date . '01' ) ) . '</a></th>
								<td width="1%"><span>' . esc_html( $sales ) . '</span><span class="alt">' . woocommerce_price( $product_totals[ $date ] ) . '</span></td>
								<td class="bars">
									<span style="width:' . esc_attr( $width ) . '%">&nbsp;</span>
									<span class="alt" style="width:' . esc_attr( $width2 ) . '%">&nbsp;</span>
								</td></tr>';
							}
						} else {
							echo '<tr><td colspan="3">' . __( 'No sales :(', $DC_Product_Vendor->text_domain ) . '</td></tr>';
						}
					?>
				</tbody>
			</table>
			<?php
		}
	}
	
	/**
	* product vendors total earning report
	* @return html
	*/
	public function vendor_total_earnings_report() {
		global $DC_Product_Vendor;
		$html = '';
		$pages = get_option("dc_pages_settings_name");
		$user = wp_get_current_user();
		$vendor = get_dc_vendor($user->ID);
		
		if( $vendor ) {
			$selected_year = ( isset( $_POST['report_year'] ) && $_POST['report_year'] != 'all' ) ? $_POST['report_year'] : false;
			$selected_month = ( isset( $_POST['report_month'] ) && $_POST['report_month'] != 'all' ) ? $_POST['report_month'] : false;

			// Get all vendor commissions
			$DC_Product_Vendor_Commission = new DC_Product_Vendor_Commission();
			$vendor_term_id = get_user_meta($user->ID, '_vendor_term_id', true);
			$commissions = $DC_Product_Vendor_Commission->get_vendor_commissions( $vendor_term_id, $selected_year, $selected_month, false );
			$total_earnings = 0;
			if($commissions){
				foreach( $commissions as $commission ) {
					$earnings = get_post_meta( $commission->ID, '_commission_amount', true );
					$product_id = get_post_meta( $commission->ID, '_commission_product', true );
					$product = get_product( $product_id );
					//print_r($commission);
					//echo $product_id;
					if($product) {
						$has_parent = !empty($product->parent->id) ? $product->parent->id : '';
						$parent_product = get_product( $has_parent );
						if(!empty($has_parent)) {
							if( ! isset( $data[ $has_parent ]['product'] ) ) {
									$data[ $has_parent ]['product'] = $parent_product->get_title();
							}
			
							if( ! isset( $data[ $has_parent ]['product_url'] ) ) {
									$data[ $has_parent ]['product_url'] = get_permalink( $has_parent );
							}
			
							if( isset( $data[ $has_parent ]['sales'] ) ) {
									++$data[ $has_parent ]['sales'];
							} else {
									$data[ $has_parent ]['sales'] = 1;
							}
			
							if( isset( $data[ $has_parent ]['earnings'] ) ) {
									$data[ $has_parent ]['earnings'] += $earnings;
							} else {
									$data[ $has_parent ]['earnings'] = $earnings;
							}
						} else { 
							if( ! isset( $data[ $product_id ]['product'] ) ) {
									$data[ $product_id ]['product'] = $product->get_title();
							}
			
							if( ! isset( $data[ $product_id ]['product_url'] ) ) {
									$data[ $product_id ]['product_url'] = get_permalink( $product_id );
							}
			
							if( isset( $data[ $product_id ]['sales'] ) ) {
									++$data[ $product_id ]['sales'];
							} else {
									$data[ $product_id ]['sales'] = 1;
							}
			
							if( isset( $data[ $product_id ]['earnings'] ) ) {
									$data[ $product_id ]['earnings'] += $earnings;
							} else {
									$data[ $product_id ]['earnings'] = $earnings;
							}
						}
					}
					$total_earnings += $earnings;
				}
			}

			$month_options = '<option value="all">' . __( 'All months', $DC_Product_Vendor->text_domain ) . '</option>';
			for( $i = 1; $i <= 12; $i++ ) {
					$month_num = str_pad( $i, 2, 0, STR_PAD_LEFT );
					$month_name = date( 'F', mktime( 0, 0, 0, $i + 1, 0, 0 ) );
					$month_options .= '<option value="' . esc_attr( $month_num ) . '" ' . selected( $selected_month, $month_num, false ) . '>' . $month_name . '</option>';
			}

			$year_options = '<option value="all">' . __( 'All years', $DC_Product_Vendor->text_domain ) . '</option>';
			$current_year = date( 'Y' );
			for( $i = $current_year; $i >= ( $current_year - 5 ); $i-- ) {
					$year_options .= '<option value="' . $i . '" ' . selected( $selected_year, $i, false ) . '>' . $i . '</option>';
			}

			$html .= '<div class="product_vendors_report_form">
									<form name="product_vendors_report" action="' . get_permalink() . '" method="post">
											' . __( 'Select report date:', $DC_Product_Vendor->text_domain ) . '
											<select name="report_month">' . $month_options . '</select>
											<select name="report_year">' . $year_options . '</select>
											<input type="submit" class="button" value="Submit" />
									</form>
								</div>';

			$html .= '<table class="shop_table" cellspacing="0">
									<thead>
											<tr>
													<th>' . __( 'Product', $DC_Product_Vendor->text_domain ) . '</th>
													<th>' . __( 'Sales', $DC_Product_Vendor->text_domain ) . '</th>
													<th>' . __( 'Commission', $DC_Product_Vendor->text_domain ) . '</th>         
													<th>' . __( 'Rate', $DC_Product_Vendor->text_domain ) . '</th>
											</tr>
									</thead>
									<tbody>';

			if( isset( $data ) && is_array( $data ) ) {

					foreach( $data as $product_id => $product ) {
							$html .= '<tr>
													<td><a href="' . esc_url( $product['product_url'] ) . '">' . $product['product'] . '</a></td>
													<td>' . $product['sales'] . '</td>
													<td>' . get_woocommerce_currency_symbol() . number_format( $product['earnings'], 2 ) . '</td>
													<td>' . get_post_meta($product_id, '_commission_per_product', true). '%</td>
													<td><a href="'.get_permalink($pages['view_order']).'?orders_for_product='.$product_id.'">Show Orders</a></td>
												</tr>';
					}

					$html .= '<tr>
											<td colspan="2"><b>' . __( 'Total', $DC_Product_Vendor->text_domain ) . '</b></td>
											<td>' . get_woocommerce_currency_symbol() . number_format( $total_earnings, 2 ) . '</td>
										</tr>';

			} else {
					$html .= '<tr><td colspan="3"><em>' . __( 'No sales found', $DC_Product_Vendor->text_domain ) . '</em></td></tr>';
			}

			$html .= '</tbody>
							</table>';
		}
		return $html;
	}
	
	/**
	* product vendors month earning report
	* @return html
	*/
	public function vendor_month_earnings() {
		global $DC_Product_Vendor;
		$html = '';
		$user = wp_get_current_user();
		$vendor = get_dc_vendor($user->ID);
		if( $vendor ) {
			$DC_Product_Vendor_Commission = new DC_Product_Vendor_Commission();
			$commissions = $DC_Product_Vendor_Commission->get_vendor_commissions( $vendor->term_id , date( 'Y' ), date( 'm' ), false );
			if( $commissions ) {
				$month_earnings = 0;
				foreach( $commissions as $commission ) {
					$earnings = get_post_meta( $commission->ID, '_commission_amount', true );
					$month_earnings += $earnings;
				}
				$html .= '<br><h4>This Month\'s, '.date('F Y').' Earnings : '.get_woocommerce_currency_symbol() . number_format( $month_earnings, 2) .'</h4>';
			}
		}
		
		return $html;
	}
	
	/**
	 * Output JavaScript for chart tooltips
	*/
	private function render_tooltip_js() {
		?>
		function showTooltip( x, y, contents ) {
			jQuery( '<div id="tooltip">' + contents + '</div>' ).css( {
				position: 'absolute',
				display: 'none',
				top: y + 5,
				left: x + 5,
				padding: '5px 10px',
				border: '3px solid #3da5d5',
				background: '#fff'
			} ).appendTo("body").fadeIn(200);
		}
		var previousPoint = null;
		jQuery( "#placeholder").bind( "plothover", function( event, pos, item ) {
			if ( item ) {
				if ( previousPoint != item.dataIndex ) {
					previousPoint = item.dataIndex;

					jQuery( "#tooltip" ).remove();

					var y = item.datapoint[1].toFixed( 2 );
					showTooltip( item.pageX, item.pageY, item.series.label + " - " + "<?php echo get_woocommerce_currency_symbol(); ?>" + y );
				}
			} else {
				jQuery( "#tooltip" ).remove();
				previousPoint = null;
			}
		});
		<?php
	}
}

?>