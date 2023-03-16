<?php
/**
 * Product Quantity for WooCommerce - Quantity Info Section Settings
 *
 * @version 1.7.0
 * @since   1.7.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Quantity_Info' ) ) :

class Alg_WC_PQ_Settings_Quantity_Info extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'quantity_info';
		$this->desc = __( 'Quantity Info', 'product-quantity-for-woocommerce' );
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
		$content_desc = __( 'You can use HTML and/or shortcodes here.', 'product-quantity-for-woocommerce' ) . ' ' .
			sprintf( __( 'Available shortcodes: %s.', 'product-quantity-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array(
					'[alg_wc_pq_min_product_qty]',
					'[alg_wc_pq_max_product_qty]',
					'[alg_wc_pq_product_qty_step]',
				) ) . '</code>' );
		$default_content = '<p>' .
				'[alg_wc_pq_min_product_qty before="Minimum quantity is <strong>" after="</strong><br>"]' .
				'[alg_wc_pq_max_product_qty before="Maximum quantity is <strong>" after="</strong><br>"]' .
				'[alg_wc_pq_product_qty_step before="Step is <strong>" after="</strong><br>"]' .
			'</p>';
		return array(
			array(
				'title'    => __( 'Quantity Info Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_info_options',
				'desc'     => $content_desc,
			),
			array(
				'title'    => __( 'Single product page', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_info_on_single_product',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'alg_wc_pq_qty_info_on_single_product_content',
				'default'  => $default_content,
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Archives', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_info_on_loop',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'alg_wc_pq_qty_info_on_loop_content',
				'default'  => $default_content,
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_info_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Quantity_Info();
