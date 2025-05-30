<?php
/**
 * Product Quantity for WooCommerce - Max Section Settings
 *
 * @version 5.0.3
 * @since   1.6.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PQ_Settings_Max' ) ) :

class Alg_WC_PQ_Settings_Max extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 5.0.3
	 * @since   1.6.0
	 */
	function __construct() {
		$this->id   = 'max';
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
		$this->desc = __( 'Maximum Quantity', 'product-quantity-for-woocommerce' );
	}

	/**
	 * get_settings.
	 *
	 * @version 4.7.0
	 * @since   1.6.0
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Maximum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __('Specify a maximum quantity based on one of the options below.', 'product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_max_options',
			),
			array(
				'title'    => __( 'Maximum quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_max_section_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_max_options',
			),
			array(
				'title'    => __( 'Cart Total Maximum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __('Specify maximum quantity on the cart level, <strong>regardless</strong> of number of products on it.
				The Message field will allow you to customize the notification message on wrong quantities','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_max_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Cart total quantity', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will set maximum total cart quantity. Set to zero to disable.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_max_cart_total_quantity',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
				'alg_empty_value'   => 0,
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when maximum cart total quantity is exceeded.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%max_cart_total_quantity%', '%cart_total_quantity%' ) ),
				'id'       => 'alg_wc_pq_max_cart_total_message',
				'default'  => __( 'Maximum allowed order quantity is %max_cart_total_quantity%. Your current order quantity is %cart_total_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:70%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_max_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Per Item Maximum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __('This section allows you to specify a maximum quantity for all products in your store at once (not combined), tick "Per Product"  to define a quantity on product level (Pro Feature), a field will appear on the product page to set this.','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_max_per_item_quantity_options',
			),
			array(
				'title'    => __( 'All products', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will set maximum per item quantity (for all products). Set to zero to disable.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_max_per_item_quantity',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
				'alg_empty_value'   => 0,
			),
			array(
				'title'    => __( 'Per product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_max_per_item_quantity_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when maximum per item quantity is exceeded.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%product_title%', '%max_per_item_quantity%', '%item_quantity%' ) ),
				'id'       => 'alg_wc_pq_max_per_item_message',
				'default'  => __( 'Maximum allowed quantity for %product_title% is %max_per_item_quantity%. Your current item quantity is %item_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_max_cat_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Per Category Maximum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __('Enabling this will create two new fields in all categories pages you have, one to set a maximum quantity for all products (instead of filling it one by one), and one for specifying a maximum quantity for all products <strong>combined</strong> in the cart.','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_max_per_cat_item_quantity_options',
			),
			array(
				'title'    => __( 'Per Category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each category edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per category quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_max_per_cat_item_quantity_per_product',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when maximum per item quantity is exceeded.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%category_title%', '%max_per_item_quantity%', '%item_quantity%' ) ),
				'id'       => 'alg_wc_pq_max_cat_message',
				'default'  => __( 'Maximum allowed quantity for category %category_title% is %max_per_item_quantity%.  Your current quantity for this category is %item_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_max_per_attribute_quantity_options',
			),
			array(
				'title'    => __( 'Per Attribute Maximum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __('This option works the exact same way as category, you also get the option to enable it per attributes that are selected in the field below instead of enabling it to all attributes at once.','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_max_per_attribute_item_quantity_options',
			),
			array(
				'title'    => __( 'Per Attribute', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each attribute edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per attribute quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_max_per_attribute_item_quantity',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'desc'     => __( 'Product Attributes', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Leave blank to use all', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_max_per_attribute_selected',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_attribute_field_lists(),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when maximum per item quantity is not reached.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%attribute_title%', '%max_per_item_quantity%', '%item_quantity%' ) ),
				'id'       => 'alg_wc_pq_max_per_attribute_message',
				'default'  => __( 'Maximum allowed quantity for attribute %attribute_title% is %max_per_item_quantity%.  Your current quantity for this attribute is %item_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_max_per_item_quantity_options',
			),
			array(
				'title' => __( 'Useful information', 'product-quantity-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => $this->section_notes(
					array(
						sprintf(
							__( 'To make the maximum quantity appear on page load, set an option from the %s section to %s.', 'product-quantity-for-woocommerce' ),
							'<strong>' . '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pq#alg_wc_pq_qty_forcing_qty_options-description' ) . '">' . __( 'General > Initial Quantity Options', 'product-quantity-for-woocommerce' ) . '</a>' . '</strong>',
							'<code>' . __( 'Max quantity', 'product-quantity-for-woocommerce' ) . '</code>'
						),
						__( 'If the default quantity is higher than maximum quantity, the initial quantity will be set to maximum.', 'product-quantity-for-woocommerce' ),
					)
				),
				'id'    => 'alg_wc_pq_max_useful_options',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_max_useful_options',
			),
		);
	}

	/**
	 * get_attribute_lists
	 *
	 * @version 1.8.0
	 * @since   1.6.0
	 */
	function get_attribute_field_lists() {
		$return_fields = array();
		$attribute_taxonomies = alg_wc_pq_wc_get_attribute_taxonomies();
		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {
				$name = alg_pq_wc_attribute_taxonomy_name( $tax->attribute_name );
				$return_fields[$tax->attribute_id] =  __( $tax->attribute_label, 'product-quantity-for-woocommerce' );
			}
		}
		return $return_fields;
	}

}

endif;

return new Alg_WC_PQ_Settings_Max();
