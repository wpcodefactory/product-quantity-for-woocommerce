<?php
/**
 * Product Quantity for WooCommerce - Price by Qty Section Settings
 *
 * @version 5.0.3
 * @since   1.7.0
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PQ_Settings_Price_Unit' ) ) :

class Alg_WC_PQ_Settings_Price_Unit extends Alg_WC_PQ_Settings_Section {
	
	/**
	 * Constructor.
	 *
	 * @version 5.0.3
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id = 'price_unit';
		parent::__construct();
	}

	/**
	 * set_section_variables.
	 *
	 * @version 5.0.3
	 * @since   5.0.3
	 *
	 * @return void
	 */
	public function set_section_variables() {
		parent::set_section_variables();
		$this->desc = __( 'Price Unit', 'product-quantity-for-woocommerce' );
	}

	/**
	 * get_settings.
	 *
	 * @version 4.5.23
	 * @since   1.7.0
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Price Unit Options', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Price Unit will be shown beside product price in archive, product and cart pages. Use a global text to apply all products, or specify it per category, or define it on product by product.<br> Note: This section will not work if you have enabled "Total Price by quantity" <strong>AND</strong> selected the total price to appear "Instead of the price"', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_price_unit_options',
			),
			array(
				'title'    => __( 'Price Unit', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_qty_price_unit_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Allow price unit on category level', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Enable this option to create a field on category edit page that will allow specifying this value on all products under that category.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_unit_category_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				// 'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Allow price unit on product level', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Enable this option to create a field on the product edit page that will allow specifying this value for that product.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use unit on product level.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_qty_price_unit_product_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Show Price unit on category/ shop/ archive pages', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Show Price Unit on archive/category/shop pages (you need to enable quantity on archive pages from General tab).', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use unit on category/ shop/ archive pages.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_qty_price_unit_show_archive_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			
			array(
				'title'    => __( 'Allow price unit on email order item', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Enable this option to display the unit for that product in the email order item.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to show unit on email order item.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_qty_price_unit_email_order_item_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Price Unit', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Use string value here, you can define the parameter (per, for each, /) with unit, for example, to show per KG next to price, enter "per KG" here in the box', 'product-quantity-for-woocommerce' ),
				'desc'     => '',
				'id'       => 'alg_wc_pq_qty_price_unit',
				'type'     => 'text',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_price_unit_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Price_Unit();
