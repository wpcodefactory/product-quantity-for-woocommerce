<?php
/**
 * Product Quantity for WooCommerce - Admin Class
 *
 * @version 1.6.1
 * @since   1.6.1
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Admin' ) ) :

class Alg_WC_PQ_Admin {

	/**
	 * Constructor.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_pq_admin_columns_enabled', 'no' ) ) {
			add_filter( 'manage_edit-product_columns',        array( $this, 'add_admin_product_columns' ), PHP_INT_MAX );
			add_action( 'manage_product_posts_custom_column', array( $this, 'render_admin_product_columns' ), PHP_INT_MAX );
		}
	}

	/**
	 * add_admin_product_columns.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] `[alg_wc_pq_min_exact_allowed_product_qty]`
	 * @todo    [dev] `[alg_wc_pq_min_exact_disallowed_product_qty]`
	 */
	function add_admin_product_columns( $columns ) {
		$all_columns = array(
			'alg_wc_pq_min_qty'  => __( 'Min Qty', 'product-quantity-for-woocommerce' ),
			'alg_wc_pq_max_qty'  => __( 'Max Qty', 'product-quantity-for-woocommerce' ),
			'alg_wc_pq_qty_step' => __( 'Qty Step', 'product-quantity-for-woocommerce' ),
		);
		$columns_to_add = get_option( 'alg_wc_pq_admin_columns', array() );
		if ( empty( $columns_to_add ) ) {
			$columns_to_add = array_keys( $all_columns );
		}
		foreach ( $columns_to_add as $column_to_add ) {
			$columns[ $column_to_add ]  = $all_columns[ $column_to_add ];
		}
		return $columns;
	}

	/**
	 * render_admin_product_columns.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 */
	function render_admin_product_columns( $column ) {
		switch ( $column ) {
			case 'alg_wc_pq_min_qty':
				echo do_shortcode( '[alg_wc_pq_min_product_qty]' );
				break;
			case 'alg_wc_pq_max_qty':
				echo do_shortcode( '[alg_wc_pq_max_product_qty]' );
				break;
			case 'alg_wc_pq_qty_step':
				echo do_shortcode( '[alg_wc_pq_product_qty_step]' );
				break;
		}
	}

}

endif;

return new Alg_WC_PQ_Admin();
