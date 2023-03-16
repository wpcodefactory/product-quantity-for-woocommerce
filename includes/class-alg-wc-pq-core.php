<?php
/**
 * Product Quantity for WooCommerce - Core Class
 *
 * @version 1.8.1
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Core' ) ) :

class Alg_WC_PQ_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.8.1
	 * @since   1.0.0
	 * @todo    [fix] mini-cart number of items for decimal qty
	 * @todo    [dev] implement `is_any_section_enabled()`
	 * @todo    [dev] code refactoring: split this into more separate files (e.g. `class-alg-wc-pq-checker.php` etc.)
	 * @todo    [dev] (maybe) pre-load all options (i.e. `init_options()` and `$this->options`)
	 * @todo    [dev] (maybe) bundle products
	 * @todo    [feature] quantity per category (and/or tag) (i.e. not per individual products)
	 * @todo    [feature] implement `force_js_check_exact_qty()`
	 * @todo    [feature] add "treat variable as simple" option
	 * @todo    [feature] quantities by user roles
	 * @todo    [feature] add option to replace product's add to cart form on archives with form from the single product page
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_pq_enabled', 'yes' ) ) {
			// Disable plugin by URL
			if ( '' != ( $urls = get_option( 'alg_wc_pq_disable_urls', '' ) ) ) {
				$urls = array_map( 'trim', explode( PHP_EOL, $urls ) );
				$url  = $_SERVER['REQUEST_URI'];
				if ( in_array( $url, $urls ) ) {
					return;
				}
			}
			// Core
			$this->messenger = require_once( 'class-alg-wc-pq-messenger.php' );
			if (
				'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_exact_qty_allowed_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_exact_qty_disallowed_section_enabled', 'no' )
			) {
				if ( 'yes' === get_option( 'alg_wc_pq_validate_on_checkout', 'yes' ) ) {
					add_action( 'woocommerce_checkout_process',                                array( $this, 'check_order_quantities' ) );
				}
				add_action( 'woocommerce_before_cart',                                         array( $this, 'check_order_quantities' ) );
				if ( 'yes' === get_option( 'alg_wc_pq_stop_from_seeing_checkout', 'no' ) ) {
					add_action( 'wp',                                                          array( $this, 'block_checkout' ), PHP_INT_MAX );
				}
			}
			// Min/max
			if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) || 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
				add_filter( 'woocommerce_available_variation',                                 array( $this, 'set_quantity_input_min_max_variation' ), PHP_INT_MAX, 3 );
				if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
					add_filter( 'woocommerce_quantity_input_min',                              array( $this, 'set_quantity_input_min' ), PHP_INT_MAX, 2 );
				}
				if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
					add_filter( 'woocommerce_quantity_input_max',                              array( $this, 'set_quantity_input_max' ), PHP_INT_MAX, 2 );
				}
				// Force on archives
				if ( 'disabled' != ( $this->force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ) ) ) {
					add_filter( 'woocommerce_loop_add_to_cart_args',                           array( $this, 'force_qty_on_loop' ), PHP_INT_MAX, 2 );
				}
			}
			// Step
			if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
				add_filter( 'woocommerce_quantity_input_step',                                 array( $this, 'set_quantity_input_step' ), PHP_INT_MAX, 2 );
			}
			// Scripts
			require_once( 'class-alg-wc-pq-scripts.php' );
			// For cart & for `input_value`
			add_filter( 'woocommerce_quantity_input_args',                                     array( $this, 'set_quantity_input_args' ), PHP_INT_MAX, 2 );
			// Decimal qty
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
				add_action( 'init',                                                            array( $this, 'float_stock_amount' ), PHP_INT_MAX );
			}
			// Sold individually
			if ( 'yes' === get_option( 'alg_wc_pq_all_sold_individually_enabled', 'no' ) ) {
				add_filter( 'woocommerce_is_sold_individually',                                '__return_true', PHP_INT_MAX );
			}
			// Styling
			if ( '' != get_option( 'alg_wc_pq_qty_input_style', '' ) ) {
				add_action( 'wp_head',                                                         array( $this, 'style_qty_input' ), PHP_INT_MAX );
			}
			// Hide "Update cart" button
			if ( 'yes' === get_option( 'alg_wc_pq_qty_hide_update_cart', 'no' ) ) {
				add_action( 'wp_head',                                                         array( $this, 'hide_update_cart_button' ), PHP_INT_MAX );
			}
			// "Add to cart" validation
			if ( 'notice' === get_option( 'alg_wc_pq_add_to_cart_validation', 'disable' ) ) {
				add_filter( 'woocommerce_add_to_cart_validation',                              array( $this, 'validate_on_add_to_cart' ), PHP_INT_MAX, 3 );
			} elseif ( 'correct' === get_option( 'alg_wc_pq_add_to_cart_validation', 'disable' ) ) {
				add_filter( 'woocommerce_add_to_cart_quantity',                                array( $this, 'correct_on_add_to_cart' ), PHP_INT_MAX, 2 );
			}
			// Qty rounding
			if ( 'no' != ( $this->round_on_add_to_cart = get_option( 'alg_wc_pq_round_on_add_to_cart', 'no' ) ) ) {
				add_filter( 'woocommerce_add_to_cart_quantity',                                array( $this, 'round_on_add_to_cart' ), PHP_INT_MAX, 2 );
			}
			// Dropdown
			if ( 'yes' === get_option( 'alg_wc_pq_qty_dropdown', 'no' ) ) {
				add_filter( 'wc_get_template',                                                 array( $this, 'replace_quantity_input_template' ), PHP_INT_MAX, 5 );
			}
			// Shortcodes
			require_once( 'class-alg-wc-pq-shortcodes.php' );
			// Quantity info
			require_once( 'class-alg-wc-pq-qty-info.php' );
			// Admin columns
			require_once( 'class-alg-wc-pq-admin.php' );
			// Price by Qty
			if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_enabled', 'no' ) ) {
				add_action( 'wp_ajax_'        . 'alg_wc_pq_update_price_by_qty',               array( $this, 'ajax_price_by_qty' ) );
				add_action( 'wp_ajax_nopriv_' . 'alg_wc_pq_update_price_by_qty',               array( $this, 'ajax_price_by_qty' ) );
			}
			// Order item meta
			if ( 'yes' === get_option( 'alg_wc_pq_save_qty_in_order_item_meta', 'no' ) ) {
				add_action( 'woocommerce_new_order_item',                                      array( $this, 'add_qty_to_order_item_meta' ), PHP_INT_MAX, 3 );
			}
		}
	}

	/**
	 * add_qty_to_order_item_meta.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] maybe add option to overwrite the default `_qty` meta (as it's `absint( $order_item['qty'] )`)
	 */
	function add_qty_to_order_item_meta( $item_id, $order_item, $order_id ) {
		wc_add_order_item_meta( $item_id, get_option( 'alg_wc_pq_save_qty_in_order_item_meta_key', '_alg_wc_pq_qty' ), $order_item['qty'] );
	}

	/**
	 * round_on_add_to_cart.
	 *
	 * @version 1.6.2
	 * @since   1.6.2
	 * @todo    [feature] (maybe) add `precision` option
	 */
	function round_on_add_to_cart( $quantity, $product_id ) {
		$func = $this->round_on_add_to_cart;
		return $func( $quantity );
	}

	/**
	 * force_qty_on_loop.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function force_qty_on_loop( $args, $product ) {
		$args['quantity'] = ( 'min' === $this->force_on_loop ?
			$this->set_quantity_input_min( $args['quantity'], $product ) : $this->set_quantity_input_max( $args['quantity'], $product ) );
		return $args;
	}

	/**
	 * replace_quantity_input_template.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function replace_quantity_input_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( 'global/quantity-input.php' === $template_name ) {
			return alg_wc_pq()->plugin_path() . '/includes/templates/global/quantity-input.php';
		}
		return $located;
	}

	/**
	 * get_cart_item_quantities.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 */
	function get_cart_item_quantities( $product_id = 0, $quantity = 0 ) {
		if ( ! isset( WC()->cart ) ) {
			$cart_item_quantities = array();
		} else {
			$cart_item_quantities = WC()->cart->get_cart_item_quantities();
			if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
				$cart_item_quantities = array();
			}
		}
		if ( 0 != $product_id ) {
			if ( ! isset( $cart_item_quantities[ $product_id ] ) ) {
				$cart_item_quantities[ $product_id ] = 0;
			}
			$cart_item_quantities[ $product_id ] += $quantity;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_sum_variations', 'no' ) ) {
			if ( 0 != $product_id && ( $product = wc_get_product( $product_id ) ) ) {
				$children   = $product->get_children();
				$qty_to_add = 0;
				foreach ( $cart_item_quantities as $cart_item_product_id => $cart_item_quantity ) {
					if ( $cart_item_product_id != $product_id && in_array( $cart_item_product_id, $children ) ) {
						$qty_to_add += $cart_item_quantity;
						$cart_item_quantities[ $cart_item_product_id ] = 0;
					}
				}
				$cart_item_quantities[ $product_id ] += $qty_to_add;
			}
		}
		return $cart_item_quantities;
	}

	/**
	 * correct_on_add_to_cart.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [fix] (important) fix "X products have been added to your cart." notice
	 * @todo    [dev] (important) maybe need to add a notice on corrected qty = 0
	 * @todo    [dev] (important) (maybe) "Exact quantities" should be executed first? (same in `validate_on_add_to_cart()`, `block_checkout()` and `check_order_quantities()`)
	 */
	function correct_on_add_to_cart( $quantity, $product_id ) {
		// Prepare data
		$cart_item_quantities = $this->get_cart_item_quantities( $product_id, $quantity );
		$cart_total_quantity  = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		$cart_item_quantity   = $cart_item_quantities[ $product_id ];
		// Min & Max
		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
				// Cart total quantity
				if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
					return ( $this->get_min_max_cart_total_qty( $min_or_max ) - ( $cart_total_quantity - $quantity ) );
				}
				// Per item quantity
				if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
					return $this->get_product_qty_min_max( $product_id, 0, $min_or_max );
				}
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			// Cart total
			if ( $quantity != ( $fixed_qty = $this->check_step_cart_total_qty( $cart_total_quantity, true, $quantity ) ) ) {
				return $fixed_qty;
			}
			// Products
			if ( $quantity != ( $fixed_qty = $this->check_product_step( $product_id, $quantity, true ) ) ) {
				return $fixed_qty;
			}
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				if ( $quantity != ( $fixed_qty = $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, true ) ) ) {
					return $fixed_qty;
				}
			}
		}
		return $quantity;
	}

	/**
	 * validate_on_add_to_cart.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] (maybe) separate messages for min/max (i.e. different from "cart" messages)?
	 */
	function validate_on_add_to_cart( $passed, $product_id, $quantity ) {
		// Prepare data
		if ( ! isset( $cart_item_quantities ) ) {
			$cart_item_quantities = $this->get_cart_item_quantities( $product_id, $quantity );
			$cart_total_quantity  = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
			$cart_item_quantity   = $cart_item_quantities[ $product_id ];
		}
		// Min & Max
		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
				// Cart total quantity
				if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
					$this->messenger->print_message( $min_or_max . '_cart_total_quantity', false, $this->get_min_max_cart_total_qty( $min_or_max ), $cart_total_quantity );
					return false;
				}
				// Per item quantity
				if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
					$this->messenger->print_message( $min_or_max . '_per_item_quantity', false, $this->get_product_qty_min_max( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
					return false;
				}
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			// Cart total
			if ( ! $this->check_step_cart_total_qty( $cart_total_quantity ) ) {
				$this->messenger->print_message( 'step_cart_total_quantity', false, $this->get_step_cart_total_qty(), $cart_total_quantity );
				return false;
			}
			// Products
			if ( ! $this->check_product_step( $product_id, $quantity ) ) {
				$this->messenger->print_message( 'step_quantity', false, $this->get_product_qty_step( $product_id ), $quantity, $product_id );
				return false;
			}
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				if ( ! $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity ) ) {
					$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, false, $this->get_product_exact_qty( $product_id, $allowed_or_disallowed ), $quantity, $product_id );
					return false;
				}
			}
		}
		// Passed
		return $passed;
	}

	/**
	 * style_qty_input.
	 *
	 * @version 1.6.0
	 * @since   1.3.0
	 */
	function style_qty_input() {
		echo '<style>' . 'input.qty,select.qty{' . get_option( 'alg_wc_pq_qty_input_style', '' ) . '}</style>';
	}

	/**
	 * hide_update_cart_button.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] add option to make qty input readonly
	 * @todo    [dev] add option to make "Update cart" button always enabled (i.e. even in case if quantities were not changed)
	 */
	function hide_update_cart_button() {
		echo '<style>' . '.cart button[name="update_cart"] { display: none; }</style>';
	}

	/**
	 * float_stock_amount.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function float_stock_amount() {
		remove_filter( 'woocommerce_stock_amount', 'intval' );
		add_filter(    'woocommerce_stock_amount', 'floatval' );
	}

	/**
	 * set_quantity_input_args.
	 *
	 * @version 1.7.0
	 * @since   1.2.0
	 * @todo    [dev] re-check do we really need to set `step` here?
	 */
	function set_quantity_input_args( $args, $product ) {
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			$args['min_value']   = $this->set_quantity_input_min(  $args['min_value'], $product );
		} elseif ( 'yes' === get_option( 'alg_wc_pq_force_cart_min_enabled', 'no' ) ) {
			$args['min_value']   = 1;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			$args['max_value']   = $this->set_quantity_input_max(  $args['max_value'], $product );
		}
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			$args['step']        = $this->set_quantity_input_step( $args['step'],      $product );
		}
		if ( 'disabled' != ( $force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' ) ) && is_product() ) {
			$args['input_value'] = ( 'min' === $force_on_single ?
				$this->set_quantity_input_min( $args['min_value'], $product ) : $this->set_quantity_input_max( $args['max_value'], $product ) );
		}
		$args['product_id'] = ( $product ? $product->get_id() : 0 );
		return $args;
	}

	/**
	 * get_product_qty_step.
	 *
	 * @version 1.8.0
	 * @since   1.1.0
	 */
	function get_product_qty_step( $product_id, $default_step = 0 ) {
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			if ( 'yes' === apply_filters( 'alg_wc_pq_quantity_step_per_product', 'no' ) && 0 != ( $step_per_product = apply_filters( 'alg_wc_pq_quantity_step_per_product_value', 0, $product_id ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_step', $step_per_product, $product_id );
			} else {
				return apply_filters( 'alg_wc_pq_get_product_qty_step', ( 0 != ( $step_all_products = get_option( 'alg_wc_pq_step', 0 ) ) ? $step_all_products : $default_step ), $product_id );
			}
		} else {
			return $default_step;
		}
	}

	/**
	 * set_quantity_input_step.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function set_quantity_input_step( $step, $_product ) {
		return $this->get_product_qty_step( $this->get_product_id( $_product ), $step );
	}

	/**
	 * ajax_price_by_qty.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 * @todo    [dev] customizable position (instead of the price; after the price, before the price etc.) (NB: maybe do not display for qty=1)
	 * @todo    [dev] add option to disable "price by qty" on initial screen (i.e. before qty input was changed)
	 * @todo    [dev] (maybe) add sale price
	 * @todo    [dev] (maybe) add optional "in progress" message (for slow servers)
	 */
	function ajax_price_by_qty( $param ) {
		if ( isset( $_POST['alg_wc_pq_qty'] ) && '' !== $_POST['alg_wc_pq_qty'] && ! empty( $_POST['alg_wc_pq_id'] ) ) {
			$placeholders = array(
				'%price%'   => wc_price( wc_get_price_to_display( wc_get_product( $_POST['alg_wc_pq_id'] ), array( 'qty' => $_POST['alg_wc_pq_qty'] ) ) ),
				'%qty%'     => $_POST['alg_wc_pq_qty'],
			);
			echo str_replace( array_keys( $placeholders ), $placeholders,
				get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ) ) );
		}
		die();
	}

	/**
	 * get_product_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_product_id( $_product ) {
		if ( ! isset( $this->is_wc_version_below_3 ) ) {
			$this->is_wc_version_below_3 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
		}
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		if ( $this->is_wc_version_below_3 ) {
			return ( isset( $_product->variation_id ) ) ? $_product->variation_id : $_product->id;
		} else {
			return $_product->get_id();
		}
	}

	/**
	 * get_product_qty_min_max.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function get_product_qty_min_max( $product_id, $default, $min_or_max ) {
		if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
			// Check if "Sold individually" is enabled for the product
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_sold_individually() ) {
				return $default;
			}
			// Per product
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_quantity_per_product', 'no', $min_or_max ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_quantity_per_product_value', 'no', $product_id, $min_or_max ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
				}
			}
			// All products
			if ( 0 != ( $value = get_option( 'alg_wc_pq_' . $min_or_max . '_per_item_quantity', 0 ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
			}
		}
		return $default;
	}

	/**
	 * set_quantity_input_min_max_variation.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function set_quantity_input_min_max_variation( $args, $_product, $_variation ) {
		$variation_id = $this->get_product_id( $_variation );
		$args['min_qty'] = $this->get_product_qty_min_max( $variation_id, $args['min_qty'], 'min' );
		$args['max_qty'] = $this->get_product_qty_min_max( $variation_id, $args['max_qty'], 'max' );
		$_max = $_variation->get_max_purchase_quantity();
		if ( -1 != $_max && $args['min_qty'] > $_max ) {
			$args['min_qty'] = $_max;
		}
		if ( -1 != $_max && $args['max_qty'] > $_max ) {
			$args['max_qty'] = $_max;
		}
		if ( $args['min_qty'] < 0 ) {
			$args['min_qty'] = '';
		}
		if ( $args['max_qty'] < 0 ) {
			$args['max_qty'] = '';
		}
		return $args;
	}

	/**
	 * set_quantity_input_min_or_max.
	 *
	 * @version 1.7.0
	 * @since   1.6.0
	 * @todo    [dev] (important) rename this (and probably some other `set_...()` functions)
	 */
	function set_quantity_input_min_or_max( $qty, $_product, $min_or_max ) {
		$value = $this->get_product_qty_min_max( $this->get_product_id( $_product ), $qty, $min_or_max );
		$_max  = $_product->get_max_purchase_quantity();
		return ( -1 == $_max || $value < $_max ? $value : $_max );
	}

	/**
	 * set_quantity_input_min.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function set_quantity_input_min( $qty, $_product ) {
		return $this->set_quantity_input_min_or_max( $qty, $_product, 'min' );
	}

	/**
	 * set_quantity_input_max.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function set_quantity_input_max( $qty, $_product ) {
		return $this->set_quantity_input_min_or_max( $qty, $_product, 'max' );
	}

	/**
	 * block_checkout.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function block_checkout() {
		if ( ! isset( WC()->cart ) ) {
			return;
		}
		if ( ! is_checkout() ) {
			return;
		}
		$cart_item_quantities = WC()->cart->get_cart_item_quantities();
		if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
			return;
		}
		$cart_total_quantity = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		// Max quantity
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			if ( ! $this->check_min_max( 'max', $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Min quantity
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			if ( ! $this->check_min_max( 'min', $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			if ( ! $this->check_step( $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				if ( ! $this->check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, false, true ) ) {
					wp_safe_redirect( wc_get_cart_url() );
					exit;
				}
			}
		}
	}

	/**
	 * check_order_quantities.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [dev] code refactoring min/max (same in `block_checkout()`)
	 */
	function check_order_quantities() {
		if ( ! isset( WC()->cart ) ) {
			return;
		}
		$cart_item_quantities = WC()->cart->get_cart_item_quantities();
		if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
			return;
		}
		$cart_total_quantity = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		$_is_cart = is_cart();
		// Max quantity
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			$this->check_min_max( 'max', $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Min quantity
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			$this->check_min_max( 'min', $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			$this->check_step( $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				$this->check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, $_is_cart, false );
			}
		}
	}

	/**
	 * get_min_max_cart_total_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_min_max_cart_total_qty( $min_or_max ) {
		return get_option( 'alg_wc_pq_' . $min_or_max . '_cart_total_quantity', 0 );
	}

	/**
	 * check_min_max_cart_total_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) {
		if ( 0 != ( $min_or_max_cart_total_quantity = $this->get_min_max_cart_total_qty( $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $cart_total_quantity > $min_or_max_cart_total_quantity ) ||
				( 'min' === $min_or_max && $cart_total_quantity < $min_or_max_cart_total_quantity )
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * check_product_min_max.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_product_min_max( $product_id, $min_or_max, $quantity ) {
		if ( 0 != ( $product_min_max = $this->get_product_qty_min_max( $product_id, 0, $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $quantity > $product_min_max ) ||
				( 'min' === $min_or_max && $quantity < $product_min_max )
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * check_min_max.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function check_min_max( $min_or_max, $cart_item_quantities, $cart_total_quantity, $_is_cart, $_return ) {
		// Cart total quantity
		if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
			if ( $_return ) {
				return false;
			} else {
				$this->messenger->print_message( $min_or_max . '_cart_total_quantity', $_is_cart, $this->get_min_max_cart_total_qty( $min_or_max ), $cart_total_quantity );
			}
		}
		// Per item quantity
		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
			if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( $min_or_max . '_per_item_quantity', $_is_cart, $this->get_product_qty_min_max( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
				}
			}
		}
		// Passed
		if ( $_return ) {
			return true;
		}
	}

	/**
	 * get_product_exact_qty.
	 *
	 * @version 1.8.0
	 * @since   1.5.0
	 * @todo    [feature] (maybe) total qty of item in cart
	 * @todo    [feature] (maybe) total items in cart
	 */
	function get_product_exact_qty( $product_id, $allowed_or_disallowed, $default_exact_qty = '' ) {
		if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
			if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_value', '', $product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else {
				return ( '' !== ( $exact_qty_all_products = get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed, '' ) ) ? $exact_qty_all_products : $default_exact_qty );
			}
		} else {
			return $default_exact_qty;
		}
	}

	/**
	 * process_exact_qty_option.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] (important) qty range in `print_message()`
	 * @todo    [dev] qty range: power of X (i.e. instead of adding range step)
	 */
	function process_exact_qty_option( $qty_option ) {
		$_qty = array_map( 'trim', explode( ',', $qty_option ) );
		$qty  = array();
		foreach ( $_qty as $value ) {
			if ( false !== strpos( $value, '[' ) ) {
				if ( 0 === strpos( $value, '[' ) && ( ( strlen( $value ) - 1 ) == strpos( $value, ']' ) ) ) {
					$value = substr( $value, 1, ( strlen( $value ) - 2 ) );
					$value = array_map( 'trim', explode( '|', $value ) );
					if ( 2 === count( $value ) ) {
						$range = explode( '-', $value[0] );
						if ( 2 === count( $range ) ) {
							for ( $i = $range[0]; $i <= $range[1]; $i += $value[1] ) {
								$qty[] = $i;
							}
						} // else skipping the value (wrong format)
					} // else skipping the value (wrong format)
				} // else skipping the value (wrong format)
			} else {
				$qty[] = $value;
			}
		}
		return $qty;
	}

	/**
	 * check_product_exact_qty.
	 *
	 * @version 1.7.0
	 * @since   1.5.0
	 * @todo    [dev] (important) rethink qty correction on `disallowed`
	 * @todo    [dev] (important) check if all `$product_exact_qty` elements are `is_numeric()`
	 * @todo    [dev] (important) check if possible float and int comparison works properly in `abs( $quantity - $closest ) > abs( $item - $quantity )`
	 */
	function check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, $do_fix = false ) {
		$product_exact_qty = $this->get_product_exact_qty( $product_id, $allowed_or_disallowed );
		if ( '' != $product_exact_qty ) {
			$product_exact_qty = $this->process_exact_qty_option( $product_exact_qty );
			sort( $product_exact_qty );
			$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $cart_item_quantity, $product_exact_qty ) : ! in_array( $cart_item_quantity, $product_exact_qty ) );
			if ( ! $do_fix ) {
				return $is_valid;
			} elseif ( ! $is_valid ) {
				if ( 'allowed' === $allowed_or_disallowed ) {
					$closest = null;
					foreach ( $product_exact_qty as $item ) {
						if ( $closest === null || abs( $cart_item_quantity - $closest ) > abs( $item - $cart_item_quantity ) ) {
							$closest = $item;
						}
					}
					return ( null !== $closest ? ( $closest - ( $cart_item_quantity - $quantity ) ) : $quantity );
				} else { // 'disallowed'
					$_cart_item_quantity = $cart_item_quantity;
					while ( true ) {
						$_cart_item_quantity++;
						if ( ! in_array( $_cart_item_quantity, $product_exact_qty ) ) {
							return ( $_cart_item_quantity - ( $cart_item_quantity - $quantity ) );
						}
					}
				}
			}
		}
		return ( ! $do_fix ? true : $quantity );
	}

	/**
	 * check_exact_qty.
	 *
	 * @version 1.7.0
	 * @since   1.5.0
	 */
	function check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, $_is_cart, $_return ) {
		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
			if ( ! $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $cart_item_quantity, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, $_is_cart, $this->get_product_exact_qty( $product_id, $allowed_or_disallowed ), $cart_item_quantity, $product_id );
				}
			}
		}
		// Passed
		if ( $_return ) {
			return true;
		}
	}

	/**
	 * check_product_step.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] `$multiplier` should be calculated automatically according to the `$qty_step_settings` value (same in `force_js_check_step()`)
	 */
	function check_product_step( $product_id, $quantity, $do_fix = false ) {
		$product_qty_step = $this->get_product_qty_step( $product_id );
		if ( 0 != $product_qty_step ) {
			$min_value = $this->get_product_qty_min_max( $product_id, 0, 'min' );
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
				$multiplier         = floatval( 1000000 );
				$_min_value         = intval( round( floatval( $min_value )        * $multiplier ) );
				$_quantity          = intval( round( floatval( $quantity )         * $multiplier ) );
				$_product_qty_step  = intval( round( floatval( $product_qty_step ) * $multiplier ) );
			} else {
				$_min_value         = $min_value;
				$_quantity          = $quantity;
				$_product_qty_step  = $product_qty_step;
			}
			$_quantity = $_quantity - $_min_value;
			$_reminder = $_quantity % $_product_qty_step;
			$is_valid  = ( 0 == $_reminder );
			if ( ! $do_fix ) {
				return $is_valid;
			} elseif ( ! $is_valid ) {
				$step_auto_correct = get_option( 'alg_wc_pq_add_to_cart_validation_step_auto_correct', 'round' );
				$extra_qty = ( 'round_down' != $step_auto_correct && ( 'round_up' == $step_auto_correct || $_reminder * 2 >= $_product_qty_step ) ?
					$_product_qty_step : 0 );
				$quantity = $_quantity + $extra_qty - $_reminder + $_min_value;
				if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
					$quantity = $quantity / $multiplier;
				}
				return $quantity;
			}
		}
		return ( ! $do_fix ? true : $quantity );
	}

	/**
	 * get_step_cart_total_qty.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function get_step_cart_total_qty() {
		return get_option( 'alg_wc_pq_step_cart_total_quantity', 0 );
	}

	/**
	 * check_step_cart_total_qty.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] (important) (maybe) code refactoring (merge with `check_product_step()`)
	 */
	function check_step_cart_total_qty( $cart_total_quantity, $do_fix = false, $product_qty = 0 ) {
		if ( 0 != ( $step_cart_total_quantity = $this->get_step_cart_total_qty() ) ) {
			$is_decimal = ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) );
			if ( $is_decimal ) {
				$multiplier                 = floatval( 1000000 );
				$_cart_total_quantity       = intval( round( floatval( $cart_total_quantity )      * $multiplier ) );
				$_step_cart_total_quantity  = intval( round( floatval( $step_cart_total_quantity ) * $multiplier ) );
			} else {
				$_cart_total_quantity       = $cart_total_quantity;
				$_step_cart_total_quantity  = $step_cart_total_quantity;
			}
			$_reminder = $_cart_total_quantity % $_step_cart_total_quantity;
			$is_valid  = ( 0 == $_reminder );
			if ( ! $do_fix ) {
				return $is_valid;
			} elseif ( ! $is_valid ) {
				if ( $is_decimal ) {
					$product_qty = intval( round( floatval( $product_qty ) * $multiplier ) );
				}
				$step_auto_correct = get_option( 'alg_wc_pq_add_to_cart_validation_step_auto_correct', 'round' );
				$extra_qty = ( 'round_down' != $step_auto_correct && ( 'round_up' == $step_auto_correct || $_reminder * 2 >= $_step_cart_total_quantity ) ?
					$_step_cart_total_quantity : 0 );
				$product_qty = $product_qty + $extra_qty - $_reminder;
				if ( $is_decimal ) {
					$product_qty = $product_qty / $multiplier;
				}
				return $product_qty;
			}
		}
		return ( ! $do_fix ? true : $product_qty );
	}

	/**
	 * check_step.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] (maybe) force `min` in cart to `1` (as it may be zero now)
	 */
	function check_step( $cart_item_quantities, $cart_total_quantity, $_is_cart, $_return ) {
		// Cart total quantity
		if ( ! $this->check_step_cart_total_qty( $cart_total_quantity ) ) {
			if ( $_return ) {
				return false;
			} else {
				$this->messenger->print_message( 'step_cart_total_quantity', $_is_cart, $this->get_step_cart_total_qty(), $cart_total_quantity );
			}
		}
		// Per item step
		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
			if ( ! $this->check_product_step( $product_id, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( 'step_quantity', $_is_cart, $this->get_product_qty_step( $product_id ), $cart_item_quantity, $product_id );
				}
			}
		}
		// Passed
		if ( $_return ) {
			return true;
		}
	}

}

endif;

return new Alg_WC_PQ_Core();
