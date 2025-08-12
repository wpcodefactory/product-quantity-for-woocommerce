<?php
/**
 * Product Quantity for WooCommerce - Functions
 *
 * @version 5.1.4
 * @since   5.1.4
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alg_wc_pq_enqueue_script' ) ) {
	/**
	 * alg_wc_pq_enqueue_script.
	 *
	 * @version 5.1.4
	 * @since   5.1.4
	 */
	function alg_wc_pq_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $args = array() ) {
		if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
			$src = str_replace( '.js', '.min.js', $src );
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $args );
	}
}
