<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		DC_Product_Vendor_Shortcode
 * @version		1.0.0
 * @package		DC_Product_Vendor
 * @author 		DualCube
 */
class DC_Product_Vendor_Shortcode {

	public $list_product;

	public function __construct() {
		//vendor_dashboard
		add_shortcode('vendor_dashboard', array(&$this, 'vendor_dashboard_shortcode'));
		//shop_settings
		add_shortcode('shop_settings', array(&$this, 'shop_settings_shortcode'));
		//vendor_report
		add_shortcode('vendor_report', array(&$this, 'vendor_report_shortcode'));
		//vendor_orders
		add_shortcode('vendor_orders', array(&$this, 'vendor_orders_shortcode'));
		//vendor_order_detail
		add_shortcode('vendor_order_detail', array(&$this, 'vendor_order_detail_shortcode'));
		//vendor_orders_by_product
		add_shortcode('vendor_orders_by_product', array(&$this, 'vendor_orders_by_product_shortcode'));
		
		//vendor_coupons
		add_shortcode('vendor_coupons', array(&$this, 'vendor_coupons_shortcode'));
		
		// Recent Products 
		add_shortcode( 'dc_recent_products', array(&$this, 'recent_products'));
		// Products by vendor
		add_shortcode( 'dc_products', array(&$this, 'products'));
		//Featured products by vendor
		add_shortcode( 'dc_featured_products', array(&$this, 'featured_products'));
		// Sale products by vendor
		add_shortcode( 'dc_sale_products', array(&$this, 'sale_products'));
		// Top Rated products by vendor 
		add_shortcode( 'dc_top_rated_products', array(&$this, 'top_rated_products'));
		// Best Selling product 
		add_shortcode( 'dc_best_selling_products', array(&$this, 'best_selling_products'));
		// List products in a category shortcode
		add_shortcode( 'dc_product_category', array(&$this, 'product_category'));
		// List of paginated vendors 
		add_shortcode( 'dc_vendorslist', array(&$this, 'dc_vendorslist' ) ); 
	}

	/**
	 * Vendor Dashboard
	 *
	 * @return void
	 */
	public function vendor_dashboard_shortcode($attr) {
		global $DC_Product_Vendor;
		$this->load_class('vendor-dashboard');
		return $this->shortcode_wrapper(array('WC_Vendor_Dashboard_Shortcode', 'output'));
	}
	
	/**
	 * vendor orders by product
	 *
	 * @return void
	 */
	public function vendor_orders_by_product_shortcode($attr) {
		global $DC_Product_Vendor;
		$this->load_class('vendor-orders-by-product');
		return $this->shortcode_wrapper(array('WC_Vendor_Orders_By_Product_Shortcode', 'output'));
	}
	
	/**
	 * vendor shop settings
	 *
	 * @return void
	 */
	public function shop_settings_shortcode($attr) {
		global $DC_Product_Vendor;
		$this->load_class('shop-setting');
		return $this->shortcode_wrapper(array('WC_Shop_Setting_Shortcode', 'output'));
	}

	/**
	 * vendor report
	 *
	 * @return void
	 */
	public function vendor_report_shortcode($attr) {
		global $DC_Product_Vendor;
		$this->load_class('vendor-report');
		return $this->shortcode_wrapper(array('WC_Vendor_Report_Shortcode', 'output'));
	}
	
	/**
	 * vendor orders
	 *
	 * @return void
	 */
	public function vendor_orders_shortcode($attr) {
		global $DC_Product_Vendor;
		$this->load_class('vendor-orders');
		return $this->shortcode_wrapper(array('WC_Vendor_Orders_Shortcode', 'output'));
	}
	
	/**
	 * vendor orer detail
	 *
	 * @return void
	 */
	public function vendor_order_detail_shortcode($attr) {
		global $DC_Product_Vendor;
		$this->load_class('vendor-view-order-dtl');
		return $this->shortcode_wrapper(array('WC_Vendor_Order_Detail_Shortcode', 'output'));
	}
	
	/**
	 * vendor orer detail
	 *
	 * @return void
	 */
	public function vendor_coupons_shortcode($attr) {
		global $DC_Product_Vendor;
		$this->load_class('vendor-used-coupon');
		return $this->shortcode_wrapper(array('WC_Vendor_Coupon_Shortcode', 'output'));
	}
	
	
	
	/**
	 * Helper Functions
	 */

	/**
	 * Shortcode Wrapper
	 *
	 * @access public
	 * @param mixed $function
	 * @param array $atts (default: array())
	 * @return string
	 */
	public function shortcode_wrapper($function, $atts = array()) {
		ob_start();
		call_user_func($function, $atts);
		return ob_get_clean();
	}

	/**
	 * Shortcode CLass Loader
	 *
	 * @access public
	 * @param mixed $class_name
	 * @return void
	 */
	
	public function load_class($class_name = '') {
		global $DC_Product_Vendor;
		if ('' != $class_name && '' != $DC_Product_Vendor->token) {
			require_once ('shortcode/class-' . esc_attr($DC_Product_Vendor->token) . '-shortcode-' . esc_attr($class_name) . '.php');
		}
	}
	/**
	 * get vendor
	 *
	 * @return void
	 */
	public static function get_vendor ( $slug ) { 

		$vendor_id = get_user_by('slug', $slug); 

		if (!empty($vendor_id)) { 
			$author = $vendor_id->ID; 
		} else $author = '';

		return $author; 

	}
	/**
	 * list all recent products
	 *
	 * @return void
	 */
	public static function recent_products( $atts ) {
			global $woocommerce_loop, $DC_Product_Vendor;
 
			extract( shortcode_atts( array(
				'per_page' 	=> '12',
				'vendor' 	=> '', 
				'columns' 	=> '4',
				'orderby' 	=> 'date',
				'order' 	=> 'desc'
			), $atts ) );
 
			$meta_query = WC()->query->get_meta_query();
			
			$args = array(
				'post_type'				=> 'product',
				'post_status'			=> 'publish',
				'ignore_sticky_posts'	=> 1,
				'posts_per_page' 		=> $per_page,
				'orderby' 				=> $orderby,
				'order' 				=> $order,
				'meta_query' 			=> $meta_query
			);
			
			if ( !empty( $vendor ) ) {
				$args['tax_query'][] = array(
					'taxonomy' 		=> $DC_Product_Vendor->taxonomy->taxonomy_name,
					'field' => 'slug',
					'terms' => sanitize_title($vendor)
				);
			}
 
			ob_start();
 
			$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
 
			$woocommerce_loop['columns'] = $columns;
 
			if ( $products->have_posts() ) : ?>
 
				<?php woocommerce_product_loop_start(); ?>
 
					<?php while ( $products->have_posts() ) : $products->the_post(); ?>
 
						<?php wc_get_template_part( 'content', 'product' ); ?>
 
					<?php endwhile; // end of the loop. ?>
 
				<?php woocommerce_product_loop_end(); ?>
 
			<?php endif;
 
			wp_reset_postdata();
 
			return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}
	
	/**
	 * list all products
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function products( $atts ) {
		global $woocommerce_loop, $DC_Product_Vendor;

		if ( empty( $atts ) ) return '';

		extract( shortcode_atts( array(
			'vendor' 	=> '',
			'columns' 	=> '4',
			'orderby'   => 'title',
			'order'     => 'asc'
		), $atts ) );



		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'orderby' 				=> $orderby,
			'order' 				=> $order,
			'posts_per_page' 		=> -1,
			'meta_query' 			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array('catalog', 'visible'),
					'compare' 	=> 'IN'
				)
			)
		);
		
		if ( !empty( $vendor ) ) {
			$args['tax_query'][] = array(
				'taxonomy' 		=> $DC_Product_Vendor->taxonomy->taxonomy_name,
				'field' => 'slug',
				'terms' => sanitize_title($vendor)
			);
		}

		if ( isset( $atts['skus'] ) ) {
			$skus = explode( ',', $atts['skus'] );
			$skus = array_map( 'trim', $skus );
			$args['meta_query'][] = array(
				'key' 		=> '_sku',
				'value' 	=> $skus,
				'compare' 	=> 'IN'
			);
		}

		if ( isset( $atts['ids'] ) ) {
			$ids = explode( ',', $atts['ids'] );
			$ids = array_map( 'trim', $ids );
			$args['post__in'] = $ids;
		}
		
		
		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}


	/**
	 * list all featured products
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function featured_products( $atts ) {
		global $woocommerce_loop, $DC_Product_Vendor;

		extract( shortcode_atts( array(
			'vendor' => '',
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'orderby' 	=> 'date',
			'order' 	=> 'desc'
		), $atts ) );

		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' 		=> $per_page,
			'orderby' 				=> $orderby,
			'order' 				=> $order,
			'meta_query'			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array('catalog', 'visible'),
					'compare'	=> 'IN'
				),
				array(
					'key' 		=> '_featured',
					'value' 	=> 'yes'
				)
			)
		);
		
		if ( !empty( $vendor ) ) {
			$args['tax_query'][] = array(
				'taxonomy' 		=> $DC_Product_Vendor->taxonomy->taxonomy_name,
				'field' => 'slug',
				'terms' => sanitize_title($vendor)
			);
		}

		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}
	
	/**
	 * List all products on sale
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function sale_products( $atts ) {
		global $woocommerce_loop, $DC_Product_Vendor;

		extract( shortcode_atts( array(
			'vendor' 		=> '', 
			'per_page'      => '12',
			'columns'       => '4',
			'orderby'       => 'title',
			'order'         => 'asc'
		), $atts ) );

		// Get products on sale
		$product_ids_on_sale = wc_get_product_ids_on_sale();

		$meta_query   = array();
		$meta_query[] = WC()->query->visibility_meta_query();
		$meta_query[] = WC()->query->stock_status_meta_query();
		$meta_query   = array_filter( $meta_query );

		$args = array(
			'posts_per_page'	=> $per_page,
			'orderby' 			=> $orderby,
			'order' 			=> $order,
			'no_found_rows' 	=> 1,
			'post_status' 		=> 'publish',
			'post_type' 		=> 'product',
			'meta_query' 		=> $meta_query,
			'post__in'			=> array_merge( array( 0 ), $product_ids_on_sale )
		);
		
		if ( !empty( $vendor ) ) {
			$args['tax_query'][] = array(
				'taxonomy' 		=> $DC_Product_Vendor->taxonomy->taxonomy_name,
				'field' => 'slug',
				'terms' => sanitize_title($vendor)
			);
		}
		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * List top rated products on sale by vendor
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function top_rated_products( $atts ) {
		global $woocommerce_loop, $DC_Product_Vendor;

		extract( shortcode_atts( array(
			'vendor'		=> '', 
			'per_page'      => '12',
			'columns'       => '4',
			'orderby'       => 'title',
			'order'         => 'asc'
			), $atts ) );

		$args = array(
			'post_type' 			=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'   => 1,
			'orderby' 				=> $orderby,
			'order'					=> $order,
			'posts_per_page' 		=> $per_page,
			'meta_query' 			=> array(
				array(
					'key' 			=> '_visibility',
					'value' 		=> array('catalog', 'visible'),
					'compare' 		=> 'IN'
				)
			)
		);
		
		if ( !empty( $vendor ) ) {
			$args['tax_query'][] = array(
				'taxonomy' 		=> $DC_Product_Vendor->taxonomy->taxonomy_name,
				'field' => 'slug',
				'terms' => sanitize_title($vendor)
			);
		}

		ob_start();

		add_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		remove_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * List best selling products on sale per vendor
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function best_selling_products( $atts ) {
		global $woocommerce_loop, $DC_Product_Vendor;

		extract( shortcode_atts( array(
			'vendor'		=> '', 
			'per_page'      => '12',
			'columns'       => '4'
		), $atts ) );

		$args = array(
			'post_type' 			=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'   => 1,
			'posts_per_page'		=> $per_page,
			'meta_key' 		 		=> 'total_sales',
			'orderby' 		 		=> 'meta_value_num',
			'meta_query' 			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array( 'catalog', 'visible' ),
					'compare' 	=> 'IN'
				)
			)
		);
		
		if ( !empty( $vendor ) ) {
			$args['tax_query'][] = array(
				'taxonomy' 		=> $DC_Product_Vendor->taxonomy->taxonomy_name,
				'field' => 'slug',
				'terms' => sanitize_title($vendor)
			);
		}
		
		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * List products in a category shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function product_category( $atts ) {
		global $woocommerce_loop, $DC_Product_Vendor;

		extract( shortcode_atts( array(
			'vendor'   => '', 
			'per_page' => '12',
			'columns'  => '4',
			'orderby'  => 'title',
			'order'    => 'desc',
			'category' => '',  // Slugs
			'operator' => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
		), $atts ) );

		if ( ! $category ) {
			return '';
		}

		// Default ordering args
		$ordering_args = WC()->query->get_catalog_ordering_args( $orderby, $order );

		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'orderby' 				=> $ordering_args['orderby'],
			'order' 				=> $ordering_args['order'],
			'posts_per_page' 		=> $per_page,
			'meta_query' 			=> array(
				array(
					'key' 			=> '_visibility',
					'value' 		=> array('catalog', 'visible'),
					'compare' 		=> 'IN'
				)
			),
			'tax_query' 			=> array(
				array(
					'taxonomy' 		=> 'product_cat',
					'terms' 		=> array_map( 'sanitize_title', explode( ',', $category ) ),
					'field' 		=> 'slug',
					'operator' 		=> $operator
				)
			)
		);
		
		if ( !empty( $vendor ) ) {
			$args['tax_query'][] = array(
				'taxonomy' 		=> $DC_Product_Vendor->taxonomy->taxonomy_name,
				'field' => 'slug',
				'terms' => sanitize_title($vendor)
			);
		}
		
		if ( isset( $ordering_args['meta_key'] ) ) {
			$args['meta_key'] = $ordering_args['meta_key'];
		}

		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		woocommerce_reset_loop();
		wp_reset_postdata();

		$return = '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';

		// Remove ordering query arguments
		WC()->query->remove_ordering_args();

		return $return;
	}

	/**
	  * 	list of vendors 
	  * 
	  * 	@param $atts shortcode attributs 
	*/
	public function dc_vendorslist( $atts ) {
		global $DC_Product_Vendor;
		
		extract( shortcode_atts( array(
			'orderby'  => 'registered',
			'order'    => 'ASC',
		), $atts ) );
		
    $vendors = '';
    $vendor_sort_type = $_GET['vendor_sort_type'];
    if(isset($vendor_sort_type)) {
    	$orderby = $vendor_sort_type;
    	$order = 'ASC';
    } 
		$get_all_vendors = get_dc_vendors(array('orderby' => $orderby, 'order' => $order));
		
		$vendors .= '<div class="vendor_list">';
		$vendors .= '<form name="vendor_sort" method="get" ><div class="vendor_sort">';
		$vendors .= '<select class="select short" id="vendor_sort_type" name="vendor_sort_type">';
		if($vendor_sort_type) {
			if($vendor_sort_type == 'registered') {
				$option = '<option value="">Select Option</option><option selected value="registered">By date</option><option value="name">By Alphabetically</option>';
			} else if($vendor_sort_type == 'name') {
				$option = '<option value="">Select Option</option><option value="registered">By date</option><option selected value="name">By Alphabetically</option>';
			} else {
				$option = '<option value="">Select Option</option><option value="registered">By date</option><option value="name">By Alphabetically</option>';
			}
		} else {
			if($orderby == 'registered') {
				$option = '<option value="">Select Option</option><option selected value="registered">By date</option><option value="name">By Alphabetically</option>';
			} else if($orderby == 'name') {
				$option = '<option value="">Select Option</option><option  value="registered">By date</option><option selected value="name">By Alphabetically</option>';
			}
		}
		$vendors .= $option;
		$vendors .= '</select>&nbsp;&nbsp;&nbsp;<input type="submit" value="Sort" />';
		$vendors .= '</div>';
		$vendors .= '</form>';
		
		foreach ( $get_all_vendors as $get_vendor ) {
			if(!$get_vendor->image) $get_vendor->image = $DC_Product_Vendor->plugin_url . 'assets/images/WP-stdavatar.png';
			$vendors .= '<div style="display:inline-block; margin-right:10%;">
       							 <center>
       							 		<a href="'.$get_vendor->permalink.'"><img width="125" class="vendor_img" src="'. $get_vendor->image .'" id="vendor_image_display"></a><br />
       							 		<a href="'.$get_vendor->permalink.'" class="button">'.$get_vendor->user_data->display_name.'</a>
       							 		<br /><br />
       							 </center>
       							</div>';
		}
		$vendors .= '</div>';
		return $vendors;
	}
}
?>
