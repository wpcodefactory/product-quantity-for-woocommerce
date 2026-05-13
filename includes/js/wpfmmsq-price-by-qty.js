/**
 * wpfmmsq-price-by-qty.js
 *
 * @version 5.3.4
 * @since   1.6.1
 */
var wpfmmsq_fresh_nonce = null;
var wpfmmsq_nonce_request = null;

function wpfmmsq_get_fresh_nonce( callback ) {
	if ( wpfmmsq_fresh_nonce !== null ) {
		callback( wpfmmsq_fresh_nonce );
		return;
	}

	if ( wpfmmsq_nonce_request !== null ) {
		wpfmmsq_nonce_request.done( callback );
		return;
	}

	wpfmmsq_nonce_request = jQuery.ajax( {
		url: wpfmmsq_price_by_qty_obj.ajax_url,
		type: 'POST',
		data: {
			action: 'wpfmmsq_refresh_nonce',
			nonce: wpfmmsq_price_by_qty_obj.nonce,
		},
		success: function ( response ) {
			if ( response.success ) {
				wpfmmsq_fresh_nonce = response.data.nonce;
				wpfmmsq_nonce_request = null;
				callback( wpfmmsq_fresh_nonce );
			}
		}
	} );
}

function wpfmmsq_update_price_by_qty( e, qty = null, attribute = null ) {
	var selected_val = 0;
	// if in archive page
	var product_id = wpfmmsq_price_by_qty_obj.product_id;
	if ( product_id == 0 ) {
		if ( e.type == 'change' ) {
			product_id = jQuery( this )
				.closest( 'form' )
				.find( 'button.add_to_cart_button' )
				.attr( 'data-product_id' );
		}
	}
	if ( jQuery( this ).hasClass( "qty" ) ) {
		selected_val = jQuery( '.variations select option:selected' ).val();
	} else {
		if ( e.type == 'change' ) {
			selected_val = jQuery( this ).val();
		} else {
			selected_val = jQuery( '.variations select option:selected' ).val();
		}
	}

	var quantity_fetch = jQuery( '.qty' ).val();
	// if in archive page
	if ( wpfmmsq_price_by_qty_obj.product_id == 0 ) {
		if ( e.type == 'change' ) {
			quantity_fetch = jQuery( this ).val();
		}
	}
	var attribute_fetch = wpfmmsq_get_attributes();
	var data = {
		'action': 'wpfmmsq_update_price_by_qty',
		'wpfmmsq_qty': (
			null !== qty ? qty : quantity_fetch
		),
		'wpfmmsq_id': product_id,
		'selected_val': selected_val,
		'quantity_fetch': quantity_fetch,
		'attribute': (
			null !== attribute ? attribute : attribute_fetch
		)
	};

	var ajax_async = (
		wpfmmsq_price_by_qty_obj.ajax_async == 'yes' ? false : true
	);

	wpfmmsq_get_fresh_nonce( function ( fresh_nonce ) {
		data.nonce = fresh_nonce;
		jQuery.ajax( {
			type: 'POST',
			url: wpfmmsq_price_by_qty_obj.ajax_url,
			data: data,
			async: ajax_async,
			success: function ( response ) {
				if ( wpfmmsq_price_by_qty_obj.product_id == 0 ) {
					if ( response.length > 0 ) {
						if ( 'instead' == wpfmmsq_price_by_qty_obj.position ) {
							jQuery( '.product.post-' + product_id ).find( '.price' ).html( response );
						} else {
							jQuery( '.product.post-' + product_id ).find( 'p.wpfmmsq-price-display-by-qty' ).html( response );
						}
					}
				} else {
					if ( 'instead' == wpfmmsq_price_by_qty_obj.position ) {
						if ( response.length > 0 ) {
							jQuery( 'p.price' ).html( response );
							if ( wpfmmsq_price_by_qty_obj.replace_variation_price ) {
								jQuery( '.woocommerce-variation-price .price' ).html( response );
							}
						}
					} else {
						jQuery( 'p.wpfmmsq-price-display-by-qty' ).html( response );
					}
				}
			},
		} );
	} );
}

jQuery( document ).ready( function () {
	if (
		'instead' != wpfmmsq_price_by_qty_obj.position &&
		parseInt( wpfmmsq_price_by_qty_obj.product_id ) != 0
	) {
		var price_display_by_qty_element = '<p class="wpfmmsq-price-display-by-qty"></p>';
		switch ( wpfmmsq_price_by_qty_obj.position ) {
			case 'before':
				jQuery( 'p.price' ).before( price_display_by_qty_element );
				break;
			case 'after':
				jQuery( 'p.price' ).after( price_display_by_qty_element );
				break;
			case 'before_add_to_cart':
				jQuery( 'button.single_add_to_cart_button' )
					.closest( 'form' )
					.before( price_display_by_qty_element );
				break;
			case 'after_add_to_cart':
				jQuery( 'button.single_add_to_cart_button' )
					.closest( 'form' )
					.after( price_display_by_qty_element );
				break;
		}
	}

	var $attribute = '';
	var attribute = jQuery( 'select' ).data( 'attribute_name' );
	if ( 'undefined' !== typeof attribute ) {
		$attribute = wpfmmsq_get_attributes();
	}

	jQuery( '[name="quantity"]' )
		.not( ".disable_price_by_qty" )
		.on( 'change', wpfmmsq_update_price_by_qty );

	var $el = jQuery( '[name="quantity"]' );
	var val = +$el.val();
	if ( $el.attr( 'min' ) !== undefined ) {
		val = Math.max( val, +$el.attr( 'min' ) );
	}
	if ( $el.attr( 'max' ) !== undefined ) {
		val = Math.min( val, +$el.attr( 'max' ) );
	}
	var $qty_val = val;

	if ( 'undefined' !== typeof $qty_val ) {
		wpfmmsq_update_price_by_qty( false, $qty_val, $attribute );
	}

} );

function wpfmmsq_get_attributes() {
	var jsonObj = [];
	jQuery( 'select' ).each( function () {
		var attribute_name = jQuery( this ).data( 'attribute_name' );
		var attribute_value = jQuery( this ).val();
		if ( 'undefined' !== typeof attribute_name && 'undefined' !== typeof attribute_value ) {
			item = {};
			item [ attribute_name ] = attribute_value;
			jsonObj.push( item );
		}
	} );
	return JSON.stringify( jsonObj );
}
