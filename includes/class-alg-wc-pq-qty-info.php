<?php
/**
 * Product Quantity for WooCommerce - Quantity Info Class
 *
 * @version 1.7.0
 * @since   1.7.0
 * @author  WPWhale
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
		// Quantity info on single product page
		if ( 'yes' === get_option( 'alg_wc_pq_qty_info_on_single_product', 'no' ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'output_qty_info_on_single_product' ), 31 );
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

	/**
	 * output_qty_info_on_single_product.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 * @todo    [dev] (important) info: position & priority (same for `loop`)
	 * @todo    [dev] info: variations (same for `loop`)
	 */
	function output_qty_info_on_single_product() {
		echo do_shortcode( get_option( 'alg_wc_pq_qty_info_on_single_product_content', $this->qty_info_default_content ) );
	}

	/**
	 * output_qty_info_on_loop.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function output_qty_info_on_loop() {
		echo do_shortcode( get_option( 'alg_wc_pq_qty_info_on_loop_content', $this->qty_info_default_content ) );
	}

}

endif;

return new Alg_WC_PQ_Quantity_Info();
