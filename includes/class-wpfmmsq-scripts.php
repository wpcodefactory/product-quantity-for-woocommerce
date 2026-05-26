<?php
/**
 * Product Quantity for WooCommerce - Scripts Class
 *
 * @version 5.4.1
 * @since   1.7.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Scripts' ) ) :

	class WPFMMSQ_Scripts {

		/**
		 * Constructor.
		 *
		 * @version 5.3.4
		 * @since   1.7.0
		 */
		function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Enqueue frontend scripts.
		 *
		 * @version 5.4.1
		 * @since   1.0.0
		 *
		 * @todo    [dev] (maybe) Price by qty: add `prepend` and `append` positions
		 * @todo    [dev] (important) (maybe) `force_js_check_min_max()` should go *before* the `force_js_check_step()`?
		 * @todo    [feature] `'force_on_add_to_cart' => ( 'yes' === get_option( 'wpfmmsq_variation_force_on_add_to_cart', 'no' ) )`
		 * @todo    [feature] (maybe) `force_on_add_to_cart` for simple products
		 * @todo    [feature] (maybe) make this optional (for min/max quantities)
		 */
		function enqueue_scripts() {
			// Variable products
			if (
				( $do_load_all_variations = (
					'yes' === get_option( 'wpfmmsq_variation_do_load_all', 'no' ) )
				) ||
				(
					( $_product = wc_get_product( get_the_ID() ) ) &&
					$_product->is_type( 'variable' )
				)
			) {
				$wpfmmsq_quantities_options = array(
					'reset_to_min'           => (
						'reset_to_min' === get_option( 'wpfmmsq_variation_change', 'do_nothing' )
					),
					'reset_to_max'           => (
						'reset_to_max' === get_option( 'wpfmmsq_variation_change', 'do_nothing' )
					),
					'reset_to_default'       => (
						'reset_to_default' === get_option( 'wpfmmsq_variation_change', 'do_nothing' )
					),
					'reset_to_lowest_fixed'  => (
						'reset_to_lowest_fixed' === get_option( 'wpfmmsq_variation_change', 'do_nothing' )
					),
					'do_load_all_variations' => $do_load_all_variations,
					'max_value_fallback'     => $max_value_fallback = get_option(
						'wpfmmsq_qty_dropdown_max_value_fallback',
						100
					),
					'is_dropdown_enabled'    => get_option( 'wpfmmsq_qty_dropdown', 'no' ),
					'wpfmmsq_is_catalog'     => 'no',
					'nonce'                  => wp_create_nonce( 'wpfmmsq_nonce' ),
				);

				if ( is_product_category() || is_shop() ) {
					$wpfmmsq_quantities_options['wpfmmsq_is_catalog'] = 'yes';
				}

				$wpfmmsq_product_quantities = array();
				if ( $do_load_all_variations ) {
					foreach (
						wc_get_products( array( 'return' => 'ids', 'limit' => - 1, 'type' => 'variable' ) ) as $product_id
					) {
						if ( $_product = wc_get_product( $product_id ) ) {
							foreach ( $_product->get_available_variations() as $variation ) {
								$variation_id = $variation['variation_id'];

								$min_qty   = wpfmmsq()->core->get_product_qty_min_max(
									$product_id,
									$variation['min_qty'],
									'min',
									$variation_id
								);
								$max_qty   = wpfmmsq()->core->get_product_qty_min_max(
									$product_id,
									$variation['max_qty'],
									'max',
									$variation_id
								);
								$step      = wpfmmsq()->core->get_product_qty_step(
									$product_id,
									1,
									$variation['variation_id']
								);
								$label     = wpfmmsq()->core->get_dropdown_option_label(
									$variation['variation_id']
								);
								$default   = wpfmmsq()->core->get_product_qty_default(
									$variation['variation_id'], 1
								);
								$exact_qty = wpfmmsq()->core->get_product_exact_qty(
									$product_id,
									'allowed',
									'', $variation_id
								);

								$wpfmmsq_product_quantities[ $variation['variation_id'] ] = array(
									'min_qty'       => $min_qty,
									'max_qty'       => $max_qty,
									'step'          => $step,
									'label'         => $label,
									'default'       => $default,
									'exact_qty'     => $exact_qty,
									'vprice_by_qty' => $this->wpfmmsq_prices_by_variation(
										$variation_id,
										$min_qty,
										$max_qty,
										$step,
										$exact_qty
									),
								);
							}
						}
					}
				}
				wpfmmsq_enqueue_script(
					'wpfmmsq-variable',
					trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-variable.js',
					array( 'jquery' ),
					wpfmmsq()->version,
					true
				);
				wp_localize_script(
					'wpfmmsq-variable',
					'wpfmmsq_product_quantities',
					$wpfmmsq_product_quantities
				);
				wp_localize_script(
					'wpfmmsq-variable',
					'wpfmmsq_quantities_options',
					$wpfmmsq_quantities_options
				);
			}

			// Price by qty
			if ( 'yes' === get_option( 'wpfmmsq_qty_price_by_qty_enabled', 'no' ) ) {
				if (
					( $_product = wc_get_product( get_the_ID() ) ) &&
					$_product->is_type( 'simple' ) && is_product()
				) {
					if ( wpfmmsq()->core->wpfmmsq_price_by_qty_is_disable( $_product ) ) {
						return;
					}
					if ( $_product->is_purchasable() ) {
						wpfmmsq_enqueue_script(
							'wpfmmsq-price-by-qty',
							trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-price-by-qty.js',
							array( 'jquery' ),
							wpfmmsq()->version,
							true
						);
						wp_localize_script(
							'wpfmmsq-price-by-qty',
							'wpfmmsq_price_by_qty_obj',
							array(
								'ajax_url'                => admin_url( 'admin-ajax.php' ),
								'product_id'              => get_the_ID(),
								'position'                => get_option( 'wpfmmsq_qty_price_by_qty_position', 'instead' ),
								'replace_variation_price' => 'yes' === get_option(
										'wpfmmsq_qty_price_by_qty_variation_price',
										'no'
									),
								'ajax_async'              => get_option( 'wpfmmsq_false_ajax_async', 'no' ),
								'nonce'                   => wp_create_nonce( 'wpfmmsq_nonce' ),
							)
						);
					}
				}
			}
			// variable Price by qty
			if (
				'yes' === get_option( 'wpfmmsq_qty_price_by_qty_enabled', 'no' ) &&
				'yes' === get_option( 'wpfmmsq_qty_price_by_qty_enabled_variable', 'no' ) &&
				( $_product = wc_get_product( get_the_ID() ) ) && $_product->is_type( 'variable' ) &&
				is_product()
			) {
				wpfmmsq_enqueue_script(
					'wpfmmsq-price-by-qty',
					trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-price-by-qty.js',
					array( 'jquery' ),
					wpfmmsq()->version,
					true
				);
				wp_localize_script(
					'wpfmmsq-price-by-qty',
					'wpfmmsq_price_by_qty_obj',
					array(
						'ajax_url'                => admin_url( 'admin-ajax.php' ),
						'product_id'              => get_the_ID(),
						'position'                => get_option(
							'wpfmmsq_qty_price_by_qty_position',
							'instead'
						),
						'replace_variation_price' => 'yes' === get_option(
								'wpfmmsq_qty_price_by_qty_variation_price',
								'no'
							),
						'ajax_async'              => get_option( 'wpfmmsq_false_ajax_async', 'no' ),
						'nonce'                   => wp_create_nonce( 'wpfmmsq_nonce' ),
					)
				);
			}
			// Force JS step check
			if ( 'yes' === get_option( 'wpfmmsq_step_section_enabled', 'no' ) ) {
				$force_check_step_periodically = (
					'yes' === get_option( 'wpfmmsq_force_js_check_step_periodically', 'no' )
				);
				$force_check_step_on_change    = (
					'yes' === get_option( 'wpfmmsq_force_js_check_step', 'no' )
				);
				if (
					$force_check_step_periodically ||
					$force_check_step_on_change
				) {
					wpfmmsq_enqueue_script(
						'wpfmmsq-force-step-check',
						trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-force-step-check.js',
						array( 'jquery' ),
						wpfmmsq()->version,
						true
					);
					wp_localize_script(
						'wpfmmsq-force-step-check',
						'wpfmmsq_force_step_check_options',
						array(
							'force_check_step_periodically'    => $force_check_step_periodically,
							'force_check_step_on_change'       => $force_check_step_on_change,
							'force_check_step_periodically_ms' => get_option(
								'wpfmmsq_force_js_check_period_ms',
								1000
							),
						)
					);
				}
			}
			// Force JS min/max check
			if (
				'yes' === get_option( 'wpfmmsq_max_section_enabled', 'no' ) ||
				'yes' === get_option( 'wpfmmsq_min_section_enabled', 'no' )
			) {
				$force_check_min_max_periodically = (
					'yes' === get_option( 'wpfmmsq_force_js_check_min_max_periodically', 'no' )
				);
				$force_check_min_max_on_change    = (
					'yes' === get_option( 'wpfmmsq_force_js_check_min_max', 'no' )
				);
				if (
					$force_check_min_max_periodically ||
					$force_check_min_max_on_change
				) {
					wpfmmsq_enqueue_script(
						'wpfmmsq-force-min-max-check',
						trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-force-min-max-check.js',
						array( 'jquery' ),
						wpfmmsq()->version,
						true
					);
					wp_localize_script(
						'wpfmmsq-force-min-max-check',
						'wpfmmsq_force_min_max_check_options',
						array(
							'force_check_min_max_periodically'    => $force_check_min_max_periodically,
							'force_check_min_max_on_change'       => $force_check_min_max_on_change,
							'force_check_min_max_periodically_ms' => get_option(
								'wpfmmsq_force_js_check_period_ms',
								1000
							),
						)
					);
				}
			}
			// Qty rounding
			if ( 'no' != (
				$round_with_js_func = get_option( 'wpfmmsq_round_with_js', 'no' ) )
			) {
				wpfmmsq_enqueue_script(
					'wpfmmsq-force-rounding',
					trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-force-rounding.js',
					array( 'jquery' ),
					wpfmmsq()->version,
					true
				);
				wp_localize_script(
					'wpfmmsq-force-rounding',
					'wpfmmsq_force_rounding_options',
					array(
						'round_with_js_func' => $round_with_js_func,
					)
				);
			}

			if (
				'yes' === get_option( 'wpfmmsq_qty_dropdown', 'no' ) &&
				'yes' == get_option( 'wpfmmsq_qty_dropdown_search_filter', 'no' )
			) {
				wp_register_style(
					'select2-algwc',
					trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/css/wpfmmsq-select2.css',
					false,
					'1.0',
					'all'
				);
				wp_register_script(
					'select2-algwc',
					trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-select2.js',
					array( 'jquery' ),
					'1.0',
					true
				);

				wp_enqueue_style( 'select2-algwc' );
				wpfmmsq_enqueue_script( 'select2-algwc' );
				wp_add_inline_script(
					'select2-algwc',
					'jQuery(function($){$(".select-front-2").select2();});'
				);
			}

			$alg_wc_pq_step_per_item_quantity_allow_all_remaining = get_option(
				'wpfmmsq_step_per_item_quantity_allow_all_remaining',
				'no'
			);
			$alg_wc_pq_step_section_enabled                       = get_option( 'wpfmmsq_step_section_enabled', 'no' );

			// update html5 message
			if (
				( $_product = wc_get_product( get_the_ID() ) ) &&
				( $_product->is_type( 'simple' ) ||
				  $_product->is_type( 'variable' ) ) &&
				is_product()
			) {

				$replaced_values_overflow  = array( '%product_title%' => $_product->get_title() );
				$message_template_overflow = get_option(
					'wpfmmsq_max_per_item_message',
					__(
						'Maximum allowed quantity for %product_title% is %max_per_item_quantity%. Your current item quantity is %item_quantity%.',
						'product-quantity-for-woocommerce'
					)
				);
				$message_template_overflow = do_shortcode( $message_template_overflow );

				$_notice_overflow = html_entity_decode(
					str_replace(
						array_keys( $replaced_values_overflow ),
						array_values( $replaced_values_overflow ),
						$message_template_overflow
					)
				);

				$replaced_values_underflow  = array(
					'%product_title%' => $_product->get_title(),
				);
				$message_template_underflow = get_option(
					'wpfmmsq_min_per_item_message',
					__(
						'Minimum allowed quantity for %product_title% is %min_per_item_quantity%. Your current item quantity is %item_quantity%.',
						'product-quantity-for-woocommerce'
					)
				);
				$message_template_underflow = do_shortcode( $message_template_underflow );

				$_notice_underflow = html_entity_decode(
					str_replace(
						array_keys( $replaced_values_underflow ),
						array_values( $replaced_values_underflow ),
						$message_template_underflow
					)
				);

				wpfmmsq_enqueue_script(
					'wpfmmsq-invalid-html5-browser-msg',
					trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-invalid-html5-browser-msg.js',
					array( 'jquery' ),
					wpfmmsq()->version,
					true
				);
				wp_localize_script(
					'wpfmmsq-invalid-html5-browser-msg',
					'wpfmmsq_invalid_html_five_message',
					array(
						'rangeOverflow'  => $_notice_overflow,
						'rangeUnderflow' => $_notice_underflow,
					)
				);

				if (
					$alg_wc_pq_step_section_enabled == 'yes' &&
					$alg_wc_pq_step_per_item_quantity_allow_all_remaining == 'yes'
				) {

					if ( $_product->is_type( 'simple' ) || $_product->is_type( 'variable' ) ) {
						$data = array();

						if ( $_product->is_type( 'simple' ) ) {
							$product_id     = $_product->get_id();
							$variation_id   = 0;
							$stock_quantity = $_product->get_stock_quantity();
							$min_qty        = wpfmmsq()->core->get_product_qty_min_max(
								$product_id,
								1,
								'min',
								$variation_id
							);
							$max_qty        = wpfmmsq()->core->get_product_qty_min_max(
								$product_id,
								1,
								'max',
								$variation_id
							);
							$step           = wpfmmsq()->core->get_product_qty_step(
								$product_id,
								1,
								$variation_id
							);

							if ( $stock_quantity > $max_qty ) {
								$max_qty = $stock_quantity;
							}

							$data = array(
								'min_qty' => $min_qty,
								'max_qty' => $max_qty,
								'step'    => $step,
							);
						}

						wpfmmsq_enqueue_script(
							'wpfmmsq-quantity-steps',
							trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-quantity-steps.js',
							array( 'jquery' ),
							wpfmmsq()->version,
							true
						);
						wp_localize_script(
							'wpfmmsq-quantity-steps',
							'wpfmmsq_runtime_steps',
							array(
								'data' => $data,
								'page' => 'product'
							)
						);

					}

				}

			}

			if (
				$alg_wc_pq_step_section_enabled == 'yes' &&
				$alg_wc_pq_step_per_item_quantity_allow_all_remaining == 'yes'
			) {

				if ( is_cart() ) {

					$localize_arr         = array();
					$localize_arr['page'] = 'cart';

					foreach ( WC()->cart->get_cart() as $cart_item ) {

						$key          = $cart_item['key'];
						$product_id   = $cart_item['product_id'];
						$variation_id = $cart_item['variation_id'];
						$target_id    = ( $variation_id > 0 ? $variation_id : $product_id );

						$_product = wc_get_product( $target_id );

						if ( ! $_product ) {
							continue;
						}

						$stock_quantity = $_product->get_stock_quantity();
						$min_qty        = wpfmmsq()->core->get_product_qty_min_max(
							$product_id,
							1,
							'min',
							$variation_id
						);
						$max_qty        = wpfmmsq()->core->get_product_qty_min_max(
							$product_id,
							0,
							'max',
							$variation_id
						);
						$step           = (float) wpfmmsq()->core->get_product_qty_step(
							$product_id,
							1,
							$variation_id
						);

						if ( '' !== $stock_quantity && null !== $stock_quantity && $stock_quantity > $max_qty ) {
							$max_qty = $stock_quantity;
						}

						if ( $max_qty > 0 && $step > 0 ) {

							$localize_arr['data'][ $key ]['min_qty'] = $min_qty;
							$localize_arr['data'][ $key ]['max_qty'] = $max_qty;
							$localize_arr['data'][ $key ]['step']    = $step;

						}

					}

					wpfmmsq_enqueue_script(
						'wpfmmsq-quantity-steps',
						trailingslashit( wpfmmsq()->plugin_url() ) . 'includes/js/wpfmmsq-quantity-steps.js',
						array( 'jquery' ),
						wpfmmsq()->version,
						true );
					wp_localize_script(
						'wpfmmsq-quantity-steps',
						'wpfmmsq_runtime_steps',
						$localize_arr
					);
				}

			}

		}

		/**
		 * wpfmmsq_prices_by_variation.
		 *
		 * @version 5.3.4
		 * @since   1.6.1
		 */
		function wpfmmsq_prices_by_variation( $variation_id, $min, $max, $step, $exact_qty ) {
			if ( 'yes' !== get_option( 'wpfmmsq_qty_price_by_qty_enabled', 'no' ) ) {
				return '';
			}
			$product = new WC_Product_Variation( $variation_id );

			$perunit_price = $product->get_price();
			$return_prices = array();
			if ( empty( $exact_qty ) ) {
				if ( $min < $max ) {
					if ( ! empty( $step ) ) {
						foreach ( range( $min, $max, $step ) as $i ) {
							$price                        = round( $perunit_price * $i, 2 );
							$display_price                = wc_price( $price );
							$return_prices[ (string) $i ] = $display_price;
						}
					}
				}
			} else if ( strpos( $exact_qty, ',' ) === false ) {
				$value                            = $exact_qty;
				$price                            = round( $perunit_price * $value, 2 );
				$display_price                    = wc_price( $price );
				$return_prices[ (string) $value ] = $display_price;
			} else {
				$fixed_qty = wpfmmsq()->core->process_exact_qty_option( $exact_qty );
				$values    = $fixed_qty;
				foreach ( $values as $value ) {
					$price                            = round( $perunit_price * $value, 2 );
					$display_price                    = wc_price( $price );
					$return_prices[ (string) $value ] = $display_price;
				}
			}

			return $return_prices;
		}

	}

endif;

return new WPFMMSQ_Scripts();
