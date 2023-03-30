<?php
/**
 * Product Quantity for WooCommerce - Shortcodes Class
 *
 * @version 1.8.0
 * @since   1.6.0
 * @author  WPFactory
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
		add_shortcode( 'alg_wc_pq_product_qty_price_unit', array( $this, 'product_qty_price_unit' ) );
		add_shortcode( 'alg_wc_pq_translate', 		 array( $this, 'language_shortcode' ) );
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
			case 'price_unit':
				return alg_wc_pq()->core->set_quantity_input_price_unit( '', $product );
		}
	}
	
	/**
	 * get_min_or_max_or_step_value_main_var.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 * @todo    [dev] maybe rethink default values for `min` and `step`
	 */
	function get_min_or_max_or_step_value_main_var( $product, $min_or_max_or_step ) {
		switch ( $min_or_max_or_step ) {
			case 'min':
				return alg_wc_pq()->core->set_quantity_input_min( 0, $product );
			case 'max':
				return alg_wc_pq()->core->set_quantity_input_max( 0, $product );
			case 'step':
				return alg_wc_pq()->core->set_quantity_input_step( 0, $product );
			case 'price_unit':
				return alg_wc_pq()->core->set_quantity_input_price_unit( '', $product );
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

		if ( 0 != ( $value = $this->get_min_or_max_or_step_value_main_var( $product, $min_or_max_or_step ) ) ) {
				$values[] = $value;
		}
		else
		{
			foreach ( $product->get_available_variations() as $variation ) {
				if ( 0 != ( $value = $this->get_min_or_max_or_step_value( wc_get_product( $variation['variation_id'] ), $min_or_max_or_step ) ) ) {
					$values[] = $value;
				}
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
	 * product_qty_price_unit.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function product_qty_price_unit( $atts, $content = '' ) {
		return $this->output_shortcode( $this->get_min_or_max_or_step( 'price_unit' ), $atts );
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
		global $product;
		
		if(isset($atts['thousand_sep']) && $atts['thousand_sep']=='yes'){
			if(!empty($value) && is_numeric($value)){
				$value = number_format($value);
			}
		}
		
		if(isset($atts['price_unit']) && $atts['price_unit']=='yes'){
			$unit = alg_wc_pq()->core->alg_wc_pq_get_product_price_unit($product, $value, true);
			if(!empty($unit)){
				$value = $value . ' ' . $unit;
			}else{
				return '';
			}
		}
		
		if(isset($atts['unit']) && $atts['unit']=='yes'){
			$unit = alg_wc_pq()->core->alg_wc_pq_get_product_price_unit($product, $value, false);
			if(!empty($unit)){
				$value = $value . ' ' . $unit;
			}else{
				return '';
			}
		}
		return ( ! empty( $value ) ? ( ( isset( $atts['before'] ) ? $atts['before'] : '' ) . $value . ( isset( $atts['after'] ) ? $atts['after'] : '' ) ) : '' );
	}
	
	/**
	 * language_shortcode.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function language_shortcode( $atts, $content = '' ) {
		
		$current_language = '';
		if (function_exists('pll_current_language')) {
			$current_language = strtolower( pll_current_language() );
		}
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$current_language = strtolower( ICL_LANGUAGE_CODE );
		}
		
		// E.g.: [alg_wc_pq_translate lang="EN,DE" lang_text="Text for EN & DE" not_lang_text="Text for other languages"]
        if ( isset( $atts['lang_text'] ) && isset( $atts['not_lang_text'] ) && ! empty( $atts['lang'] ) ) {
            return ( empty( $current_language ) || ! in_array( $current_language, array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ?
                $atts['not_lang_text'] : $atts['lang_text'];
        }
		
		// [alg_wc_pq_translate lang="DE"]Die zulässige Menge für %product_title% ist %allowed_quantity%.[/alg_wc_pq_translate][alg_wc_pq_translate lang="NL"]Toegestane hoeveelheid voor %product_title% is %allowed_quantity%.[/alg_wc_pq_translate]
		if ( ! empty( $atts['lang'] ) && strlen( trim ( $atts['lang'] ) ) == 2) {
			if ( strtolower( trim ( $atts['lang'] ) ) == $current_language ) {
				return $content;
			}
		}
		
        // E.g.: [alg_wc_pq_translate lang="EN,DE"]Text for EN & DE[/alg_wc_pq_translate][alg_wc_pq_translate not_lang="EN,DE"]Text for other languages[/alg_wc_pq_translate]
        return (
            ( ! empty( $atts['lang'] )     && ( empty( $current_language ) || ! in_array( $current_language, array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ) ||
            ( ! empty( $atts['not_lang'] ) &&     ! empty( $current_language ) &&   in_array( $current_language , array_map( 'trim', explode( ',', strtolower( $atts['not_lang'] ) ) ) ) )
        ) ? '' : $content;

		
	}

}

endif;

return new Alg_WC_PQ_Shortcodes();
