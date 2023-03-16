<?php
/**
 * Product Quantity for WooCommerce - Min Section Settings
 *
 * @version 1.8.0
 * @since   1.6.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Min' ) ) :

class Alg_WC_PQ_Settings_Min extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function __construct() {
		$this->id   = 'min';
		$this->desc = __( 'Minimum Quantity', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.8.0
	 * @since   1.6.0
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_min_options',
			),
			array(
				'title'    => __( 'Minimum quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_min_section_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_min_options',
			),
			array(
				'title'    => __( 'Cart Total Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_min_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Cart total quantity', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will set minimum total cart quantity. Set to zero to disable.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_min_cart_total_quantity',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when minimum cart total quantity is not reached.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%min_cart_total_quantity%', '%cart_total_quantity%' ) ),
				'id'       => 'alg_wc_pq_min_cart_total_message',
				'default'  => __( 'Minimum allowed order quantity is %min_cart_total_quantity%. Your current order quantity is %cart_total_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_min_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Per Item Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_min_per_item_quantity_options',
			),
			array(
				'title'    => __( 'All products', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will set minimum per item quantity (for all products). Set to zero to disable.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_min_per_item_quantity',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
			),
			array(
				'title'    => __( 'Per product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per item quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_min_per_item_quantity_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when minimum per item quantity is not reached.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%product_title%', '%min_per_item_quantity%', '%item_quantity%' ) ),
				'id'       => 'alg_wc_pq_min_per_item_message',
				'default'  => __( 'Minimum allowed quantity for %product_title% is %min_per_item_quantity%. Your current item quantity is %item_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_min_per_item_quantity_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Min();
