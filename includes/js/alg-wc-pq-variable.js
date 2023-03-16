/**
 * alg-wc-pq-variable.js
 *
 * @version 1.7.0
 * @since   1.0.0
 */

/**
 * check_qty
 *
 * @version 1.7.0
 * @since   1.0.0
 * @todo    [dev] (maybe) `jQuery( '[name=quantity]' ).val( '0' )` on `jQuery.isEmptyObject( product_quantities[ variation_id ] )` (i.e. instead of `return`)
 */
function check_qty() {
	var variation_id = jQuery( this ).val();
	if ( 0 == variation_id || jQuery.isEmptyObject( product_quantities[ variation_id ] ) ) {
		return;
	}
	var quantity_input = jQuery( this ).parent().find( '[name=quantity]' );
	// Step
	var step = parseFloat( product_quantities[ variation_id ][ 'step' ] );
	if ( 0 != step ) {
		quantity_input.attr( 'step', step );
	}
	// Min/max
	var current_qty = quantity_input.val();
	if ( quantities_options[ 'reset_to_min' ] ) {
		quantity_input.val( product_quantities[ variation_id ][ 'min_qty' ] );
	} else if ( quantities_options[ 'reset_to_max' ] ) {
		quantity_input.val( product_quantities[ variation_id ][ 'max_qty' ] );
	} else if ( current_qty < parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) ) {
		quantity_input.val( product_quantities[ variation_id ][ 'min_qty' ] );
	} else if ( current_qty > parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) ) {
		quantity_input.val( product_quantities[ variation_id ][ 'max_qty' ] );
	}
}

/**
 * check_qty_all
 *
 * @version 1.7.0
 * @since   1.7.0
 */
function check_qty_all() {
	jQuery( '[name=variation_id]' ).each( check_qty );
}

/**
 * document ready
 *
 * @version 1.7.0
 * @since   1.0.0
 */
jQuery( document ).ready( function() {
	if ( quantities_options[ 'do_load_all_variations' ] ) {
		jQuery( 'body' ).on( 'change', check_qty_all );
	} else {
		jQuery( '[name=variation_id]' ).on( 'change', check_qty );
	}
} );
