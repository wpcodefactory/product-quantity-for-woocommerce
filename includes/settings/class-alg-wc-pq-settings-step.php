<?php
/**
 * Product Quantity for WooCommerce - Step Section Settings
 *
 * @version 1.8.0
 * @since   1.6.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Step' ) ) :

class Alg_WC_PQ_Settings_Step extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function __construct() {
		$this->id   = 'step';
		$this->desc = __( 'Quantity Step', 'product-quantity-for-woocommerce' );
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
				'title'    => __( 'Quantity Step Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_step_options',
			),
			array(
				'title'    => __( 'Quantity step', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_step_section_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_step_options',
			),
			array(
				'title'    => __( 'Cart Total Quantity Step Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_step_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Cart total quantity', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will set step for total cart quantity. Set to zero to disable.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_step_cart_total_quantity',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when cart total quantity step is wrong.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%step_cart_total_quantity%', '%cart_total_quantity%' ) ),
				'id'       => 'alg_wc_pq_step_cart_total_message',
				'default'  => __( 'Quantity total cart step is %step_cart_total_quantity%. Your current order quantity is %cart_total_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_step_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Per Item Quantity Step Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_step_per_item_options',
			),
			array(
				'title'    => __( 'All products', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will set quantity step for all products. Set to zero to disable.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_step',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
			),
			array(
				'title'    => __( 'Per product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to set per product quantity step.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_step_per_product_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when quantity step is incorrect.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%product_title%', '%quantity_step%', '%quantity%' ) ),
				'id'       => 'alg_wc_pq_step_message',
				'default'  => __( 'Quantity step for %product_title% is %quantity_step%. Your current quantity is %quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_step_per_item_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Step();
