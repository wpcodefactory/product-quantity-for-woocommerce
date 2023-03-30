<?php
/**
 * Product Quantity for WooCommerce - Messenger Class
 *
 * @version 1.7.2
 * @since   1.7.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Messenger' ) ) :

class Alg_WC_PQ_Messenger {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		return true;
	}

	/**
	 * print_message.
	 *
	 * @version 1.7.2
	 * @since   1.0.0
	 * @todo    [dev] step: more replaced values in message (e.g. `%lower_valid_quantity%`, `%higher_valid_quantity%` )
	 * @todo    [feature] customizable notice type on `! $_is_cart` (in checkout must always be `error` though)
	 */
	function print_message( $message_type, $_is_cart, $required_quantity, $total_quantity, $_product_id = 0 ) {
		if ( $_is_cart ) {
			if ( 'no' === get_option( 'alg_wc_pq_cart_notice_enabled', 'no' ) ) {
				return;
			}
		}
		if(!is_numeric($required_quantity) && !in_array($message_type, array('exact_qty_allowed', 'exact_qty_disallowed'))){
			$required_quantity = floatval($required_quantity);
		}
		switch ( $message_type ) {
			case 'step_cart_total_quantity':
				$replaced_values = array(
					'%step_cart_total_quantity%' => $required_quantity,
					'%cart_total_quantity%'      => $total_quantity,
				);
				$message_template = get_option( 'alg_wc_pq_step_cart_total_message',
					__( 'Quantity total cart step is %step_cart_total_quantity%. Your current order quantity is %cart_total_quantity%.', 'product-quantity-for-woocommerce' ) );
				break;
			case 'max_cart_total_quantity':
				$replaced_values = array(
					'%max_cart_total_quantity%' => $required_quantity,
					'%cart_total_quantity%'     => $total_quantity,
				);
				$message_template = get_option( 'alg_wc_pq_max_cart_total_message',
					__( 'Maximum allowed order quantity is %max_cart_total_quantity%. Your current order quantity is %cart_total_quantity%.', 'product-quantity-for-woocommerce' ) );
				break;
			case 'min_cart_total_quantity':
				$replaced_values = array(
					'%min_cart_total_quantity%' => $required_quantity,
					'%cart_total_quantity%'     => $total_quantity,
				);
				$message_template = get_option( 'alg_wc_pq_min_cart_total_message',
					__( 'Minimum allowed order quantity is %min_cart_total_quantity%. Your current order quantity is %cart_total_quantity%.', 'product-quantity-for-woocommerce' ) );
				break;
			case 'max_per_item_quantity':
				$_product = wc_get_product( $_product_id );
				$replaced_values = array(
					'%max_per_item_quantity%' => $required_quantity,
					'%item_quantity%'         => $total_quantity,
					'%product_title%'         => $_product->get_title(),
				);
				$message_template = get_option( 'alg_wc_pq_max_per_item_message',
					__( 'Maximum allowed quantity for %product_title% is %max_per_item_quantity%. Your current item quantity is %item_quantity%.', 'product-quantity-for-woocommerce' ) );
				break;
			case 'min_per_item_quantity':
				$_product = wc_get_product( $_product_id );
				$replaced_values = array(
					'%min_per_item_quantity%' => $required_quantity,
					'%item_quantity%'         => $total_quantity,
					'%product_title%'         => $_product->get_title(),
				);
				$message_template = get_option( 'alg_wc_pq_min_per_item_message',
					__( 'Minimum allowed quantity for %product_title% is %min_per_item_quantity%. Your current item quantity is %item_quantity%.', 'product-quantity-for-woocommerce' ) );
				break;
			case 'step_quantity':
				$_product = wc_get_product( $_product_id );
				$remaining_to_next = ($required_quantity*2 - $total_quantity);
				$next = ($required_quantity*2);
				$replaced_values = array(
					'%quantity_step%'         => $required_quantity,
					'%quantity%'              => $total_quantity,
					'%product_title%'         => $_product->get_title(),
					'%remaining_to_next%'     => $remaining_to_next,
					'%next%'     			  => $next,
				);
				$message_template = get_option( 'alg_wc_pq_step_message',
					__( 'Quantity step for %product_title% is %quantity_step%. Your current quantity is %quantity%.', 'product-quantity-for-woocommerce' ) );
				break;
			case 'exact_qty_allowed':
				$_product = wc_get_product( $_product_id );
				$replaced_values = array(
					'%allowed_quantity%'      => implode( ', ', array_map( 'trim', explode( ',', $required_quantity ) ) ),
					'%quantity%'              => $total_quantity,
					'%product_title%'         => $_product->get_title(),
				);
				$message_template = get_option( 'alg_wc_pq_exact_qty_allowed_message',
					__( 'Allowed quantity for %product_title% is %allowed_quantity%. Your current quantity is %quantity%.', 'product-quantity-for-woocommerce' ) );
				break;
			case 'exact_qty_disallowed':
				$_product = wc_get_product( $_product_id );
				$replaced_values = array(
					'%disallowed_quantity%'   => implode( ', ', array_map( 'trim', explode( ',', $required_quantity ) ) ),
					'%quantity%'              => $total_quantity,
					'%product_title%'         => $_product->get_title(),
				);
				$message_template = get_option( 'alg_wc_pq_exact_qty_disallowed_message',
					__( 'Disallowed quantity for %product_title% is %disallowed_quantity%. Your current quantity is %quantity%.', 'product-quantity-for-woocommerce' ) );
				break;
		}
		
		if(function_exists('pll__')) {
			$message_template = __( pll__( $message_template ) , 'product-quantity-for-woocommerce' );
		} else {
			$message_template = __( apply_filters('widget_title', $message_template) , 'product-quantity-for-woocommerce' );
		}
		
		$message_template = do_shortcode( $message_template );

		$_notice = html_entity_decode(str_replace( array_keys( $replaced_values ), array_values( $replaced_values ), $message_template ));
		if ( $_is_cart ) {
			wc_print_notice( $_notice, get_option( 'alg_wc_pq_cart_notice_type', 'notice' ) );
		} else {
			wc_add_notice( $_notice, 'error' );
		}
	}

}

endif;

return new Alg_WC_PQ_Messenger();
