<?php
/**
 * Product Quantity for WooCommerce - Fixed Section Settings
 *
 * @version 1.8.0
 * @since   1.6.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Fixed' ) ) :

class Alg_WC_PQ_Settings_Fixed extends Alg_WC_PQ_Settings_Section {


	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function __construct() {
		$this->id   = 'fixed';
		$this->desc = __( 'Fixed Quantity', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.8.0
	 * @since   1.6.0
	 */
	function get_settings() {
		$fields = array(
			array(
				'title'    => __( 'Allowed Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'	   => __('Specify fixed (allowed/disallowed) quantities for products in your store, settings here overwrite minimum/maximum/or step quantities (if defined site-wide), <br>only the values here will be accepted for the products that have this field defined.
				Functions here follow the same logic in minimum/maximum quantities on cart/all products/product level quantities.','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_exact_qty_allowed_options',
			),
			array(
				'title'    => __( 'Allowed quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_exact_qty_allowed_section_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'All products', 'product-quantity-for-woocommerce' ),
				'desc'     => sprintf( __( 'Allowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<code>3,7,9</code>' ),
				'desc_tip' => __( 'Ignored if empty.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_exact_qty_allowed',
				'default'  => '',
				'type'     => 'text',
				'css'      => 'width:70%;',
			),
			array(
				'title'    => __( 'Per product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to set per product allowed quantities.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_exact_qty_allowed_per_product_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%product_title%', '%allowed_quantity%', '%quantity%' ) ),
				'desc_tip' => __( 'Message to be displayed to customer on wrong allowed quantities.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_exact_qty_allowed_message',
				'default'  => __( 'Allowed quantity for %product_title% is %allowed_quantity%. Your current quantity is %quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:70%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Per Category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Enabling this will create two new fields in all categories pages you have, one to set a fixed quantity for all products (instead of filling it one by one), and one for specifying a fixed quantity for all products <strong>combined</strong> in the cart.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per category quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_exact_qty_allowed_per_cat_item_quantity_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer on wrong allowed quantities.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%category_title%', '%allowed_quantity%', '%quantity%' ) ),
				'id'       => 'alg_wc_pq_exact_qty_allowed_cat_message',
				'default'  => __( 'Allowed quantity for category %category_title% is %allowed_quantity%.  Your current quantity for this category is %quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			
			array(
				'title'    => __( 'Per Attribute', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Enabling this option will create a field for fixed quantities per attributes that are selected in the field below instead of enabling it to all attributes at once.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per category quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_exact_qty_allowed_per_attribute_item_quantity',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'desc'     => __( 'Product Attributes', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Leave blank to use all', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_exact_qty_allowed_per_attributes_selected',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_attribute_field_lists(),
			),
		);
		
		$fields2 = array(	
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer on wrong allowed quantities in attribute.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%attribute_item_title%', '%allowed_quantity%', '%quantity%' ) ),
				'id'       => 'alg_wc_pq_exact_qty_allowed_attribute_item_message',
				'default'  => __( 'Allowed quantity for attribute %attribute_item_title% is %allowed_quantity%.  Your current quantity for this attribute item is %quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_exact_qty_allowed_options',
			),
			array(
				'title'    => __( 'Disallowed Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_exact_qty_disallowed_options',
			),
			array(
				'title'    => __( 'Disallowed quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_exact_qty_disallowed_section_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'All products', 'product-quantity-for-woocommerce' ),
				'desc'     => sprintf( __( 'Disallowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<code>3,7,9</code>' ),
				'desc_tip' => __( 'Ignored if empty.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_exact_qty_disallowed',
				'default'  => '',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Per product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to set per product disallowed quantities.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_exact_qty_disallowed_per_product_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%product_title%', '%disallowed_quantity%', '%quantity%' ) ),
				'desc_tip' => __( 'Message to be displayed to customer on wrong disallowed quantities.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_exact_qty_disallowed_message',
				'default'  => __( 'Disallowed quantity for %product_title% is %disallowed_quantity%. Your current quantity is %quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_exact_qty_disallowed_options',
			),
			array(
				'title'    => __( 'Cart Total Fixed Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_exact_min_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Cart fixed quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( '', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_exact_cart_total_quantity_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Cart total quantity', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will set fixed total cart quantity. Set to zero to disable.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_exact_cart_total_quantity',
				'default'  => 0,
				'type'     => 'text',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when fixed cart total quantity is not reached.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%min_cart_total_quantity%', '%cart_total_quantity%' ) ),
				'id'       => 'alg_wc_pq_exact_cart_total_message',
				'default'  => __( 'Allowed order quantity is %min_cart_total_quantity%. Your current cart quantity is %cart_total_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_exact_options',
			),
		);
		
		return array_merge($fields,$fields2);
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

return new Alg_WC_PQ_Settings_Fixed();
