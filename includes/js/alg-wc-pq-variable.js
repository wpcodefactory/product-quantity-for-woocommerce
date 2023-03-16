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
	var quantity_dropdown = jQuery( this ).parent().find( 'select[name=quantity_pq_dropdown]' );
	
	// Step
	var step = parseFloat( product_quantities[ variation_id ][ 'step' ] );
	var default_qty = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'default' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'default' ] ) : 1  );
	var lowest_qty = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'lowest_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'lowest_qty' ] ) : 1  );

	if ( 0 != step ) {
		quantity_input.attr( 'step', step );
	}
	// quantity_input.attr('value',12);
	// Min/max
	var current_qty = quantity_input.val();
	
	if ( quantities_options[ 'reset_to_lowest_fixed' ] ) {
		quantity_input.val( lowest_qty );
	} else if ( quantities_options[ 'reset_to_default' ] ) {
		quantity_input.val( default_qty );
		/*
		if (default_qty > 0) {
			if ( default_qty < parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) ) {
				default_qty = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) : 1  );
				quantity_input.attr( 'value', default_qty );
			} else if ( default_qty > parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) ) {
				default_qty = ( ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) ) && parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) > 0 ) ? parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) : 100  );
				quantity_input.attr( 'value', default_qty );
			} else {
				quantity_input.attr( 'value', default_qty );
			}
		}
		*/
	} else if ( quantities_options[ 'reset_to_min' ] ) {
		// off for variation product
		// quantity_input.val( product_quantities[ variation_id ][ 'min_qty' ] );
	} else if ( quantities_options[ 'reset_to_max' ] ) {
		quantity_input.val( product_quantities[ variation_id ][ 'max_qty' ] );
	} else if ( current_qty < parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) ) {
		quantity_input.val( product_quantities[ variation_id ][ 'min_qty' ] );
	} else if ( current_qty > parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) ) {
		quantity_input.val( product_quantities[ variation_id ][ 'max_qty' ] );
	}


	if ( quantity_dropdown.length > 0 ) {
		change_select_options( variation_id, quantity_dropdown, default_qty );
	}else{
		if(!isNaN (parseFloat( product_quantities[ variation_id ][ 'max_qty' ] )) ) {
			// quantity_input.prop( 'max', product_quantities[ variation_id ][ 'max_qty' ] );
		}
		if(!isNaN (parseFloat( product_quantities[ variation_id ][ 'min_qty' ] )) ) {
			// quantity_input.prop( 'min', product_quantities[ variation_id ][ 'min_qty' ] );
		}
	}
}

/**
 * change_select_options
 *
 * @version 1.7.0
 * @since   1.7.0
 */
function change_select_options( variation_id, quantity_dropdown, default_qty ) {
	
	var step = parseFloat( product_quantities[ variation_id ][ 'step' ] );
	var max_value_fallback = parseFloat( quantities_options[ 'max_value_fallback' ]);
	var max_qty = ( ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) ) && parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) > 0 ) ? parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) : max_value_fallback  );
	var min_qty = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) : 1  );
	var label_singular = product_quantities[ variation_id ][ 'label' ][ 'singular' ];
	var label_plural = product_quantities[ variation_id ][ 'label' ][ 'plural' ];
	var exact_qty = ( ( product_quantities[ variation_id ][ 'exact_qty' ] === null || product_quantities[ variation_id ][ 'exact_qty' ] == null) ? '' : product_quantities[ variation_id ][ 'exact_qty' ] );
	var vprice_by_qty = ( ( product_quantities[ variation_id ][ 'vprice_by_qty' ] === null || product_quantities[ variation_id ][ 'vprice_by_qty' ] == null) ? '' : product_quantities[ variation_id ][ 'vprice_by_qty' ] );


	var n = exact_qty.indexOf(",");
	
	var html = '';
	var selected = '';

	if(exact_qty === ''){
		for (i = min_qty; i <= max_qty; i = i + step) {
			var vl = Math.round((i + Number.EPSILON) * 100) / 100;
			var option_label_txt = ( vl > 1 ? label_plural : label_singular );
			var option_txt = option_label_txt.replace("%qty%", vl);
			var price = '';
			if(vprice_by_qty!==''){
				price = vprice_by_qty[vl];
				option_txt = option_txt.replace("%price%", price);
			}
			selected = (vl===default_qty ? 'selected="selected"' : '' );
			html += '<option value="' + vl + '" ' + selected + '>' + option_txt + '</option>';
		}
	} else if (n === -1) {
		var option_label_txt = ( exact_qty > 1 ? label_plural : label_singular );
		var option_txt = option_label_txt.replace("%qty%", exact_qty);
		var price = '';
		if(vprice_by_qty!==''){
			price = vprice_by_qty[vl];
			option_txt = option_txt.replace("%price%", price);
		}
		selected = 'selected="selected"';
		html += '<option value="' + exact_qty + '" ' + selected + '>' + option_txt + '</option>';
	} else {
		var exact_arr = new Array();
		exact_arr = exact_qty.split(',');
		for (a in exact_arr ) {
			exact_arr[a] = parseFloat( exact_arr[a] );
			var option_label_txt = ( exact_arr[a] > 1 ? label_plural : label_singular );
			var option_txt = option_label_txt.replace("%qty%", exact_arr[a]);
			var price = '';
			if(vprice_by_qty!==''){
				price = vprice_by_qty[vl];
				option_txt = option_txt.replace("%price%", price);
			}
			selected = (exact_arr[a]===default_qty ? 'selected="selected"' : '' );
			html += '<option value="' + exact_arr[a] + '" ' + selected + '>' + option_txt + '</option>';
		}
	}

	var sel_id = quantity_dropdown.attr('id');
	jQuery( "#"+sel_id ).html(html).change();
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
		// jQuery( 'body' ).on( 'change', check_qty_all );
		jQuery( '[name=variation_id]' ).on( 'change', check_qty );
	} else {
		jQuery( '[name=variation_id]' ).on( 'change', check_qty );
	}
	
	jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
		var variation_id = variation.variation_id;
		var quantity_input_var = jQuery( this ).parent().find( '[name=quantity]' );
		var quantity_select_var = jQuery( this ).parent().find( '[name=quantity_pq_dropdown]' );
		var cur_val = quantity_input_var.val();

		if(variation_id > 0 && product_quantities[ variation_id ] !== undefined) {
			
			var quantity_dropdown_var = jQuery( this ).parent().find( 'select[name=quantity_pq_dropdown]' );
			var max_qty_var = ( ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) ) && parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) > 0 ) ? parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) : ''  );
			var min_qty_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) : 1  );
			var default_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'default' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'default' ] ) : 1  );
			var lowest_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'lowest_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'lowest_qty' ] ) : 1  );
			
			if ( quantity_dropdown_var.length <= 0 ) {
				quantity_input_var.prop( 'max', max_qty_var );
				quantity_input_var.prop( 'min', min_qty_var );
				
				
				if ( quantities_options[ 'reset_to_lowest_fixed' ] ) {
					quantity_input_var.val( lowest_var ).change();
				}else if ( quantities_options[ 'reset_to_min' ] ) {
					quantity_input_var.val( min_qty_var ).change();
				} else if ( quantities_options[ 'reset_to_max' ] ) {
					quantity_input_var.val( max_qty_var ).change();
				} else if( default_var > 0 ) {
					quantity_input_var.val(default_var).change();
				}else if(cur_val < min_qty_var ) {
					quantity_input_var.val(min_qty_var).change();
				}
				
				
			}else{
				quantity_dropdown_var.change();
			}
		}else{
			if(quantity_select_var.length > 0){
				get_dropdown_options(variation_id, quantity_select_var);
			}else{
				get_options_input(variation_id, quantity_input_var);
			}
		}
	});
} );

function get_options_input(variation_id, quantity_input_var){
	var data = {
		'action'        : 'alg_wc_pq_update_get_input_options',
		'variation_id' : variation_id
	};
	jQuery.ajax( {
		type   : 'POST',
		url    : woocommerce_params.ajax_url,
		data   : data,
		async  : true,
		dataType : 'json',
		success: function( response ) {
			if (response.min > 0) {
				quantity_input_var.prop( 'min', response.min );
			}
			if (response.max > 0) {
				quantity_input_var.prop( 'max', response.max );
			}
			if (response.step > 0) {
				quantity_input_var.prop( 'step', response.step );
			}
			if (response.default > 0) {
				quantity_input_var.val( response.default );
			}
			quantity_input_var.change();
		},
	} );
}
function get_dropdown_options(variation_id, quantity_select_var){
	var data = {
		'action'        : 'alg_wc_pq_update_get_dropdown_options',
		'variation_id' : variation_id
	};
	jQuery.ajax( {
		type   : 'POST',
		url    : woocommerce_params.ajax_url,
		data   : data,
		async  : true,
		success: function( response ) {
			if (response.length > 0) {
				quantity_select_var.html( response );
				quantity_select_var.change();
			}
		},
	} );
}