<?php
/**
 * Product Quantity for WooCommerce - Deprecated Shortcodes
 *
 * Adds backward-compatible aliases for shortcodes renamed in version 5.3.1.
 * Old shortcode tags (alg_wc_pq_*) are forwarded to new tags (wpfmmsq_*).
 *
 * @version 5.3.1
 * @since   5.3.1
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

/**
 * wpfmmsq_deprecated_shortcode_map.
 *
 * Returns the mapping of old shortcode tags to new shortcode tags.
 *
 * @version 5.3.1
 * @since   5.3.1
 *
 * @return array
 */
function wpfmmsq_deprecated_shortcode_map() {
	return array(
		'alg_wc_pq_min_product_qty'        => 'wpfmmsq_min_product_qty',
		'alg_wc_pq_max_product_qty'        => 'wpfmmsq_max_product_qty',
		'alg_wc_pq_product_qty_step'       => 'wpfmmsq_product_qty_step',
		'alg_wc_pq_product_qty_price_unit' => 'wpfmmsq_product_qty_price_unit',
		'alg_wc_pq_translate'              => 'wpfmmsq_translate',
	);
}

/**
 * Register deprecated shortcode aliases.
 *
 * Each old shortcode tag is registered and, when used, forwards its call to
 * the new shortcode tag while notifying developers via _deprecated_function().
 *
 * Priority 20 ensures the new shortcodes (registered on init at default
 * priority) are already in place before the forwarding callbacks run.
 *
 * @version 5.3.1
 * @since   5.3.1
 */
add_action( 'init', function () {
	foreach ( wpfmmsq_deprecated_shortcode_map() as $old => $new ) {
		add_shortcode( $old, function ( $atts, $content, $tag ) use ( $old, $new ) {
			global $shortcode_tags;

			_deprecated_function(
				'[' . esc_html( $old ) . ']',
				'5.3.1',
				'[' . esc_html( $new ) . ']'
			);

			if ( isset( $shortcode_tags[ $new ] ) ) {
				return call_user_func( $shortcode_tags[ $new ], $atts, $content, $new );
			}

			return '';
		} );
	}
}, 20 );
