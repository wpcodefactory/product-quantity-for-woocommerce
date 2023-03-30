<?php
/**
 * Product Quantity for WooCommerce - Admin Section Settings
 *
 * @version 1.7.0
 * @since   1.7.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Admin' ) ) :

class Alg_WC_PQ_Settings_Admin extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'admin';
		$this->desc = __( 'Admin', 'product-quantity-for-woocommerce' );
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
				'title'    => __( 'Admin Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_admin_options',
			),
			array(
				'title'    => __( 'Admin columns', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Will add quantity columns to admin products list.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_admin_columns_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Columns', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Leave blank to add all available columns.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_admin_columns',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					'alg_wc_pq_min_qty'  => __( 'Min Qty', 'product-quantity-for-woocommerce' ),
					'alg_wc_pq_max_qty'  => __( 'Max Qty', 'product-quantity-for-woocommerce' ),
					'alg_wc_pq_qty_step' => __( 'Qty Step', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_admin_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Admin();
