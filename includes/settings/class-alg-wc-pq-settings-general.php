<?php
/**
 * Product Quantity for WooCommerce - General Section Settings
 *
 * @version 1.8.0
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_General' ) ) :

class Alg_WC_PQ_Settings_General extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 * @todo    [feature] Force initial quantity on single product page - add "Custom value" option
	 * @todo    [feature] Force initial quantity on single product page - per product
	 */
	function get_settings() {
		$main_settings = array(
			array(
				'title'    => __( 'Product Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_options',
			),
			array(
				'title'    => __( 'Product Quantity for WooCommerce', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_options',
			),
		);
		$general_settings = array(
			array(
				'title'    => __( 'General Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_general_options',
			),
			array(
				'title'    => __( 'Decimal quantities', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'If enabled you will be able to enter decimal quantities in step, min, max etc. quantity options.', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_decimal_quantities_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Sold individually', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will enable "Sold individually" (no quantities) option for <strong>all products</strong> at once.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_all_sold_individually_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( '"Add to cart" validation', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_add_to_cart_validation',
				'default'  => 'disable',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'disable' => __( 'Do not validate', 'product-quantity-for-woocommerce' ),
					'notice'  => __( 'Validate and add notices', 'product-quantity-for-woocommerce' ),
					'correct' => __( 'Validate and auto-correct quantities', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'desc'     => __( 'Step auto-correct', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Ignored unless "Validate and auto-correct quantities" option is selected above.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_add_to_cart_validation_step_auto_correct',
				'default'  => 'round',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'round'      => __( 'Round', 'product-quantity-for-woocommerce' ),
					'round_up'   => __( 'Round up', 'product-quantity-for-woocommerce' ),
					'round_down' => __( 'Round down', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Cart notices', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_cart_notice_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Cart notice type', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_cart_notice_type',
				'default'  => 'notice',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'notice'  => __( 'Notice', 'product-quantity-for-woocommerce' ),
					'error'   => __( 'Error', 'product-quantity-for-woocommerce' ),
					'success' => __( 'Success', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Block checkout page', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Stops customer from reaching the <strong>checkout</strong> page on wrong quantities.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Customer will be redirected to the <strong>cart</strong> page.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_stop_from_seeing_checkout',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_general_options',
			),
			array(
				'title'    => __( 'Force Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_forcing_qty_options',
			),
			array(
				'title'    => __( 'Force initial quantity on single product page', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_on_single',
				'default'  => 'disabled',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'disabled' => __( 'Do not force', 'product-quantity-for-woocommerce' ),
					'min'      => __( 'Force to min quantity', 'product-quantity-for-woocommerce' ),
					'max'      => __( 'Force to max quantity', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Force initial quantity on archives', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_on_loop',
				'default'  => 'disabled',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'disabled' => __( 'Do not force', 'product-quantity-for-woocommerce' ),
					'min'      => __( 'Force to min quantity', 'product-quantity-for-woocommerce' ),
					'max'      => __( 'Force to max quantity', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Force minimum quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Will force all minimum quantities to %s.', 'product-quantity-for-woocommerce' ), '<code>1</code>' ) . ' ' .
					__( 'This includes cart items, grouped products etc.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Ignored if "Minimum quantity" section is enabled.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_cart_min_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_forcing_qty_options',
			),
			array(
				'title'    => __( 'Variable Products Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_variable_products_options',
			),
			array(
				'title'    => __( 'Load all variations', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'For compatibility with some other plugins, e.g. with "YITH WooCommerce Quick View" plugin.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_variation_do_load_all',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'On variation change', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_variation_change',
				'default'  => 'do_nothing',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'do_nothing'   => __( 'Do nothing', 'product-quantity-for-woocommerce' ),
					'reset_to_min' => __( 'Reset to min quantity', 'product-quantity-for-woocommerce' ),
					'reset_to_max' => __( 'Reset to max quantity', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Sum variations', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_sum_variations',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_variable_products_options',
			),
		);
		return array_merge( $main_settings, $general_settings );
	}

}

endif;

return new Alg_WC_PQ_Settings_General();
