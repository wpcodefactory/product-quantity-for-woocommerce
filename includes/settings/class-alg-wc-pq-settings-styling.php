<?php
/**
 * Product Quantity for WooCommerce - Styling Section Settings
 *
 * @version 1.7.0
 * @since   1.7.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Styling' ) ) :

class Alg_WC_PQ_Settings_Styling extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'styling';
		$this->desc = __( 'Styling', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] (maybe) add "Enable section" option
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Styling Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_styling_options',
			),
			array(
				'title'    => __( 'Quantity input style', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Ignored if empty.', 'product-quantity-for-woocommerce' ),
				'desc'     => sprintf( __( 'E.g.: %s', 'product-quantity-for-woocommerce' ), '<code>width: 100px !important; max-width: 100px !important;</code>' ),
				'id'       => 'alg_wc_pq_qty_input_style',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:70%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_styling_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Styling();
