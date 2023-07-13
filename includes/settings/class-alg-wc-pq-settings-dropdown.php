<?php
/**
 * Product Quantity for WooCommerce - Dropdown Section Settings
 *
 * @version 1.8.1
 * @since   1.7.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Dropdown' ) ) :

class Alg_WC_PQ_Settings_Dropdown extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'dropdown';
		$this->desc = __( 'Quantity Dropdown', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.8.1
	 * @since   1.7.0
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Quantity Dropdown Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_dropdown_options',
				'desc'     => __( 'Show a dropdown instead of the default quantity input field in WooCommerce. To show a dropdown, it has to end somewhere, so it will work only if any of (Maximum Quantity, Fixed Quantity, Max. Fallback) is present.', 'product-quantity-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Quantity as dropdown', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_qty_dropdown',
				'default'  => 'no',
				'type'     => 'checkbox',
				
			),
			array(
				'title'    => __( 'Add filter / search on the top of dropdown', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_qty_dropdown_search_filter',
				'default'  => 'no',
				'type'     => 'checkbox',
				
			),
			array(
				'title'    => __( 'Max value fallback', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Define a value for any product that has no maximum or fixed quantities defined and you want it to have a dropdown.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_max_value_fallback',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
			),
			array(
				'title'    => __( 'Thousand Separator', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable', 'product-quantity-for-woocommerce' ) . '</strong>',
				'desc_tip' => __('Dealing with large values? Define a thousand separator other than the default comma ,', 'product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_qty_dropdown_thousand_separator_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				/*'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),*/
			),
			
			array(
				'title'    => __( 'Separator', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'use separator like ( ,)', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_thousand_separator',
				'default'  => ',',
				'type'     => 'text',
				'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
				'css'      => 'width:50px;',
			),
			array(
				'title'    => __( 'Disable dropdown on cart', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Tick to disable dropdown on cart', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_qty_dropdown_disable_dropdown_on_cart',
				'default'  => 'no',
				'type'     => 'checkbox',
				
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_dropdown_options',
			),
			array(
				'title'    => __( 'Dropdown Labels', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __( 'Use the below two fields to show a text (like unit) next to values appearing in dropdown (i.e. 1 KG, 10 KGs, 20 pieces, 30 liters), you can define singular & plural texts.
				You can also define this label on product level by enabling "Labels per product"', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_label_options',
			),
			array(
				'title'    => __( 'Singular label template', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%qty%', '%price%' ) ) . ' ' .
					sprintf( __( 'For example try %s', 'product-quantity-for-woocommerce' ),  '<code>%qty% for %price%</code>' ),
				'id'       => 'alg_wc_pq_qty_dropdown_label_template_singular',
				'default'  => '%qty%',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Plural label template', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%qty%', '%price%' ) ) . ' ' .
					sprintf( __( 'For example try %s', 'product-quantity-for-woocommerce' ),  '<code>%qty% for %price%</code>' ),
				'id'       => 'alg_wc_pq_qty_dropdown_label_template_plural',
				'default'  => '%qty%',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Labels per product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add "Product Quantity" meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ) . apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per product.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_qty_dropdown_label_template_is_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_dropdown_qty_archive_options',
			),
			array(
				'title'    => __( 'Dropdown per category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'If you want to show dropdown on your store but with excluding some categories, you can enter these categories in the below field.', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_dropdown_per_category_options',
			),
			array(
				'title'    => __( 'Enable per category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per category.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_qty_dropdown_enable_per_category',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Exclude product categories', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_per_category_categories',
				'desc_tip' => __( 'Selected categories will be dropdown disabled', 'product-quantity-for-woocommerce' ),
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_categories(),
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_dropdown_label_options',
			),
			
			array(
				'title'    => __( 'Template', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Optional quantity dropdown frontend template modifications.', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_dropdown_template_options',
			),
			array(
				'title'    => __( 'Before', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Will be outputted on frontend before the quantity dropdown box.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'You can use HTML and/or shortcodes here.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Ignored if empty.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_template_before',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'After', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Will be outputted on frontend after the quantity dropdown box.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'You can use HTML and/or shortcodes here.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Ignored if empty.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_template_after',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_dropdown_template_options',
			),
			array(
				'title'    => __( 'Quantity dropdown on archive pages', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'     => __( 'Enable showing a quantity dropdown instead of the quantity field on archive/category/shop pages for simple products only (you need to enable quantity on archive pages from General tab).', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_dropdown_qty_archive_options_title',
			),
			array(
				'title'    => __( 'Enable dropdown on archive pages', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use dropdown on archive pages.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_qty_dropdown_qty_archive_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_dropdown_template_options',
			),
			
		);
	}
	
	/**
	 * get_attribute_lists
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

}

endif;

return new Alg_WC_PQ_Settings_Dropdown();
