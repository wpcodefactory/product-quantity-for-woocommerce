<?php
/**
 * Product Quantity for WooCommerce - Deprecated Functions
 *
 * Backwards-compatible wrappers for global functions renamed in 5.3.1.
 * Each shim calls `_deprecated_function()` so developers are notified
 * via the WordPress debug log, then proxies to the current function.
 *
 * @version 5.3.1
 * @since   5.3.1
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alg_wc_pq' ) ) {
	/**
	 * alg_wc_pq.
	 *
	 * @version    5.3.1
	 * @since      1.0.0
	 * @deprecated 5.3.1 Use wpfmmsq() instead.
	 *
	 * @return WPFMMSQ
	 */
	function alg_wc_pq() {
		_deprecated_function( __FUNCTION__, '5.3.1', 'wpfmmsq()' );

		return wpfmmsq();
	}
}

if ( ! function_exists( 'alg_wc_pq_check_free_active' ) ) {
	/**
	 * alg_wc_pq_check_free_active.
	 *
	 * @version    5.3.1
	 * @since      1.0.0
	 * @deprecated 5.3.1 Use wpfmmsq_check_free_active() instead.
	 */
	function alg_wc_pq_check_free_active() {
		_deprecated_function( __FUNCTION__, '5.3.1', 'wpfmmsq_check_free_active()' );
		wpfmmsq_check_free_active();
	}
}

if ( ! function_exists( 'alg_wc_pq_check_if_active_plugin' ) ) {
	/**
	 * alg_wc_pq_check_if_active_plugin.
	 *
	 * @version    5.3.1
	 * @since      1.6.3
	 * @deprecated 5.3.1 Use wpfmmsq_check_if_active_plugin() instead.
	 *
	 * @param   string  $plugin_dir      Plugin directory.
	 * @param   string  $plugin_file     Plugin file.
	 * @param   array   $active_plugins  Active plugins list.
	 *
	 * @return bool
	 */
	function alg_wc_pq_check_if_active_plugin( $plugin_dir, $plugin_file, $active_plugins ) {
		_deprecated_function( __FUNCTION__, '5.3.1', 'wpfmmsq_check_if_active_plugin()' );

		return wpfmmsq_check_if_active_plugin( $plugin_dir, $plugin_file, $active_plugins );
	}
}

if ( ! function_exists( 'alg_wc_pq_do_disable' ) ) {
	/**
	 * alg_wc_pq_do_disable.
	 *
	 * @version    5.3.1
	 * @since      1.6.3
	 * @deprecated 5.3.1 Use wpfmmsq_do_disable() instead.
	 *
	 * @param   string  $basename  Plugin basename.
	 *
	 * @return bool
	 */
	function alg_wc_pq_do_disable( $basename ) {
		_deprecated_function( __FUNCTION__, '5.3.1', 'wpfmmsq_do_disable()' );

		return wpfmmsq_do_disable( $basename );
	}
}

if ( ! function_exists( 'alg_wc_pq_wc_get_attribute_taxonomies' ) ) {
	/**
	 * alg_wc_pq_wc_get_attribute_taxonomies.
	 *
	 * @version    5.3.1
	 * @since      1.6.3
	 * @deprecated 5.3.1 Use wpfmmsq_wc_get_attribute_taxonomies() instead.
	 *
	 * @return array
	 */
	function alg_wc_pq_wc_get_attribute_taxonomies() {
		_deprecated_function( __FUNCTION__, '5.3.1', 'wpfmmsq_wc_get_attribute_taxonomies()' );

		return wpfmmsq_wc_get_attribute_taxonomies();
	}
}

if ( ! function_exists( 'alg_wc_pq_enqueue_script' ) ) {
	/**
	 * alg_wc_pq_enqueue_script.
	 *
	 * @version    5.3.1
	 * @since      5.1.4
	 * @deprecated 5.3.1 Use wpfmmsq_enqueue_script() instead.
	 *
	 * @param   string        $handle  Script handle.
	 * @param   string        $src     Script source.
	 * @param   array         $deps    Script dependencies.
	 * @param   string|false  $ver     Script version.
	 * @param   array         $args    Script args.
	 *
	 * @return void
	 */
	function alg_wc_pq_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $args = array() ) {
		_deprecated_function( __FUNCTION__, '5.3.1', 'wpfmmsq_enqueue_script()' );
		wpfmmsq_enqueue_script( $handle, $src, $deps, $ver, $args );
	}
}

if ( ! function_exists( 'alg_pq_wc_attribute_taxonomy_name' ) ) {
	/**
	 * alg_pq_wc_attribute_taxonomy_name.
	 *
	 * @version    5.3.1
	 * @since      5.3.1
	 * @deprecated 5.3.1 Use wpfmmsq_wc_attribute_taxonomy_name() instead.
	 *
	 * @param   string  $attribute_name  Attribute name.
	 *
	 * @return string
	 */
	function alg_pq_wc_attribute_taxonomy_name( $attribute_name ) {
		_deprecated_function( __FUNCTION__, '5.3.1', 'wpfmmsq_wc_attribute_taxonomy_name()' );

		return wpfmmsq_wc_attribute_taxonomy_name( $attribute_name );
	}
}

if ( ! function_exists( 'alg_pq_wc_sanitize_taxonomy_name' ) ) {
	/**
	 * alg_pq_wc_sanitize_taxonomy_name.
	 *
	 * @version    5.3.1
	 * @since      5.3.1
	 * @deprecated 5.3.1 Use wpfmmsq_wc_sanitize_taxonomy_name() instead.
	 *
	 * @param   string  $taxonomy  Taxonomy name.
	 *
	 * @return string
	 */
	function alg_pq_wc_sanitize_taxonomy_name( $taxonomy ) {
		_deprecated_function( __FUNCTION__, '5.3.1', 'wpfmmsq_wc_sanitize_taxonomy_name()' );

		return wpfmmsq_wc_sanitize_taxonomy_name( $taxonomy );
	}
}
