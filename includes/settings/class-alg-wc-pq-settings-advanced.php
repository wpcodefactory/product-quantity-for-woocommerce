<?php
/**
 * Product Quantity for WooCommerce - Advanced Section Settings
 *
 * @version 1.8.1
 * @since   1.7.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Advanced' ) ) :

class Alg_WC_PQ_Settings_Advanced extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'advanced';
		$this->desc = __( 'Advanced', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.8.1
	 * @since   1.7.0
	 * @todo    [dev] (maybe) add "Enable section" option
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'JS Check Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_advanced_force_js_check_options',
			),
			array(
				'title'    => __( 'Force on change', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Min/max quantity', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_js_check_min_max',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Quantity step', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_js_check_step',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Force periodically', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Min/max quantity', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_js_check_min_max_periodically',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Quantity step', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_js_check_step_periodically',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Period (ms)', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_js_check_period_ms',
				'default'  => 1000,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 100 ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_advanced_force_js_check_options',
			),
			array(
				'title'    => __( 'Order Item Meta Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_advanced_order_item_meta_options',
			),
			array(
				'title'    => __( 'Save quantity in order item meta', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_save_qty_in_order_item_meta',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Meta key', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_save_qty_in_order_item_meta_key',
				'default'  => '_alg_wc_pq_qty',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_advanced_order_item_meta_options',
			),
			array(
				'title'    => __( 'Rounding Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_rounding_options',
			),
			array(
				'title'    => __( 'Round on add to cart', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Makes sense only if "Decimal quantities" option is enabled.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_round_on_add_to_cart',
				'default'  => 'no',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'no'    => __( 'Do not round', 'product-quantity-for-woocommerce' ),
					'round' => __( 'Round', 'product-quantity-for-woocommerce' ),
					'ceil'  => __( 'Round up', 'product-quantity-for-woocommerce' ),
					'floor' => __( 'Round down', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Round with JavaScript', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_round_with_js',
				'default'  => 'no',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'no'    => __( 'Do not round', 'product-quantity-for-woocommerce' ),
					'round' => __( 'Round', 'product-quantity-for-woocommerce' ),
					'ceil'  => __( 'Round up', 'product-quantity-for-woocommerce' ),
					'floor' => __( 'Round down', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_rounding_options',
			),
			array(
				'title'    => __( 'Cart Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_cart_options',
			),
			array(
				'title'    => __( 'Hide "Update cart" button', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Hide', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_hide_update_cart',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_cart_options',
			),
			array(
				'title'    => __( 'Advanced Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_cart_advanced_options',
			),
			array(
				'title'    => __( 'Disable plugin by URL', 'product-quantity-for-woocommerce' ),
				'desc'     => sprintf( __( 'Relative URLs. E.g.: %s. One per line.', 'product-quantity-for-woocommerce' ),
					'<code>/product/my-grouped-product/</code>' ),
				'id'       => 'alg_wc_pq_disable_urls',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:100%',
			),
			array(
				'title'    => __( 'Validate on checkout', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Validate quantities on the checkout page.', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_validate_on_checkout',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_cart_advanced_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Advanced();
