/**
 * alg-wc-pq-force-min-max-check.js
 *
 * @version 1.6.2
 * @since   1.6.2
 */

function alg_pq_check_qty_min_max_for_input( qty_input ) {
	if ( qty_input.length ) {
		var attr_min = qty_input.attr( 'min' );
		var attr_max = qty_input.attr( 'max' );
		if (        typeof attr_min !== typeof undefined && false !== attr_min && '' !== attr_min && parseFloat( qty_input.val() ) < parseFloat( attr_min ) ) {
			qty_input.val( attr_min );
		} else if ( typeof attr_max !== typeof undefined && false !== attr_max && '' !== attr_max && parseFloat( qty_input.val() ) > parseFloat( attr_max ) ) {
			qty_input.val( attr_max );
		}
	}
}

function alg_pq_check_qty_min_max_all() {
	jQuery( 'input[id^=quantity_]' ).each( function() { alg_pq_check_qty_min_max_for_input( jQuery( this ) ) } );
}

if ( force_min_max_check_options.force_check_min_max_periodically ) {
	setInterval( alg_pq_check_qty_min_max_all, force_min_max_check_options.force_check_min_max_periodically_ms );
}

if ( force_min_max_check_options.force_check_min_max_on_change ) {
	jQuery( document ).on( 'change', 'input[id^=quantity_]', function() { alg_pq_check_qty_min_max_for_input( jQuery( this ) ) } );
}
