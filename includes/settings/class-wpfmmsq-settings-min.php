<?php
/**
 * Product Quantity for WooCommerce - Min Section Settings
 *
 * @version 5.3.9
 * @since   1.6.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Settings_Min' ) ) :

	class WPFMMSQ_Settings_Min extends WPFMMSQ_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 5.3.4
		 * @since   1.6.0
		 */
		function __construct() {
			$this->id = 'min';
			parent::__construct();
		}

		/**
		 * set_section_variables.
		 *
		 * @version 5.3.4
		 * @since   5.0.3
		 *
		 * @return void
		 */
		public function set_section_variables() {
			parent::set_section_variables();
			$this->desc = __( 'Minimum Quantity', 'product-quantity-for-woocommerce' );
		}

		/**
		 * get_settings.
		 *
		 * @version 5.3.9
		 * @since   1.6.0
		 */
		function get_settings() {
			return array(
				array(
					'title' => __( 'Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'Manage the minimum quantity for products.', 'product-quantity-for-woocommerce' ),
					'id'    => 'wpfmmsq_min_options',
				),
				array(
					'title'   => __( 'Minimum quantity', 'product-quantity-for-woocommerce' ),
					'desc'    => '<strong>' . __( 'Enable Minimum quantity section', 'product-quantity-for-woocommerce' ) . '</strong>',
					'id'      => 'wpfmmsq_min_section_enabled',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'             => __( 'Add to cart button', 'product-quantity-for-woocommerce' ),
					'desc'              => __( 'Hide "Add To Cart" button when stock < min quantity', 'product-quantity-for-woocommerce' ),
					apply_filters( 'wpfmmsq_settings', '<br>' . sprintf( 'You will need %s to use per attribute quantity options.',
							'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
					'id'                => 'wpfmmsq_min_hide_add_to_cart_less_stock',
					'default'           => 'no',
					'type'              => 'checkbox',
					'custom_attributes' => apply_filters( 'wpfmmsq_settings', array( 'disabled' => 'disabled' ) ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_min_options',
				),
				array(
					'title' => __( 'Hide quantity input', 'product-quantity-for-woocommerce' ),
					'desc'  => __( 'Hide quantity input if stock quantity matches minimum.', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'wpfmmsq_min_qty_input_hide_qty_input_options',
				),
				array(
					'title'   => __( 'Product page', 'product-quantity-for-woocommerce' ),
					'desc'    => __( 'Hide quantity input on product page', 'product-quantity-for-woocommerce' ),
					'id'      => 'wpfmmsq_min_hide_qty_input_on_product_page_if_stock_matches_min',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Cart', 'product-quantity-for-woocommerce' ),
					'desc'    => __( 'Hide quantity input on cart', 'product-quantity-for-woocommerce' ),
					'id'      => 'wpfmmsq_min_hide_qty_input_on_cart_if_stock_matches_min',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'    => __( 'Loop', 'product-quantity-for-woocommerce' ),
					'desc'     => __( 'Hide quantity input on loop', 'product-quantity-for-woocommerce' ),
					'desc_tip' => __( 'Loop considers shop, category and tag pages.', 'product-quantity-for-woocommerce' ),
					'id'       => 'wpfmmsq_min_hide_qty_input_on_loop_if_stock_matches_min',
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_min_qty_input_hide_qty_input_options',
				),
				array(
					'title' => __( 'Cart Total Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'Specify minimum quantity on the cart level, <strong>regardless</strong> of number of products on it.
				The Message field will allow you to customize the notification message on wrong quantities', 'product-quantity-for-woocommerce' ),
					'id'    => 'wpfmmsq_min_cart_total_quantity_options',
				),
				array(
					'title'             => __( 'Cart total quantity', 'product-quantity-for-woocommerce' ),
					'desc_tip'          => __( 'This will set minimum total cart quantity. Set to zero to disable.', 'product-quantity-for-woocommerce' ),
					'id'                => 'wpfmmsq_min_cart_total_quantity',
					'default'           => 0,
					'type'              => 'number',
					'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
					'alg_empty_value'   => 0,
				),
				array(
					'title'       => __( 'Message', 'product-quantity-for-woocommerce' ),
					'desc_tip'    => __( 'Message to be displayed to customer when minimum cart total quantity is not reached.', 'product-quantity-for-woocommerce' ),
					'desc'        => $this->message_replaced_values( array( '%min_cart_total_quantity%', '%cart_total_quantity%' ) ),
					'id'          => 'wpfmmsq_min_cart_total_message',
					'default'     => __( 'Minimum allowed order quantity is %min_cart_total_quantity%. Your current order quantity is %cart_total_quantity%.', 'product-quantity-for-woocommerce' ),
					'type'        => 'textarea',
					'css'         => 'width:70%;',
					'wpfmmsq_raw' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_min_cart_total_quantity_options',
				),
				array(
					'title' => __( 'Per Item Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'This section allows you to specify a minimum quantity for all products in your store at once (not combined), tick "Per Product"  to define a quantity on product level (Pro Feature), a field will appear on the product page to set this.', 'product-quantity-for-woocommerce' ),
					'id'    => 'wpfmmsq_min_per_item_quantity_options',
				),
				array(
					'title'             => __( 'All products', 'product-quantity-for-woocommerce' ),
					'desc_tip'          => __( 'This will set minimum per item quantity (for all products). Set to zero to disable.', 'product-quantity-for-woocommerce' ),
					'id'                => 'wpfmmsq_min_per_item_quantity',
					'default'           => 0,
					'type'              => 'number',
					'custom_attributes' => array( 'min' => 0, 'step' => $this->get_qty_step_settings() ),
					'alg_empty_value'   => 0,
				),
				array(
					'title'    => __( 'Per product', 'product-quantity-for-woocommerce' ),
					'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
					'desc_tip' => __( 'This will add meta box to each product\'s edit page.', 'product-quantity-for-woocommerce' ),
					'id'       => 'wpfmmsq_min_per_item_quantity_per_product',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'title'       => __( 'Message', 'product-quantity-for-woocommerce' ),
					'desc_tip'    => __( 'Message to be displayed to customer when minimum per item quantity is not reached.', 'product-quantity-for-woocommerce' ),
					'desc'        => $this->message_replaced_values( array( '%product_title%', '%min_per_item_quantity%', '%item_quantity%' ) ),
					'id'          => 'wpfmmsq_min_per_item_message',
					'default'     => __( 'Minimum allowed quantity for %product_title% is %min_per_item_quantity%. Your current item quantity is %item_quantity%.', 'product-quantity-for-woocommerce' ),
					'type'        => 'textarea',
					'css'         => 'width:100%;',
					'wpfmmsq_raw' => true,
				),

				
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_min_cat_cart_total_quantity_options',
				),
                array(
					'title' => __( 'Selling below minimum', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'Control whether products can sell below the minimum quantity.', 'product-quantity-for-woocommerce' ),
					'id'    => 'wpfmmsq_min_allow_selling_below_minimum_options',
				),
				array(
					'title'             => __( 'Allow selling below minimum', 'product-quantity-for-woocommerce' ),
					'desc'              => __( 'Allow selling below minimum quantity if stock < min', 'product-quantity-for-woocommerce' ),
					'desc_tip'          => __( 'Adds a checkbox to product\'s edit page after min quantity box, useful when you are almost out of stock and want to sell the remaining quantity.', 'product-quantity-for-woocommerce' ) .
					                       apply_filters( 'wpfmmsq_settings', '<br>' . sprintf( 'You will need %s to use per item quantity options.',
						                       '<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
					'id'                => 'wpfmmsq_min_per_item_quantity_per_product_allow_selling_below_stock',
					'default'           => 'no',
					'type'              => 'checkbox',
					'custom_attributes' => apply_filters( 'wpfmmsq_settings', array( 'disabled' => 'disabled' ) ),
				),
				array(
					'title'             => __( 'Apply to all products', 'product-quantity-for-woocommerce' ),
					'desc'              => __( 'After saving changes this option will be reset. Please keep in mind this is a bulk operation, so on large catalogs it may take a little while.', 'product-quantity-for-woocommerce' ),
					'desc_tip'          => __( 'Choose whether to enable or disable the flag for all published products, or leave it neutral to do nothing. Save changes to apply it.', 'product-quantity-for-woocommerce' ) .
					                       apply_filters( 'wpfmmsq_settings', '<br>' . sprintf( 'You will need %s to use per item quantity options.',
							                       '<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
					'id'                => 'wpfmmsq_min_per_item_quantity_per_product_allow_selling_below_stock_save',
					'default'           => '',
					'type'              => 'select',
					'options'           => array(
						''   => __( '-', 'product-quantity-for-woocommerce' ),
						'yes' => __( 'Enable all products', 'product-quantity-for-woocommerce' ),
						'no'  => __( 'Disable all products', 'product-quantity-for-woocommerce' ),
					),
					'custom_attributes' => apply_filters( 'wpfmmsq_settings', array( 'disabled' => 'disabled' ) ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_min_allow_selling_below_minimum_options',
				),
				array(
					'title' => __( 'Per Category Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'Enabling this will create two new fields in all categories pages you have, one to set a minimum quantity for all products (instead of filling it one by one), and one for specifying a minimum quantity for all products <strong>combined</strong> in the cart.', 'product-quantity-for-woocommerce' ),
					'id'    => 'wpfmmsq_min_per_cat_item_quantity_options',
				),
				array(
					'title'             => __( 'Per Category', 'product-quantity-for-woocommerce' ),
					'desc'              => __( 'Enable', 'product-quantity-for-woocommerce' ),
					'desc_tip'          => __( 'This will add meta box to each category edit page.', 'product-quantity-for-woocommerce' ) .
					                       apply_filters( 'wpfmmsq_settings', '<br>' . sprintf( 'You will need %s to use per category quantity options.',
							                       '<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
					'id'                => 'wpfmmsq_min_per_cat_item_quantity_per_product',
					'default'           => 'no',
					'type'              => 'checkbox',
					'custom_attributes' => apply_filters( 'wpfmmsq_settings', array( 'disabled' => 'disabled' ) ),
				),
				array(
					'title'       => __( 'Message', 'product-quantity-for-woocommerce' ),
					'desc_tip'    => __( 'Message to be displayed to customer when minimum per item quantity is not reached.', 'product-quantity-for-woocommerce' ),
					'desc'        => $this->message_replaced_values( array( '%category_title%', '%min_per_item_quantity%', '%item_quantity%' ) ),
					'id'          => 'wpfmmsq_min_cat_message',
					'default'     => __( 'Minimum allowed quantity for category %category_title% is %min_per_item_quantity%.  Your current quantity for this category is %item_quantity%.', 'product-quantity-for-woocommerce' ),
					'type'        => 'textarea',
					'css'         => 'width:100%;',
					'wpfmmsq_raw' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_min_per_attribute_quantity_options',
				),
				array(
					'title' => __( 'Per Attribute Minimum Quantity Options', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'This option works the exact same way as category, you also get the option to enable it per attributes that are selected in the field below instead of enabling it to all attributes at once.', 'product-quantity-for-woocommerce' ),
					'id'    => 'wpfmmsq_min_per_attribute_item_quantity_options',
				),
				array(
					'title'             => __( 'Per Attribute', 'product-quantity-for-woocommerce' ),
					'desc'              => __( 'Enable', 'product-quantity-for-woocommerce' ),
					'desc_tip'          => __( 'This will add meta box to each attribute edit page.', 'product-quantity-for-woocommerce' ) .
					                       apply_filters( 'wpfmmsq_settings', '<br>' . sprintf( 'You will need %s to use per attribute quantity options.',
							                       '<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
					'id'                => 'wpfmmsq_min_per_attribute_item_quantity',
					'default'           => 'no',
					'type'              => 'checkbox',
					'custom_attributes' => apply_filters( 'wpfmmsq_settings', array( 'disabled' => 'disabled' ) ),
				),
				array(
					'desc'     => __( 'Product Attributes', 'product-quantity-for-woocommerce' ),
					'desc_tip' => __( 'Leave blank to use all', 'product-quantity-for-woocommerce' ),
					'id'       => 'wpfmmsq_min_per_attribute_selected',
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => $this->get_attribute_field_lists(),
				),
				array(
					'title'       => __( 'Message', 'product-quantity-for-woocommerce' ),
					'desc_tip'    => __( 'Message to be displayed to customer when minimum per item quantity is not reached.', 'product-quantity-for-woocommerce' ),
					'desc'        => $this->message_replaced_values( array( '%attribute_title%', '%min_per_item_quantity%', '%item_quantity%' ) ),
					'id'          => 'wpfmmsq_min_per_attribute_message',
					'default'     => __( 'Minimum allowed quantity for attribute %attribute_title% is %min_per_item_quantity%.  Your current quantity for this attribute is %item_quantity%.', 'product-quantity-for-woocommerce' ),
					'type'        => 'textarea',
					'css'         => 'width:100%;',
					'wpfmmsq_raw' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_min_per_item_quantity_options',
				),
				array(
					'title' => __( 'Useful information', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => $this->section_notes(
						array(
							__( 'The initial default quantity visible will be set to minimum.', 'product-quantity-for-woocommerce' ),
							__( 'If the default quantity is lower than minimum quantity, the initial quantity will be set to minimum.', 'product-quantity-for-woocommerce' ),
						)
					),
					'id'    => 'wpfmmsq_min_useful_options',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_min_useful_options',
				),
			);
		}

		/**
		 * get_attribute_lists
		 *
		 * @version 5.3.4
		 * @since   1.6.0
		 */
		function get_attribute_field_lists() {
			$return_fields        = array();
			$attribute_taxonomies = wpfmmsq_wc_get_attribute_taxonomies();
			if ( $attribute_taxonomies ) {
				foreach ( $attribute_taxonomies as $tax ) {
					$name                                = wpfmmsq_wc_attribute_taxonomy_name( $tax->attribute_name );
					$return_fields[ $tax->attribute_id ] = $tax->attribute_label;
				}
			}

			return $return_fields;
		}

	}

endif;

return new WPFMMSQ_Settings_Min();
