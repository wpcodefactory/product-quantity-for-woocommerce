<?php
/**
 * Product Quantity for WooCommerce - Max Section Settings
 *
 * @version 4.9.4
 * @since   1.6.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PQ_Settings_Default' ) ) :

class Alg_WC_PQ_Settings_Default extends Alg_WC_PQ_Settings_Section {

	/**
	 * id.
	 *
	 * @var   string
	 * @since 4.6.0
	 */
	public $id = null;

	/**
	 * desc.
	 *
	 * @var   string
	 * @since 4.6.0
	 */
	public $desc = null;

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function __construct() {

		$this->id   = 'default';
		$this->desc = __( 'Default Quantity', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 4.9.4
	 * @since   1.6.0
	 */
	function get_settings() {
		return array(
			array(
				'title' => __( 'Default Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => sprintf( __( 'Define a quantity that\'s different from the default %s that appears on any product when the page loads.', 'product-quantity-for-woocommerce' ), '<code>1</code>' ),
				'id'    => 'alg_wc_pq_default_options',
			),
			array(
				'title'    => __( 'Default quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_default_section_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_default_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Per Item Default Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __('This section allows you to specify a default quantity for all products in your store at once, tick "Per Product"  to define a quantity on product level (Pro Feature), a field will appear on the product page to set this.','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_default_per_item_quantity_options',
			),
			array(
				'title'    => __( 'All products', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will set default per item quantity (for all products). Set to zero to disable.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_default_per_item_quantity',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
				'alg_empty_value'   => 0,
			),
			array(
				'title'    => __( 'Per product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per item quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_default_per_item_quantity_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when default per item quantity is exceeded.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%product_title%', '%default_per_item_quantity%', '%item_quantity%' ) ),
				'id'       => 'alg_wc_pq_default_per_item_message',
				'default'  => __( 'Default quantity for %product_title% is %default_per_item_quantity%. Your current item quantity is %item_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:70%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_default_cat_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Per Category Default Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __('Ticking this option will create a new field under your store categories pages where you will be able to set a default quantity that will be applied to all products under that category.','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_default_per_cat_item_quantity_options',
			),
			array(
				'title'    => __( 'Per Category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each category edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per category quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_default_per_cat_item_quantity_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_default_per_item_quantity_options',
			),
			array(
				'title' => __( 'Useful information', 'product-quantity-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => $this->section_notes(
					array(
						sprintf(
							__( 'To make the default quantity appear on page load, set an option from the %s section to %s.', 'product-quantity-for-woocommerce' ),
							'<strong>' . '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pq#alg_wc_pq_qty_forcing_qty_options-description' ) . '">' . __( 'General > Initial Quantity Options', 'product-quantity-for-woocommerce' ) . '</a>' . '</strong>',
							'<code>' . __( 'Default quantity', 'product-quantity-for-woocommerce' ) . '</code>'
						),
						__( 'You can have a default quantity different from the minimum quantity.', 'product-quantity-for-woocommerce' ),
						__( 'If the default quantity is lower than minimum quantity, the initial quantity will be set to minimum.', 'product-quantity-for-woocommerce' ),
					)
				),
				'id'    => 'alg_wc_pq_default_useful_options',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_default_useful_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Default();
