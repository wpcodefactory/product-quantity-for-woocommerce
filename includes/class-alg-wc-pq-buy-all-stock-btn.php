<?php
/**
 * Product Quantity for WooCommerce - Buy All Stock Button Class
 *
 * @version 5.3.0
 * @since   5.3.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PQ_Buy_All_Stock_Btn' ) ) :

	class Alg_WC_PQ_Buy_All_Stock_Btn {

		/**
		 * Constructor.
		 *
		 * @version 5.3.0
		 * @since   5.3.0
		 */
		function __construct() {
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'render_buy_all_stock_button' ), PHP_INT_MAX );
			add_filter( 'woocommerce_add_to_cart_quantity', array( $this, 'override_quantity_for_buy_all_stock' ), 10, 2 );
			add_filter( 'woocommerce_available_variation', array( $this, 'add_buy_all_stock_variation_data' ), 10, 3 );
		}

		/**
		 * render_buy_all_stock_button.
		 *
		 * @version 5.3.0
		 * @since   5.3.0
		 */
		function render_buy_all_stock_button() {
			global $product;

			$can_render_button = ( $product && is_a( $product, 'WC_Product' ) && ( $product->is_type( 'variable' ) || $product->managing_stock() ) );

			if (
				'yes' === get_option( 'alg_wc_pq_buy_all_stock_button_enabled', 'no' ) &&
				$can_render_button &&
				$product->is_purchasable() &&
				$product->is_in_stock() &&
				! $product->is_type( 'external' ) &&
				! $product->is_type( 'grouped' )
			) {
				$button_label = get_option( 'alg_wc_pq_buy_all_stock_button_label', __( 'Buy all stock', 'product-quantity-for-woocommerce' ) );
				$button_class = get_option( 'alg_wc_pq_buy_all_stock_button_class', 'button alt alg-wc-pq-buy-all-stock-button' );
				$alert_msg    = get_option( 'alg_wc_pq_buy_all_stock_button_alert_msg', __( 'Please select product options with managed stock before using Buy all stock.', 'product-quantity-for-woocommerce' ) );
				if ( '' === $button_label ) {
					$button_label = __( 'Buy all stock', 'product-quantity-for-woocommerce' );
				}
				if ( '' === $button_class ) {
					$button_class = 'button alt alg-wc-pq-buy-all-stock-button';
				}
				if ( '' === $alert_msg ) {
					$alert_msg = __( 'Please select product options with managed stock before using Buy all stock.', 'product-quantity-for-woocommerce' );
				}

				$button_classes_arr = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( $button_class ) ) ) );
				if ( ! in_array( 'alg-wc-pq-buy-all-stock-button', $button_classes_arr, true ) ) {
					$button_classes_arr[] = 'alg-wc-pq-buy-all-stock-button';
				}

				$button_classes = implode( ' ', $button_classes_arr );
				$button_attrs   = ' data-alert-msg="' . esc_attr( $alert_msg ) . '"';
				if ( $product->is_type( 'variable' ) ) {
					$button_classes .= ' disabled wc-variation-selection-needed';
					$button_attrs   .= ' aria-disabled="true"';
				}

				// Buy all submits its own dedicated flag. For simple products, add-to-cart
				// is provided as hidden input so this submit button can use the custom name.
				if ( ! $product->is_type( 'variable' ) ) {
					echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( $product->get_id() ) . '">';
				}
				echo '<button type="submit" name="alg_wc_pq_buy_all_stock_button" value="1" class="' . esc_attr( $button_classes ) . '" data-parent-managing-stock="' . esc_attr( $product->managing_stock() ? 'yes' : 'no' ) . '"' . $button_attrs . '>' . esc_html( $button_label ) . '</button>';

				if ( $product->is_type( 'variable' ) ) {
					$inline_js = '
						jQuery( function( $ ) {
							$( document ).on( "click", ".alg-wc-pq-buy-all-stock-button", function( event ) {
								var $button = $( this );
								if ( $button.hasClass( "disabled" ) || $button.hasClass( "wc-variation-selection-needed" ) ) {
									event.preventDefault();
									alert( $button.data( "alert-msg" ) );
								}
							} );

							$( document ).on( "found_variation reset_data hide_variation", "form.variations_form", function( event, variation ) {
								var $form = $( this );
								var $button = $form.find( ".alg-wc-pq-buy-all-stock-button" );
								if ( ! $button.length ) {
									return;
								}

								var parentManagesStock = ( "yes" === $button.data( "parent-managing-stock" ) );
								var variationManagesStock = ( variation && "yes" === variation.alg_wc_pq_managing_stock );
								var enableButton = ( "found_variation" === event.type && ( parentManagesStock || variationManagesStock ) );

								$button.attr( "aria-disabled", enableButton ? "false" : "true" );
								$button.toggleClass( "disabled wc-variation-selection-needed", ! enableButton );
							} );
						} );
					';

					wp_enqueue_script( 'wc-add-to-cart-variation' );
					wp_add_inline_script( 'wc-add-to-cart-variation', $inline_js );
				}
			}
		}

		/**
		 * add_buy_all_stock_variation_data.
		 *
		 * Adds stock-management info used by the Buy all stock button JS toggle.
		 *
		 * @version 5.3.0
		 * @since   5.3.0
		 *
		 * @param   array                 $variation_data  Variation data sent to JS.
		 * @param   WC_Product            $product         Parent variable product.
		 * @param   WC_Product_Variation  $variation       Current variation.
		 *
		 * @return array
		 */
		function add_buy_all_stock_variation_data( $variation_data, $product, $variation ) {
			$variation_data['alg_wc_pq_managing_stock'] = ( $variation->managing_stock() ? 'yes' : 'no' );

			return $variation_data;
		}

		/**
		 * is_buy_all_stock_request.
		 *
		 * @version 5.3.0
		 * @since   5.3.0
		 *
		 * @return bool
		 */
		function is_buy_all_stock_request() {
			return (
				'yes' === get_option( 'alg_wc_pq_buy_all_stock_button_enabled', 'no' ) &&
				isset( $_REQUEST['alg_wc_pq_buy_all_stock_button'] ) &&
				'1' === sanitize_text_field( wp_unslash( $_REQUEST['alg_wc_pq_buy_all_stock_button'] ) )
			);
		}

		/**
		 * override_quantity_for_buy_all_stock.
		 *
		 * Filters the quantity before WooCommerce adds the product to the cart.
		 *
		 * @version 5.3.0
		 * @since   5.3.0
		 */
		function override_quantity_for_buy_all_stock( $quantity, $product_id ) {
			$target_id = isset( $_REQUEST['variation_id'] ) && absint( $_REQUEST['variation_id'] ) > 0
				? absint( $_REQUEST['variation_id'] )
				: $product_id;

			if ( ! $this->is_buy_all_stock_request() || ! ( $product = wc_get_product( $target_id ) ) || ! is_a( $product, 'WC_Product' ) ) {
				return $quantity;
			}

			$stock = $product->get_stock_quantity();

			// Variation may not manage its own stock – fall back to parent.
			if ( ( ! is_numeric( $stock ) || $stock <= 0 ) && $product->is_type( 'variation' ) ) {
				$parent = wc_get_product( $product->get_parent_id() );
				$stock  = ( $parent ? $parent->get_stock_quantity() : $stock );
			}

			if ( ! is_numeric( $stock ) || $stock <= 0 ) {
				return $quantity;
			}

			$cart_qty = 0;
			if ( isset( WC()->cart ) && ! empty( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if ( $product->is_type( 'variation' ) ) {
						if ( isset( $cart_item['variation_id'] ) && absint( $cart_item['variation_id'] ) === $target_id ) {
							$cart_qty += (float) $cart_item['quantity'];
						}
					} else {
						if ( isset( $cart_item['product_id'] ) && absint( $cart_item['product_id'] ) === $target_id ) {
							$cart_qty += (float) $cart_item['quantity'];
						}
					}
				}
			}

			$remaining_stock = wc_stock_amount( $stock ) - $cart_qty;

			if ( $remaining_stock <= 0 ) {
				return $quantity;
			}

			return wc_stock_amount( $remaining_stock );
		}

	}

endif;

return new Alg_WC_PQ_Buy_All_Stock_Btn();
