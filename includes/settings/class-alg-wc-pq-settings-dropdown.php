<?php
/**
 * Product Quantity for WooCommerce - Dropdown Section Settings
 *
 * @version 1.8.1
 * @since   1.7.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Dropdown' ) ) :

class Alg_WC_PQ_Settings_Dropdown extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'dropdown';
		$this->desc = __( 'Quantity Dropdown', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.8.1
	 * @since   1.7.0
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Quantity Dropdown Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_dropdown_options',
				'desc'     => __( 'Will replace standard WooCommerce quantity number input with dropdown.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Please note that <strong>maximum quantity</strong> value must be set for the product (either via "Maximum Quantity" section or e.g. by setting maximum available product stock quantity or with "Max value fallback" option below).', 'product-quantity-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Quantity as dropdown', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_qty_dropdown',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Max value fallback', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Will be used if no maximum quantity is set for the product and always for variable products.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Ignored if set to zero.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_max_value_fallback',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_dropdown_options',
			),
			array(
				'title'    => __( 'Dropdown Labels', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_dropdown_label_options',
			),
			array(
				'title'    => __( 'Singular label template', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%qty%' ) ) . ' ' .
					sprintf( __( 'For example try %s', 'product-quantity-for-woocommerce' ),  '<code>%qty% item</code>' ),
				'id'       => 'alg_wc_pq_qty_dropdown_label_template_singular',
				'default'  => '%qty%',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Plural label template', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%qty%' ) ) . ' ' .
					sprintf( __( 'For example try %s', 'product-quantity-for-woocommerce' ),  '<code>%qty% items</code>' ),
				'id'       => 'alg_wc_pq_qty_dropdown_label_template_plural',
				'default'  => '%qty%',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Labels per product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add "Product Quantity" meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_label_template_is_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_dropdown_label_options',
			),
			array(
				'title'    => __( 'Template', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Optional quantity dropdown frontend template modifications.', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_dropdown_template_options',
			),
			array(
				'title'    => __( 'Before', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Will be outputted on frontend before the quantity dropdown box.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'You can use HTML and/or shortcodes here.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Ignored if empty.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_template_before',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'After', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Will be outputted on frontend after the quantity dropdown box.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'You can use HTML and/or shortcodes here.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Ignored if empty.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_template_after',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_dropdown_template_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Dropdown();
