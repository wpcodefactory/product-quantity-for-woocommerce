<?php
/**
 * Product Quantity for WooCommerce - Buy All Stock Button Class
 *
 * @version 5.3.7
 * @since   5.3.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Buy_All_Stock_Btn' ) ) :

	class WPFMMSQ_Buy_All_Stock_Btn {

		/**
		 * buy_all_stock_added_qty.
		 *
		 * Quantity calculated for the current Buy all stock request.
		 *
		 * @version 5.3.7
		 * @since   5.3.7
		 *
		 * @var float
		 */
		protected $buy_all_stock_added_qty = 0;

		/**
		 * buy_all_stock_product_title.
		 *
		 * Product title used in Buy all stock success notice.
		 *
		 * @version 5.3.7
		 * @since   5.3.7
		 *
		 * @var string
		 */
		protected $buy_all_stock_product_title = '';

		/**
		 * Constructor.
		 *
		 * @version 5.3.7
		 * @since   5.3.0
		 */
		function __construct() {
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'render_buy_all_stock_button' ), PHP_INT_MAX );
			add_filter( 'woocommerce_add_to_cart_quantity', array( $this, 'override_quantity_for_buy_all_stock' ), 10, 2 );
			add_filter( 'woocommerce_available_variation', array( $this, 'add_buy_all_stock_variation_data' ), 10, 3 );
			add_filter( 'wc_add_to_cart_message_html', array( $this, 'override_buy_all_stock_success_notice' ), 10, 2 );
		}

		/**
		 * render_buy_all_stock_button.
		 *
		 * @version 5.3.4
		 * @since   5.3.0
		 */
		function render_buy_all_stock_button() {
			global $product;

			$can_render_button = ( $product && is_a( $product, 'WC_Product' ) && ( $product->is_type( 'variable' ) || $product->managing_stock() ) );

			if (
				'yes' === get_option( 'wpfmmsq_buy_all_stock_button_enabled', 'no' ) &&
				$can_render_button &&
				$product->is_purchasable() &&
				$product->is_in_stock() &&
				! $product->is_type( 'external' ) &&
				! $product->is_type( 'grouped' )
			) {
				$button_label = get_option( 'wpfmmsq_buy_all_stock_button_label', __( 'Buy all stock', 'product-quantity-for-woocommerce' ) );
				$button_class = get_option( 'wpfmmsq_buy_all_stock_button_class', 'button alt wpfmmsq-buy-all-stock-button' );
				$alert_msg    = get_option( 'wpfmmsq_buy_all_stock_button_alert_msg', __( 'Please select product options with managed stock before using Buy all stock.', 'product-quantity-for-woocommerce' ) );
				if ( '' === $button_label ) {
					$button_label = __( 'Buy all stock', 'product-quantity-for-woocommerce' );
				}
				if ( '' === $button_class ) {
					$button_class = 'button alt wpfmmsq-buy-all-stock-button';
				}
				if ( '' === $alert_msg ) {
					$alert_msg = __( 'Please select product options with managed stock before using Buy all stock.', 'product-quantity-for-woocommerce' );
				}

				$button_classes_arr = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( $button_class ) ) ) );
				if ( ! in_array( 'wpfmmsq-buy-all-stock-button', $button_classes_arr, true ) ) {
					$button_classes_arr[] = 'wpfmmsq-buy-all-stock-button';
				}

				$button_classes = implode( ' ', $button_classes_arr );
				$is_variable    = $product->is_type( 'variable' );
				if ( $is_variable ) {
					$button_classes .= ' disabled wc-variation-selection-needed';
				}

				// Buy all submits its own dedicated flag. For simple products, add-to-cart
				// is provided as hidden input so this submit button can use the custom name.
				if ( ! $is_variable ) {
					echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( $product->get_id() ) . '">';
				}
				echo '<button type="submit" name="wpfmmsq_buy_all_stock_button" value="1" class="' . esc_attr( $button_classes ) . '" data-parent-managing-stock="' . esc_attr( $product->managing_stock() ? 'yes' : 'no' ) . '" data-alert-msg="' . esc_attr( $alert_msg ) . '"' . ( $is_variable ? ' aria-disabled="true"' : '' ) . '>' . esc_html( $button_label ) . '</button>';

				if ( $product->is_type( 'variable' ) ) {
					$this->enqueue_buy_all_stock_script();
				}
			}
		}

		/**
		 * enqueue_buy_all_stock_script.
		 *
		 * Enqueues the inline JS that handles the Buy all stock button behavior for variable products.
		 *
		 * @version 5.3.4
		 * @since   5.3.1
		 */
		function enqueue_buy_all_stock_script() {
			ob_start();
			?>
			<script>
				jQuery( function ( $ ) {
					$( document ).on( "click", ".wpfmmsq-buy-all-stock-button", function ( event ) {
						var $button = $( this );
						if ( $button.hasClass( "disabled" ) || $button.hasClass( "wc-variation-selection-needed" ) ) {
							event.preventDefault();
							alert( $button.data( "alert-msg" ) );
						}
					} );

					$( document ).on( "found_variation reset_data hide_variation", "form.variations_form", function ( event, variation ) {
						var $form = $( this );
						var $button = $form.find( ".wpfmmsq-buy-all-stock-button" );
						if ( !$button.length ) {
							return;
						}

						var parentManagesStock = ( "yes" === $button.data( "parent-managing-stock" ) );
						var variationManagesStock = ( variation && "yes" === variation.wpfmmsq_managing_stock );
						var enableButton = ( "found_variation" === event.type && ( parentManagesStock || variationManagesStock ) );

						$button.attr( "aria-disabled", enableButton ? "false" : "true" );
						$button.toggleClass( "disabled wc-variation-selection-needed", !enableButton );
					} );
				} );
			</script>
			<?php
			$script    = ob_get_clean();
			$inline_js = trim( str_replace( array( '<script>', '</script>' ), '', $script ) );

			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_add_inline_script( 'wc-add-to-cart-variation', $inline_js );
		}

		/**
		 * add_buy_all_stock_variation_data.
		 *
		 * Adds stock-management info used by the Buy all stock button JS toggle.
		 *
		 * @version 5.3.4
		 * @since   5.3.0
		 *
		 * @param   array                 $variation_data  Variation data sent to JS.
		 * @param   WC_Product            $product         Parent variable product.
		 * @param   WC_Product_Variation  $variation       Current variation.
		 *
		 * @return array
		 */
		function add_buy_all_stock_variation_data( $variation_data, $product, $variation ) {
			$variation_data['wpfmmsq_managing_stock'] = ( $variation->managing_stock() ? 'yes' : 'no' );

			return $variation_data;
		}

		/**
		 * is_buy_all_stock_request.
		 *
		 * @version 5.3.4
		 * @since   5.3.0
		 *
		 * @return bool
		 */
		function is_buy_all_stock_request() {
			return (
				'yes' === get_option( 'wpfmmsq_buy_all_stock_button_enabled', 'no' ) &&
				isset( $_REQUEST['wpfmmsq_buy_all_stock_button'] ) &&
				'1' === sanitize_text_field( wp_unslash( $_REQUEST['wpfmmsq_buy_all_stock_button'] ) )
			);
		}

		/**
		 * override_quantity_for_buy_all_stock.
		 *
		 * Filters the quantity before WooCommerce adds the product to the cart.
		 *
		 * @version 5.3.7
		 * @since   5.3.0
		 */
		function override_quantity_for_buy_all_stock( $quantity, $product_id ) {
			$this->buy_all_stock_added_qty     = 0;
			$this->buy_all_stock_product_title = '';

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

			$this->buy_all_stock_added_qty     = wc_stock_amount( $remaining_stock );
			$this->buy_all_stock_product_title = $product->get_name();

			return $this->buy_all_stock_added_qty;
		}

		/**
		 * Overrides success notice message for Buy all stock requests.
		 *
		 * Placeholders: %qty%, %product_title%.
		 *
		 * @version 5.3.7
		 * @since   5.3.7
		 *
		 * @param string $message  Original WooCommerce success message.
		 * @param array  $products Added products map.
		 *
		 * @return string
		 */
		function override_buy_all_stock_success_notice( $message, $products ) {
			if ( ! $this->is_buy_all_stock_request() || $this->buy_all_stock_added_qty <= 0 ) {
				return $message;
			}

			$template = get_option(
				'wpfmmsq_buy_all_stock_button_success_msg',
				__( '%qty% x %product_title% has been added to your cart.', 'product-quantity-for-woocommerce' )
			);

			if ( '' === $template ) {
				$template = __( '%qty% x %product_title% has been added to your cart.', 'product-quantity-for-woocommerce' );
			}

			$custom_text = str_replace(
				array( '%qty%', '%product_title%' ),
				array( (string) $this->buy_all_stock_added_qty, $this->buy_all_stock_product_title ),
				$template
			);

			return sprintf(
				'<a href="%s" class="button wc-forward">%s</a> %s',
				esc_url( wc_get_page_permalink( 'cart' ) ),
				esc_html__( 'View cart', 'woocommerce' ),
				esc_html( $custom_text )
			);
		}

	}

endif;

return new WPFMMSQ_Buy_All_Stock_Btn();
