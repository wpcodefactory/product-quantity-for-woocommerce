<?php
/**
 * Product Quantity for WooCommerce - General Section Settings
 *
 * @version 1.8.0
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_General' ) ) :

class Alg_WC_PQ_Settings_General extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 * @todo    [feature] Force initial quantity on single product page - add "Custom value" option
	 * @todo    [feature] Force initial quantity on single product page - per product
	 */
	function get_settings() {
		$main_settings = array(
			array(
				'title'    => __( 'Product Quantity Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_options',
			),
			array(
				'title'    => __( 'Product Quantity for WooCommerce', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_options',
			),
		);
		$general_settings = array(
			array(
				'title'    => __( 'General Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'desc'	   => __('This section contains settings that will be applied generally to your store, regardless of other settings defined in other tabs.
				A description or tooltip is provided on almost every option to explain what it does and how it affects behavior based on your needs.','product-quantity-for-woocommerce'),
				'id'       => 'alg_wc_pq_general_options',
			),
			array(
				'title'    => __( 'Decimal quantities', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Use decimal values on minimum/maximum/step quantities, <br>so to allow orders to buy in decimal, a decimal value needs to be used in min/max/step (i.e. to sell in 0.5 quantity, it has to be defined on step for example)', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_decimal_quantities_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Decimal quantities in admin order', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Allow store admins to create orders with decimal values from store backend', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_decimal_quantities_admin_order_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Sold individually', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'This will enable "Sold individually" (no quantities) option for <strong>all products</strong> at once.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_all_sold_individually_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Force minimum quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Will force all minimum quantities to %s.', 'product-quantity-for-woocommerce' ), '<code>1</code>' ) . ' ' .
					__( 'This includes cart items, grouped products etc.', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Ignored if "Minimum quantity" section is enabled.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_cart_min_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( '"Add to cart" validation', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Configure if you want to validate correct amounts on “Add to Cart”, you can block reaching cart, or correct quantities automatically (see next option for rounding).
				For most stores, the “Validate and add notices” is recommended.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_add_to_cart_validation',
				'default'  => 'disable',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'disable' => __( 'Do not validate', 'product-quantity-for-woocommerce' ),
					'notice'  => __( 'Validate and add notices', 'product-quantity-for-woocommerce' ),
					'correct' => __( 'Validate and auto-correct quantities', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'desc'     => __( 'Step auto-correct', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Ignored unless "Validate and auto-correct quantities" option is selected above.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_add_to_cart_validation_step_auto_correct',
				'default'  => 'round',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'round'      => __( 'Round', 'product-quantity-for-woocommerce' ),
					'round_up'   => __( 'Round up', 'product-quantity-for-woocommerce' ),
					'round_down' => __( 'Round down', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Cart notices', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Show error or notification messages on cart if users reached cart with wrong quantities.', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_cart_notice_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Cart notice type', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_cart_notice_type',
				'default'  => 'notice',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'notice'  => __( 'Notice', 'product-quantity-for-woocommerce' ),
					'error'   => __( 'Error', 'product-quantity-for-woocommerce' ),
					'success' => __( 'Success', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Block checkout page', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Block users from reaching checkout on wrong quantities (if notification is selected in Cart Notices)', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_stop_from_seeing_checkout',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_general_options',
			),
			array(
				'title'    => __( 'Quantity on archive pages', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_general_qty_archive_options_title',
			),
			array(
				'title'    => __( 'Enable quantity on archive pages', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Allow customers to select quantities and add to cart from shop/archive/categories pages, <strong>this works only on simple products.</strong>', 'product-quantity-for-woocommerce' ) .
					apply_filters( 'alg_wc_pq_settings', '<br>' . sprintf( 'You will need %s to use quantity box on archive page.',
						'<a target="_blank" href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' . 'Product Quantity for WooCommerce Pro' . '</a>' ) ),
				'id'       => 'alg_wc_pq_add_quantity_archive_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pq_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_general_qty_archive_options',
			),
			array(
				'title'    => __( 'Language Guide', 'product-quantity-for-woocommerce' ),
				'desc'    => '<table class="form-table"><tbody><tr valign="top" class=""><th scope="row" class="titledesc">' . __( 'WPML OR Polylang', 'product-quantity-for-woocommerce' ) . '</th><td class="forminp forminp-checkbox">' . __( 'If you are using multi-language store with WPML or Polylang, you can use shortcodes to show different languages, example: [alg_wc_pq_translate lang="EN"] Allowed quantity for %product_title% is %allowed_quantity%. Your current quantity is %quantity%.[/alg_wc_pq_translate] can be used to show English messages, similar to other languages you have, use [alg_wc_pq_translate not_lang=" "] as fallback for non-defined languages.', 'product-quantity-for-woocommerce' ) . '</td></tr></tbody></table>',
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_language_guide',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_language_guide',
			),
			array(
				'title'    => __( 'Force Quantity Options', 'product-quantity-for-woocommerce' ),
				'desc'    => __( 'This section will allow you to set what value (quantity) you want to show on page load, whether on product pages, or archives (shop/category) pages.', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_forcing_qty_options',
			),
			array(
				'title'    => __( 'Force initial quantity on single product page', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_on_single',
				'default'  => 'disabled',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'disabled' => __( 'Do not force', 'product-quantity-for-woocommerce' ),
					'min'      => __( 'Force to min quantity', 'product-quantity-for-woocommerce' ),
					'max'      => __( 'Force to max quantity', 'product-quantity-for-woocommerce' ),
					'default'  => __( 'Force to default quantity', 'product-quantity-for-woocommerce' ),
					'exact_allowed'  => __( 'Force to lowest fixed quantity', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Force initial quantity on archives', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_force_on_loop',
				'default'  => 'disabled',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'disabled' => __( 'Do not force', 'product-quantity-for-woocommerce' ),
					'min'      => __( 'Force to min quantity', 'product-quantity-for-woocommerce' ),
					'max'      => __( 'Force to max quantity', 'product-quantity-for-woocommerce' ),
					'default'  => __( 'Force to default quantity', 'product-quantity-for-woocommerce' ),
					'exact_allowed'  => __( 'Force to lowest fixed quantity', 'product-quantity-for-woocommerce' ),
				),
			),
			
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_forcing_qty_options',
			),
			array(
				'title'    => __( 'Variable Products Options', 'product-quantity-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_variable_products_options',
			),
			array(
				'title'    => __( 'Load all variations', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Please leave this option <strong>unticked</strong> unless you have YITH WooCommerce Quick View plugin AND you are having issues with loading variations customized with this plugin, if it didn’t fix the issue, please revert it back unticked and send us a support request.', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_variation_do_load_all',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'On variation change', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_variation_change',
				'desc_tip' => __('Select what you want to happen to quantity field when you change a variation, you can leave the quantity as is, reset to min, max, or default for the new selected variation', 'product-quantity-for-woocommerce'),
				'default'  => 'do_nothing',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'do_nothing'   => __( 'Do nothing', 'product-quantity-for-woocommerce' ),
					'reset_to_min' => __( 'Reset to min quantity', 'product-quantity-for-woocommerce' ),
					'reset_to_max' => __( 'Reset to max quantity', 'product-quantity-for-woocommerce' ),
					'reset_to_default' => __( 'Reset to default quantity', 'product-quantity-for-woocommerce' ),
					'reset_to_lowest_fixed' => __( 'Reset to lowest fixed quantity', 'product-quantity-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Sum variations', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_sum_variations',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_variable_products_options',
			),
		);
		return array_merge( $main_settings, $general_settings );
	}

}

endif;

return new Alg_WC_PQ_Settings_General();
