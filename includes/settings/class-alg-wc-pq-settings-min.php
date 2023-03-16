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
		
		$allow_all_product_button = '';
		if ( 'yes' === get_option( 'alg_wc_pq_min_per_item_quantity_per_product_allow_selling_below_stock', 'yes' ) ) {
		$allow_all_product_button = '<br><a class="button" href="' . add_query_arg( 'alg_wc_pq_all_below_stock', 'yes' ) . '" title="' .
				__( 'Enable to all simple products, disable to remove at once', 'product-quantity-for-woocommerce' ) . '">' .
					__( 'Allow all product', 'product-quantity-for-woocommerce' ) . '</a>';
		}
		$allow_all_product_button = '';
					
		return array(
			array(
				'title'    => __( 'Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'	   => __('Specify a minimum quantity based on one of the options below. Note that to make Minimum quantity appears on page load, you will have to configure this on General >> Force Quantity Options >> Force to Minimum quantity.','product-quantity-for-woocommerce'),
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
				'desc'	   => __('Specify minimum quantity on the cart level, <strong>regardless</strong> of number of products on it.
				The Message field will allow you to customize the notification message on wrong quantities','product-quantity-for-woocommerce'),
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
				'css'      => 'width:70%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_min_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Per Item Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'	   => __('This section allows you to specify a minimum quantity for all products in your store at once (not combined), tick “Per Product”  to define a quantity on product level (Pro Feature), a field will appear on the product page to set this.','product-quantity-for-woocommerce'),
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
				'desc_tip' => __( 'This will add meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_min_per_item_quantity_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
				/*'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),*/
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
				'title'    => __( 'Allow selling below minimum quantity if stock < min.', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add checkbox to product\'s edit page after min quantity box, useful when you are almost out of stock and want to sell the remaining quantity', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per item quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ) . $allow_all_product_button,
				'id'       => 'alg_wc_pq_min_per_item_quantity_per_product_allow_selling_below_stock',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Allow all products', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Save all simple products.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per item quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_min_per_item_quantity_per_product_allow_selling_below_stock_save',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			
			array(
				'type'     => 'sectionend',				
				'id'       => 'alg_wc_pq_min_cat_cart_total_quantity_options',
			),
			array(
				'title'    => __( 'Per Category Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'	   => __('Enabling this will create two new fields in all categories pages you have, one to set a minimum quantity for all products (instead of filling it one by one), and one for specifying a minimum quantity for all products <strong>combined</strong> in the cart.','product-quantity-for-woocommerce'),				
				'id'       => 'alg_wc_pq_min_per_cat_item_quantity_options',
			),
			array(
				'title'    => __( 'Per Category', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each category edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per category quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_min_per_cat_item_quantity_per_product',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when minimum per item quantity is not reached.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%category_title%', '%min_per_item_quantity%', '%item_quantity%' ) ),
				'id'       => 'alg_wc_pq_min_cat_message',
				'default'  => __( 'Minimum allowed quantity for category %category_title% is %min_per_item_quantity%.  Your current quantity for this category is %item_quantity%.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'type'     => 'sectionend',				
				'id'       => 'alg_wc_pq_min_per_attribute_quantity_options',
			),
			array(
				'title'    => __( 'Per Attribute Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'	   => __('This option works the exact same way as category, you also get the option to enable it per attributes that are selected in the field below instead of enabling it to all attributes at once.','product-quantity-for-woocommerce'),				
				'id'       => 'alg_wc_pq_min_per_attribute_item_quantity_options',
			),
			array(
				'title'    => __( 'Per Attribute', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will add meta box to each attribute edit page.', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use per attribute quantity options.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_min_per_attribute_item_quantity',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'desc'     => __( 'Product Attributes', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Leave blank to use all', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_min_per_attribute_selected',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_attribute_field_lists(),
			),
			array(
				'title'    => __( 'Message', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Message to be displayed to customer when minimum per item quantity is not reached.', 'product-quantity-for-woocommerce' ),
				'desc'     => $this->message_replaced_values( array( '%attribute_title%', '%min_per_item_quantity%', '%item_quantity%' ) ),
				'id'       => 'alg_wc_pq_min_per_attribute_message',
				'default'  => __( 'Minimum allowed quantity for attribute %attribute_title% is %min_per_item_quantity%.  Your current quantity for this attribute is %item_quantity%.', 'product-quantity-for-woocommerce' ),
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

return new Alg_WC_PQ_Settings_Min();
