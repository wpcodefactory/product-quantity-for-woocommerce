/**
 * alg-wc-pq-price-by-qty.js
 *
 * @version 1.7.3
 * @since   1.6.1
 */

function alg_wc_pq_update_price_by_qty( e, qty = null ) {
	var data = {
		'action'        : 'alg_wc_pq_update_price_by_qty',
		'alg_wc_pq_qty' : ( null !== qty ? qty : jQuery( this ).val() ),
		'alg_wc_pq_id'  : alg_wc_pq_update_price_by_qty_object.product_id,
	};
	jQuery.ajax( {
		type   : 'POST',
		url    : alg_wc_pq_update_price_by_qty_object.ajax_url,
		data   : data,
		success: function( response ) {
			if ( 'instead' == alg_wc_pq_update_price_by_qty_object.position ) {
				jQuery( 'p.price' ).html( response );
			} else {
				jQuery( 'p.alg-wc-pq-price-display-by-qty' ).html( response );
			}
		},
	} );
}

jQuery( document ).ready( function() {
	if ( 'instead' != alg_wc_pq_update_price_by_qty_object.position ) {
		var price_display_by_qty_element = '<p class="alg-wc-pq-price-display-by-qty"></p>';
		switch ( alg_wc_pq_update_price_by_qty_object.position ) {
			case 'before':
				jQuery( 'p.price' ).before( price_display_by_qty_element );
				break;
			case 'after':
				jQuery( 'p.price' ).after( price_display_by_qty_element );
				break;
		}
	}
	var $qty_val = jQuery( '[name="quantity"]' ).val();
	if ( 'undefined' !== typeof $qty_val ) {
		alg_wc_pq_update_price_by_qty( false, $qty_val );
	}
	jQuery( '[name="quantity"]' ).on( 'input change', alg_wc_pq_update_price_by_qty );
} );
