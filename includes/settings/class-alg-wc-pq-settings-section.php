<?php
/**
 * Product Quantity for WooCommerce - Section Settings
 *
 * @version 1.6.0
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Section' ) ) :

class Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_pq',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_pq_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * message_replaced_values.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function message_replaced_values( $values ) {
		$message_template = ( 1 == count( $values ) ?
			__( 'Replaced value: %s.', 'product-quantity-for-woocommerce' ) : __( 'Replaced values: %s.', 'product-quantity-for-woocommerce' ) );
		return sprintf( $message_template, '<code>' . implode( '</code>, <code>', $values ) . '</code>' );
	}

	/**
	 * get_qty_step_settings.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 * @todo    [dev] customizable `$qty_step_settings` (i.e. instead of always `0.000001`)
	 */
	function get_qty_step_settings() {
		if ( ! isset( $this->qty_step_settings ) ) {
			$this->qty_step_settings = ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ? '0.000001' : '1' );
		}
		return $this->qty_step_settings;
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

}

endif;
