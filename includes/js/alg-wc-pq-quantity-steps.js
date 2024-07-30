/**
 * alg-wc-pq-quantity-steps.js
 *
 * @version 4.6.10
 * @since   4.6.10
 * @todo    Step Quanity > Allow adding all quantity in stock (skip step restriction)
 */

jQuery(document).ready(function() {
		
		if(alg_wc_pq_support_runtime_steps.page == 'product') {
			jQuery( '[name="quantity"]' ).on("change", function(event){
				
				
				var step_begining = parseInt(alg_wc_pq_support_runtime_steps.data.step);
				var max_begining = parseInt(alg_wc_pq_support_runtime_steps.data.max_qty);
				var min_begining = parseInt(alg_wc_pq_support_runtime_steps.data.min_qty);
				
				
				
				// var $that = jQuery( '[name="quantity"]' );
				var $that = jQuery( this );
				var cval = $that.val();
				var step = $that.attr('step');
				var max = $that.attr('max');
				var min = $that.attr('min');
				
				var remainder = max_begining % step_begining;
				var max_round_value = max_begining - remainder;
				var max_count = max_round_value / step_begining;
				
				
				
				if( cval == max_round_value ) {
					$that.attr( 'step', remainder );
					$that.attr( 'min', max_round_value );
				}
				
				if( cval == min ){ 
					$that.attr( 'step', step_begining );
					$that.attr( 'min', min_begining );
				}
				
		  });
		}
		
		if( alg_wc_pq_support_runtime_steps.page == 'cart' ) {
			
			var data_loop = alg_wc_pq_support_runtime_steps.data;
			
			for (const key in data_loop) {
				
				var cart_field_name = `cart[${key}][qty]`;
				
				jQuery( '[name="'+cart_field_name+'"]' ).on("change", function(event){
					
					var step_begining = parseInt(data_loop[key].step);
					var max_begining = parseInt(data_loop[key].max_qty);
					var min_begining = parseInt(data_loop[key].min_qty);
					
					
					var $that = jQuery( this );
					var cval = $that.val();
					var step = $that.attr('step');
					var max = $that.attr('max');
					var min = $that.attr('min');
					
					var remainder = max_begining % step_begining;
					var max_round_value = max_begining - remainder;
					var max_count = max_round_value / step_begining;
					
					console.log(cval);
					
					if( cval == max_round_value ) {
						$that.attr( 'step', remainder );
						$that.attr( 'min', max_round_value );
					}
					
					if( cval == min ){ 
						$that.attr( 'step', step_begining );
						$that.attr( 'min', min_begining );
					}
				
				
				});
			}

		}
});