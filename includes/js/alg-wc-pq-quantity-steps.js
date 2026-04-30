/**
 * alg-wc-pq-quantity-steps.js
 *
 * @version 5.0.7
 * @since   4.6.10
 * @todo    Step Quanity > Allow adding all quantity in stock (skip step restriction)
 */

jQuery( document ).ready( function () {

	function enforce_non_empty_quantity_value( input ) {
		if ( ! input ) {
			return;
		}

		const parseNumeric = ( value ) => {
			const parsed = parseFloat( value );
			return ( isNaN( parsed ) ? null : parsed );
		};

		let current = parseNumeric( input.value );
		const min = parseNumeric( input.min );
		const max = parseNumeric( input.max );

		if ( null !== current ) {
			if ( null !== min && current < min ) {
				current = min;
			}

			if ( null !== max && current > max ) {
				current = max;
			}

			input.value = current;
			input.dataset.algWcPqLastValid = current;
			return;
		}

		let fallback = parseNumeric( input.dataset.algWcPqLastValid );

		if ( null === fallback && null !== max ) {
			fallback = max;
		}

		if ( null === fallback && null !== min ) {
			fallback = min;
		}

		if ( null !== fallback ) {
			input.value = fallback;
			input.dataset.algWcPqLastValid = fallback;
		}
	}

	function queue_non_empty_enforcement( input ) {
		if ( ! input ) {
			return;
		}

		setTimeout( function() {
			enforce_non_empty_quantity_value( input );
		}, 0 );
	}

	function manage_skip_step_restriction( input ) {
		if ( ! input ) {
			return;
		}

		if ( input.dataset.algWcPqSkipStepBound === 'yes' ) {
			input.dispatchEvent( new CustomEvent( 'alg_wc_pq_refresh_state' ) );
			return;
		}

		input.dataset.algWcPqSkipStepBound = 'yes';
		let lastValue = '';
		let originalMin = input.min;
		let originalMax = input.max;
		let originalStep = input.step;
		let lastPossibleValue = '';
		let lastValidValue = '';

		const getNumericValue = ( value ) => {
			const parsed = parseFloat( value );
			return ( isNaN( parsed ) ? null : parsed );
		};

		const syncOriginalState = ( targetInput ) => {
			if ( targetInput.dataset.skipMode !== 'skip' ) {
				originalMin = targetInput.min;
				originalMax = targetInput.max;
				originalStep = targetInput.step;
			}
		};

		const refreshRuntimeState = ( targetInput ) => {
			targetInput.dataset.skipMode = '';
			lastValue = '';
			lastPossibleValue = '';
			syncOriginalState( targetInput );
			rememberLastValidValue( targetInput );
		};

		const rememberLastValidValue = ( targetInput ) => {
			const numericValue = getNumericValue( targetInput.value );
			if ( null === numericValue ) {
				return;
			}

			const minValue = getNumericValue( targetInput.min !== '' ? targetInput.min : originalMin );
			const maxValue = getNumericValue( targetInput.max !== '' ? targetInput.max : originalMax );

			if ( null !== minValue && numericValue < minValue ) {
				return;
			}

			if ( null !== maxValue && numericValue > maxValue ) {
				return;
			}

			lastValidValue = numericValue;
		};

		const normalizeEmptyValue = ( targetInput ) => {
			const minValue = getNumericValue( targetInput.min !== '' ? targetInput.min : originalMin );
			const maxValue = getNumericValue( targetInput.max !== '' ? targetInput.max : originalMax );
			let currentValue = getNumericValue( targetInput.value );

			rememberLastValidValue( targetInput );

			if ( null === currentValue ) {
				currentValue = getNumericValue( lastValidValue );
			}

			if ( null === currentValue ) {
				currentValue = getNumericValue( lastPossibleValue );
			}

			if ( null === currentValue ) {
				if ( null !== maxValue ) {
					currentValue = maxValue;
				} else if ( null !== minValue ) {
					currentValue = minValue;
				}
			}

			if ( null === currentValue ) {
				return;
			}

			if ( null !== minValue && currentValue < minValue ) {
				currentValue = minValue;
			}

			if ( null !== maxValue && currentValue > maxValue ) {
				currentValue = maxValue;
			}

			targetInput.value = currentValue;
			rememberLastValidValue( targetInput );
			queue_non_empty_enforcement( targetInput );
		};

		syncOriginalState( input );
		rememberLastValidValue( input );

		input.addEventListener( 'beforeinput', ( e ) => {
			let targetInput = e.target;
			syncOriginalState( targetInput );
			rememberLastValidValue( targetInput );

			const maxNumeric = getNumericValue( originalMax );
			if ( null === maxNumeric ) {
				return;
			}

			if ( parseFloat( lastValue ) === parseFloat( e.data ) ) {
				input.dataset.skipMode = 'skip';
				targetInput.step = '';
				targetInput.min = originalMin;
				lastPossibleValue = targetInput.value;
				targetInput.value = maxNumeric;
			} else {
				input.dataset.skipMode = '';
				targetInput.step = originalStep;
				targetInput.min = originalMin;
			}
			if ( 'skip' !== targetInput.dataset.skipMode && getNumericValue( targetInput.value ) === maxNumeric ) {
				input.dataset.skipMode = 'lastPossibleValue';
			}
			lastValue = parseFloat( e.data );
		} );

		input.addEventListener( 'change', ( e ) => {
			let targetInput = e.target;
			syncOriginalState( targetInput );

			if ( 'skip' === targetInput.dataset.skipMode ) {
				const maxNumeric = getNumericValue( originalMax );
				if ( null === maxNumeric ) {
					normalizeEmptyValue( targetInput );
					return;
				}

				targetInput.min = originalMin;
				targetInput.value = maxNumeric;
				lastValue = targetInput.value;
			} else if ( 'lastPossibleValue' === targetInput.dataset.skipMode && lastPossibleValue>0) {
				targetInput.value = lastPossibleValue;
				lastValue = targetInput.value;
			}

			normalizeEmptyValue( targetInput );
			rememberLastValidValue( targetInput );
		} );

		input.addEventListener( 'input', ( e ) => {
			normalizeEmptyValue( e.target );
			rememberLastValidValue( e.target );
			queue_non_empty_enforcement( e.target );
		} );

		input.addEventListener( 'blur', ( e ) => {
			normalizeEmptyValue( e.target );
			rememberLastValidValue( e.target );
			queue_non_empty_enforcement( e.target );
		} );

		input.addEventListener( 'invalid', ( e ) => {
			normalizeEmptyValue( e.target );
			rememberLastValidValue( e.target );
			queue_non_empty_enforcement( e.target );
		} );

		input.addEventListener( 'alg_wc_pq_refresh_state', ( e ) => {
			refreshRuntimeState( e.target );
			normalizeEmptyValue( e.target );
		} );
	}

	function bind_product_quantity_input() {
		const input = document.querySelector( '[name="quantity"]' );
		manage_skip_step_restriction( input );
	}

	if ( alg_wc_pq_support_runtime_steps.page == 'product' ) {
		bind_product_quantity_input();

		jQuery( '.variations_form' ).on( 'show_variation found_variation reset_data hide_variation woocommerce_variation_select_change', function () {
			setTimeout( function () {
				bind_product_quantity_input();
			}, 0 );
		} );
	}

	if ( alg_wc_pq_support_runtime_steps.page == 'cart' ) {
		const data_loop = alg_wc_pq_support_runtime_steps.data;
		for ( const key in data_loop ) {
			var cart_field_name = `cart[${ key }][qty]`;
			const inputs = document.querySelectorAll('[name="' + cart_field_name + '"]');
			inputs.forEach(input => {
				manage_skip_step_restriction(input);
				enforce_non_empty_quantity_value( input );
			});
		}
	}

	jQuery( document ).on( 'input change blur', '[name="quantity"], input.qty, input[id^="quantity_"], input[name$="[qty]"]', function() {
		enforce_non_empty_quantity_value( this );
		queue_non_empty_enforcement( this );
	} );

	jQuery( document ).on( 'click', '.plus, .minus, .quantity-plus, .quantity-minus, .qty-plus, .qty-minus', function() {
		const qtyInput = jQuery( this ).closest( '.quantity, .qty' ).find( 'input.qty, [name="quantity"]' ).get( 0 );
		queue_non_empty_enforcement( qtyInput );
	} );

	setInterval( function() {
		document.querySelectorAll( '[name="quantity"], input.qty, input[id^="quantity_"], input[name$="[qty]"]' ).forEach( function( input ) {
			enforce_non_empty_quantity_value( input );
		} );
	}, 300 );
} );