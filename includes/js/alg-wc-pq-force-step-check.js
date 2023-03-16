/**
 * alg-wc-pq-force-js-check.js
 *
 * @version 1.6.2
 * @since   1.6.2
 * @todo    [dev] (important) (maybe) fire some event (e.g. `change`) on correction (e.g. for `alg-wc-pq-price-by-qty.js`)
 * @todo    [feature] (maybe) add separate option for the cart (same in `alg-wc-pq-force-min-max-check.js`)
 */

function alg_pq_check_qty_step_for_input( qty_input ) {
	if ( qty_input.length ) {
		var attr_step = qty_input.attr( 'step' );
		var value     = qty_input.val();
		if (
			typeof attr_step !== typeof undefined && false !== attr_step && '' !== attr_step &&
			typeof value     !== typeof undefined && false !== value     && '' !== value
		) {
			var multiplier = 1000000;
			value          = parseFloat( value );
			attr_step      = parseFloat( attr_step );
			var attr_min   = qty_input.attr( 'min' );
			if ( typeof attr_min === typeof undefined || false === attr_min || '' === attr_min ) {
				attr_min   = 1;
			}
			attr_min       = parseFloat( attr_min );
			value          = ( ( Math.round( value * multiplier) - Math.round( attr_min  * multiplier) ) / multiplier );
			var reminder   = ( ( Math.round( value * multiplier) % Math.round( attr_step * multiplier) ) / multiplier );
			if ( 0 != reminder ) {
				var step_to_add = 0;
				var next_step   = reminder * 2;
				if ( next_step >= attr_step ) {
					step_to_add = attr_step;
				}
				qty_input.val( ( Math.round( value * multiplier ) + Math.round( step_to_add * multiplier ) - Math.round( reminder * multiplier ) + Math.round( attr_min * multiplier ) ) / multiplier );
			}
		}
	}
}

function alg_pq_check_qty_step_all() {
	jQuery( 'input[id^=quantity_]' ).each( function() { alg_pq_check_qty_step_for_input( jQuery( this ) ) } );
}

if ( force_step_check_options.force_check_step_periodically ) {
	setInterval( alg_pq_check_qty_step_all, force_step_check_options.force_check_step_periodically_ms );
}

if ( force_step_check_options.force_check_step_on_change ) {
	jQuery( document ).on( 'change', 'input[id^=quantity_]', function() { alg_pq_check_qty_step_for_input( jQuery( this ) ) } );
}
