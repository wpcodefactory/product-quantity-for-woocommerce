<?php
/**
 * Product quantity inputs // Drop down by WPFactory
 *
 * @version 7.8.0
 * @since   1.6.0
 * @todo    [dev] (important) re-check new template in WC 3.6
 * @todo    [dev] (important) dropdown: variable products (check "validate on add to cart") (i.e. without fallbacks)
 * @todo    [dev] (important) dropdown: maybe fallback min & step?
 * @todo    [dev] (important) dropdown: "exact (i.e. fixed)" quantity: variable products
 * @todo    [dev] (important) dropdown: "exact (i.e. fixed)" quantity: add zero in cart?
 * @todo    [dev] (maybe) dropdown: labels: per variation
 *
 * wc_version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $product_id ) ) {
	$product = wc_get_product( $product_id );
} elseif ( isset( $GLOBALS['product'] ) ) {
	$product = $GLOBALS['product'];
} else {
	$product = false;
}

$disable_price_by_qty = '';
$disable_price_by_qty_str = $classes;
if(is_string($disable_price_by_qty_str)){
	$pos = strrpos($disable_price_by_qty_str, 'disable_price_by_qty');
	if($pos !== false) {
		$disable_price_by_qty = 'disable_price_by_qty';
	}
}
$variation_id = 0;
$variation_max = 0;
$variation_exact = '';
$productType =  $product->get_type();
$is_dropdown_disable = alg_wc_pq()->core->alg_wc_pq_qty_dropdown_is_disable( $product );
if( $productType == 'variable' &&  'yes' === get_option( 'alg_wc_pq_variation_do_load_all', 'no' ) ) {
	if ( $_product = wc_get_product( $product_id ) ) {

		foreach ( $_product->get_available_variations() as $variation ) {
			$variation_id = $variation['variation_id'];
			if( $variation_max <= 0 ) {
				$variation_max = alg_wc_pq()->core->get_product_qty_min_max (  $_product->get_id(), (float) $variation['max_qty'], 'max', $variation_id );
			}
			if( empty( $variation_exact) ) {
				$variation_exact = alg_wc_pq()->core->get_product_exact_qty( $_product->get_id(), 'allowed', '', $variation_id );
			}
		}
	}
}else if( $productType == 'variation' ){
	$variation_id = $product->get_variation_id();
	$parent_id = $product->get_parent_id();
	$_product = wc_get_product( $parent_id );
	$is_dropdown_disable = alg_wc_pq()->core->alg_wc_pq_qty_dropdown_is_disable( $_product );

	if( $variation_max <= 0 ) {
		if(is_cart()){
			$variation_max = alg_wc_pq()->core->get_product_qty_min_max (  $_product->get_id(), 0, 'max', $variation_id );
		}else{
			$variation_max = alg_wc_pq()->core->get_product_qty_min_max (  $_product->get_id(), (float) $product->get_max_purchase_quantity(), 'max', $variation_id );
		}
	}
	if( empty( $variation_exact) ) {
		$variation_exact = alg_wc_pq()->core->get_product_exact_qty( $_product->get_id(), 'allowed', '', $variation_id );
	}
	$product = $_product;
}

$alg_wc_max = alg_wc_pq()->core->set_quantity_input_max(  0, $product );

$max_purchase_quantity = $product->get_max_purchase_quantity();

if ( $max_value && $min_value === $max_value ) {
	?>
	<div class="quantity hidden">
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty <?php echo $disable_price_by_qty; ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
	</div>
	<?php
} elseif ( ( ( ! empty( $alg_wc_max ) || 0 != ( $max_value_fallback = get_option( 'alg_wc_pq_qty_dropdown_max_value_fallback', 0 ) ) ) && ! empty( $step ) && !$is_dropdown_disable ) ||  ( ($productType=='variable' || $productType=='variation') && ( $variation_max > 0 || ! empty($variation_exact) ) && !$is_dropdown_disable  ) ) { // dropdown
	if ( empty( $max_value ) ) {
		$max_value = $max_value_fallback;
	}
	?>
	<div class="quantity dropdown_pq first">
		<?php echo do_shortcode( get_option( 'alg_wc_pq_qty_dropdown_template_before', '' ) ); ?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></label>
		<select
			id="<?php echo esc_attr( $input_id ); ?>_pq_dropdown"
			class="qty ajax-ready select-front-2 <?php echo $disable_price_by_qty; ?>"
			name="<?php echo esc_attr( $input_name ); ?>_pq_dropdown"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>" >
			<?php
			// Values
			$values = array();
			/*for ( $i = $min_value; $i <= $max_value; $i = $i + $step ) {*/
			$range = range($min_value, $max_value, $step);
			foreach ($range as $i) {
				$values[] = (float) round($i, 7);
			}
			
			$end = end($range);
			if(!empty($end)){
				$last = (float) $end + (float) $step;
				if((string) $last <= (string) $max_value){
					$values[] = (float) round($last, 7);
				}
			}
			
			if ( ! empty( $input_value ) && ! in_array( $input_value, $values ) && $input_value > $min_value && is_cart()) {
				$values[] = (float) round($input_value, 7);
			}
			
			
			// Fixed qty
			foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
				
				if ( $product && '' != ( $fixed_qty = alg_wc_pq()->core->get_product_exact_qty( $product->get_id(), $allowed_or_disallowed, '', $variation_id ) ) ) {
					
					$fixed_qty = alg_wc_pq()->core->process_exact_qty_option( $fixed_qty );
					$values = array_unique(array_merge($values, $fixed_qty));
					$values = array_values($values);
					
					foreach ( $values as $i => $value ) {
						if (
							( 'allowed'    === $allowed_or_disallowed && ! in_array( $value, $fixed_qty ) ) ||
							( 'disallowed' === $allowed_or_disallowed &&   in_array( $value, $fixed_qty ) )
						) {
							unset( $values[ $i ] );
						}
					}

				}
			}
			

			// Labels
			$label_template_singular = '';
			$label_template_plural   = '';
			if ( $product && 'yes' === get_option( 'alg_wc_pq_qty_dropdown_label_template_is_per_product', 'no' ) ) {
				$product_or_parent_id    = ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
				$label_template_singular = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_singular', true );
				$label_template_plural   = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_plural',   true );
			}
			if ( '' === $label_template_singular ) {
				$label_template_singular = do_shortcode(get_option( 'alg_wc_pq_qty_dropdown_label_template_singular', '%qty%' ));
			}
			if ( '' === $label_template_plural ) {
				$label_template_plural   = do_shortcode(get_option( 'alg_wc_pq_qty_dropdown_label_template_plural',   '%qty%' ));
			}
			
			// Select options
			foreach ( $values as $value ) {
				if($max_purchase_quantity >= $value || $max_purchase_quantity < 0){
					$price = wc_get_price_to_display( $product , array( 'qty' => $value ) );
					$display_price = wc_price( $price );
					?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $input_value ); ?>><?php
						echo str_replace( array('%qty%','%price%'), array(alg_wc_pq()->core->get_quantity_with_sep( $value ), $display_price), ( $value < 2 ? $label_template_singular : $label_template_plural ) ); ?></option><?php
				}
			}
			?>
		</select>
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $input_name ); ?>" class="qty ajax-ready <?php echo $disable_price_by_qty; ?>" value="<?php echo esc_attr( $input_value ); ?>">
		<?php echo do_shortcode( get_option( 'alg_wc_pq_qty_dropdown_template_after', '' ) ); ?>
	</div>
	<?php
}else if( 'yes' === get_option( 'alg_wc_pq_exact_qty_allowed_section_enabled', 'no' ) && '' != ( $fixed_qty = alg_wc_pq()->core->get_product_exact_qty( $product->get_id(), 'allowed' ) ) && !$is_dropdown_disable ){ 
	?>
	<div class="quantity dropdown_pq second">
		<?php echo do_shortcode( get_option( 'alg_wc_pq_qty_dropdown_template_before', '' ) ); ?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></label>
		<select
			id="<?php echo esc_attr( $input_id ); ?>_pq_dropdown"
			class="qty ajax-ready select-front-2 <?php echo $disable_price_by_qty; ?>"
			name="<?php echo esc_attr( $input_name ); ?>_pq_dropdown"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>" >
			<?php
			// Values
			$fixed_qty = alg_wc_pq()->core->process_exact_qty_option( $fixed_qty );
			$values = $fixed_qty;
			
			/*
			if ( ! empty( $input_value ) && ! in_array( $input_value, $values ) && $input_value > $min_value && is_cart()) {
				$values[] = $input_value;
			}
			*/
			
			asort( $values );
			// Fixed qty
			foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
				if ( $product && '' != ( $fixed_qty = alg_wc_pq()->core->get_product_exact_qty( $product->get_id(), $allowed_or_disallowed, '', $variation_id ) ) ) {
					$fixed_qty = alg_wc_pq()->core->process_exact_qty_option( $fixed_qty );
					foreach ( $values as $i => $value ) {
						if (
							( 'allowed'    === $allowed_or_disallowed && ! in_array( $value, $fixed_qty ) ) ||
							( 'disallowed' === $allowed_or_disallowed &&   in_array( $value, $fixed_qty ) )
						) {
							unset( $values[ $i ] );
						}
					}
				}
			}

			if ( ! empty( $input_value ) && ! in_array( $input_value, $values ) && $input_value > $min_value && is_cart()) {
				$values[] = $input_value;
			}
			
			asort( $values );

			// Labels
			$label_template_singular = '';
			$label_template_plural   = '';
			if ( $product && 'yes' === get_option( 'alg_wc_pq_qty_dropdown_label_template_is_per_product', 'no' ) ) {
				$product_or_parent_id    = ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
				$label_template_singular = do_shortcode(get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_singular', true ));
				$label_template_plural   = do_shortcode(get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_plural',   true ));
			}
			if ( '' === $label_template_singular ) {
				$label_template_singular = do_shortcode(get_option( 'alg_wc_pq_qty_dropdown_label_template_singular', '%qty%' ));
			}
			if ( '' === $label_template_plural ) {
				$label_template_plural   = do_shortcode(get_option( 'alg_wc_pq_qty_dropdown_label_template_plural',   '%qty%' ));
			}
			
			if(is_product()){
				$default_quantity = alg_wc_pq()->core->get_product_qty_default( $product->get_id(), 'no' );
				if($default_quantity != 'no'){
					$input_value = $default_quantity;
				}
			}
			
			// Select options
			foreach ( $values as $value ) {
				if($max_purchase_quantity >= $value || $max_purchase_quantity < 0){
					$price = wc_get_price_to_display( $product , array( 'qty' => $value ) );
					$display_price = wc_price( $price );
					?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $input_value ); ?>><?php
						echo str_replace( array('%qty%','%price%'), array(alg_wc_pq()->core->get_quantity_with_sep( $value ), $display_price), ( $value < 2 ? $label_template_singular : $label_template_plural ) ); ?></option><?php
				}
			}
			?>
		</select>
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $input_name ); ?>" class="qty ajax-ready <?php echo $disable_price_by_qty; ?>" value="<?php echo esc_attr( $input_value ); ?>">
		<?php echo do_shortcode( get_option( 'alg_wc_pq_qty_dropdown_template_after', '' ) ); ?>
	</div>
	<?php
}else { // WC default
	/* translators: %s: Quantity. */
	$label = ! empty( $args['product_name'] ) ? sprintf( __( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) ) : __( 'Quantity', 'woocommerce' );
	?>
	<div class="quantity">
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $label ); ?></label>
		<input
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
			step="<?php echo esc_attr( $step ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
			size="4"
			inputmode="<?php echo esc_attr( $inputmode ); ?>" />
	</div>
	<?php
}