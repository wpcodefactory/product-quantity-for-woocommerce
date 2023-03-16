<?php
/**
 * Product Quantity for WooCommerce - Price by Qty Section Settings
 *
 * @version 1.7.3
 * @since   1.7.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Price_By_Qty' ) ) :

class Alg_WC_PQ_Settings_Price_By_Qty extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'price_by_qty';
		$this->desc = __( 'Price Display by Quantity', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.7.3
	 * @since   1.7.0
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Price Display by Quantity Options', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'With this section you can display product price for different quantities in real time (i.e. price is automatically updated when customer changes product\'s quantity).', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Please note that this section will only work for <strong>simple type products</strong>.', 'product-quantity-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Please note that this section is not designed to change product prices - if you need to change product\'s price depending on quantity in cart, we suggest using our %s plugin.', 'product-quantity-for-woocommerce' ),
						'<a href="https://wordpress.org/plugins/wholesale-pricing-woocommerce/" target="_blank">Wholesale Pricing for WooCommerce</a>' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_price_by_qty_options',
			),
			array(
				'title'    => __( 'Price display by quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_qty_price_by_qty_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Template', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'You can use HTML here.', 'product-quantity-for-woocommerce' ),
				'desc'     => sprintf( __( 'Placeholders: %s.', 'product-quantity-for-woocommerce' ),
					'<code>' . implode( '</code>, <code>', array( '%price%', '%qty%' ) ) . '</code>' ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_template',
				'default'  => __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Position', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_position',
				'default'  => 'instead',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'before'  => __( 'Before the price', 'product-quantity-for-woocommerce' ),
					'instead' => __( 'Instead of the price', 'product-quantity-for-woocommerce' ),
					'after'   => __( 'After the price', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_price_by_qty_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Price_By_Qty();
