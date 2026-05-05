<?php
/**
 * Product Quantity for WooCommerce - Deprecated Hooks
 *
 * Adds backward-compatible aliases for hooks renamed in version 5.3.2.
 * Old hook names (alg_wc_pq_* and alg_wc_*) are forwarded to new hook names (wpfmmsq_*).
 *
 * @version 5.3.2
 * @since   5.3.1
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

/**
 * wpfmmsq_deprecated_hook_map.
 *
 * Returns the mapping of old hook names to new hook names.
 *
 * @version 5.3.2
 * @since   5.3.1
 *
 * @return array
 */
function wpfmmsq_deprecated_hook_map() {
	return array(
		'alg_wc_pq_settings'                                        => 'wpfmmsq_settings',
		'alg_wc_pq_advertise'                                       => 'wpfmmsq_advertise',
		'alg_wc_pq_check_user_role'                                 => 'wpfmmsq_check_user_role',
		'alg_wc_pq_cart_total_quantity'                             => 'wpfmmsq_cart_total_quantity',
		'alg_wc_pq_after_meta_box_settings'                         => 'wpfmmsq_after_meta_box_settings',
		'alg_wc_pq_qty_dropdown_thousand_separator'                 => 'wpfmmsq_qty_dropdown_thousand_separator',
		'alg_wc_pq_get_product_qty_min'                             => 'wpfmmsq_get_product_qty_min',
		'alg_wc_pq_get_product_qty_max'                             => 'wpfmmsq_get_product_qty_max',
		'alg_wc_pq_get_product_qty_step'                            => 'wpfmmsq_get_product_qty_step',
		'alg_wc_pq_get_product_qty_default'                         => 'wpfmmsq_get_product_qty_default',
		'alg_wc_pq_quantity_step_per_product'                       => 'wpfmmsq_quantity_step_per_product',
		'alg_wc_pq_quantity_step_per_product_value'                 => 'wpfmmsq_quantity_step_per_product_value',
		'alg_wc_pq_quantity_step_per_product_cat'                   => 'wpfmmsq_quantity_step_per_product_cat',
		'alg_wc_pq_quantity_step_per_product_cat_value'             => 'wpfmmsq_quantity_step_per_product_cat_value',
		'alg_wc_pq_per_item_quantity_per_product'                   => 'wpfmmsq_per_item_qty_per_product',
		'alg_wc_pq_per_item_quantity_per_product_value'             => 'wpfmmsq_per_item_qty_per_product_value',
		'alg_wc_pq_per_item_quantity_per_product_value_allvar'      => 'wpfmmsq_per_item_qty_per_product_value_allvar',
		'alg_wc_pq_per_item_cat_quantity_per_product'               => 'wpfmmsq_per_item_cat_qty_per_product',
		'alg_wc_pq_per_item_cat_quantity_per_product_value'         => 'wpfmmsq_per_item_cat_qty_per_product_value',
		'alg_wc_pq_per_item_attr_quantity_per_product'              => 'wpfmmsq_per_item_attr_qty_per_product',
		'alg_wc_pq_per_item_attr_quantity_per_product_value'        => 'wpfmmsq_per_item_attr_qty_per_product_value',
		'alg_wc_pq_per_item_default_quantity_per_product'           => 'wpfmmsq_per_item_default_qty_per_product',
		'alg_wc_pq_per_item_default_quantity_per_product_value'     => 'wpfmmsq_per_item_default_qty_per_product_value',
		'alg_wc_pq_per_item_cat_default_quantity_per_product'       => 'wpfmmsq_per_item_cat_default_qty_per_product',
		'alg_wc_pq_per_item_cat_default_quantity_per_product_value' => 'wpfmmsq_per_item_cat_default_qty_per_product_value',
		'alg_wc_pq_exact_qty_per_product'                           => 'wpfmmsq_exact_qty_per_product',
		'alg_wc_pq_exact_qty_per_product_value'                     => 'wpfmmsq_exact_qty_per_product_value',
		'alg_wc_pq_exact_qty_per_product_value_allvar'              => 'wpfmmsq_exact_qty_per_product_value_allvar',
		'alg_wc_pq_exact_qty_per_product_cat'                       => 'wpfmmsq_exact_qty_per_product_cat',
		'alg_wc_pq_exact_qty_per_product_cat_value'                 => 'wpfmmsq_exact_qty_per_product_cat_value',
		'alg_wc_pq_exact_qty_per_product_attr'                      => 'wpfmmsq_exact_qty_per_product_attr',
		'alg_wc_pq_exact_qty_per_product_attr_value'                => 'wpfmmsq_exact_qty_per_product_attr_value',
		'alg_wc_product_quantity_floatval'                          => 'wpfmmsq_product_quantity_floatval',
	);
}

/**
 * Register deprecated hook aliases.
 *
 * For each old hook, we hook into the new hook and forward any callbacks
 * registered on the old hook name. Also, when the new hook fires, any
 * callbacks registered on the old hook are called via apply_filters on the
 * old name so external code using the old hook names continues to work.
 *
 * @version 5.3.2
 * @since   5.3.1
 */
add_action( 'init', function () {
	foreach ( wpfmmsq_deprecated_hook_map() as $old => $new ) {
		$deprecated_version = '5.3.1';

		// Forward filters: if someone hooked into the old name, pipe through new name
		add_filter( $new, function () use ( $old, $new, $deprecated_version ) {
			$args = func_get_args();
			// Only forward if there are actually callbacks on the old hook
			if ( has_filter( $old ) ) {
				_deprecated_hook( esc_html( $old ), $deprecated_version, esc_html( $new ) );

				return call_user_func_array( 'apply_filters', array_merge( array( $old ), $args ) );
			}

			return $args[0];
		}, 5, PHP_INT_MAX );

		// Forward actions: if someone hooked into the old action name
		add_action( $new, function () use ( $old, $new, $deprecated_version ) {
			$args = func_get_args();
			if ( has_action( $old ) ) {
				_deprecated_hook( esc_html( $old ), $deprecated_version, esc_html( $new ) );
				call_user_func_array( 'do_action', array_merge( array( $old ), $args ) );
			}

			return ( isset( $args[0] ) ? $args[0] : null );
		}, 5, PHP_INT_MAX );
	}
}, 1 );
