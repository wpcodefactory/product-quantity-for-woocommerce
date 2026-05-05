/**
 * wpfmmsq-force-rounding.js
 *
 * @version 5.3.1
 * @since   1.6.2
 * @todo    [dev] maybe rewrite `switch` with variable function names
 * @todo    [feature] maybe add `precision` option
 * @todo    [feature] optional `on_change` and/or `periodically` (with customizable `period_ms`)
 */

var wpfmmsq_force_round_periodically = true;
var wpfmmsq_force_round_periodically_ms = 1000;
var wpfmmsq_force_round_on_change = true;

function wpfmmsq_force_rounding_for_input( qty_input ) {
	if ( qty_input.length ) {
		var value = qty_input.val();
		if ( typeof value !== typeof undefined && false !== value && '' !== value ) {
			switch ( wpfmmsq_force_rounding_options.round_with_js_func ) {
				case 'round':
					qty_input.val( Math.round( value ) );
					break;
				case 'ceil':
					qty_input.val( Math.ceil( value ) );
					break;
				case 'floor':
					qty_input.val( Math.floor( value ) );
					break;
			}
		}
	}
}

function wpfmmsq_force_rounding_all() {
	jQuery( 'input[id^=quantity_]' ).each( function () {
		wpfmmsq_force_rounding_for_input( jQuery( this ) )
	} );
}

if ( wpfmmsq_force_round_periodically ) {
	setInterval( wpfmmsq_force_rounding_all, wpfmmsq_force_round_periodically_ms );
}

if ( wpfmmsq_force_round_on_change ) {
	jQuery( document ).on( 'change', 'input[id^=quantity_]', function () {
		wpfmmsq_force_rounding_for_input( jQuery( this ) )
	} );
}
