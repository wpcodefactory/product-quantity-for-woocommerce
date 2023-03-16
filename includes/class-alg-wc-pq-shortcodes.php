<?php
/**
 * Product Quantity for WooCommerce - Shortcodes Class
 *
 * @version 1.8.0
 * @since   1.6.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Shortcodes' ) ) :

class Alg_WC_PQ_Shortcodes {

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 * @todo    [dev] `[alg_wc_pq_min_exact_allowed_product_qty]`
	 * @todo    [dev] `[alg_wc_pq_min_exact_disallowed_product_qty]`
	 */
	function __construct() {
		add_shortcode( 'alg_wc_pq_min_product_qty',  array( $this, 'min_product_qty' ) );
		add_shortcode( 'alg_wc_pq_max_product_qty',  array( $this, 'max_product_qty' ) );
		add_shortcode( 'alg_wc_pq_product_qty_step', array( $this, 'product_qty_step' ) );
	}

	/**
	 * get_min_or_max_or_step_value.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 * @todo    [dev] maybe rethink default values for `min` and `step`
	 */
	function get_min_or_max_or_step_value( $product, $min_or_max_or_step ) {
		switch ( $min_or_max_or_step ) {
			case 'min':
				return alg_wc_pq()->core->set_quantity_input_min( 1, $product );
			case 'max':
				return alg_wc_pq()->core->set_quantity_input_max( 0, $product );
			case 'step':
				return alg_wc_pq()->core->set_quantity_input_step( 1, $product );
		}
	}

	/**
	 * get_min_or_max_or_step_variable.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_min_or_max_or_step_variable( $product, $min_or_max_or_step ) {
		$result = '';
		$values = array();
		foreach ( $product->get_available_variations() as $variation ) {
			if ( 0 != ( $value = $this->get_min_or_max_or_step_value( wc_get_product( $variation['variation_id'] ), $min_or_max_or_step ) ) ) {
				$values[] = $value;
			}
		}
		if ( ! empty( $values ) ) {
			asort( $values );
			$min_value = current( $values );
			$max_value = end( $values );
			$result    = ( $min_value !== $max_value ?
				sprintf( '%s &ndash; %s', number_format( $min_value, 0, '.', '' ), number_format( $max_value, 0, '.', '' ) ) : $min_value );
		}
		return $result;
	}

	/**
	 * get_min_or_max_or_step.
	 *
	 * @version 1.8.0
	 * @since   1.6.0
	 */
	function get_min_or_max_or_step( $min_or_max_or_step ) {
		global $product;
		if ( ! $product ) {
			return '';
		}
		if ( ! $product->is_type( 'variable' ) ) {
			return $this->get_min_or_max_or_step_value( $product, $min_or_max_or_step );
		} else {
			return $this->get_min_or_max_or_step_variable( $product, $min_or_max_or_step );
		}
	}

	/**
	 * min_product_qty.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function min_product_qty( $atts, $content = '' ) {
		return $this->output_shortcode( $this->get_min_or_max_or_step( 'min' ), $atts );
	}

	/**
	 * max_product_qty.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function max_product_qty( $atts, $content = '' ) {
		return $this->output_shortcode( $this->get_min_or_max_or_step( 'max' ), $atts );
	}

	/**
	 * product_qty_step.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function product_qty_step( $atts, $content = '' ) {
		return $this->output_shortcode( $this->get_min_or_max_or_step( 'step' ), $atts );
	}

	/**
	 * output_shortcode.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function output_shortcode( $value, $atts ) {
		return ( ! empty( $value ) ? ( ( isset( $atts['before'] ) ? $atts['before'] : '' ) . $value . ( isset( $atts['after'] ) ? $atts['after'] : '' ) ) : '' );
	}

}

endif;

return new Alg_WC_PQ_Shortcodes();
