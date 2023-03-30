<?php
/**
 * Product Quantity for WooCommerce - Quantity Info Class
 *
 * @version 1.7.0
 * @since   1.7.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Quantity_Info' ) ) :

class Alg_WC_PQ_Quantity_Info {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		
		if(!isset($_GET['et_fb']) && !isset($_GET['et_bfb'])){
			
			if ( !empty(get_option( 'alg_wc_pq_exact_cart_total_quantity', 0 ) ) && 'yes' === get_option( 'alg_wc_pq_exact_cart_total_quantity_enabled', 'no' )) {
				add_action( 'woocommerce_check_cart_items', array( $this, 'set_exact_qty_for_cart' ));
			}
			// Quantity info on single product page
			if ( 'yes' === get_option( 'alg_wc_pq_qty_info_on_single_product', 'no' )  || 'yes' === get_option( 'alg_wc_pq_qty_info_on_single_product_custom_hook', 'no' )) {
				if ( 'yes' === get_option( 'alg_wc_pq_qty_info_on_single_product', 'no' ) ){
					add_action( 'woocommerce_single_product_summary', array( $this, 'output_qty_info_on_single_product' ), 31 );
				}
				
				if ( 'yes' === get_option( 'alg_wc_pq_qty_info_on_single_product_custom_hook', 'no' ) ){
					$hook = get_option( 'alg_wc_pq_qty_info_on_single_product_custom_hook_name', 'woocommerce_single_product_summary' );
					$hook_priority = get_option( 'alg_wc_pq_qty_info_on_single_product_custom_hook_priority', 31 );
					add_action( $hook, array( $this, 'output_qty_info_on_single_product' ), $hook_priority );
				}
			}
			// Quantity info on archives
			if ( 'yes' === get_option( 'alg_wc_pq_qty_info_on_loop', 'no' ) ) {
				add_action( 'woocommerce_after_shop_loop_item',   array( $this, 'output_qty_info_on_loop' ), 11 );
			}
			// Qty info default content
			$this->qty_info_default_content = '<p>' .
					'[alg_wc_pq_min_product_qty before="Minimum quantity is <strong>" after="</strong><br>"]' .
					'[alg_wc_pq_max_product_qty before="Maximum quantity is <strong>" after="</strong><br>"]' .
					'[alg_wc_pq_product_qty_step before="Step is <strong>" after="</strong><br>"]' .
				'</p>';
		}
	}

		function set_exact_qty_for_cart() 
	{
		// Only run in the Cart or Checkout pages
		if( is_cart() || is_checkout() ) {
			global $woocommerce;
			
			$alg_wc_pq_exact_cart_total_quantity = get_option( 'alg_wc_pq_exact_cart_total_quantity', '' );
			$alg_wc_pq_exact_cart_total_message = get_option( 'alg_wc_pq_exact_cart_total_message', 'Allowed order quantity is %min_cart_total_quantity%. Your current cart quantity is %cart_total_quantity%.' );
			// Set the minimum number of products before checking out
			$fixed_quantities = explode(',',$alg_wc_pq_exact_cart_total_quantity);
			// Get the Cart's total number of products
			$cart_num_products = WC()->cart->cart_contents_count;
			$cart_num_products = array_sum(alg_wc_pq()->core->get_cart_item_quantities());

			if( $cart_num_products > 0 && !in_array($cart_num_products,$fixed_quantities) ) {
				// Display our error message
				wc_add_notice( str_replace(array('%min_cart_total_quantity%','%cart_total_quantity%'), array($alg_wc_pq_exact_cart_total_quantity,$cart_num_products), $alg_wc_pq_exact_cart_total_message ),
				'error' );
			}
		}
	}
	
	/**
	 * output_qty_info_on_single_product.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 * @todo    [dev] (important) info: position & priority (same for `loop`)
	 * @todo    [dev] info: variations (same for `loop`)
	 */
	function output_qty_info_on_single_product() {
		if(!$this->alg_wc_pq_qty_info_is_disable()){
			echo do_shortcode( get_option( 'alg_wc_pq_qty_info_on_single_product_content', $this->qty_info_default_content ) );
		}
	}

	/**
	 * output_qty_info_on_loop.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function output_qty_info_on_loop() {
		if(!$this->alg_wc_pq_qty_info_is_disable()){
			echo do_shortcode( get_option( 'alg_wc_pq_qty_info_on_loop_content', $this->qty_info_default_content ) );
		}
	}
	
	/**
	 * alg_wc_pq_qty_info_is_disable.
	 *
	 * @version 1.3.9
	 * @since   1.3.3
	 */
	function alg_wc_pq_qty_info_is_disable() {
		global $product;
		if ( ! $product ) {
			return false;
		}
		
		if (alg_wc_pq()->core->disable_product_id_by_url_option( $product->get_id() )){
			return true;
		}

		$alg_wc_pq_qty_info_enable_per_category = get_option( 'alg_wc_pq_qty_info_enable_per_category', 'no' );
		$alg_wc_pq_qty_info_per_category_categories = get_option( 'alg_wc_pq_qty_info_per_category_categories', array() );
		
		if($alg_wc_pq_qty_info_enable_per_category == 'yes' ) {
			if(!empty($alg_wc_pq_qty_info_per_category_categories)) {
				if ( !empty($product) && $product->get_id() > 0 && ! is_admin() ) {
					$product_id = $product->get_id();
					$product_cats_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
					if(!empty($product_cats_ids)) {
						foreach($product_cats_ids as $cat_id) {
							if(in_array($cat_id, $alg_wc_pq_qty_info_per_category_categories)) {
								return true;
							}
						}
					}
					
				}
			}
		}
		return false;
	}

}

endif;

return new Alg_WC_PQ_Quantity_Info();
