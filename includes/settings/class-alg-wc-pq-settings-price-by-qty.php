<?php
/**
 * Product Quantity for WooCommerce - Price by Qty Section Settings
 *
 * @version 1.7.3
 * @since   1.7.0
 * @author  WPFactory
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
		$this->desc = __( 'Total Price by Quantity', 'product-quantity-for-woocommerce' );
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
				'title'    => __( 'Total Price by Quantity Options', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'With this section you can display product price for different quantities in real time (i.e. price is automatically updated when customer changes product\'s quantity).', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_price_by_qty_options',
			),
			array(
				'title'    => __( 'Total Price by Quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_qty_price_by_qty_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Total Price by Quantity for variable product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ) ,
				'id'       => 'alg_wc_pq_qty_price_by_qty_enabled_variable',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Allow defining unit on product level', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_unit_input_enabled',
				'desc_tip' => __( 'This will create two new fields on any product edit page, for singular & plural units to be used for that product.', 'product-quantity-for-woocommerce') .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use unit on product level.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Allow defining unit on category level', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_by_cat_qty_unit_input_enabled',
				'desc_tip' => __( 'This will create two new fields on any category edit page, for singular & plural units to be used for all products under that category.', 'product-quantity-for-woocommerce') .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use unit on category level.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			
			array(
				'title'    => __( 'Allow defining unit on attribute level', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each attribute edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per attribute quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_qty_price_by_attribute_qty_unit_input_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'desc'     => __( 'Product Attributes', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Leave blank to use all', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_by_attribute_qty_unit_input_selected',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_attribute_field_lists(),
			),
			
			
			array(
				'title'    => __( 'Global label template: Singular', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This field will be used if no product/category units are defined', 'product-quantity-for-woocommerce' ),
				'desc'     => '',
				'id'       => 'alg_wc_pq_qty_price_by_qty_unit_singular',
				'type'     => 'text',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Global label template: Plural', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This field will be used if no product/category units are defined', 'product-quantity-for-woocommerce' ),
				'desc'     => '',
				'id'       => 'alg_wc_pq_qty_price_by_qty_unit_plural',
				'type'     => 'text',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Template', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'You can use HTML here.', 'product-quantity-for-woocommerce' ),
				'desc'     => sprintf( __( 'Placeholders: %s. %s', 'product-quantity-for-woocommerce' ),
					'<code>' . implode( '</code>, <code>', array( '%price%', '%qty%', '%unit%' ) ) . '</code>', __('(The %unit% placeholder will read from 3 places, with priority-level defined: First, it will read if a unit is defined on Product Level, if not defined, then it will check if defined on Category Level, if not defined, it will read from Global level defined on this page. If your store is using the same unit for all products, you can use the unit here in the field without any placeholder)','product-quantity-for-woocommerce') ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_template',
				'default'  => __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Position', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_position',
				'desc_tip' => __('Select where you want this will appear, if you select "Instead of the price", then the settings defined under "Price Unit" will not work since it will be overwritten by this feature.','product-quantity-for-woocommerce'),
				'default'  => 'instead',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'before'  => __( 'Before the price', 'product-quantity-for-woocommerce' ),
					'instead' => __( 'Instead of the price', 'product-quantity-for-woocommerce' ),
					'after'   => __( 'After the price', 'product-quantity-for-woocommerce' ),
					'before_add_to_cart'   => __( 'Before add to cart', 'product-quantity-for-woocommerce' ),
					'after_add_to_cart'   => __( 'After add to cart', 'product-quantity-for-woocommerce' ),
					
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_price_by_qty_qty_archive_options',
			),
			array(
				'title'    => __( 'Price by quantity on archive pages', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'	   => __('To enable this feature on the whole store but exclude some categories, you can enter these categories in the below field.','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_qty_price_by_qty_qty_archive_options_title',
			),
			array(
				'title'    => __( 'Enable Price by quantity on archive pages', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use Price by quantity on archive pages.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_qty_archive_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_price_by_qty_options_percategory',
			),
			array(
				'title'    => __( 'Total Price by Quantity for category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Show Total Price by Quantity on archive/category/shop pages (you need to enable quantity on archive pages from General tab).', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_price_by_qty_per_category_options',
			),
			array(
				'title'    => __( 'Enable per category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per category.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_price_by_qty_enable_per_category',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Exclude product categories', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_price_by_qty_per_category_categories',
				'desc_tip' => __( 'Selected categories will be Total Price by Quantity disabled', 'product-quantity-for-woocommerce' ),
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_categories(),
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_price_by_qty_options',
			),
			
		);
	}
	
	/**
	 * get_product_categories
	 *
	 * @version 1.8.0
	 * @since   1.6.0
	 */
	function get_product_categories() {
		$return_fields = array();
		$orderby = 'name';
		$order = 'asc';
		$hide_empty = false ;
		$cat_args = array(
			'orderby'    => $orderby,
			'order'      => $order,
			'hide_empty' => $hide_empty,
		);
		 
		$product_categories = get_terms( 'product_cat', $cat_args );
		if ( $product_categories ) {
			foreach ( $product_categories as $key => $category ) {
				$return_fields[$category->term_id] =  __( $category->name, 'product-quantity-for-woocommerce' );
			}
		}
		return $return_fields;
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

return new Alg_WC_PQ_Settings_Price_By_Qty();
