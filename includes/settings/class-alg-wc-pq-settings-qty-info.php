<?php
/**
 * Product Quantity for WooCommerce - Quantity Info Section Settings
 *
 * @version 1.7.0
 * @since   1.7.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Quantity_Info' ) ) :

class Alg_WC_PQ_Settings_Quantity_Info extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'quantity_info';
		$this->desc = __( 'Quantity Info', 'product-quantity-for-woocommerce' );
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
		$content_desc = __( 'You can use HTML and/or shortcodes here.', 'product-quantity-for-woocommerce' ) . ' ' .
			sprintf( __( 'Available shortcodes: %s.', 'product-quantity-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array(
					'[alg_wc_pq_min_product_qty]',
					'[alg_wc_pq_max_product_qty]',
					'[alg_wc_pq_product_qty_step]',
					'[alg_wc_pq_product_qty_price_unit]',
				) ) . '</code>' );
		$default_content = '<p>' .
				'[alg_wc_pq_min_product_qty before="Minimum quantity is <strong>" after="</strong><br>"]' .
				'[alg_wc_pq_max_product_qty before="Maximum quantity is <strong>" after="</strong><br>"]' .
				'[alg_wc_pq_product_qty_step before="Step is <strong>" after="</strong><br>"]' .
				'[alg_wc_pq_product_qty_price_unit before="Price unit is <strong>" after="</strong><br>"]' .
			'</p>';
		return array(
			array(
				'title'    => __( 'Quantity Info Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_info_options',
				'desc'     => $content_desc,
			),
			array(
				'title'    => __( 'Single product page', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_info_on_single_product',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			
			array(
				'id'       => 'alg_wc_pq_qty_info_on_single_product_content',
				'default'  => $default_content,
				'type'     => 'textarea',
				'css'      => 'width:70%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Archives', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_info_on_loop',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'alg_wc_pq_qty_info_on_loop_content',
				'default'  => $default_content,
				'type'     => 'textarea',
				'css'      => 'width:70%;min-height:100px;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_info_options_percategory',
			),
			array(
				'title'    => __( 'Quantity info for category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( '', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_info_per_category_options',
			),
			array(
				'title'    => __( 'Enable per category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( '', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_info_enable_per_category',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Exclude product categories', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_info_per_category_categories',
				'desc_tip' => __( 'Selected categories will be quantity info disabled', 'product-quantity-for-woocommerce' ),
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_categories(),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_info_options_advance',
			),
			array(
				'title'    => __( 'Advanced', 'product-quantity-for-woocommerce' ),
				'desc'     => __( '', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_info_per_category_advance',
			),
			array(
				'title'    => __( 'Single product page custom hook', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Please note that using hook needs some programing skills in WordPress, so please don\'t enable the custom hook options unless you\'re aware of how this affect the website, as using wrong hooks may cause fatal issues. <a target="_blank" href="https://woocommerce.github.io/code-reference/hooks/hooks.html">Please refer to this official documentation for references.</a>', 'product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_qty_info_on_single_product_custom_hook',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Custom hook name', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Input custom hook name as per woocommerce document.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_info_on_single_product_custom_hook_name',
				'default'  => '',
				'type'     => 'text'
			),
			array(
				'title'    => __( 'Custom hook priority', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Input custom hook priority as per woocommerce document.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_info_on_single_product_custom_hook_priority',
				'default'  => '',
				'type'     => 'text'
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_info_options',
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

return new Alg_WC_PQ_Settings_Quantity_Info();
