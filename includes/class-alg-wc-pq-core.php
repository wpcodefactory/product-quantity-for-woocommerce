<?php
/**
 * Product Quantity for WooCommerce - Core Class
 *
 * @version 4.5.20
 * @since   1.0.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Wdr\App\Controllers\Configuration;
use Wdr\App\Controllers\ManageDiscount;
use Wdr\App\Controllers\DiscountCalculator;
use Wdr\App\Helpers\Rule;
use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! class_exists( 'Alg_WC_PQ_Core' ) ) :

class Alg_WC_PQ_Core {

	public $attribute_taxonomies = array();
	/**
	 * Excluded product ids.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	public $excluded_pids = array();
	
	/**
	 * is enabled.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $enabled_priceunit_category = 'no';
	
	public $attr_taxonomies = array();
	
	public $force_on_loop_archive ='disabled';
	
	public $price_by_qty_qty_archive_enabled ='no';
	
	public $alg_wc_pq_exact_qty_allowed_section_enabled ='no';
	
	public $alg_wc_pq_force_on_single ='disabled';
	
	public $alg_wc_pq_force_on_loop ='disabled';
	

	/**
	 * Constructor.
	 *
	 * @version 4.5.20
	 * @since   1.0.0
	 * @todo    [fix] mini-cart number of items for decimal qty
	 * @todo    [dev] implement `is_any_section_enabled()`
	 * @todo    [dev] code refactoring: split this into more separate files (e.g. `class-alg-wc-pq-checker.php` etc.)
	 * @todo    [dev] (maybe) pre-load all options (i.e. `init_options()` and `$this->options`)
	 * @todo    [dev] (maybe) bundle products
	 * @todo    [feature] quantity per category (and/or tag) (i.e. not per individual products)
	 * @todo    [feature] implement `force_js_check_exact_qty()`
	 * @todo    [feature] add "treat variable as simple" option
	 * @todo    [feature] quantities by user roles
	 * @todo    [feature] add option to replace product's add to cart form on archives with form from the single product page
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_pq_enabled', 'yes' ) ) {

			// Disbale plugin by user who after purchase first order.
			if ( 'yes' === get_option( 'alg_wc_pq_disable_by_order_per_user', 'no' ) ) {
				if($this->has_purchased_first()){
					return;
				}
			}

			if ( ! $this->check_user_role() ) {
				return;
			}
			// Disable plugin by URL
			if ( '' != ( $urls = get_option( 'alg_wc_pq_disable_urls', '' ) ) ) {
				$urls = array_map( 'trim', explode( PHP_EOL, $urls ) );
				$url  = $_SERVER['REQUEST_URI'];
				if ( in_array( $url, $urls ) ) {

					return;
				}
				
				$ids = get_option( 'alg_wc_pq_disable_urls_excluded_pids', '' );
				$ids = array_map( 'trim', explode( ',', $ids ) );
				$this->excluded_pids = array_unique (array_merge ($this->excluded_pids, $ids));
			}
			
			// Disable plugin by category
			if ( '' != ( $disable_categories = get_option( 'alg_wc_pq_disable_by_category', '' ) ) ) {

				$d_cat_ids = get_option( 'alg_wc_pq_disable_category_excluded_pids', '' );
				if ( '' != $d_cat_ids ) {
					$d_cat_ids = array_map( 'trim', explode( ',', $d_cat_ids ) );
					$this->excluded_pids = array_unique (array_merge ($this->excluded_pids, $d_cat_ids));
				}
			}
			

			// Core
			$this->messenger = require_once( 'class-alg-wc-pq-messenger.php' );
			if (
				'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_exact_qty_allowed_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_exact_qty_disallowed_section_enabled', 'no' )
			) {
				if ( 'yes' === get_option( 'alg_wc_pq_validate_on_checkout', 'yes' ) ) {
					add_action( 'woocommerce_checkout_process',                                array( $this, 'check_order_quantities' ) );
				}
				add_action( 'woocommerce_before_cart',                                         array( $this, 'check_order_quantities' ) );
				if ( 'yes' === get_option( 'alg_wc_pq_stop_from_seeing_checkout', 'no' ) ) {
					add_action( 'wp',                                                          array( $this, 'block_checkout' ), PHP_INT_MAX );
				}
			}
			// Min/max
			if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) || 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
				add_filter( 'woocommerce_available_variation',                                 array( $this, 'set_quantity_input_min_max_variation' ), PHP_INT_MAX, 3 );
				if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
					add_filter( 'woocommerce_quantity_input_min',                              array( $this, 'set_quantity_input_min' ), PHP_INT_MAX, 2 );
					add_filter('woocommerce_store_api_product_quantity_minimum', 			   array( $this, 'store_api_product_min_quantity'), PHP_INT_MAX, 3);
					add_filter( 'woocommerce_is_purchasable', 								   array( $this, 'disable_purchased_products'), PHP_INT_MAX, 2 );
				}
				if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
					add_filter( 'woocommerce_quantity_input_max',                              array( $this, 'set_quantity_input_max' ), PHP_INT_MAX, 2 );
					add_filter('woocommerce_store_api_product_quantity_maximum', 			   array( $this, 'store_api_product_max_quantity'), PHP_INT_MAX, 3);
				}
				// Force on archives
				if ( 'disabled' != ( $this->force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ) ) ) {
					add_filter( 'woocommerce_loop_add_to_cart_args',                           array( $this, 'force_qty_on_loop' ), PHP_INT_MAX, 2 );
				}
			}
			
			// Step
			if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {

				add_action( 'admin_init', function() {
					global $pagenow;
					$current_postid = ( (isset($_GET['post']) && !empty($_GET['post'])) ? $_GET['post'] : 0);

					if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
						if ( !( ( $pagenow == 'post.php' ) && ( OrderUtil::get_order_type( $current_postid ) === 'shop_order' ) ) ) {
						add_filter( 'woocommerce_quantity_input_step',                                 array( $this, 'set_quantity_input_step' ), PHP_INT_MAX, 2 );
						
						} else if ( ( ( $pagenow == 'post.php' ) && ( OrderUtil::get_order_type( $current_postid ) === 'shop_order' ) ) && 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
							
							add_filter( 'woocommerce_quantity_input_step',                                 array( $this, 'admin_set_quantity_input_step' ), PHP_INT_MAX, 2 );
						}
					} else {
						if ( !( ( $pagenow == 'post.php' ) && ( get_post_type( $current_postid ) == 'shop_order' ) ) ) {
							add_filter( 'woocommerce_quantity_input_step',                                 array( $this, 'set_quantity_input_step' ), PHP_INT_MAX, 2 );
							
						} else if ( ( ( $pagenow == 'post.php' ) && ( get_post_type( $current_postid ) == 'shop_order' ) ) && 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
							
							add_filter( 'woocommerce_quantity_input_step',                                 array( $this, 'admin_set_quantity_input_step' ), PHP_INT_MAX, 2 );
						}
					}
				}, PHP_INT_MAX );
				
				add_filter('woocommerce_store_api_product_quantity_multiple_of', 				   array( $this, 'store_api_product_step_quantity'), PHP_INT_MAX, 3);
			}
			// Scripts
			require_once( 'class-alg-wc-pq-scripts.php' );
			// For cart & for `input_value`
			add_filter( 'woocommerce_quantity_input_args',                                     array( $this, 'set_quantity_input_args' ), PHP_INT_MAX, 2 );
			// Decimal qty
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
				add_action( 'init',                                                            array( $this, 'float_stock_amount' ), PHP_INT_MAX );
				add_action( 'save_post', 					  								   array( $this, 'save_stock_status_overwrite_thresold' ), PHP_INT_MAX, 3 );
				add_action( 'woocommerce_save_product_variation', 							   array( $this, 'save_variation_stock_status_overwrite_thresold'), PHP_INT_MAX, 2 );
				add_action( 'woocommerce_product_set_stock', 								   array( $this, 'alg_wc_woocommerce_product_set_stock_action' ), PHP_INT_MAX, 1 );
				add_action( 'woocommerce_variation_set_stock', 								   array( $this, 'alg_wc_woocommerce_product_set_stock_action' ), PHP_INT_MAX, 1 );
			}
			// Sold individually
			if ( 'yes' === get_option( 'alg_wc_pq_all_sold_individually_enabled', 'no' ) ) {
				add_filter( 'woocommerce_is_sold_individually',                                '__return_true', PHP_INT_MAX );
			}
			// Styling
			if ( '' != get_option( 'alg_wc_pq_qty_input_style', '' ) ) {
				add_action( 'wp_head',                                                         array( $this, 'style_qty_input' ), PHP_INT_MAX );
			}
			// Hide "Update cart" button
			if ( 'yes' === get_option( 'alg_wc_pq_qty_hide_update_cart', 'no' ) ) {
				add_action( 'wp_head',                                                         array( $this, 'hide_update_cart_button' ), PHP_INT_MAX );
			}
			// "Add to cart" validation
			if ( 'notice' === get_option( 'alg_wc_pq_add_to_cart_validation', 'disable' ) ) {
				add_filter( 'woocommerce_add_to_cart_validation',                              array( $this, 'validate_on_add_to_cart' ), PHP_INT_MAX, 4 );
			} elseif ( 'correct' === get_option( 'alg_wc_pq_add_to_cart_validation', 'disable' ) ) {
				add_filter( 'woocommerce_add_to_cart_quantity',                                array( $this, 'correct_on_add_to_cart' ), PHP_INT_MAX, 2 );
			} else {
				add_filter( 'woocommerce_add_to_cart_validation',                              array( $this, 'not_validate_on_add_to_cart' ), PHP_INT_MAX, 4 );
			}
			// Qty rounding
			if ( 'no' != ( $this->round_on_add_to_cart = get_option( 'alg_wc_pq_round_on_add_to_cart', 'no' ) ) ) {
				add_filter( 'woocommerce_add_to_cart_quantity',                                array( $this, 'round_on_add_to_cart' ), PHP_INT_MAX, 2 );
			}
			// Dropdown
			if ( 'yes' === get_option( 'alg_wc_pq_qty_dropdown', 'no' ) ) {
				add_filter( 'wc_get_template',                                                 array( $this, 'replace_quantity_input_template' ), PHP_INT_MAX, 5 );
			}else{
				if ( 'yes' === get_option( 'alg_wc_pq_replace_woocommerce_quantity_field', 'no' ) ) {
					add_filter( 'wc_get_template',                                                 array( $this, 'replace_quantity_input_template_html_five' ), PHP_INT_MAX, 5 );
				}
			}
			
			// Shortcodes
			require_once( 'class-alg-wc-pq-shortcodes.php' );
			// Quantity info
			$this->qty_info = require_once( 'class-alg-wc-pq-qty-info.php' );
			// Admin columns
			require_once( 'class-alg-wc-pq-admin.php' );
			
			$this->attribute_taxonomies = alg_wc_pq_wc_get_attribute_taxonomies();
			
			// Price by Qty
			if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_enabled', 'no' ) ) {
				add_action( 'wp_ajax_'        . 'alg_wc_pq_update_price_by_qty',               array( $this, 'ajax_price_by_qty' ) );
				add_action( 'wp_ajax_nopriv_' . 'alg_wc_pq_update_price_by_qty',               array( $this, 'ajax_price_by_qty' ) );
				
				$this->attr_taxonomies = $this->get_allowed_attribute_tax();
			}
			// Price by Qty
			// if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_enabled_variable', 'no' ) ) {
			// 	add_action( 'wp_ajax_'        . 'alg_wc_pq_update_price_by_qty_variable',               array( $this, 'ajax_price_by_qty_variable' ) );
			// 	add_action( 'wp_ajax_nopriv_' . 'alg_wc_pq_update_price_by_qty_variable',               array( $this, 'ajax_price_by_qty_variable' ) );
			// }
			// Order item meta
			if ( 'yes' === get_option( 'alg_wc_pq_save_qty_in_order_item_meta', 'no' ) ) {
				add_action( 'woocommerce_new_order_item',                                      array( $this, 'add_qty_to_order_item_meta' ), PHP_INT_MAX, 3 );
			}
			
			add_filter( 'woocommerce_paypal_line_item', array( $this, 'change_paypal_line_item_quantity_type' ), 100, 5 );
			
			add_filter( 'woocommerce_get_price_html', array( $this, 'pq_change_product_price_unit'), 99, 2 );
			
			$this->alg_wc_pq_qty_price_unit_enabled = get_option( 'alg_wc_pq_qty_price_unit_enabled', 'no' );
			if ( 'yes' === get_option( 'alg_wc_pq_qty_price_unit_enabled', 'no' ) ) {
				$this->enabled_priceunit_category = get_option( 'alg_wc_pq_qty_price_unit_category_enabled', 'no' );
				$this->enabled_priceunit_product = get_option( 'alg_wc_pq_qty_price_unit_product_enabled', 'no' );
				$this->enabled_priceunit_product_archive = get_option( 'alg_wc_pq_qty_price_unit_show_archive_enabled', 'no' );
				
				
				add_filter( 'woocommerce_cart_item_price', array( $this, 'pq_change_cart_product_price_unit'), 99, 3 );
				add_filter( 'woocommerce_email_order_item_quantity', array( $this, 'pq_filter_woocommerce_email_order_item_quantity'), 99, 2 );
			}
			
			
			
			$this->alg_wc_pq_qty_price_by_qty_position = get_option( 'alg_wc_pq_qty_price_by_qty_position', 'instead' );
			$this->alg_wc_pq_qty_price_by_qty_enabled = get_option( 'alg_wc_pq_qty_price_by_qty_enabled', 'no' );
			
			$this->price_by_qty_qty_archive_enabled = get_option( 'alg_wc_pq_qty_price_by_qty_qty_archive_enabled', 'no' );
			
			$this->alg_wc_pq_add_quantity_archive_enabled = get_option( 'alg_wc_pq_add_quantity_archive_enabled', 'no' );
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'alg_wc_add_archive_quantity_fields' ), PHP_INT_MAX, 2 );
			
			if ( 'yes' === get_option( 'alg_wc_pq_add_quantity_archive_enabled', 'no' ) ) {
				add_action( 'init', array( $this, 'alg_wc_quantity_handler' ) );
				add_action( 'init', array( $this, 'alg_wc_confirm_add' ) );
				add_action( 'wp_footer', array( $this, 'alg_wc_archive_quanitity_filed_style'), PHP_INT_MAX );
			}
			
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
				add_filter( 'wc_add_to_cart_message_html', array( $this, 'alg_wc_add_to_cart_message_html'), 10, 2 );
			}
			
			add_action( 'admin_init',     array( $this, 'alg_wc_pq_all_below_stock' ) );
			
			$this->force_on_loop_archive = get_option( 'alg_wc_pq_force_on_loop', 'disabled' );
			
			$this->alg_wc_pq_exact_qty_allowed_section_enabled = get_option( 'alg_wc_pq_exact_qty_allowed_section_enabled', 'no' );
			
			$this->alg_wc_pq_force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' );
			
			$this->alg_wc_pq_force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' );
			
			// get dropdown option
			if ( 'yes' === get_option( 'alg_wc_pq_qty_dropdown', 'no' ) ) {
				add_action( 'wp_ajax_'        . 'alg_wc_pq_update_get_dropdown_options',               array( $this, 'alg_wc_pq_update_get_dropdown_options' ) );
				add_action( 'wp_ajax_nopriv_' . 'alg_wc_pq_update_get_dropdown_options',               array( $this, 'alg_wc_pq_update_get_dropdown_options' ) );
			}
			
			add_action( 'wp_ajax_'        . 'alg_wc_pq_update_get_input_options',               array( $this, 'alg_wc_pq_update_get_input_options' ) );
			add_action( 'wp_ajax_nopriv_' . 'alg_wc_pq_update_get_input_options',               array( $this, 'alg_wc_pq_update_get_input_options' ) );
		}
	}

	/**
	 * has_purchased_first.
	 *
	 * @version 4.5.10
	 * @since   4.5.10
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 * @todo    [dev] customizable position (instead of the price; after the price, before the price etc.) (NB: maybe do not display for qty=1)
	 * @todo    [dev] add option to disable "price by qty" on initial screen (i.e. before qty input was changed)
	 * @todo    [dev] (maybe) add sale price
	 * @todo    [dev] (maybe) add optional "in progress" message (for slow servers)
	 */
	 
	 function has_purchased_first() {
		wp_cookie_constants();
		require_once ABSPATH . WPINC . '/pluggable.php';
		
		  if(!is_user_logged_in()) return false;

		  $transient = 'has_bought_'.get_current_user_id();
		  $has_bought = get_transient($transient);

		  if(!$has_bought) {

		  // Get all customer orders
		  $customer_orders = get_posts( array(
			'numberposts' => 1, // one order is enough
			'meta_key'    => '_customer_user',
			'meta_value'  => get_current_user_id(),
			'post_type'   => 'shop_order', // WC orders post type
			'post_status' => 'wc-completed', // Only orders with "completed" status
			'fields'      => 'ids', // Return Ids "completed"
		  ) );

			$has_bought = count($customer_orders) > 0 ? true : false;

			set_transient($transient, $has_bought, WEEK_IN_SECONDS);

		  }

		  // return "true" when customer has already at least one order (false if not)
		  return $has_bought; 
	}
	
	/**
	 * alg_wc_pq_update_get_input_options.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 * @todo    [dev] customizable position (instead of the price; after the price, before the price etc.) (NB: maybe do not display for qty=1)
	 * @todo    [dev] add option to disable "price by qty" on initial screen (i.e. before qty input was changed)
	 * @todo    [dev] (maybe) add sale price
	 * @todo    [dev] (maybe) add optional "in progress" message (for slow servers)
	 */
	function alg_wc_pq_update_get_input_options(){
		$return = array();
		$variation_id = $_REQUEST['variation_id'];
		$product_id = wp_get_post_parent_id($variation_id);
		$variation_exact = $this->get_product_exact_qty( $product_id, 'allowed', '', $variation_id );
		$min = $this->get_product_qty_min_max( $product_id, 1, 'min', $variation_id );
		$max = $this->get_product_qty_min_max( $product_id, 0, 'max', $variation_id );
		$step = $this->get_product_qty_step( $product_id, 1, $variation_id );
		
		$default = 0;
		
		$min_meta_key = 'alg_wc_pq_min_all_product';
		$max_meta_key = 'alg_wc_pq_max_all_product';
		$step_meta_key = 'alg_wc_pq_step';
		
		$min_attribute_enable = false;
		$max_attribute_enable = false;
		$step_attribute_enable = false;
		
		$attr_min = '';
		$attr_max = '';
		$attr_step = '';
		
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			if ( 'yes' === get_option( 'alg_wc_pq_min_per_attribute_item_quantity', 'no' ) ) {
				$min_attribute_enable = true;
			}
		}
		
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			if ( 'yes' === get_option( 'alg_wc_pq_max_per_attribute_item_quantity', 'no' ) ) {
				$max_attribute_enable = true;
			}
		}
		
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			if ( 'yes' === get_option( 'alg_wc_pq_step_per_attribute_item_quantity', 'no' ) ) {
				$step_attribute_enable = true;
			}
		}
		
		if( !empty( $this->attribute_taxonomies ) ){
			foreach( $this->attribute_taxonomies as $tax ) {
				$name = alg_pq_wc_attribute_taxonomy_name( $tax->attribute_name );
				$post_meta_slug = 'attribute_' . $name;
				$value = get_post_meta($variation_id, $post_meta_slug, true);
				if(!empty($value)){
					$term = get_term_by( 'slug', $value, $name );
					if(!empty($term)){
						$term_id = $term->term_id;
						$term_meta = get_option( "taxonomy_product_attribute_item_$term_id" );
						
						if($min_attribute_enable && empty($attr_min)){
							if (!empty($term_meta) && is_array($term_meta) && isset($term_meta[$min_meta_key]) && !empty($term_meta[$min_meta_key])) {
									$attr_min = $term_meta[$min_meta_key];
							}
						}
						
						if($max_attribute_enable && empty($attr_max)){
							if (!empty($term_meta) && is_array($term_meta) && isset($term_meta[$max_meta_key]) && !empty($term_meta[$max_meta_key])) {
									$attr_max = $term_meta[$max_meta_key];
							}
						}
						
						if($step_attribute_enable && empty($attr_step)){
							if (!empty($term_meta) && is_array($term_meta) && isset($term_meta[$step_meta_key]) && !empty($term_meta[$step_meta_key])) {
									$attr_step = $term_meta[$step_meta_key];
							}
						}
					}
				}
			}
		}
		
		$min = ($attr_min > 0 ? $attr_min : $min);
		$max = ($attr_max > 0 ? $attr_max : $max);
		$step = ($attr_step > 0 ? $attr_step : $step);
		
		if('reset_to_lowest_fixed' === get_option( 'alg_wc_pq_variation_change', 'do_nothing' )){
			$exact_qty  = $this->get_product_exact_qty( $product_id, 'allowed', '', $variation_id );
			$lowest_qty = 0;
			if(!empty($exact_qty)){
				$exact_qty_arr = explode(',', $exact_qty);
				$lowest_qty = min($exact_qty_arr);
				$default = $lowest_qty;
			}
		} else if('reset_to_default' === get_option( 'alg_wc_pq_variation_change', 'do_nothing' )){
			$default = $this->get_product_qty_default( $product_id, 0 );
		}
							
		if($default < $min){
			$default = $min;
		}

		if($max <= $min){
			$max = '';
		}
		
		if('disabled' === get_option( 'alg_wc_pq_force_on_single', 'disabled' )){
			$default = '';
		}
		
		$product = wc_get_product($variation_id);
		
		$return['min'] = $min;
		$return['max'] = $max;
		$return['step'] = $step;
		$return['default'] = $default;
		echo json_encode($return);
		die();
	}
	
	/**
	 * alg_wc_pq_update_get_dropdown_options.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 * @todo    [dev] customizable position (instead of the price; after the price, before the price etc.) (NB: maybe do not display for qty=1)
	 * @todo    [dev] add option to disable "price by qty" on initial screen (i.e. before qty input was changed)
	 * @todo    [dev] (maybe) add sale price
	 * @todo    [dev] (maybe) add optional "in progress" message (for slow servers)
	 */
	function alg_wc_pq_update_get_dropdown_options(){
		$return = '';
		$variation_id = $_REQUEST['variation_id'];
		$product_id = wp_get_post_parent_id($variation_id);
		$variation_exact = $this->get_product_exact_qty( $product_id, 'allowed', '', $variation_id );
		$product = wc_get_product($variation_id);
		
		// Labels
		$label_template_singular = '';
		$label_template_plural   = '';
		if ( $product && 'yes' === get_option( 'alg_wc_pq_qty_dropdown_label_template_is_per_product', 'no' ) ) {
			$product_or_parent_id    = $product_id;
			$label_template_singular = do_shortcode(get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_singular', true ));
			$label_template_plural   = do_shortcode(get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_plural',   true ));
		}
		if ( '' === $label_template_singular ) {
			$label_template_singular = do_shortcode(get_option( 'alg_wc_pq_qty_dropdown_label_template_singular', '%qty%' ));
		}
		if ( '' === $label_template_plural ) {
			$label_template_plural   = do_shortcode(get_option( 'alg_wc_pq_qty_dropdown_label_template_plural',   '%qty%' ));
		}
		
		ob_start();
		if(!empty($variation_exact)){
			$fixed_qty = $this->process_exact_qty_option( $variation_exact );
			if(!empty($fixed_qty) && count($fixed_qty) > 0){
				foreach($fixed_qty as $qty){
					$price = wc_get_price_to_display( $product , array( 'qty' => $qty ) );
					$display_price = wc_price( $price );
					?><option value="<?php echo esc_attr( $qty ); ?>" ><?php
						echo str_replace( array('%qty%','%price%'), array($this->get_quantity_with_sep( $qty ), $display_price), ( $qty < 2 ? $label_template_singular : $label_template_plural ) ); ?></option><?php
				}
			}
		}
		$return = ob_get_contents();
		ob_end_clean();
		echo $return;
		die();
	}
	
	/**
	 * alg_create_products_xml.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 * @todo    [dev] with wp_safe_redirect there is no notice displayed
	 */
	function alg_wc_pq_all_below_stock() {
		if ( isset( $_GET['alg_wc_pq_all_below_stock'] ) ) {
			$args = array(
				'post_type' => 'product',
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
			$loop = new WP_Query( $args );
			if ( $loop->have_posts() ): while ( $loop->have_posts() ): $loop->the_post();
			$id = get_the_ID();
			update_post_meta($id, '_alg_wc_pq_min_allow_selling_below_stock', 'yes');
			endwhile; endif; wp_reset_postdata();
			wp_safe_redirect( remove_query_arg( 'alg_wc_pq_all_below_stock' ) );
			exit;
		}
	}
	
	function alg_wc_add_to_cart_message_html( $message, $products ) {
		
		$count = 0;
		$titles = array();
		foreach ( $products as $product_id => $qty ) {
			$titles[] = ( $qty > 1 ? $qty . ' &times; ' : '' ) . sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'product-quantity-for-woocommerce' ), strip_tags( get_the_title( $product_id ) ) );
			$count += $qty;
		}

		$titles     = array_filter( $titles );
		$added_text = sprintf( _n('%s have been added to cart.', '%s have been added to cart.', $count, 'product-quantity-for-woocommerce' ), wc_format_list_of_items( $titles ) );
		$message    = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( wc_get_page_permalink( 'cart' ) ), esc_html__( 'View cart', 'product-quantity-for-woocommerce' ), esc_html( $added_text ) );

		
		return $message;
	}
	
	/**
	 * alg_wc_archive_quanitity_filed_style.
	 *
	 * @version 1.3.9
	 * @since   1.3.3
	 */
	public function alg_wc_archive_quanitity_filed_style() {
		?>
		<style>
			input.quantity-alg-wc{
				float:left;
				width: 50%;
			}
		</style>
		<?php
	}
	/**
	 * alg_wc_quantity_handler.
	 *
	 * @version 1.3.9
	 * @since   1.3.3
	 */
	public function alg_wc_quantity_handler() {
		wc_enqueue_js( '
		jQuery(function($) {
		$("form.cart").on("change", "input.qty", function() {
        $(this.form).find("[data-quantity]").attr("data-quantity", this.value);  //used attr instead of data, for WC 4.0 compatibility
		});
		' );

		wc_enqueue_js( '
		$(document.body).on("adding_to_cart", function() {
			$("a.added_to_cart").remove();
		});
		});
		' );
	}

	/**
	 * alg_wc_confirm_add.
	 *
	 * @version 1.3.9
	 * @since   1.3.3
	 */
	public function alg_wc_confirm_add() {
		wc_enqueue_js( '
		jQuery(document.body).on("added_to_cart", function( data ) {

		// jQuery(".added_to_cart").after("<p class=\'confirm_add\'>Item Added</p>");
		});

		' );
	}
	
	/**
	 * alg_wc_pq_qty_dropdown_is_enabled.
	 *
	 * @version 1.3.9
	 * @since   1.3.3
	 */
	function alg_wc_pq_price_by_qty_is_disable( $product ) {
		if ( ! $product ) {
			return false;
		}
		$alg_wc_pq_price_by_qty_enable_per_category = get_option( 'alg_wc_pq_price_by_qty_enable_per_category', 'no' );
		$alg_wc_pq_price_by_qty_per_category_categories = get_option( 'alg_wc_pq_price_by_qty_per_category_categories', array() );
		
		if($alg_wc_pq_price_by_qty_enable_per_category == 'yes' ) {
			if(!empty($alg_wc_pq_price_by_qty_per_category_categories)) {
				if ( !empty($product) && $product->get_id() > 0 && ! is_admin() ) {
					$product_id = $product->get_id();
					$product_cats_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
					if(!empty($product_cats_ids)) {
						foreach($product_cats_ids as $cat_id) {
							if(in_array($cat_id, $alg_wc_pq_price_by_qty_per_category_categories)) {
								return true;
							}
						}
					}
					
				}
			}
		}
		return false;
	}
	
	/**
	 * alg_wc_add_quantity_fields.
	 *
	 * @version 1.3.9
	 * @since   1.3.3
	 */
	function alg_wc_add_archive_quantity_fields( $html, $product ) {
		if($this->alg_wc_pq_qty_price_by_qty_enabled === 'yes' && $this->alg_wc_pq_qty_price_by_qty_position === 'before_add_to_cart'){
			if($this->price_by_qty_qty_archive_enabled == 'yes'){
				$html = '<p class="alg-wc-pq-price-display-by-qty">'.$this->alg_wc_pq_update_price_by_qty_on_load($product, '').'</p>' . $html;
			}
		}
		if($this->alg_wc_pq_add_quantity_archive_enabled === 'yes'){
			//add quantity field only to simple products
			if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
				$disableClass = '';
				if(alg_wc_pq()->core->alg_wc_pq_price_by_qty_is_disable($product)){
					$disableClass = ' disable_price_by_qty';
				}
				$args = array('classes'=>' qty quantity-alg-wc'.$disableClass);
				$html = '';
				if($this->alg_wc_pq_qty_price_by_qty_enabled === 'yes' && $this->alg_wc_pq_qty_price_by_qty_position === 'before_add_to_cart'){
					$html = '<p class="alg-wc-pq-price-display-by-qty">'.$this->alg_wc_pq_update_price_by_qty_on_load($product, '').'</p>';
				}
				//rewrite form code for add to cart button
				$html .= '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
				$html .= woocommerce_quantity_input( $args, $product, false );
				
				if($this->force_on_loop_archive=='min'){
					$data_quantity = $this->get_product_qty_min_max( $product->get_id(), 1, 'min' );
				}else if($this->force_on_loop_archive=='max'){
					$data_quantity = $this->get_product_qty_min_max( $product->get_id(), 1, 'max' );
				}else if($this->force_on_loop_archive=='default'){
					$data_quantity = $this->get_product_qty_default( $product->get_id(), 1 );
				}else{
					$data_quantity = $this->get_product_qty_min_max( $product->get_id(), 1, 'min' );
				}
				
				$html .= '<button type="submit" data-quantity="'. $data_quantity . '" data-product_id="' . $product->get_id() . '" class="button alt ajax_add_to_cart add_to_cart_button product_type_simple">' . esc_html( $product->add_to_cart_text() ) . '</button>';
				$html .= '</form>';
			}
		}
		if($this->alg_wc_pq_qty_price_by_qty_enabled === 'yes' && $this->alg_wc_pq_qty_price_by_qty_position === 'after_add_to_cart'){
			if($this->price_by_qty_qty_archive_enabled == 'yes'){
				$html .= '<p class="alg-wc-pq-price-display-by-qty">'.$this->alg_wc_pq_update_price_by_qty_on_load($product, '').'</p>';
			}
		}
		return $html;
	}
	/**
	 * alg_wc_pq_qty_dropdown_is_enabled.
	 *
	 * @version 1.3.9
	 * @since   1.3.3
	 */
	function alg_wc_pq_qty_dropdown_is_disable( $product ) {
		$alg_wc_pq_qty_dropdown_enable_per_category = get_option( 'alg_wc_pq_qty_dropdown_enable_per_category', 'no' );
		$alg_wc_pq_qty_dropdown_per_category_categories = get_option( 'alg_wc_pq_qty_dropdown_per_category_categories', array() );
		
		if($alg_wc_pq_qty_dropdown_enable_per_category == 'yes' ) {
			if(!empty($alg_wc_pq_qty_dropdown_per_category_categories)) {
				if ( !empty($product) && $product->get_id() > 0 && ! is_admin() ) {
					$product_id = $product->get_id();
					$product_cats_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
					if(!empty($product_cats_ids)) {
						foreach($product_cats_ids as $cat_id) {
							if(in_array($cat_id, $alg_wc_pq_qty_dropdown_per_category_categories)) {
								return true;
							}
						}
					}
					
				}
			}
		}
		return false;
	}
	
	
	/**
	 * set_quantity_input_price_unit.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function set_quantity_input_price_unit($default, $product) {
		$unit = '';
		if($this->alg_wc_pq_qty_price_unit_enabled === 'yes'){
			$productType =  $product->get_type();
			if( $this->is_show_unit() ) {
				$unit = get_option( 'alg_wc_pq_qty_price_unit', '' );
				if ( !empty($product) && $product->get_id() > 0 && ! is_admin() ) {
					$product_id = $product->get_id();
					
					if( $this->enabled_priceunit_category == 'yes' || $this->enabled_priceunit_product == 'yes') {
						$product_unit = $this->get_term_price_unit( $product_id );
						$unit = (!empty($product_unit) ? $product_unit : $unit );
					}
				}
			}
		}
		return do_shortcode($unit);
	}
	
	/**
	 * pq_filter_woocommerce_email_order_item_quantity.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function pq_filter_woocommerce_email_order_item_quantity( $qty_display, $item ) {
		
		$unit = get_option( 'alg_wc_pq_qty_price_unit', '' );
		$product = $item->get_product();
		if ( is_object( $product ) ) {
			$product_id = $product->get_id();
			if($product_id > 0){
				
				
				if( $this->is_show_unit() ) {
					
					if( $this->enabled_priceunit_category == 'yes' || $this->enabled_priceunit_product == 'yes') {
						$product_unit = $this->get_term_price_unit( $product_id );
						$unit = (!empty($product_unit) ? $product_unit : $unit );
					}
				}
				
				if( !empty( $unit ) ) {
					return $qty_display . __( ' ( Price unit: ', 'product-quantity-for-woocommerce' ) . $unit . ' )';
				}
			}
		}
		
		return $qty_display;
	}
	
	/**
	 * pq_change_product_price_unit.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function pq_change_product_price_unit( $price, $product ) {

		if( $this->disable_product_id_by_url_option( $product->get_id() ) ) {
			return $price;
		}
		
		$productType =  $product->get_type();
		
		if($this->alg_wc_pq_qty_price_unit_enabled === 'yes' && $productType=='variation'){
			$unit = $this->alg_wc_pq_get_product_price_unit($product, 1, true);
			return $price . $unit;
		}
		
		$data_quantity = 1;
		
		if( (is_shop() || is_product_tag() || is_product_category() || is_front_page() || is_home() || (defined('DOING_AJAX') && DOING_AJAX)) ) {
			if($this->force_on_loop_archive=='min'){
				$data_quantity = $this->get_product_qty_min_max( $product->get_id(), 1, 'min' );
			}else if($this->force_on_loop_archive=='max'){
				$data_quantity = $this->get_product_qty_min_max( $product->get_id(), 1, 'max' );
			}else if($this->force_on_loop_archive=='default'){
				$data_quantity = $this->get_product_qty_default( $product->get_id(), 1 );
			}else{
				$data_quantity = $this->get_product_qty_min_max( $product->get_id(), 1, 'min' );
			}
		}
		
		
		if($this->alg_wc_pq_qty_price_by_qty_enabled === 'yes' && $this->alg_wc_pq_qty_price_by_qty_position === 'instead'){
			if ( 'disabled' != ( $force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' ) ) && is_product()) {
				
				if($force_on_single == 'min'){
					$qty = $this->get_product_qty_min_max( $product->get_id(), 1, 'min' );
				}else if($force_on_single == 'max'){
					$qty = $this->get_product_qty_min_max( $product->get_id(), 0, 'max' );
				}else if($force_on_single == 'default'){
					$qty = $this->get_product_qty_default( $product->get_id(), 1 );
				}
				if($qty > 1){
					$finalprice = $this->alg_wc_pq_update_price_by_qty_on_load($product, '', $qty);
					if(!empty($finalprice)){
						return $finalprice;
					}
				}
			}
		}
		
		if( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_qty_archive_enabled', 'no' ) ) {
			if( (is_shop() || is_product_tag() || is_product_category() ) ) {
				if($this->alg_wc_pq_qty_price_by_qty_enabled === 'yes' && $this->alg_wc_pq_qty_price_by_qty_position === 'before'){
					$price = '<p class="alg-wc-pq-price-display-by-qty">'.$this->alg_wc_pq_update_price_by_qty_on_load($product, '').'</p>' . $price;
				}
			}
		}
		if($this->alg_wc_pq_qty_price_unit_enabled === 'yes'){
			
			if( $this->is_show_unit() ) {
				$unit = get_option( 'alg_wc_pq_qty_price_unit', '' );
				if ( !empty($product) && $product->get_id() > 0 && !empty($price) && ! is_admin() ) {
					$product_id = $product->get_id();
					if($productType=='variation'){
						$product_id = $product->get_variation_id();
					}
					if( $this->enabled_priceunit_category == 'yes' || $this->enabled_priceunit_product == 'yes') {
						$product_unit = $this->get_term_price_unit( $product_id );
						$unit = (!empty($product_unit) ? $product_unit : $unit );
					}
					$price .= ' <span class="alg_pq_wc_price_unit">'.do_shortcode($unit).'</span>';
				}
			}
		}
		if( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_qty_archive_enabled', 'no' ) ) {
			if( (is_shop() || is_product_tag() || is_product_category() || is_front_page() || is_home() || (defined('DOING_AJAX') && DOING_AJAX)) ) {
				if($this->alg_wc_pq_qty_price_by_qty_enabled === 'yes' && $this->alg_wc_pq_qty_price_by_qty_position === 'after'){
					$price .= '<p class="alg-wc-pq-price-display-by-qty">'.$this->alg_wc_pq_update_price_by_qty_on_load($product, '', $data_quantity).'</p>';
				}
				if($this->alg_wc_pq_qty_price_by_qty_enabled === 'yes' && $this->alg_wc_pq_qty_price_by_qty_position === 'instead'){
					$price = $this->alg_wc_pq_update_price_by_qty_on_load($product, $price, $data_quantity);
				}
			}
		}
		
		return $price;
	}
	
	/**
	 * pq_change_cart_product_price_unit.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function pq_change_cart_product_price_unit( $price, $cart_item, $cart_item_key ) {
		
		$unit = get_option( 'alg_wc_pq_qty_price_unit', '' );
		if ( isset( $cart_item['product_id'] ) && !empty( $cart_item['product_id'] ) && !empty($price) && ! is_admin() ) {
			
			$product_id = $cart_item['product_id'];
			if( $this->disable_product_id_by_url_option( $product_id ) ) {
				return $price;
			}
			if( $this->enabled_priceunit_category=='yes' || $this->enabled_priceunit_product == 'yes' ) {
				$product_unit = $this->get_term_price_unit( $product_id );
				$unit = (!empty($product_unit) ? $product_unit : $unit );
			}
			$price .= ' <span class="alg_pq_wc_price_unit">'.do_shortcode($unit).'</span>';
		}
		return $price;
	}
	
	/**
	 * is_show_unit.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function is_show_unit() {
		if( is_product() ) {
			return true;
		}else if( $this->enabled_priceunit_product_archive == 'yes' ) {
			if( is_shop() || is_product_category() || is_product_tag() ) {
				return true;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * get_term_price_unit.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function get_term_price_unit ( $product_id  ) {
		$terms = get_the_terms( $product_id, 'product_cat' );
		$product_meta = get_post_meta($product_id, '_alg_wc_pq_price_unit', true);
		if( !empty( $product_meta ) && $this->enabled_priceunit_product == 'yes' ) {
			return $product_meta;
		}else if( !empty($terms) ) {
			foreach ($terms as $term) {
				$t_id = $term->term_id;
				$term_meta = get_option( "taxonomy_product_cat_$t_id" );
				if (!empty($term_meta) && is_array($term_meta))
				{
					if(isset( $term_meta['alg_wc_pq_category_price_unit'] ) && !empty( $term_meta['alg_wc_pq_category_price_unit'] )) {
						return $term_meta['alg_wc_pq_category_price_unit'];
					}
				}
			}
		}
		
		return '';
	}
	
	/**
	 * check_user_role.
	 *
	 * @version 1.3.9
	 * @since   1.3.3
	 */
	function check_user_role() {
		return apply_filters( 'alg_wc_pq_check_user_role', true, $this );
	}
	
	/**
	 * disable_product_id_by_url_option.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] maybe add option to overwrite the default `_qty` meta (as it's `absint( $order_item['qty'] )`)
	 */
	function disable_product_id_by_url_option( $product_id ) {
		if ( count($this->excluded_pids) > 0 ) {
			if ( in_array( $product_id, $this->excluded_pids ) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * change_paypal_line_item_quantity_type.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function change_paypal_line_item_quantity_type( $item, $item_name, $quantity, $amount, $item_number ) {
		if (strpos($quantity, '.')) {
			$quantity = (float) $quantity;
			$totamount = $amount * $quantity;
			$item['quantity'] = 1;
			$item['amount'] = $totamount;
		}
		return $item;
	}

	/**
	 * add_qty_to_order_item_meta.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] maybe add option to overwrite the default `_qty` meta (as it's `absint( $order_item['qty'] )`)
	 */
	function add_qty_to_order_item_meta( $item_id, $order_item, $order_id ) {
		wc_add_order_item_meta( $item_id, get_option( 'alg_wc_pq_save_qty_in_order_item_meta_key', '_alg_wc_pq_qty' ), $order_item['qty'] );
	}
	
	

	/**
	 * round_on_add_to_cart.
	 *
	 * @version 1.6.2
	 * @since   1.6.2
	 * @todo    [feature] (maybe) add `precision` option
	 */
	function round_on_add_to_cart( $quantity, $product_id ) {
		$func = $this->round_on_add_to_cart;
		return $func( $quantity );
	}

	/**
	 * force_qty_on_loop.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function force_qty_on_loop( $args, $product ) {
		$args['quantity'] = ( 'min' === $this->force_on_loop ?
			$this->set_quantity_input_min( $args['quantity'], $product ) : $this->set_quantity_input_max( $args['quantity'], $product ) );
		return $args;
	}
	
	/**
	 * replace_quantity_input_template_html_five.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function replace_quantity_input_template_html_five( $located, $template_name, $args, $template_path, $default_path ){
		if ( 'global/quantity-input.php' === $template_name ) {
			return alg_wc_pq()->plugin_path() . '/includes/templates/global/quantity-html5-input.php';
		}
		return $located;
	}

	/**
	 * replace_quantity_input_template.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function replace_quantity_input_template( $located, $template_name, $args, $template_path, $default_path ) {
		if( (is_shop() || is_product_tag() || is_product_category() ) ) {
			if( 'yes' === get_option( 'alg_wc_pq_qty_dropdown_qty_archive_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_add_quantity_archive_enabled', 'no' ) ){
				if ( 'global/quantity-input.php' === $template_name ) {
					return alg_wc_pq()->plugin_path() . '/includes/templates/global/quantity-input.php';
				}
			}
		}else if( is_cart() ){
			if( 'no' === get_option( 'alg_wc_pq_qty_dropdown_disable_dropdown_on_cart', 'no' ) ){
				if ( 'global/quantity-input.php' === $template_name ) {
					return alg_wc_pq()->plugin_path() . '/includes/templates/global/quantity-input.php';
				}
			}
		}else{
			if ( 'global/quantity-input.php' === $template_name ) {
				return alg_wc_pq()->plugin_path() . '/includes/templates/global/quantity-input.php';
			}
		}
		return $located;
	}
	
	/**
	 * get_dropdown_option_label.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_dropdown_option_label( $id = 0 ) {
		if ( empty($id) ) {
			return '';
		}
		$product = wc_get_product( $id ); 
		$label_template_singular = '';
		$label_template_plural   = '';
		if ( $product && 'yes' === get_option( 'alg_wc_pq_qty_dropdown_label_template_is_per_product', 'no' ) ) {
			$product_or_parent_id    = ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
			$label_template_singular = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_singular', true );
			$label_template_plural   = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_plural',   true );
		}
		if ( '' === $label_template_singular ) {
			$label_template_singular = get_option( 'alg_wc_pq_qty_dropdown_label_template_singular', '%qty%' );
		}
		if ( '' === $label_template_plural ) {
			$label_template_plural   = get_option( 'alg_wc_pq_qty_dropdown_label_template_plural',   '%qty%' );
		}
		return array( 'singular'=> $label_template_singular , 'plural'=> $label_template_plural );
	}
	
	/**
	 * alc_wg_get_cart_item_quantities.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 */
	 
	function alc_wg_get_cart_item_quantities($group_by_variation = false) {
		$quantities = array();
		$woosb_keys = array();
		
		if(!empty(WC()->cart)){
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				
				if(isset($values['woosb_keys']) && !empty($values['woosb_keys'])){
					$woosb_keys = array_merge($woosb_keys, $values['woosb_keys']);
				}

				if(in_array($cart_item_key, $woosb_keys))
				{
					continue;
				}
				
				if(isset($values['woosb_ids']) && !empty($values['woosb_ids'])){
					$total_qty = 0;
					$ids_qty = $values['woosb_ids'];
					$ids_qty_arr = explode(',', $ids_qty);
					if(isset($ids_qty_arr) && !empty($ids_qty_arr)){
						foreach($ids_qty_arr as $ids_qty_item){
							$parts = explode('/', $ids_qty_item);
							$bundle_item_qty = $parts[1] * $values['quantity'];
							$total_qty = $total_qty + $bundle_item_qty;
						}
						$values['quantity'] = $total_qty;
					}
				}
				
				$product = $values['data']; $product_id = ( isset($values['product_id']) ? $values['product_id'] : 0 );
				if(!$group_by_variation){
					/* $quantities[ $product->get_stock_managed_by_id() ] = isset( $quantities[ $product->get_stock_managed_by_id() ] ) ? $quantities[ $product->get_stock_managed_by_id() ] + $values['quantity'] : $values['quantity']; */  $quantities[ $product_id ] = isset( $quantities[ $product_id ] ) ? $quantities[ $product_id ] + $values['quantity'] : $values['quantity'];
				}else{
					$product_id = ( isset($values['product_id']) ? $values['product_id'] : 0 );
					$variation_id = ( isset($values['variation_id']) ? $values['variation_id'] : 0 );
					if($variation_id == 0){
						$variation_id = $product_id;
					}
					$quantities[ $variation_id ] = isset( $quantities[ $variation_id ] ) ? $quantities[ $variation_id ] + $values['quantity'] : $values['quantity'];
				}
			}
		}

		return $quantities;
	}

	/**
	 * get_cart_item_quantities.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 */
	function get_cart_item_quantities( $product_id = 0, $quantity = 0 , $sort_by_sumup_variation = false) {
		
		if ( ! isset( WC()->cart ) ) {
			$cart_item_quantities = array();
		} else {
			// $cart_item_quantities = WC()->cart->get_cart_item_quantities();
			$cart_item_quantities = $this->alc_wg_get_cart_item_quantities();

			if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
				$cart_item_quantities = array();
			}
		}
		
		if ( count($cart_item_quantities) > 0 ) {
			if ( count( $this->excluded_pids ) > 0 ) {
				foreach ( $this->excluded_pids as $id ) {
					unset($cart_item_quantities[$id]);
				}
			}				
		}
		

		if ( 0 != $product_id ) {
			if ( ! isset( $cart_item_quantities[ $product_id ] ) ) {
				$cart_item_quantities[ $product_id ] = 0;
			}
			$cart_item_quantities[ $product_id ] += $quantity;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_sum_variations', 'no' ) || $sort_by_sumup_variation ) {
			if ( 0 != $product_id && ( $product = wc_get_product( $product_id ) ) ) {
				$children   = $product->get_children();
				$qty_to_add = 0;
				foreach ( $cart_item_quantities as $cart_item_product_id => $cart_item_quantity ) {
					if ( $cart_item_product_id != $product_id && in_array( $cart_item_product_id, $children ) ) {
						$qty_to_add += $cart_item_quantity;
						$cart_item_quantities[ $cart_item_product_id ] = 0;
					}
				}
				$cart_item_quantities[ $product_id ] += $qty_to_add;
			}
		}
		return $cart_item_quantities;
	}

	/**
	 * correct_on_add_to_cart.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [fix] (important) fix "X products have been added to your cart." notice
	 * @todo    [dev] (important) maybe need to add a notice on corrected qty = 0
	 * @todo    [dev] (important) (maybe) "Exact quantities" should be executed first? (same in `validate_on_add_to_cart()`, `block_checkout()` and `check_order_quantities()`)
	 */
	function correct_on_add_to_cart( $quantity, $product_id ) {
		// disable if url excluded
		if( $this->disable_product_id_by_url_option($product_id) ) {
			return $quantity;
		}
		// Prepare data
		$cart_item_quantities = $this->get_cart_item_quantities( $product_id, $quantity );
		$cart_total_quantity  = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		$cart_item_quantity   = $cart_item_quantities[ $product_id ];
		// Min & Max
		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
				// Cart total quantity
				if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
					return ( $this->get_min_max_cart_total_qty( $min_or_max ) - ( $cart_total_quantity - $quantity ) );
				}
				// Per item quantity
				if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
					return $this->get_product_qty_min_max( $product_id, 0, $min_or_max );
				}
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			// Cart total
			if ( $quantity != ( $fixed_qty = $this->check_step_cart_total_qty( $cart_total_quantity, true, $quantity ) ) ) {
				return $fixed_qty;
			}
			// Products
			if ( $quantity != ( $fixed_qty = $this->check_product_step( $product_id, $quantity, true ) ) ) {
				return $fixed_qty;
			}
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				if ( $quantity != ( $fixed_qty = $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, true ) ) ) {
					return $fixed_qty;
				}
			}
		}
		return $quantity;
	}
	
	/**
	 * get_cartitem_by_category.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	 
	function get_cartitem_by_category()
	{
		$category = array();
		// $cart_all_item_quantities = WC()->cart->get_cart_item_quantities();
		$cart_all_item_quantities = $this->alc_wg_get_cart_item_quantities();

		if (isset($cart_all_item_quantities) && !empty($cart_all_item_quantities))
		{
			foreach ($cart_all_item_quantities as $product_id => $product_count)
			{
				if( $this->disable_product_id_by_url_option($product_id) ) {
					continue;
				}
				$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
				if(isset($term_list) && count($term_list) > 0)
				{
					foreach ($term_list as $term)
					{
						if(isset($category[$term]) && !empty($category[$term]))
						{
							$category[$term] = (int) $category[$term] + $product_count;
						}
						else
						{
							$category[$term] = $product_count;
						}
					}
				}
			}
		}

		if (count($category) > 0)
		{
			return $category;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * get_cartitem_by_product_attribute.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	 
	function get_cartitem_by_product_attribute()
	{
		$quantities = array();
		$woosb_keys = array();
		$cart_all_item_quantities = array();
		
		if(!empty(WC()->cart)){
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				
				if(isset($values['woosb_keys']) && !empty($values['woosb_keys'])){
					$woosb_keys = array_merge($woosb_keys, $values['woosb_keys']);
				}

				if(in_array($cart_item_key, $woosb_keys))
				{
					continue;
				}
				
				$product = $values['data'];
				$pid = ( isset($values['product_id']) ? $values['product_id'] : 0 );
				$vid = ( isset($values['variation_id']) ? $values['variation_id'] : 0 );
				
				$quantities[$pid]['product_count'] = isset( $quantities[ $pid ]['product_count'] ) ? (float) $quantities[ $pid ]['product_count'] + $values['quantity'] : $values['quantity'];
				$cart_all_item_quantities[$pid] = $quantities[ $pid ]['product_count'];
				if($vid > 0){
					$quantities[ $pid ]['child']['id'][] = $vid;
					$quantities[ $pid ]['child']['quantity'][] = $values['quantity'];
				}
				
			}
		}

		$category = array();
		
		if (isset($cart_all_item_quantities) && !empty($cart_all_item_quantities))
		{
			foreach ($cart_all_item_quantities as $product_id => $product_count)
			{
				if( $this->disable_product_id_by_url_option($product_id) ) {
					continue;
				}

				$isvariation = 'no';
				$all_variation_attr_value = array();
				if(isset($quantities[ $product_id ]['child']) && !empty($quantities[ $product_id ]['child'])){
					 $attr = get_post_meta($product_id, '_product_attributes', true);

					 if(isset($attr) && !empty($attr)){
						 foreach($attr as $att_key=>$att_value){
							 if($att_value['is_variation'] == 1){
								 foreach($quantities[ $product_id ]['child']['id'] as $ky=>$chid){
									 $key_name = 'attribute_' . $att_key;
									 $value = get_post_meta($chid, $key_name, true);
									 if(!empty($value)){
										$attr_detail = get_term_by( 'slug', $value, $att_key );
										if(isset($attr_detail) &&  !empty($attr_detail)){
											$all_variation_attr_value[$att_key][] = $attr_detail->term_id;
											$all_variation_attr_value[$att_key]['quantity'][$attr_detail->term_id] = $quantities[ $product_id ]['child']['quantity'][$ky];
										}
									 }
								 }
							 }
						 }
					 }
					 $isvariation = 'yes';
				}

				if( !empty( $this->attribute_taxonomies ) ){
					foreach( $this->attribute_taxonomies as $tax ) {
						$name = alg_pq_wc_attribute_taxonomy_name( $tax->attribute_name );
						
							$term_list = wp_get_post_terms($product_id, $name, array('fields'=>'ids'));
							if(isset($all_variation_attr_value[$name])){
								
								if(isset($term_list) && count($term_list) > 0)
								{
									foreach ($term_list as $term)
									{
										if(in_array($term, $all_variation_attr_value[$name])){
											if(isset($category[$term]) && !empty($category[$term]))
											{
												$category[$term] = (int) $category[$term] + $all_variation_attr_value[$name]['quantity'][$term];
											}
											else
											{
												$category[$term] = $all_variation_attr_value[$name]['quantity'][$term];
											}
										}
									}
								}
							}else{
								if(isset($term_list) && count($term_list) > 0)
								{
									foreach ($term_list as $term)
									{
										if(isset($category[$term]) && !empty($category[$term]))
										{
											$category[$term] = (int) $category[$term] + $product_count;
										}
										else
										{
											$category[$term] = $product_count;
										}
									}
								}
							}
					}
				}
			}
		}
		
		
		if (count($category) > 0)
		{
			return $category;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * get_cartitem_groupby_parent_id.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function get_cartitem_groupby_parent_id()
	{
		$main_products = array();
		// $cart_all_item_quantities = WC()->cart->get_cart_item_quantities();
		$cart_all_item_quantities = $this->alc_wg_get_cart_item_quantities(true);

		if (isset($cart_all_item_quantities) && !empty($cart_all_item_quantities))
		{
			foreach ($cart_all_item_quantities as $product_id => $product_count)
			{
				if( $this->disable_product_id_by_url_option($product_id) ) {
					continue;
				}
				$product = wc_get_product( $product_id );
				$product_type = $product->post_type;
				if ( $product_type == 'product_variation' ) {
					$product_id = $product->get_parent_id();
					$allvar_cart_item_quantities = $this->get_cart_item_quantities( $product_id, 0, true );
					$cart_item_quantity = $allvar_cart_item_quantities[ $product_id ];
					
					if( ! in_array( $product_id, array_keys($main_products) ) ) {
						$main_products[$product_id] = $cart_item_quantity;
					}
					
				}
			}
		}

		if (count($main_products) > 0)
		{
			return $main_products;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * not_validate_on_add_to_cart.
	 *
	 * @version 4.5.9
	 * @since   4.5.9
	 * @todo    [dev] (maybe) separate messages for min/max (i.e. different from "cart" messages)?
	 */
	function not_validate_on_add_to_cart( $passed, $product_id, $quantity, $variation_id = 0 ) {
		if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity']) && $_POST['quantity'] <= 0 ){
			$_notice = __( 'Please choose the quantity of item add to cart. It should be more than zero', 'product-quantity-for-woocommerce' );
			wc_add_notice( $_notice, 'error' );
			return false;
		}
		
		// Passed
		return $passed;
	}

	/**
	 * validate_on_add_to_cart.
	 *
	 * @version 4.5.9
	 * @since   1.4.0
	 * @todo    [dev] (maybe) separate messages for min/max (i.e. different from "cart" messages)?
	 */
	function validate_on_add_to_cart( $passed, $product_id, $quantity, $variation_id = 0 ) {

		if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity']) && $_POST['quantity'] <= 0 ){
			$_notice = __( 'Please choose the quantity of item add to cart. It should be more than zero', 'product-quantity-for-woocommerce' );
			wc_add_notice( $_notice, 'error' );
			return false;
		}

		// disable if url excluded
		if( $this->disable_product_id_by_url_option( $product_id ) ) {
			return $passed;
		}
		// Prepare data
		if ( ! isset( $cart_item_quantities ) ) {
			$cart_item_quantities = $this->get_cart_item_quantities( $product_id, $quantity );
			$cart_total_quantity  = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
			$cart_item_quantity   = $cart_item_quantities[ $product_id ];
		}
		
		$cartitem_by_category = $this->get_cartitem_by_category();

		// get categories 
		$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
		
		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			if ( 'yes' === get_option( 'alg_wc_pq_' .$min_or_max. '_section_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_per_cat_item_quantity_per_product' , 'no' ) ) {
				if(isset($term_list) && count($term_list) > 0)
				{
					foreach ($term_list as $term)
					{
						$t_id = $term;
						$term_meta = get_option( "taxonomy_product_cat_$t_id" );
						if (!empty($term_meta) && is_array($term_meta))
						{
							$term_qty = (isset($cartitem_by_category[$term]) ? (int) $cartitem_by_category[$term] + $quantity : $quantity);
							$alg_wc_pq_min_or_max = 'alg_wc_pq_'.$min_or_max;
							$cat_quantity = (int) $term_meta[$alg_wc_pq_min_or_max];
							if ($cat_quantity > 0)
							{
								$trm = get_term_by( 'id', $t_id, 'product_cat' );
								if ($min_or_max=='max')
								{
									if($cat_quantity < $term_qty)
									{
										$message_template = get_option( $alg_wc_pq_min_or_max.'_cat_message',
											__( 'Maximum allowed quantity for category '.$trm->name.' is '.$cat_quantity.'. Your current quantity for this category is '.$term_qty.'.', 'product-quantity-for-woocommerce' ) );
										$_notice = str_replace(array('%category_title%','%max_per_item_quantity%','%item_quantity%'),array($trm->name,$cat_quantity,$term_qty),$message_template);
										wc_add_notice( $_notice, 'error' );
										return false;
									}
								}
								
							}
						}
					}
				}
			}
			
		}
		
		if($variation_id > 0 && $product_id > 0){
			$pre_product_id = $product_id;
			$product_id = $variation_id;
		}
		// Min & Max
		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
				// Cart total quantity
				if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
					$this->messenger->print_message( $min_or_max . '_cart_total_quantity', false, $this->get_min_max_cart_total_qty( $min_or_max ), $cart_total_quantity );
					return false;
				}
				// Per item quantity
				if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
					$this->messenger->print_message( $min_or_max . '_per_item_quantity', false, $this->get_product_qty_min_max( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
					return false;
				}
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			// Cart total
			if ( ! $this->check_step_cart_total_qty( $cart_total_quantity ) ) {
				$this->messenger->print_message( 'step_cart_total_quantity', false, $this->get_step_cart_total_qty(), $cart_total_quantity );
				return false;
			}
			// Products
			if ( ! $this->check_product_step( $product_id, $quantity ) ) {
				$this->messenger->print_message( 'step_quantity', false, $this->get_product_qty_step( $product_id ), $quantity, $product_id );
				return false;
			}
		}
		
		if($variation_id > 0 && $product_id > 0){
			$product_id = $pre_product_id;
		}
		
		
		
		// Exact quantities variation
		if ($variation_id > 0)
		{
			foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
				if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
					if ( ! $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, false, $variation_id ) ) {
						$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, false, $this->get_product_exact_qty( $product_id, $allowed_or_disallowed, '', $variation_id ), $quantity, $variation_id );
						return false;
					}
				}
			}

			
			// Min & Max
			
			$cart_item_quantities = $this->get_cart_item_quantities( $product_id, $quantity, true );
			$cart_total_quantity  = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
			$cart_item_quantity   = $cart_item_quantities[ $product_id ];
			
			foreach ( array( 'max' ) as $min_or_max ) {
				if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
					// Cart total quantity
					if ( ! $this->check_min_max_cart_total_allvar_qty( $min_or_max, $cart_total_quantity ) ) {
						$this->messenger->print_message( $min_or_max . '_cart_total_quantity', false, $this->get_min_max_cart_total_allvar_qty( $min_or_max ), $cart_total_quantity );
						return false;
					}
					// Per item quantity
					if ( ! $this->check_product_min_max_allvar( $product_id, $min_or_max, $cart_item_quantity ) ) {
						$this->messenger->print_message( $min_or_max . '_per_item_quantity', false, $this->get_product_qty_min_max_allvar( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
						return false;
					}
				}
			}
		} else {
			// Exact quantities
			foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
				if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
					if ( ! $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity ) ) {
						$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, false, $this->get_product_exact_qty( $product_id, $allowed_or_disallowed ), $quantity, $product_id );
						return false;
					}
				}
			}
			
		}
		
		
		
		// Passed
		return $passed;
	}

	/**
	 * style_qty_input.
	 *
	 * @version 1.6.0
	 * @since   1.3.0
	 */
	function style_qty_input() {
		echo '<style>' . 'input.qty,select.qty{' . get_option( 'alg_wc_pq_qty_input_style', '' ) . '}</style>';
	}

	/**
	 * hide_update_cart_button.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] add option to make qty input readonly
	 * @todo    [dev] add option to make "Update cart" button always enabled (i.e. even in case if quantities were not changed)
	 */
	function hide_update_cart_button() {
		echo '<style>' . '.cart button[name="update_cart"] { display: none; }</style>';
	}

	/**
	 * float_stock_amount.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function float_stock_amount() {
		remove_filter( 'woocommerce_stock_amount', 'intval' );
		add_filter(    'woocommerce_stock_amount', 'floatval' );
	}
	
	/**
	 * alg_wc_woocommerce_product_set_stock_action.
	 *
	 * @version 4.5.10
	 * @since   4.5.10
	 */
	 function alg_wc_woocommerce_product_set_stock_action( $product ){
		 if(!$product){
			 return;
		 }
		 if ( $product->get_manage_stock() ) {
				
			$stock_is_above_notification_threshold = ( (float) $product->get_stock_quantity() > absint( get_option( 'woocommerce_notify_no_stock_amount', 0 ) ) );
			
			$backorders_are_allowed  = ( 'no' !== $product->get_backorders() );

			if ( $stock_is_above_notification_threshold ) {
				$new_stock_status = 'instock';
			} elseif ( $backorders_are_allowed ) {
				$new_stock_status = 'onbackorder';
			} else {
				$new_stock_status = 'outofstock';
			}
			
			update_post_meta( $product->get_id(), '_stock_status', $new_stock_status );
			
		}
	 }

	 /**
	 * save_variation_stock_status_overwrite_thresold.
	 *
	 * @version 4.5.12
	 * @since   4.5.12
	 */
	 
	 function save_variation_stock_status_overwrite_thresold( $product_id, $loop ) {
		 
		$product = new WC_Product_Variation( $product_id );
		
		if( $product ) {
			
			if ( $product->get_manage_stock() ) {
				
				
				$stock_is_above_notification_threshold = ( (float) $product->get_stock_quantity() > absint( get_option( 'woocommerce_notify_no_stock_amount', 0 ) ) );
				
				$backorders_are_allowed   = ( 'no' !== $product->get_backorders() );

				if ( $stock_is_above_notification_threshold ) {
					$new_stock_status = 'instock';
				} elseif ( $backorders_are_allowed ) {
					$new_stock_status = 'onbackorder';
				} else {
					$new_stock_status = 'outofstock';
				}
				
				update_post_meta( $product_id, '_stock_status', $new_stock_status );
				
			}
		}
	}
	 
	 
	 /**
	 * save_stock_status_overwrite_thresold.
	 *
	 * @version 4.5.14
	 * @since   4.5.10
	 */
	function save_stock_status_overwrite_thresold( $product_id, $post, $update ) {
		
		global $typenow, $wpdb;

			if ( 'product' === $post->post_type ) {
				$product = new WC_Product( $product_id );
				
				if( $product ) {
					if ( $product->get_type() == 'simple' ) {
						if ( $product->get_manage_stock() ) {
							
							$stock_is_above_notification_threshold = ( (float) $product->get_stock_quantity() > absint( get_option( 'woocommerce_notify_no_stock_amount', 0 ) ) );
							
							$backorders_are_allowed   = ( 'no' !== $product->get_backorders() );

							if ( $stock_is_above_notification_threshold ) {
								$new_stock_status = 'instock';
							} elseif ( $backorders_are_allowed ) {
								$new_stock_status = 'onbackorder';
							} else {
								$new_stock_status = 'outofstock';
							}
							
							update_post_meta( $product_id, '_stock_status', $new_stock_status );

							if ( absint( $product->get_stock_quantity() ) < 1 && $new_stock_status == 'instock' ) {
								$visibility_terms = wc_get_product_visibility_term_ids();
								update_post_meta( $product_id, '_visibility', true);
								wp_remove_object_terms( $product_id, 'outofstock', 'product_visibility' );
								wp_remove_object_terms( $product_id, 'exclude-from-catalog', 'product_visibility' );
								wp_remove_object_terms( $product_id, 'exclude-from-search', 'product_visibility' );
								
								$product_ids = array();
								$product_ids[] = $product_id;
								$terms_id = array();
								
								if ( isset( $visibility_terms['outofstock'] ) ) {
									$terms_id[] = $visibility_terms['outofstock'];
								}
								if ( isset( $visibility_terms['exclude-from-catalog'] ) ) {
									$terms_id[] = $visibility_terms['exclude-from-catalog'];
								}
								if ( isset( $visibility_terms['exclude-from-search'] ) ) {
									$terms_id[] = $visibility_terms['exclude-from-search'];
								}
								
								if ( count( $terms_id ) > 0 ) {
									$wpdb->query( "DELETE FROM " . $wpdb->prefix . "term_relationships WHERE object_id IN (".implode( ", ", $product_ids ).") AND term_taxonomy_id  IN (".implode( ", ", $terms_id ).")" );
								}
								// delete_transient( 'wc_term_counts' );
								_wc_recount_terms_by_product( $product_id );
								
							}
							
						}
					} 
				}
			} else if ( 'product_variation' === $post->post_type ) {
				
				$product = new WC_Product_Variation( $product_id );
				
				if( $product ) {
					
						if ( $product->get_manage_stock() ) {
							
							$stock_is_above_notification_threshold = ( (float) $product->get_stock_quantity() > absint( get_option( 'woocommerce_notify_no_stock_amount', 0 ) ) );
							
							$backorders_are_allowed   = ( 'no' !== $product->get_backorders() );

							if ( $stock_is_above_notification_threshold ) {
								$new_stock_status = 'instock';
							} elseif ( $backorders_are_allowed ) {
								$new_stock_status = 'onbackorder';
							} else {
								$new_stock_status = 'outofstock';
							}
							
							update_post_meta( $product_id, '_stock_status', $new_stock_status );
							
						}
				}
				
			}
			
	}

	/**
	 * set_quantity_input_args.
	 *
	 * @version 1.7.0
	 * @since   1.2.0
	 * @todo    [dev] re-check do we really need to set `step` here?
	 */
	function set_quantity_input_args( $args, $product ) {
		global $wp_query;
		
		$category_name = '';
		if(isset($wp_query->query_vars['product_cat'])){
			$category_name = $wp_query->query_vars['product_cat'];
		}
		
		if(empty($product)){
			return $args;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			$args['min_value']   = $this->set_quantity_input_min(  $args['min_value'], $product );
		} elseif ( 'yes' === get_option( 'alg_wc_pq_force_cart_min_enabled', 'no' ) ) {
			$args['min_value']   = 1;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			$args['max_value']   = $this->set_quantity_input_max(  $args['max_value'], $product );
		}
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			$args['step']        = $this->set_quantity_input_step( $args['step'],      $product );
		}
		
		
		if($this->alg_wc_pq_exact_qty_allowed_section_enabled == 'yes'){
			if($this->alg_wc_pq_force_on_single == 'exact_allowed' && is_product()){
				$fixed_qty = $this->get_product_exact_qty( $product->get_id(), 'allowed', '', 0 );
				
				if(!empty($fixed_qty)){
					$fixed_qty_arr = $this->process_exact_qty_option( $fixed_qty );
					sort($fixed_qty_arr);
					$args['input_value'] = $fixed_qty_arr[0];
				}
			}
			
			if($this->alg_wc_pq_force_on_loop == 'exact_allowed' && (is_shop() || is_product_tag() || is_product_category() || (defined('DOING_AJAX') && DOING_AJAX))){
				$fixed_qty = $this->get_product_exact_qty( $product->get_id(), 'allowed', '', 0 );
				
				if(!empty($fixed_qty)){
					$fixed_qty_arr = $this->process_exact_qty_option( $fixed_qty );
					sort($fixed_qty_arr);
					$args['input_value'] = $fixed_qty_arr[0];
				}
			}
		}
		
		
		if( (is_shop() || is_product_tag() || is_product_category() || (isset($category_name) && !empty($category_name)) || (defined('DOING_AJAX') && DOING_AJAX)) ) {
			if($this->force_on_loop_archive=='min'){
				$data_quantity = $this->get_product_qty_min_max( $product->get_id(), 1, 'min' );
				$args['input_value'] = $data_quantity;
			}else if($this->force_on_loop_archive=='max'){
				$data_quantity = $this->get_product_qty_min_max( $product->get_id(), 1, 'max' );
				$args['input_value'] = $data_quantity;
			}else if($this->force_on_loop_archive=='default'){
				$data_quantity = $this->get_product_qty_default( $product->get_id(), 1 );
				$args['input_value'] = $data_quantity;
			}
		}
		
		if(!(is_shop() || is_product_tag() || is_product_category() || is_cart() || is_checkout()) && !is_product()){
			if ((defined('DOING_AJAX') && DOING_AJAX)) {
				$args['input_value'] = (!empty($args['input_value']) ? $args['input_value'] : $this->get_product_qty_default( $product->get_id(), 1 ) );
			}else{
				if((isset($args['input_value']) && (empty($args['input_value']) || $args['input_value']==1)) || !isset($args['input_value'])){
					$args['input_value'] = $this->get_product_qty_default( $product->get_id(), $args['input_value'] );
				}
			}
			
		}
		
		if ( 'disabled' != ( $force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' ) ) && is_product() ) {
			
			if('default' === ( $force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' ))) {
				if((isset($args['input_value']) && (empty($args['input_value']) || $args['input_value']==1)) || !isset($args['input_value'])){
					$args['input_value'] = $this->get_product_qty_default( $product->get_id(), $args['input_value'] );
				}
			} else if ($this->alg_wc_pq_force_on_single !== 'exact_allowed'){
				$args['input_value'] = ( 'min' === $force_on_single ?
				$this->set_quantity_input_min( $args['min_value'], $product ) : $this->set_quantity_input_max( $args['max_value'], $product ) );
			}
		}
		
		
		if ( 'disabled' === ( $force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' ) ) && is_product() && 'no' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
			$args['min_value']   = $this->set_quantity_input_min(  1, $product );
			// $args['min_value']   = 1;
		}
		
		if( (is_shop() || is_product_tag() || is_product_category() ) ) {
			
			if ('yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' )) {
				if ( !isset($args['min_value']) || 
				$args['min_value']<= 0  || 
				('disabled' == ( $force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ) ) && !isset($args['min_value']))) {
					$args['min_value'] = 1;
				}
			}
			
			if ('yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' )) {
				if ( !isset($args['max_value']) || $args['max_value'] <= 0) {
					$stock = $product->get_stock_quantity();
					if ( $stock > 0 ) {
						$args['max_value'] = $stock;
					}
				}
			}
			
			if ( 'disabled' != ( $force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ) ) ) {
				if('default' === ( $force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ))) {
					if((isset($args['input_value']) && (empty($args['input_value']) || $args['input_value']==1)) || !isset($args['input_value'])){
						$args['input_value'] = $this->get_product_qty_default( $product->get_id(), $args['input_value'] );
					}
				} else if ($this->alg_wc_pq_force_on_loop !== 'exact_allowed'){
					$args['input_value'] = ( 'min' === $force_on_loop ?
						$this->set_quantity_input_min( $args['min_value'], $product ) : $this->set_quantity_input_max( $args['max_value'], $product ) );
				}
			}
		}
		$args['product_id'] = ( $product ? $product->get_id() : 0 );
		
		if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'POST'){
			if(isset($args['input_value']) && empty($args['input_value'])){
				$args['input_value'] = 1;
			}
		}
		
		if(isset($args['step']) && empty($args['step'])){
			$args['step'] = 1;
		}
		
		return $args;
	}

	/**
	 * get_product_qty_step.
	 *
	 * @version 4.5.14
	 * @since   1.1.0
	 */
	function get_product_qty_step( $product_id, $default_step = 0, $variation_id = 0 ) {
		$per_product_id = $product_id;
		if( $variation_id > 0 ) {
			$per_product_id = $variation_id;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			if ( 'yes' === apply_filters( 'alg_wc_pq_quantity_step_per_product', 'no' ) && 0 != ( $step_per_product = apply_filters( 'alg_wc_pq_quantity_step_per_product_value', 0, $per_product_id ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_step', $step_per_product, $per_product_id );
			} else if ( 'yes' === apply_filters( 'alg_wc_pq_quantity_step_per_product_cat', 'no' ) && 0 != ( $step_per_product = apply_filters( 'alg_wc_pq_quantity_step_per_product_cat_value', 0, $product_id ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_step', $step_per_product, $product_id );
			} else if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_attr_quantity_per_product', 'no', 'step' ) && 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_attr_quantity_per_product_value', 'no', $product_id, 'step' ) ) ) {
				// Per attribute
				return apply_filters( 'alg_wc_pq_get_product_qty_' . 'step', $value, $product_id );
			} else {
				return apply_filters( 'alg_wc_pq_get_product_qty_step', ( 0 != ( $step_all_products = get_option( 'alg_wc_pq_step', 0 ) ) ? $step_all_products : $default_step ), $product_id );
			}
			
		} else {
			return apply_filters( 'alg_wc_pq_get_product_qty_step', $default_step, $per_product_id );
		}
	}

	/**
	 * set_quantity_input_step.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function set_quantity_input_step( $step, $_product ) {
		if($_product->get_type() == 'variation'){
			$variation_id = $_product->get_id();
			$product_id = wp_get_post_parent_id($_product->get_id());
			return $this->get_product_qty_step( $product_id, $step, $variation_id );
		}
		return $this->get_product_qty_step( $this->get_product_id( $_product ), $step );
	}
	
	/**
	 * store_api_product_step_quantity.
	 *
	 * @version 4.5.14
	 * @since   1.1.0
	 */
	function store_api_product_step_quantity( $step, $_product, $cart_item ) {
		if ( 'yes' === get_option( 'alg_wc_pq_advance_wc_block_api', 'no' ) ) {
			if ( $_product->get_type() == 'variation' ) {
				$variation_id = $_product->get_id();
				$product_id = wp_get_post_parent_id( $_product->get_id() );
				$return_step_var = $this->get_product_qty_step( $product_id, $step, $variation_id );
				if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) && !empty( $return_step_var ) ) {
					if ( fmod( $return_step_var, 1 ) !== 0.00 ){
						// return decimal
						return $return_step_var;
					} else {
						// return intiger
						$return_var = (int) $return_step_var;
						if($return_var < 1) {
							$return_var = 1;
						}
						return (int) $return_var;
					}
				} else {
					$return_var = (int) $return_step_var;
					if($return_var < 1) {
						$return_var = 1;
					}
					return (int) $return_var;
				}
			}
			
			$return_step = $this->get_product_qty_step( $this->get_product_id( $_product ), $step );
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) && !empty($return_step) ){
				if ( fmod( $return_step, 1 ) !== 0.00 ) {
					// return decimal
					return $return_step;
				} else {
					// return intiger
					$return = (int) $return_step;
					if($return < 1) {
						$return = 1;
					}
					return (int) $return;
				}
			} else {
				$return = (int) $return_step;
				if ( $return < 1 ) {
					$return = 1;
				}
				return (int) $return;
			}
		} else {
			if ( $_product->get_type() == 'variation' ) {
				$variation_id = $_product->get_id();
				$product_id = wp_get_post_parent_id( $_product->get_id() );
				return $this->get_product_qty_step( $product_id, $step, $variation_id );
			}
			return $this->get_product_qty_step( $this->get_product_id( $_product ), $step );
		}
		
	}
	
	/**
	 * admin_set_quantity_input_step.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function admin_set_quantity_input_step( $step, $_product ) {
		return 0.01;
	}

    /**
     * Find matching product variation
     *
     * @param $product_id
     * @param $attributes
     * @return int
     */
    function find_matching_product_variation_id($product_id, $attributes, $getId=false)
    {
		if (is_array($attributes) && count($attributes) > 0)
		{
			$product = new WC_Product_Variable($product_id);
			$variations = $product->get_available_variations();

			if (count($variations) > 0)
			{
				foreach ($variations as $variation) 
				{
					$vAttributes =  $variation['attributes'];
					$variation_id =  $variation['variation_id'];
					
					if ( count($vAttributes) > 0 && count($vAttributes) == count($attributes) )
					{
						$diff=array_diff($attributes,$vAttributes);
						if(empty($diff))
						{
							if($getId){
								return $variation_id;
							}
							return $variation['display_price'];
						}
						else if(count($diff) < count($vAttributes) && ( in_array('', $vAttributes) || in_array(null, $vAttributes) ) )
						{
							if($getId){
								return $variation_id;
							}
							return $variation['display_price'];
						}
					}
				}
			}
		}
		return false;
    }
	
	/**
	 * get_attribute_unit_label.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 */
	function get_attribute_unit_label( $product_id, $attr_taxonomy ) {
		$return = array();
		if(!empty( $attr_taxonomy ))
		{
			foreach($attr_taxonomy as $taxonomy)
			{
				$term_list = wp_get_post_terms($product_id, $taxonomy, array('fields'=>'ids'));
				
				if(isset($term_list) && count($term_list) > 0)
				{
					foreach ($term_list as $term)
					{
						$term_meta = get_option( "taxonomy_product_attribute_item_$term" );
						
						if (!empty($term_meta) && is_array($term_meta))
						{
							$singular_meta = 'alg_wc_pq_price_by_qty_attribute_unit_singular';
							$plural_meta = 'alg_wc_pq_price_by_qty_attribute_unit_plural';
							$singular_unit = $term_meta[$singular_meta];
							$plural_unit = $term_meta[$plural_meta];
							if ( !empty($singular_unit) && !empty($plural_unit) )
							{
								$return['singular'] = $singular_unit;
								$return['plural'] = $plural_unit;
								return $return;
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * get_category_unit_label.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 */
	function get_category_unit_label( $product_id ) {
		$return = array();
		$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
		if(isset($term_list) && count($term_list) > 0)
		{
			foreach ($term_list as $term)
			{
				$term_meta = get_option( "taxonomy_product_cat_$term" );
				if (!empty($term_meta) && is_array($term_meta))
				{
					$singular_meta = 'alg_wc_pq_category_unit_singular';
					$plural_meta = 'alg_wc_pq_category_unit_plural';
					$singular_unit = $term_meta[$singular_meta];
					$plural_unit = $term_meta[$plural_meta];
					if ( !empty($singular_unit) && !empty($plural_unit) )
					{
						$return['singular'] = $singular_unit;
						$return['plural'] = $plural_unit;
						return $return;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * get_allowed_attribute_tax.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function get_allowed_attribute_tax(){
		$allowed_attribute_tax = array();
		$alg_wc_pq_qty_price_by_attribute_qty_unit_input_selected = get_option( 'alg_wc_pq_qty_price_by_attribute_qty_unit_input_selected', array() );
		if( !empty( $this->attribute_taxonomies ) ){
			foreach( $this->attribute_taxonomies as $tax ) {
				$name = alg_pq_wc_attribute_taxonomy_name( $tax->attribute_name );
				$allowed_attribute_tax[] = $name;
				if(!empty($alg_wc_pq_qty_price_by_attribute_qty_unit_input_selected) && in_array($tax->attribute_id, $alg_wc_pq_qty_price_by_attribute_qty_unit_input_selected)){
					return $allowed_attribute_tax;
				}
			}
		}
		return $allowed_attribute_tax;
	}
	
	/**
	 * alg_wc_pq_get_product_price_unit.
	 *
	 * @version 4.5.20
	 * @since   4.5.20
	 */
	function alg_wc_pq_get_product_price_unit($product, $quantitiy=1, $price_unit = false){
		
		$defaultpc = '!na';
		$defaultpcs = '!na';
		
		if ( ! $product ) {
			return '';
		}
		$product_id = $product->get_id();
		

		if($price_unit){
			$unit = '';
			if($this->alg_wc_pq_qty_price_unit_enabled === 'yes'){
				$productType =  $product->get_type();
				if( $this->is_show_unit() ) {
					$unit = get_option( 'alg_wc_pq_qty_price_unit', '' );
					if ( !empty($product) && $product->get_id() > 0 && ! is_admin() ) {
						$product_id = $product->get_id();
						
						if( $this->enabled_priceunit_category == 'yes' || $this->enabled_priceunit_product == 'yes') {
							$product_unit = $this->get_term_price_unit( $product_id );
							$unit = (!empty($product_unit) ? $product_unit : $unit );
						}
					}
				}
			}
			return do_shortcode($unit);
		}
		
		$unit = get_option( 'alg_wc_pq_qty_price_by_qty_unit_singular', 'no' );
		$units = get_option( 'alg_wc_pq_qty_price_by_qty_unit_plural', 'no' );
		$unit = ( ( !empty($unit) ) ? $unit : $defaultpc );
		$units = ( ( !empty($units) ) ? $units : $defaultpcs );
		
		$product_unit = get_post_meta($product_id, '_alg_wc_pq_qty_price_by_qty_unit_label_template_singular', true);
		$product_units = get_post_meta($product_id, '_alg_wc_pq_qty_price_by_qty_unit_label_template_plural', true);
			
		if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_unit_input_enabled', 'no' ) && !empty($product_unit) && !empty($product_units) ) {
			if ( !empty($unit) && !empty($units) )
			{
				$unit = ( !empty($product_unit) ? $product_unit : $defaultpc );
				$units = ( !empty($product_units) ? $product_units : $defaultpcs );
			}
		} else if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_cat_qty_unit_input_enabled', 'no' ) ) {
			$get_unit = $this->get_category_unit_label( $product_id );
			if($get_unit) {
				$unit = $get_unit['singular'];
				$units = $get_unit['plural'];
				$unit = ( ( !empty($unit) ) ? $unit : $defaultpc );
				$units = ( ( !empty($units) ) ? $units : $defaultpcs );
			}
		}
		
		if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_attribute_qty_unit_input_enabled', 'no' ) ) {
			
			$get_unit = $this->get_attribute_unit_label( $product_id, $this->attr_taxonomies );
			if($get_unit) {
				$aunit = $get_unit['singular'];
				$aunits = $get_unit['plural'];
				$unit = ( ( !empty($aunit) ) ? $aunit : $defaultpc );
				$units = ( ( !empty($aunits) ) ? $aunits : $defaultpcs );
			}
			
		}
		if($unit!='!na' && $units!='!na'){
			return ($quantitiy > 1 ? $units : $unit);
		}
		 return '';
	}

	/**
	 * ajax_price_by_qty.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 * @todo    [dev] customizable position (instead of the price; after the price, before the price etc.) (NB: maybe do not display for qty=1)
	 * @todo    [dev] add option to disable "price by qty" on initial screen (i.e. before qty input was changed)
	 * @todo    [dev] (maybe) add sale price
	 * @todo    [dev] (maybe) add optional "in progress" message (for slow servers)
	 */
	function ajax_price_by_qty( $param ) {
		
		$defaultpc = '!na';
		$defaultpcs = '!na';
		
		$woo_discount_rules = is_plugin_active('woo-discount-rules/woo-discount-rules.php');

		if($woo_discount_rules){
			$manageDiscount = new ManageDiscount;
			$rule = new Rule();
			$available_rules = $rule->getAvailableRules($manageDiscount->getAvailableConditions());
			$discountCalculator = new DiscountCalculator($available_rules);
			$config = new Configuration();
			$price_display_condition = $config->getConfig('show_strikeout_when', 'show_when_matched');
			
			// product detail page
			$manual_request = true;
			
			// cart page (need to apply conditionally )
			// $manual_request = false;
			
		}
		
		if ( isset( $_POST['alg_wc_pq_qty'] ) && '' !== $_POST['alg_wc_pq_qty'] && ! empty( $_POST['alg_wc_pq_id'] ) ) {
			$product = wc_get_product( $_POST['alg_wc_pq_id']  );
 			$product_id = $_POST['alg_wc_pq_id'];
			
            $pro_type =  $product->get_type();
			if(isset($_POST['selected_val'])){
				$selectedval = $_POST['selected_val'];
			}else{
				$selectedval = 0;
			}
            $selectedattribute = $_POST['attribute'];
			
			$unit = get_option( 'alg_wc_pq_qty_price_by_qty_unit_singular', 'no' );
			$units = get_option( 'alg_wc_pq_qty_price_by_qty_unit_plural', 'no' );
			$unit = ( ( !empty($unit) ) ? $unit : $defaultpc );
			$units = ( ( !empty($units) ) ? $units : $defaultpcs );
			
			
			
			$product_unit = get_post_meta($product_id, '_alg_wc_pq_qty_price_by_qty_unit_label_template_singular', true);
			$product_units = get_post_meta($product_id, '_alg_wc_pq_qty_price_by_qty_unit_label_template_plural', true);

			if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_unit_input_enabled', 'no' ) && !empty($product_unit) && !empty($product_units) ) {
				if ( !empty($unit) && !empty($units) )
				{
					$unit = ( !empty($product_unit) ? $product_unit : $defaultpc );
					$units = ( !empty($product_units) ? $product_units : $defaultpcs );
				}
			} else if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_cat_qty_unit_input_enabled', 'no' ) ) {
				$get_unit = $this->get_category_unit_label( $product_id );
				if($get_unit) {
					$unit = $get_unit['singular'];
					$units = $get_unit['plural'];
					$unit = ( ( !empty($unit) ) ? $unit : $defaultpc );
					$units = ( ( !empty($units) ) ? $units : $defaultpcs );
				}
			}
			
			if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_attribute_qty_unit_input_enabled', 'no' ) ) {
				
				$get_unit = $this->get_attribute_unit_label( $product_id, $this->attr_taxonomies );
				if($get_unit) {
					$aunit = $get_unit['singular'];
					$aunits = $get_unit['plural'];
					$unit = ( ( !empty($aunit) ) ? $aunit : $defaultpc );
					$units = ( ( !empty($aunits) ) ? $aunits : $defaultpcs );
				}
				
			}
			
			
			
			$arrangedArray = array();
			if(!empty($selectedattribute))
			{
				$selectedattribute = json_decode(stripslashes($selectedattribute),JSON_UNESCAPED_SLASHES);
				if(count($selectedattribute) > 0 && is_array($selectedattribute))
				{
					foreach($selectedattribute as $key=>$sel)
					{
						foreach($sel as $key=>$val)
						{
							if(!empty($val))
							{
								$arrangedArray[$key] = $val;
							}
						}
					}
				}
			}
			
            if($pro_type == 'variable' && ($selectedval != '' || $selectedval != 0))
            {
            	
        	$currency_symbol = get_woocommerce_currency_symbol();
        	$selectedval = $_POST['selected_val'];
			$quantity_fetch = $_POST['quantity_fetch'];
			$product_id =  $_POST['alg_wc_pq_id'] ;
			$variation_price = $this->find_matching_product_variation_id($product_id,$arrangedArray);
			$variation_id = $this->find_matching_product_variation_id($product_id,$arrangedArray, true);
			/*
			$product = new WC_Product_Variable($product_id);
			$variations = $product->get_available_variations();
			// echo '<pre>';
			//print_r($variations);
			//echo '</pre>';
			$var_data = []; 
			foreach ($variations as $variation) 
			{
				$attribute_get =  $variation['attributes'];
				//$aKeys = array_keys($attribute_get);
				//$cKeys = array_keys($arrangedArray);
				$diff=array_diff($attribute_get,$arrangedArray);
				//print_r($diff);
				foreach ($attribute_get as $attribute_get_v)
				{

					if(is_array($arrangedArray) && count($arrangedArray) > 0)
					{
						// if ($attribute_get_v == $selectedval )
						// {
							// $price_get =  $variation['price_html'];
							// echo 'price_get' . $price_get;
							$display_regular_price = $variation['display_regular_price'].'<span class="currency">'. $currency_symbol .'</span>';
							$display_price = $variation['display_price'].'<span class="currency">'. $currency_symbol .'</span>';
							
							//echo 'displayp' . $display_price;
							//echo 'qty' . $_POST['alg_wc_pq_qty'];
							$placeholders = array();
							$placeholders = array(
								'%price%'   =>$currency_symbol.''.$display_price*$quantity_fetch,
								'%qty%'     => $quantity_fetch,
							);
							echo str_replace( array_keys( $placeholders ), $placeholders,
							get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ) ) );

						// }
					}
					// exit;
				}
				// exit;
			}
			*/
			
			/**Tire pricing for variation product*/
			if( file_exists(ABSPATH . 'wp-content/plugins/tier-pricing-table-premium/src/PriceManager.php') ){
			require_once ABSPATH . 'wp-content/plugins/tier-pricing-table-premium/src/PriceManager.php';
				if(class_exists('TierPricingTable\PriceManager')){
					$PriceManager = new TierPricingTable\PriceManager();
					if($variation_id > 0){
						$tire_variation_price = $PriceManager->getPriceByRules( $quantity_fetch, $variation_id );
						if($tire_variation_price){
							$variation_price = $tire_variation_price;
						}
					}
				}
			}
			
			if(!empty($variation_price))
			{
				$placeholders = array(
					/*'%price%'   =>$currency_symbol.''.$variation_price*$quantity_fetch,*/
					'%price%'   =>wc_price( $variation_price*$quantity_fetch ),
					'%qty%'     => $quantity_fetch,
					'%unit%'     => ( $quantity_fetch > 1 ? $units : $unit ),
				);
				
				if($unit!='!na' && $units!='!na'){
					echo str_replace( array_keys( $placeholders ), $placeholders,
				get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% %unit%.', 'product-quantity-for-woocommerce' ) ) );
				}
			}
				
            }
            else if($pro_type == 'simple')
            {
			$product = wc_get_product( $_POST['alg_wc_pq_id'] );
			$product_id = $_POST['alg_wc_pq_id'];
			$quantity_fetch = $_POST['alg_wc_pq_qty'];
			
			
			if ( function_exists('icl_object_id') ) {
				global $woocommerce_wpml;
				if(!empty($woocommerce_wpml)){
					$currency = $woocommerce_wpml->get_multi_currency()->get_client_currency();
					$price = $woocommerce_wpml->multi_currency->prices->get_product_price_in_currency( $product->get_id(), $currency );
					// $price = wc_get_price_including_tax( $product, [ 'price' => $price ] );
					$price = $price*$_POST['alg_wc_pq_qty'];
					$display_price = wc_price( $price, ['currency'=>$currency] );
				}else{
					$price = wc_get_price_to_display( $product , array( 'qty' => $_POST['alg_wc_pq_qty'] ) );
					$display_price = wc_price( $price );
				}
			}else{
				$price = wc_get_price_to_display( $product , array( 'qty' => $_POST['alg_wc_pq_qty'] ) );
				
				// woo discount rule 
				if($woo_discount_rules){
					$discountprices = ManageDiscount::calculateInitialAndDiscountedPrice($product, $_POST['alg_wc_pq_qty'], $is_cart = false, true);
					$initial_price_with_tax = (isset($discountprices['initial_price_with_tax']) ? $discountprices['initial_price_with_tax'] : $discountprices['initial_price']);
					$discounted_price_with_tax = (isset($discountprices['discounted_price_with_tax']) ? $discountprices['discounted_price_with_tax'] : $discountprices['discounted_price_with_tax']);
					$qty = $_POST['alg_wc_pq_qty'];
					$productprice_after_discount = $initial_price_with_tax - $discounted_price_with_tax;
					$discountPrice = $productprice_after_discount * $qty;
					// $discountPrice = $discountCalculator->mayApplyPriceDiscount($product, $_POST['alg_wc_pq_qty'], $price, false, array(), true, $manual_request);
					if(!empty($discountPrice)){
						// $price = (isset($discountPrice['discounted_price']) ? $discountPrice['discounted_price'] : $discountPrice['initial_price']);
						$price = $discountPrice;
					}
				}
				// woo discount rule end
				
				$display_price = wc_price( $price );
			}
			
			/**Tire pricing for variation product*/
			if( file_exists(ABSPATH . 'wp-content/plugins/tier-pricing-table-premium/src/PriceManager.php') ){
			require_once ABSPATH . 'wp-content/plugins/tier-pricing-table-premium/src/PriceManager.php';
				if(class_exists('TierPricingTable\PriceManager')){
					$PriceManager = new TierPricingTable\PriceManager();
					if($variation_id > 0){
						$tire_variation_price = $PriceManager->getPriceByRules( $quantity_fetch, $variation_id );
						if($tire_variation_price){
							$variation_price = $tire_variation_price;
						}
					}
				}
			}else if( file_exists(ABSPATH . 'wp-content/plugins/tier-pricing-table/src/PriceManager.php') ){
			require_once ABSPATH . 'wp-content/plugins/tier-pricing-table/src/PriceManager.php';
				if(class_exists('TierPricingTable\PriceManager')){
					$PriceManager = new TierPricingTable\PriceManager();
					if($variation_id > 0){
						$tire_variation_price = $PriceManager->getPriceByRules( $quantity_fetch, $variation_id );
						if($tire_variation_price){
							$variation_price = $tire_variation_price;
						}
					}
				}
			}
			
			// advance dynamic pricing for woocommerce
			if ( function_exists('adp_functions')) {
				$discountPrice = adp_functions()->getDiscountedProductPrice($product, $quantity_fetch);
				$price = $discountPrice*$quantity_fetch;
				$display_price = wc_price( $price );
			}
			
			
			$placeholders = array(
				'%price%'   => $display_price,
				'%qty%'     => $_POST['alg_wc_pq_qty'],
				'%unit%'     => ( $_POST['alg_wc_pq_qty'] > 1 ? $units : $unit ),
			);
			
			if($unit!='!na' && $units!='!na'){
				echo str_replace( array_keys( $placeholders ), $placeholders,
				get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% %unit%.', 'product-quantity-for-woocommerce' ) ) );
			}
		   }
		}
		die();
	}
	
	/**
	 * alg_wc_pq_update_price_by_qty_on_load.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 */
	function alg_wc_pq_update_price_by_qty_on_load($product, $default, $qty=0, $returnUnit=false){
		
		$defaultpc = '!na';
		$defaultpcs = '!na';
		
		if(is_admin()){
			if (!(defined('DOING_AJAX') && DOING_AJAX)) {
				return '';
			}
		}
		if ( ! $product ) {
			return '';
		}
		if(alg_wc_pq()->core->alg_wc_pq_price_by_qty_is_disable($product)){
			return $default;
		}
		if($product->get_type() == 'variation'){
			$product_id = $product->get_parent_id();
		}else{
			$product_id = $product->get_id();
		}

		
		$pro_type =  $product->get_type();
		if(empty($qty)){
			$selectedval = $this->set_quantity_input_min( 1, $product );
		}else{
			$selectedval = $qty;
		}
		if($returnUnit){
			$selectedval = 1;
		}
		
		$unit = get_option( 'alg_wc_pq_qty_price_by_qty_unit_singular', 'no' );
		$units = get_option( 'alg_wc_pq_qty_price_by_qty_unit_plural', 'no' );
		$unit = ( ( !empty($unit) ) ? $unit : $defaultpc );
		$units = ( ( !empty($units) ) ? $units : $defaultpcs );
		
		
		$product_unit = get_post_meta($product_id, '_alg_wc_pq_qty_price_by_qty_unit_label_template_singular', true);
		$product_units = get_post_meta($product_id, '_alg_wc_pq_qty_price_by_qty_unit_label_template_plural', true);

		if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_unit_input_enabled', 'no' ) && !empty($product_unit) && !empty($product_units) ) {
			if ( !empty($unit) && !empty($units) )
			{
				$unit = ( !empty($product_unit) ? $product_unit : $defaultpc );
				$units = ( !empty($product_units) ? $product_units : $defaultpcs );
			}
		} else if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_cat_qty_unit_input_enabled', 'no' ) ) {
			$get_unit = $this->get_category_unit_label( $product_id );
			if($get_unit) {
				$unit = $get_unit['singular'];
				$units = $get_unit['plural'];
				$unit = ( ( !empty($unit) ) ? $unit : $defaultpc );
				$units = ( ( !empty($units) ) ? $units : $defaultpcs );
			}
		}
		
		if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_attribute_qty_unit_input_enabled', 'no' ) ) {
				
			$get_unit = $this->get_attribute_unit_label( $product_id, $this->attr_taxonomies );
			if($get_unit) {
				$aunit = $get_unit['singular'];
				$aunits = $get_unit['plural'];
				$unit = ( ( !empty($aunit) ) ? $aunit : $defaultpc );
				$units = ( ( !empty($aunits) ) ? $aunits : $defaultpcs );
			}
			
		}


		if($pro_type == 'simple' || $pro_type == 'variation')
		{
			global $woocommerce_wpml;
			if ( function_exists('icl_object_id') && isset($woocommerce_wpml) && defined( 'ICL_LANGUAGE_CODE' ) ) {
				$currency = $woocommerce_wpml->get_multi_currency()->get_client_currency();
				$price = $woocommerce_wpml->multi_currency->prices->get_product_price_in_currency( $product->get_id(), $currency );
				$price = wc_get_price_including_tax( $product, [ 'price' => $price ] );
				$price = $price*$selectedval;
				$display_price = wc_price( $price, ['currency'=>$currency] );
			}else{
				$price = wc_get_price_to_display( $product , array( 'qty' => $selectedval ) );
				$display_price = wc_price( $price );
			}
			
			
			$placeholders = array(
				'%price%'   => $display_price,
				'%qty%'     => $selectedval,
				'%unit%'     => ( $selectedval > 1 ? $units : $unit ),
			);
			
			if($unit!='!na' && $units!='!na'){
				return str_replace( array_keys( $placeholders ), $placeholders,
				get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% %unit%.', 'product-quantity-for-woocommerce' ) ) );
			}
			
	   }
	   return '';
	}

	// function ajax_price_by_qty_variable( $param ) {

	// 	if ( isset( $_POST['alg_wc_pq_qty'] ) && '' !== $_POST['alg_wc_pq_qty'] && ! empty( $_POST['alg_wc_pq_id'] ) ) {
	// 		$selectedval = $_POST['selected_val'];
	// 		$quantity_fetch = $_POST['quantity_fetch'];
	// 		$product_id =  $_POST['alg_wc_pq_id'] ;
	// 		$currency_symbol = get_woocommerce_currency_symbol();
	// 		$product = new WC_Product_Variable($product_id);
	// 		$variations = $product->get_available_variations();
	// 		//echo '<pre>';
	// 		//print_r($variations);
	// 		//echo '</pre>';
	// 		$var_data = []; 
	// 		foreach ($variations as $variation) {
	// 			/*print_r($variation);*/
	// 			$attribute_get =  $variation['attributes'];
	// 			foreach($attribute_get as $attribute_get_v)
	// 			{
					
	// 			if($attribute_get_v == $selectedval )
	// 			{
	// 				// $price_get =  $variation['price_html'];
	// 				// echo 'price_get' . $price_get;
	// 				$display_regular_price = $variation['display_regular_price'].'<span class="currency">'. $currency_symbol .'</span>';
	// 				$display_price = $variation['display_price'].'<span class="currency">'. $currency_symbol .'</span>';
					
	// 				//echo 'displayp' . $display_price;
	// 				//echo 'qty' . $_POST['alg_wc_pq_qty'];
	// 				$placeholders = array();
	// 				$placeholders = array(
	// 			'%price%'   =>$currency_symbol.''.$display_price*$quantity_fetch,
	// 			'%qty%'     => $quantity_fetch,
	// 		);
	// 		echo str_replace( array_keys( $placeholders ), $placeholders,
	// 			get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ) ) );

	// 			}
	// 				// exit;
	// 			}
	// 			// exit;
	// 		}
	// 		// $placeholders = array(
	// 		// 	'%price%'   => wc_price( wc_get_price_to_display( wc_get_product( $_POST['alg_wc_pq_id'] ), array( 'qty' => $_POST['alg_wc_pq_qty'] ) ) ),
	// 		// 	'%qty%'     => $_POST['alg_wc_pq_qty'],
	// 		// );
	// 		// echo str_replace( array_keys( $placeholders ), $placeholders,
	// 		// 	get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ) ) );
	// 	}
	// 	die();
	// }

	/**
	 * get_product_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_product_id( $_product ) {
		if ( ! isset( $this->is_wc_version_below_3 ) ) {
			$this->is_wc_version_below_3 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
		}
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		if ( $this->is_wc_version_below_3 ) {
			return ( isset( $_product->variation_id ) ) ? $_product->variation_id : $_product->get_id();
		} else {
			return $_product->get_id();
		}
	}

	/**
	 * get_product_qty_default.
	 *
	 * @version 4.5.14
	 * @since   1.0.0
	 */
	function get_product_qty_default( $product_id, $default = 1 ) {
		if ( 'yes' === get_option( 'alg_wc_pq_default_section_enabled', 'no' ) ) {
			// Check if "Sold individually" is enabled for the product
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_sold_individually() ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_default', $default, $product_id );
			}
			// Per product
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_default_quantity_per_product', 'no' ) ) {
				if ( '' != ( $value = apply_filters( 'alg_wc_pq_per_item_default_quantity_per_product_value', 'no', $product_id ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_default', $value, $product_id );
				}
			}
			
			// Per category
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_cat_default_quantity_per_product', 'no' ) ) {
				if ( '' != ( $value = apply_filters( 'alg_wc_pq_per_item_cat_default_quantity_per_product_value', 'no', $product_id ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_default', $value, $product_id );
				}
			}
			
			// All products
			if ( '' != ( $value = get_option( 'alg_wc_pq_default_per_item_quantity', 0 ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_default', $value, $product_id );
			}
			
		}
		
		
		return apply_filters( 'alg_wc_pq_get_product_qty_default', $default, $product_id );
	}
	
	/**
	 * get_product_qty_min_max.
	 *
	 * @version 4.5.14
	 * @since   1.0.0
	 */
	function get_product_qty_min_max( $product_id, $default, $min_or_max, $variation_id = 0 ) {
		
		if( $this->disable_product_id_by_url_option( $product_id ) ) {
			return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $default, $product_id );
		}
		
		if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
			// Check if "Sold individually" is enabled for the product
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_sold_individually() ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $default, $product_id );
			}
			$pid_per_product = $product_id;
			if( $variation_id > 0 ) {
				$pid_per_product = $variation_id;
			}
			// Per product
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_quantity_per_product', 'no', $min_or_max ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_quantity_per_product_value', 'no', $pid_per_product, $min_or_max ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $pid_per_product );
				}
			}
			
			// Per category
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_cat_quantity_per_product', 'no', $min_or_max ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_cat_quantity_per_product_value', 'no', $product_id, $min_or_max ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
				}
			}
			
			// Per attribute
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_attr_quantity_per_product', 'no', $min_or_max ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_attr_quantity_per_product_value', 'no', $product_id, $min_or_max ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
				}
			}
			
			// All products
			if ( 0 != ( $value = get_option( 'alg_wc_pq_' . $min_or_max . '_per_item_quantity', 0 ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
			}
		}
		return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $default, $product_id );
	}
	
		/**
	 * get_product_qty_min_max_allvar.
	 *
	 * @version 4.5.14
	 * @since   1.0.0
	 */
	function get_product_qty_min_max_allvar( $product_id, $default, $min_or_max ) {
		if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
			// Check if "Sold individually" is enabled for the product
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_sold_individually() ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $default, $product_id );
			}
			// Per product
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_quantity_per_product', 'no', $min_or_max ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_quantity_per_product_value_allvar', $default, $product_id, $min_or_max ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
				}
			}
			// All products
			if ( 0 != ( $value = get_option( 'alg_wc_pq_' . $min_or_max . '_per_item_quantity', 0 ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
			}
		}
		return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $default,  $product_id );
	}

	/**
	 * set_quantity_input_min_max_variation.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function set_quantity_input_min_max_variation( $args, $_product, $_variation ) {
		if(empty($args)) {
			return $args;
		}
		$variation_id = $this->get_product_id( $_variation );
		$args['min_qty'] = $this->get_product_qty_min_max( $variation_id, $args['min_qty'], 'min' );
		$args['max_qty'] = $this->get_product_qty_min_max( $variation_id, $args['max_qty'], 'max' );
		$_max = $_variation->get_max_purchase_quantity();
		
		/*
		if ( -1 != $_max && $args['min_qty'] > $_max ) {
			$args['min_qty'] = $_max;
		}
		*/
		
		if ( -1 != $_max && $args['max_qty'] > $_max ) {
			$args['max_qty'] = $_max;
		}
		
		if ( $args['min_qty'] < 0 ) {
			$args['min_qty'] = '';
		}
		if ( $args['max_qty'] < 0 ) {
			$args['max_qty'] = '';
		}
		return $args;
	}

	/**
	 * disable_purchased_products.
	 *
	 * @version 4.5.10
	 * @since   4.5.10
	 * @todo    [dev] (important) rename this (and probably some other `set_...()` functions)
	 */

	 function disable_purchased_products( $is_purchasable, $_product ){
	
		$value = $this->get_product_qty_min_max( $this->get_product_id( $_product ), 0, 'min' );
		$hide_add_to_cart = get_option( 'alg_wc_pq_min_hide_add_to_cart_less_stock', 'no' );
		if($hide_add_to_cart == 'yes') {
			if ( $_product->get_manage_stock() ) {
				
				$is_stock_less_than_min = ( (float) $_product->get_stock_quantity() < (float) $value );
				if($is_stock_less_than_min) {
					return false;
				}
				
			}
		}
		

		
		return $is_purchasable;
	}

	/**
	 * set_quantity_input_min_or_max.
	 *
	 * @version 1.7.0
	 * @since   1.6.0
	 * @todo    [dev] (important) rename this (and probably some other `set_...()` functions)
	 */
	function set_quantity_input_min_or_max( $qty, $_product, $min_or_max ) {
		$value = $this->get_product_qty_min_max( $this->get_product_id( $_product ), $qty, $min_or_max );
		$_max  = $_product->get_max_purchase_quantity();
		$return = ( -1 == $_max || $value < $_max ? $value : $_max );
		return $return;
	}

	/**
	 * set_quantity_input_min.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function set_quantity_input_min( $qty, $_product ) {
		return $this->set_quantity_input_min_or_max( $qty, $_product, 'min' );
	}
	
	/**
	 * store_api_product_min_quantity.
	 *
	 * @version 4.5.15
	 * @since   1.0.0
	 */
	function store_api_product_min_quantity( $qty, $_product, $cart_item ) {

		if ( 'yes' === get_option( 'alg_wc_pq_advance_wc_block_api', 'no' ) ) {

			$return_min = $this->set_quantity_input_min_or_max( $qty, $_product, 'min' );
			$return = $return_min;
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) && !empty( $return_min ) ) {
				if ( fmod($return_min, 1) !== 0.00 ) {
					// return decimal
					return $return_min;
				} else {
					// return intiger
					$return = (int) $return_min;
					if ($return < 1) {
						$return = 1;
					}
					return (int) $return;
				}
			} else {
				$return = (int) $return_min;
				if ( $return < 1 ) {
					$return = 1;
				}
				return (int) $return;
			}
			return $return_min;
		} else {
			return $this->set_quantity_input_min_or_max( $qty, $_product, 'min' );
		}
	}

	/**
	 * set_quantity_input_max.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function set_quantity_input_max( $qty, $_product ) {
		return $this->set_quantity_input_min_or_max( $qty, $_product, 'max' );
	}
	
	/**
	 * store_api_product_max_quantity.
	 *
	 * @version 4.5.15
	 * @since   1.0.0
	 */
	function store_api_product_max_quantity( $qty, $_product, $cart_item ) {
		
		if ( 'yes' === get_option( 'alg_wc_pq_advance_wc_block_api', 'no' ) ) {
			$return = $this->set_quantity_input_min_or_max( $qty, $_product, 'max' );
		
			$return_max = $return;
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) && !empty( $return_max ) ) {
				if ( fmod( $return_max, 1 ) !== 0.00) {
					// return decimal
					return $return_max;
				} else {
					// return intiger
					$return = (int) $return_max;
					if($return < 1) {
						$return = '';
					}
					return $return;
				}
			} else {
				$return = (int) $return_max;
				if ($return < 1) {
					$return = '';
				}
				return $return;
			}
			return $return;
		} else {
			return $this->set_quantity_input_min_or_max( $qty, $_product, 'max' );
		}
	}

	/**
	 * block_checkout.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function block_checkout() {
		if ( ! isset( WC()->cart ) ) {
			return;
		}
		if ( ! is_checkout() ) {
			return;
		}
		// $cart_item_quantities = WC()->cart->get_cart_item_quantities();
		$cart_item_quantities = $this->get_cart_item_quantities();
		
		if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
			return;
		} 
		$cart_total_quantity = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		// Max quantity
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			if ( ! $this->check_min_max( 'max', $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Min quantity
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			if ( ! $this->check_min_max( 'min', $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			if ( ! $this->check_step( $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				if ( ! $this->check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, false, true ) ) {
					wp_safe_redirect( wc_get_cart_url() );
					exit;
				}
			}
		}
	}

	/**
	 * check_order_quantities.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [dev] code refactoring min/max (same in `block_checkout()`)
	 */
	function check_order_quantities() {
		if ( ! isset( WC()->cart ) ) {
			return;
		}
		
		// $cart_item_quantities = WC()->cart->get_cart_item_quantities();
		$cart_item_quantities = $this->get_cart_item_quantities();
		if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
			return;
		}
		$cart_total_quantity = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		$_is_cart = is_cart();
		// Max quantity
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			$this->check_min_max( 'max', $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Min quantity
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			$this->check_min_max( 'min', $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			$this->check_step( $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				$this->check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, $_is_cart, false );
			}
		}
	}

	/**
	 * get_min_max_cart_total_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_min_max_cart_total_qty( $min_or_max ) {
		return get_option( 'alg_wc_pq_' . $min_or_max . '_cart_total_quantity', 0 );
	}
	
	/**
	 * get_min_max_cart_total_allvar_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_min_max_cart_total_allvar_qty( $min_or_max ) {
		return get_option( 'alg_wc_pq_' . $min_or_max . '_cart_total_quantity', 0 );
	}

	/**
	 * check_min_max_cart_total_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) {
		if ( 0 != ( $min_or_max_cart_total_quantity = $this->get_min_max_cart_total_qty( $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $cart_total_quantity > $min_or_max_cart_total_quantity ) ||
				( 'min' === $min_or_max && $cart_total_quantity < $min_or_max_cart_total_quantity )
			) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * check_min_max_cart_total_allvar_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_min_max_cart_total_allvar_qty( $min_or_max, $cart_total_quantity ) {
		if ( 0 != ( $min_or_max_cart_total_quantity = $this->get_min_max_cart_total_allvar_qty( $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $cart_total_quantity > $min_or_max_cart_total_quantity ) ||
				( 'min' === $min_or_max && $cart_total_quantity < $min_or_max_cart_total_quantity )
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * check_product_min_max.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_product_min_max( $product_id, $min_or_max, $quantity ) {
		if( $this->disable_product_id_by_url_option( $product_id ) ) {
			return true;
		}
		if ( 0 != ( $product_min_max = $this->get_product_qty_min_max( $product_id, 0, $min_or_max ) ) ) {
			if($min_or_max === 'max' && $product_min_max == ''){
				return true;
			}
			if (
				( 'max' === $min_or_max && $quantity > $product_min_max ) ||
				( 'min' === $min_or_max && $quantity < $product_min_max )
			) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * check_product_min_max_allvar.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_product_min_max_allvar( $product_id, $min_or_max, $quantity ) {
		if ( 0 != ( $product_min_max = $this->get_product_qty_min_max_allvar( $product_id, 0, $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $quantity > $product_min_max ) ||
				( 'min' === $min_or_max && $quantity < $product_min_max )
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * check_min_max.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function check_min_max( $min_or_max, $cart_item_quantities, $cart_total_quantity, $_is_cart, $_return ) {
		// Cart total quantity
		if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
			if ( $_return ) {
				return false;
			} else {
				$this->messenger->print_message( $min_or_max . '_cart_total_quantity', $_is_cart, $this->get_min_max_cart_total_qty( $min_or_max ), $cart_total_quantity );
			}
		}
		
		// Per category quantity
		if ( 'yes' === get_option( 'alg_wc_pq_' .$min_or_max. '_section_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_per_cat_item_quantity_per_product' , 'no' ) ) {
			$cartitem_by_category = $this->get_cartitem_by_category();
			if(isset($cartitem_by_category) && !empty($cartitem_by_category) && count($cartitem_by_category) > 0)
			{
				foreach($cartitem_by_category as $category_id=>$count)
				{
					$term_meta = get_option( "taxonomy_product_cat_$category_id" );
					if (!empty($term_meta) && is_array($term_meta))
					{
						$alg_wc_pq_min_or_max = 'alg_wc_pq_'.$min_or_max;
						$cat_quantity = ( isset($term_meta[$alg_wc_pq_min_or_max]) ) ? (int) $term_meta[$alg_wc_pq_min_or_max] :  0;
						if ($cat_quantity > 0)
						{
							$trm = get_term_by( 'id', $category_id, 'product_cat' );
							if ($min_or_max=='max')
							{
								if($cat_quantity < $count)
								{
									$message_template = get_option( $alg_wc_pq_min_or_max.'_cat_message',
										__( 'Maximum allowed quantity for category '.$trm->name.' is '.$cat_quantity.'. Your current quantity for this category is '.$count.'.', 'product-quantity-for-woocommerce' ) );
									$_notice = str_replace(array('%category_title%','%max_per_item_quantity%','%item_quantity%'),array($trm->name,$cat_quantity,$count),$message_template);
									wc_add_notice( $_notice, 'error' );
									return false;
								}
							}
							if ($min_or_max=='min')
							{
								if($cat_quantity > $count)
								{
									$message_template = get_option( $alg_wc_pq_min_or_max.'_cat_message',
										__( 'Minimum allowed quantity for category '.$trm->name.' is '.$cat_quantity.'. Your current quantity is '.$count.'.', 'product-quantity-for-woocommerce' ) );
									$_notice = str_replace(array('%category_title%','%min_per_item_quantity%','%item_quantity%'),array($trm->name,$cat_quantity,$count),$message_template);
									wc_add_notice( $_notice, 'error' );
									return false;
								}
							}
						}
					}
				}
			}
		}
		
		
		// Per item quantity
		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {

			if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( $min_or_max . '_per_item_quantity', $_is_cart, $this->get_product_qty_min_max( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
				}
			}
		}
		
		
		// Per item quantity for all variation
		$cart_item_quantities = $this->get_cartitem_groupby_parent_id();
		if ($cart_item_quantities && count($cart_item_quantities)) {
			foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
				if ( ! $this->check_product_min_max_allvar( $product_id, $min_or_max, $cart_item_quantity ) ) {
					if ( $_return ) {
						return false;
					} else {
						$this->messenger->print_message( $min_or_max . '_per_item_quantity', $_is_cart, $this->get_product_qty_min_max_allvar( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
					}
				}
			}
		}
		
		// per attribute
		if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_per_attribute_item_quantity' , 'no' ) ) {
			$cart_by_arranged_attr = $this->get_cartitem_by_product_attribute();
			if(isset($cart_by_arranged_attr) && !empty($cart_by_arranged_attr) && count($cart_by_arranged_attr) > 0)
			{
				foreach( $cart_by_arranged_attr as $attr_item_id=>$count )
				{
					$term_meta = get_option( "taxonomy_product_attribute_item_$attr_item_id" );
					if (!empty($term_meta) && is_array($term_meta))
					{
						$alg_wc_pq_min_or_max = 'alg_wc_pq_'.$min_or_max;
						$attr_quantity = $term_meta[$alg_wc_pq_min_or_max];
						if ( $attr_quantity > 0 ) 
						{
							
							if ($min_or_max=='max')
							{
								if($attr_quantity < $count)
								{
									if( !empty( $this->attribute_taxonomies ) ) {
										foreach( $this->attribute_taxonomies as $tax ) {
											$name = alg_pq_wc_attribute_taxonomy_name( $tax->attribute_name );
											$trm = get_term_by( 'id', $attr_item_id, $name );
											
											if ($min_or_max=='max' && !empty($trm) ) {
												$message_template = get_option( $alg_wc_pq_min_or_max.'_per_attribute_message',
													__( 'Maximum allowed quantity for attribute '.$trm->name.' is '.$attr_quantity.'. Your current quantity for this attribute is '.$count.'.', 'product-quantity-for-woocommerce' ) );
												$_notice = str_replace(array('%attribute_title%','%max_per_item_quantity%','%item_quantity%'),array($trm->name,$attr_quantity,$count),$message_template);
												
												if ( $_return ) {
													return false;
												} else {
													if ( $_is_cart ) {
													wc_print_notice( $_notice, get_option( 'alg_wc_pq_cart_notice_type', 'notice' ) );
													} else {
														wc_add_notice( $_notice, 'error' );
													}
												}
											}
										}
									}
								}
							}
							
							
							if ($min_or_max=='min')
							{
								if($attr_quantity > $count)
								{
									if( !empty( $this->attribute_taxonomies ) ) {
										foreach( $this->attribute_taxonomies as $tax ) {
											$name = alg_pq_wc_attribute_taxonomy_name( $tax->attribute_name );
											$trm = get_term_by( 'id', $attr_item_id, $name );
											
											if ($min_or_max=='min' && !empty($trm) ) {
												$message_template = get_option( $alg_wc_pq_min_or_max.'_per_attribute_message',
													__( 'Maximum allowed quantity for attribute '.$trm->name.' is '.$attr_quantity.'. Your current quantity for this attribute is '.$count.'.', 'product-quantity-for-woocommerce' ) );
												$_notice = str_replace(array('%attribute_title%','%min_per_item_quantity%','%item_quantity%'),array($trm->name,$attr_quantity,$count),$message_template);
												
												if ( $_return ) {
													return false;
												} else {
													if ( $_is_cart ) {
													wc_print_notice( $_notice, get_option( 'alg_wc_pq_cart_notice_type', 'notice' ) );
													} else {
														wc_add_notice( $_notice, 'error' );
													}
												}
											}
										}
									}
								}
							}
							
							
						}
					}
				}
			}
		}

		// Passed
		if ( $_return ) {
			return true;
		}
	}

	/**
	 * get_product_exact_qty.
	 *
	 * @version 1.8.0
	 * @since   1.5.0
	 * @todo    [feature] (maybe) total qty of item in cart
	 * @todo    [feature] (maybe) total items in cart
	 */
	function get_product_exact_qty( $product_id, $allowed_or_disallowed, $default_exact_qty = '', $variation_id = 0 ) {
		$per_product_id = $product_id;
		if( $variation_id > 0 ) {
			$per_product_id = $variation_id;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
			if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_value', '', $per_product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product_cat', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_cat_value', '', $product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product_attr', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_attr_value', '', $per_product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else {
				return ( '' !== ( $exact_qty_all_products = get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed, '' ) ) ? $exact_qty_all_products : $default_exact_qty );
			}
		} else {
			return $default_exact_qty;
		}
	}
	
	/**
	 * get_product_exact_qty_allvar.
	 *
	 * @version 1.8.0
	 * @since   1.5.0
	 * @todo    [feature] (maybe) total qty of item in cart
	 * @todo    [feature] (maybe) total items in cart
	 */
	function get_product_exact_qty_allvar( $product_id, $allowed_or_disallowed, $default_exact_qty = '', $variation_id = 0 ) {
		$per_product_id = $product_id;
		if( $variation_id > 0 ) {
			$per_product_id = $variation_id;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
			if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_value_allvar', '', $per_product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product_cat', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_cat_value', '', $product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product_attr', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_attr_value', '', $per_product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else {
				return ( '' !== ( $exact_qty_all_products = get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed, '' ) ) ? $exact_qty_all_products : $default_exact_qty );
			}
		} else {
			return $default_exact_qty;
		}
	}

	/**
	 * process_exact_qty_option.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] (important) qty range in `print_message()`
	 * @todo    [dev] qty range: power of X (i.e. instead of adding range step)
	 */
	function process_exact_qty_option( $qty_option ) {
		$_qty = array_map( 'trim', explode( ',', $qty_option ) );
		$qty  = array();
		foreach ( $_qty as $value ) {
			if ( false !== strpos( $value, '[' ) ) {
				if ( 0 === strpos( $value, '[' ) && ( ( strlen( $value ) - 1 ) == strpos( $value, ']' ) ) ) {
					$value = substr( $value, 1, ( strlen( $value ) - 2 ) );
					$value = array_map( 'trim', explode( '|', $value ) );
					if ( 2 === count( $value ) ) {
						$range = explode( '-', $value[0] );
						if ( 2 === count( $range ) ) {
							for ( $i = $range[0]; $i <= $range[1]; $i += $value[1] ) {
								$qty[] = $i;
							}
						} // else skipping the value (wrong format)
					} // else skipping the value (wrong format)
				} // else skipping the value (wrong format)
			} else {
				$qty[] = $value;
			}
		}
		return $qty;
	}

	/**
	 * check_product_exact_qty.
	 *
	 * @version 4.5.13
	 * @since   1.5.0
	 * @todo    [dev] (important) rethink qty correction on `disallowed`
	 * @todo    [dev] (important) check if all `$product_exact_qty` elements are `is_numeric()`
	 * @todo    [dev] (important) check if possible float and int comparison works properly in `abs( $quantity - $closest ) > abs( $item - $quantity )`
	 */
	function check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, $do_fix = false, $variation_id = 0 ) {
		if( $this->disable_product_id_by_url_option( $product_id ) ) {
			return true;
		}
		
		$product_exact_qty = $this->get_product_exact_qty( $product_id, $allowed_or_disallowed, '', $variation_id );

		if ( '' != $product_exact_qty ) {
			$product_exact_qty = $this->process_exact_qty_option( $product_exact_qty );
			sort( $product_exact_qty );
			$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $cart_item_quantity, $product_exact_qty ) : ! in_array( $cart_item_quantity, $product_exact_qty ) );
			if ( ! $do_fix ) {
				
				if( 'yes' === get_option( 'alg_wc_pq_exact_subset_sum_allowed_enabled', 'no' )) {
					/*
					$all_subset_sums = $this->subset_sums($product_exact_qty, $cart_item_quantity);
					$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $cart_item_quantity, $all_subset_sums ) : ! in_array( $cart_item_quantity, $all_subset_sums ) );
					*/
					if( 'allowed' === $allowed_or_disallowed ) {
						$is_valid = $this->is_subset_sum($product_exact_qty, count($product_exact_qty), $cart_item_quantity);
					} else {
						$is_valid = ! $this->is_subset_sum($product_exact_qty, count($product_exact_qty), $cart_item_quantity);
					}
				}
				
				return $is_valid;
			} elseif ( ! $is_valid ) {
				if ( 'allowed' === $allowed_or_disallowed ) {
					$closest = null;
					foreach ( $product_exact_qty as $item ) {
						if ( $closest === null || abs( $cart_item_quantity - $closest ) > abs( $item - $cart_item_quantity ) ) {
							$closest = $item;
						}
					}
					return ( null !== $closest ? ( $closest - ( $cart_item_quantity - $quantity ) ) : $quantity );
				} else { // 'disallowed'
					$_cart_item_quantity = $cart_item_quantity;
					while ( true ) {
						$_cart_item_quantity++;
						if ( ! in_array( $_cart_item_quantity, $product_exact_qty ) ) {
							return ( $_cart_item_quantity - ( $cart_item_quantity - $quantity ) );
						}
					}
				}
			}
		}
		return ( ! $do_fix ? true : $quantity );
	}
	
	
	/**
	 * check_product_exact_qty.
	 *
	 * @version 4.5.13
	 * @since   1.5.0
	 * @todo    [dev] (important) rethink qty correction on `disallowed`
	 * @todo    [dev] (important) check if all `$product_exact_qty` elements are `is_numeric()`
	 * @todo    [dev] (important) check if possible float and int comparison works properly in `abs( $quantity - $closest ) > abs( $item - $quantity )`
	 */
	function check_product_exact_qty_allvar( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, $do_fix = false, $variation_id = 0 ) {
		if( $this->disable_product_id_by_url_option( $product_id ) ) {
			return true;
		}
		
		$product_exact_qty = $this->get_product_exact_qty_allvar( $product_id, $allowed_or_disallowed, '' );
		if ( '' != $product_exact_qty ) {
			$product_exact_qty = $this->process_exact_qty_option( $product_exact_qty );
			sort( $product_exact_qty );
			$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $cart_item_quantity, $product_exact_qty ) : ! in_array( $cart_item_quantity, $product_exact_qty ) );
			if ( ! $do_fix ) {
				
				if ( ! $is_valid ) {
					
					if( 'yes' === get_option( 'alg_wc_pq_exact_subset_sum_allowed_enabled', 'no' ) ) {
						/*
						$all_subset_sums = $this->subset_sums($product_exact_qty, $cart_item_quantity);
						$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $cart_item_quantity, $all_subset_sums ) : ! in_array( $cart_item_quantity, $all_subset_sums ) );
						*/
						if ( 'allowed' === $allowed_or_disallowed ) {
							$is_valid = $this->is_subset_sum( $product_exact_qty, count( $product_exact_qty ), $cart_item_quantity);
						} else {
							$is_valid = ! $this->is_subset_sum( $product_exact_qty, count( $product_exact_qty ), $cart_item_quantity);
						}
					}
				}
				
				return $is_valid;
			} elseif ( ! $is_valid ) {
				if ( 'allowed' === $allowed_or_disallowed ) {
					$closest = null;
					foreach ( $product_exact_qty as $item ) {
						if ( $closest === null || abs( $cart_item_quantity - $closest ) > abs( $item - $cart_item_quantity ) ) {
							$closest = $item;
						}
					}
					return ( null !== $closest ? ( $closest - ( $cart_item_quantity - $quantity ) ) : $quantity );
				} else { // 'disallowed'
					$_cart_item_quantity = $cart_item_quantity;
					while ( true ) {
						$_cart_item_quantity++;
						if ( ! in_array( $_cart_item_quantity, $product_exact_qty ) ) {
							return ( $_cart_item_quantity - ( $cart_item_quantity - $quantity ) );
						}
					}
				}
			}
		}
		return ( ! $do_fix ? true : $quantity );
	}

	/**
	 * is_subset_sum.
	 *
	 * @version 4.5.13
	 * @since   4.5.13
	 */

	function is_subset_sum($set, $n, $sum) {
		// Base Cases
		if ($sum == 0)
			return true;
		if ($n == 0 && $sum != 0)
			return false;
		  
		// If last element is greater
		// than sum, then ignore it
		if ($set[$n - 1] > $sum)
			return $this->is_subset_sum($set, $n - 1, $sum);
		  
		/* else, check if sum can be 
		   obtained by any of the following
			(a) including the last element
			(b) excluding the last element */
		return $this->is_subset_sum($set, $n - 1, $sum) || $this->is_subset_sum($set, $n - 1, $sum - $set[$n - 1]);
	}

	/**
	 * subset_sums.
	 *
	 * @version 4.5.13
	 * @since   4.5.13
	 */
	function subset_sums($arr, $incart_qty = 0) {
		$return = array();
		$n = sizeof($arr);
		// There are totoal 2^n subsets
		$total = 1 << $n;

		// Consider all numbers
		// from 0 to 2^n - 1
		for ($i = 0; $i < $total; $i++)
		{
			$sum = 0;
	 
			// Consider binary representation of
			// current i to decide which elements
			// to pick.
			for ($j = 0; $j < $n; $j++)
				if ($i & (1 << $j))
					$sum += $arr[$j];
	 
			// Print sum of picked elements.
			if($sum > 0){
				$return[] = $sum;
				$return[] = $sum + $incart_qty;
				
				$return = array_unique($return);
				sort($return);
				$return = array_values($return);
			}
		}
		
		return $return;
	}

	/**
	 * check_exact_qty.
	 *
	 * @version 1.7.0
	 * @since   1.5.0
	 */
	function check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, $_is_cart, $_return ) {
		// Per category quantity
		if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_per_cat_item_quantity_per_product' , 'no' ) ) {

			$cartitem_by_category = $this->get_cartitem_by_category();
			if(isset($cartitem_by_category) && !empty($cartitem_by_category) && count($cartitem_by_category) > 0)
			{
				foreach($cartitem_by_category as $category_id=>$count)
				{
					$term_meta = get_option( "taxonomy_product_cat_$category_id" );
					if (!empty($term_meta) && is_array($term_meta))
					{
						$alg_wc_pq_allowed_or_disallowed = 'alg_wc_pq_exact_qty_'.$allowed_or_disallowed;
						$cat_quantity = $term_meta[$alg_wc_pq_allowed_or_disallowed];
						if ( '' != $cat_quantity ) 
						{
							
							$cat_quantity = $this->process_exact_qty_option( $cat_quantity );
							sort( $cat_quantity );
							$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $count, $cat_quantity ) : ! in_array( $count, $cat_quantity ) );
							if(!$is_valid)
							{
								$trm = get_term_by( 'id', $category_id, 'product_cat' );
								if ($allowed_or_disallowed=='allowed')
								{
									$message_template = get_option( $alg_wc_pq_allowed_or_disallowed . '_cat_message',
										__( 'Allowed quantity for category ' . $trm->name . ' is ' . implode(',',$cat_quantity) . '. Your current quantity is ' . $count , 'product-quantity-for-woocommerce' ) );
									$_notice = str_replace(array('%category_title%','%allowed_quantity%','%quantity%'),array($trm->name,implode(', ',$cat_quantity),$count),$message_template);
									
									if ( $_return ) {
										return false;
									} else {
										if ( $_is_cart ) {
										wc_print_notice( $_notice, get_option( 'alg_wc_pq_cart_notice_type', 'notice' ) );
										} else {
											wc_add_notice( $_notice, 'error' );
										}
									}
								}
							}
						}
					}
				}
				
			}
		}

		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
			
			if ( ! $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $cart_item_quantity, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, $_is_cart, $this->get_product_exact_qty( $product_id, $allowed_or_disallowed ), $cart_item_quantity, $product_id );
				}
			}
		}
		
		// Per item quantity for all variation
		$cart_item_quantities = $this->get_cartitem_groupby_parent_id();
		if ($cart_item_quantities && count($cart_item_quantities)) {
			foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
				if ( ! $this->check_product_exact_qty_allvar( $product_id, $allowed_or_disallowed,  $cart_item_quantity, $cart_item_quantity ) ) {
					if ( $_return ) {
						return false;
					} else {
						$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, $_is_cart, $this->get_product_exact_qty_allvar( $product_id, $allowed_or_disallowed ), $cart_item_quantity, $product_id );
					}
				}
			}
		}
		
		// per attribute
		if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_per_attribute_item_quantity' , 'no' ) ) {
			$cart_by_arranged_attr = $this->get_cartitem_by_product_attribute();
			if(isset($cart_by_arranged_attr) && !empty($cart_by_arranged_attr) && count($cart_by_arranged_attr) > 0)
			{
				foreach( $cart_by_arranged_attr as $attr_item_id=>$count )
				{
					$term_meta = get_option( "taxonomy_product_attribute_item_$attr_item_id" );
					if (!empty($term_meta) && is_array($term_meta))
					{
						$alg_wc_pq_allowed_or_disallowed = 'alg_wc_pq_exact_qty_'.$allowed_or_disallowed;
						$attr_quantity = $term_meta[$alg_wc_pq_allowed_or_disallowed];
						if ( '' != $attr_quantity ) 
						{
							$attr_quantity = $this->process_exact_qty_option( $attr_quantity );
							sort( $attr_quantity );

							$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $count, $attr_quantity ) : ! in_array( $count, $attr_quantity ) );
							if(!$is_valid)
							{
								if( !empty( $this->attribute_taxonomies ) ) {
									foreach( $this->attribute_taxonomies as $tax ) {
										$name = alg_pq_wc_attribute_taxonomy_name( $tax->attribute_name );
										$trm = get_term_by( 'id', $attr_item_id, $name );

											if ($allowed_or_disallowed=='allowed' && !empty($trm) ) {
												$message_template = get_option( $alg_wc_pq_allowed_or_disallowed . '_attribute_item_message',
												__( 'Allowed quantity for attribute ' . $trm->name . ' is ' . implode(',',$attr_quantity) . '. Your current quantity for this attribute item is ' . $count , 'product-quantity-for-woocommerce' ) );
												$_notice = str_replace(array('%attribute_item_title%','%allowed_quantity%','%quantity%'),array($trm->name,implode(', ',$attr_quantity),$count),$message_template);
												
												if ( $_return ) {
													return false;
												} else {
													if ( $_is_cart ) {
													wc_print_notice( $_notice, get_option( 'alg_wc_pq_cart_notice_type', 'notice' ) );
													} else {
														wc_add_notice( $_notice, 'error' );
													}
												}
											}
									}
								}
							}
						}
					}
				}
			}
		}

		// Passed
		if ( $_return ) {
			return true;
		}
	}

	/**
	 * check_product_step.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] `$multiplier` should be calculated automatically according to the `$qty_step_settings` value (same in `force_js_check_step()`)
	 */
	function check_product_step( $product_id, $quantity, $do_fix = false ) {
		$product_qty_step = $this->get_product_qty_step( $product_id );
		if(!is_numeric($product_qty_step)){
			$product_qty_step = floatval($product_qty_step);
		}
		if ( 0 != $product_qty_step ) {
			$min_value = $this->get_product_qty_min_max( $product_id, 0, 'min' );
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
				$multiplier         = floatval( 1000000 );
				$_min_value         = intval( round( floatval( $min_value )        * $multiplier ) );
				$_quantity          = intval( round( floatval( $quantity )         * $multiplier ) );
				$_product_qty_step  = intval( round( floatval( $product_qty_step ) * $multiplier ) );
			} else {
				$_min_value         = (int) $min_value;
				$_quantity          = (int) $quantity;
				$_product_qty_step  = (int) $product_qty_step;
			}
			$_quantity = $_quantity - $_min_value;
			if( $_product_qty_step > 1 ) {
				$_reminder = $_quantity % $_product_qty_step;
			}else{
				$_product_qty_step = floatval($_product_qty_step);
				$_reminder = fmod($_quantity, $_product_qty_step);
			}
			$is_valid  = ( 0 == $_reminder );
			if ( ! $do_fix ) {
				return $is_valid;
			} elseif ( ! $is_valid ) {
				$step_auto_correct = get_option( 'alg_wc_pq_add_to_cart_validation_step_auto_correct', 'round' );
				$extra_qty = ( 'round_down' != $step_auto_correct && ( 'round_up' == $step_auto_correct || $_reminder * 2 >= $_product_qty_step ) ?
					$_product_qty_step : 0 );
				$quantity = $_quantity + $extra_qty - $_reminder + $_min_value;
				if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
					$quantity = $quantity / $multiplier;
				}
				return $quantity;
			}
		}
		return ( ! $do_fix ? true : $quantity );
	}

	/**
	 * get_step_cart_total_qty.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function get_step_cart_total_qty() {
		return get_option( 'alg_wc_pq_step_cart_total_quantity', 0 );
	}

	/**
	 * check_step_cart_total_qty.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] (important) (maybe) code refactoring (merge with `check_product_step()`)
	 */
	function check_step_cart_total_qty( $cart_total_quantity, $do_fix = false, $product_qty = 0 ) {
		if ( 0 != ( $step_cart_total_quantity = $this->get_step_cart_total_qty() ) ) {
			$is_decimal = ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) );
			if ( $is_decimal ) {
				$multiplier                 = floatval( 1000000 );
				$_cart_total_quantity       = intval( round( floatval( $cart_total_quantity )      * $multiplier ) );
				$_step_cart_total_quantity  = intval( round( floatval( $step_cart_total_quantity ) * $multiplier ) );
			} else {
				$_cart_total_quantity       = $cart_total_quantity;
				$_step_cart_total_quantity  = $step_cart_total_quantity;
			}
			
			if($_step_cart_total_quantity > 0){
				$_reminder = $_cart_total_quantity % $_step_cart_total_quantity;
			}else{
				$_step_cart_total_quantity = floatval($_step_cart_total_quantity);
				$_reminder = fmod($_cart_total_quantity, $_step_cart_total_quantity);
			}
			
			$is_valid  = ( 0 == $_reminder );
			if ( ! $do_fix ) {
				return $is_valid;
			} elseif ( ! $is_valid ) {
				if ( $is_decimal ) {
					$product_qty = intval( round( floatval( $product_qty ) * $multiplier ) );
				}
				$step_auto_correct = get_option( 'alg_wc_pq_add_to_cart_validation_step_auto_correct', 'round' );
				$extra_qty = ( 'round_down' != $step_auto_correct && ( 'round_up' == $step_auto_correct || $_reminder * 2 >= $_step_cart_total_quantity ) ?
					$_step_cart_total_quantity : 0 );
				$product_qty = $product_qty + $extra_qty - $_reminder;
				if ( $is_decimal ) {
					$product_qty = $product_qty / $multiplier;
				}
				return $product_qty;
			}
		}
		return ( ! $do_fix ? true : $product_qty );
	}

	/**
	 * check_step.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] (maybe) force `min` in cart to `1` (as it may be zero now)
	 */
	function check_step( $cart_item_quantities, $cart_total_quantity, $_is_cart, $_return ) {
		// Cart total quantity
		if ( ! $this->check_step_cart_total_qty( $cart_total_quantity ) ) {
			if ( $_return ) {
				return false;
			} else {
				$this->messenger->print_message( 'step_cart_total_quantity', $_is_cart, $this->get_step_cart_total_qty(), $cart_total_quantity );
			}
		}
		// Per item step
		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
			if ( ! $this->check_product_step( $product_id, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( 'step_quantity', $_is_cart, $this->get_product_qty_step( $product_id ), $cart_item_quantity, $product_id );
				}
			}
		}
		// Passed
		if ( $_return ) {
			return true;
		}
	}
	
	/**
	 * check_step.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] (maybe) force `min` in cart to `1` (as it may be zero now)
	 */
	function get_quantity_with_sep( $qty ){
		/* ('yes' === apply_filters( 'alg_wc_pq_qty_dropdown_thousand_separator', 'no') ) */
		
		if ('yes' === get_option( 'alg_wc_pq_qty_dropdown_thousand_separator_enabled', 'no' ) ) {
			$sep = get_option( 'alg_wc_pq_qty_dropdown_thousand_separator', ',' );
			if( !empty($sep) && !empty($qty) )
			{
				if($qty < 100){
					return $qty;
				}else{
					return number_format($qty, 0, '', $sep);
				}
			}
		}
		return $qty;
	}

}

endif;

return new Alg_WC_PQ_Core();
