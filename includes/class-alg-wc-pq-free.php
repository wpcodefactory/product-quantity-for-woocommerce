<?php
/**
 * Product Quantity for WooCommerce - Pro Class
 *
 * @version 1.8.0
 * @since   1.8.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Free' ) ) :

class Alg_WC_PQ_Free {

	public $attribute_taxonomies = array();
	
	/**
	 * Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 * @todo    [dev] maybe move here: `require_once( 'includes/settings/class-alg-wc-pq-metaboxes.php' );`
	 */
	function __construct() {
		
		add_filter( 'alg_wc_pq_quantity_step_per_product',           		array( $this, 'quantity_step_per_product' ) );
		add_filter( 'alg_wc_pq_quantity_step_per_product_value',     		array( $this, 'quantity_step_per_product_value' ), 10, 2 );

		
		add_filter( 'alg_wc_pq_per_item_quantity_per_product',       		array( $this, 'per_item_quantity_per_product' ), 10, 2 );
		add_filter( 'alg_wc_pq_per_item_quantity_per_product_value', 		array( $this, 'per_item_quantity_per_product_value' ), 10, 3 );
		
	}
	
	/**
	 * quantity_step_per_product.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function quantity_step_per_product( $value ) {
		return get_option( 'alg_wc_pq_step_per_product_enabled', 'no' );
	}
	
	/**
	 * per_item_quantity_per_product.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function per_item_quantity_per_product( $value, $min_or_max ) {
		return get_option( 'alg_wc_pq_' . $min_or_max . '_per_item_quantity_per_product', 'no' );
	}
	
	
	/**
	 * quantity_step_per_product_value.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function quantity_step_per_product_value( $value, $product_id ) {
		$product = wc_get_product($product_id);
		if('yes' == get_post_meta( $product_id, '_' . 'alg_wc_pq_min_allow_selling_below_stock', true )){
			// $product = wc_get_product($product_id);
			$stock = $product->get_stock_quantity();
			$min = get_post_meta( $product_id, '_' . 'alg_wc_pq_min', true );
			if($product->managing_stock() && $stock < $min){
				return 1;
			}
		}
		$step = get_post_meta( $product_id, '_' . 'alg_wc_pq_step', true );
		if ( 'yes' === get_option( 'alg_wc_pq_step_per_item_quantity_per_product_less2x', 'no' ) ) {
			if($step > 0){
				$step = floatval($step);
				$doublestep = $step * 2;
				$stock = $product->get_stock_quantity();
				if($stock < $doublestep){
					$step = $stock - $step;
				}
			}
		}
		return $step;
	}
	
	/**
	 * per_item_quantity_per_product_value.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function per_item_quantity_per_product_value( $value, $product_id, $min_or_max ) {
		if($min_or_max == 'min'){
			if('yes' == get_post_meta( $product_id, '_' . 'alg_wc_pq_min_allow_selling_below_stock', true )){
				$product = wc_get_product($product_id);
				$stock = $product->get_stock_quantity();
				$min = get_post_meta( $product_id, '_' . 'alg_wc_pq_min', true );
				if($product->managing_stock() && $stock <= $min){
					// return 1;
					return $stock;
				}
			}
		}
		return get_post_meta( $product_id, '_' . 'alg_wc_pq_' . $min_or_max, true );
	}

}

endif;

return new Alg_WC_PQ_Free();
