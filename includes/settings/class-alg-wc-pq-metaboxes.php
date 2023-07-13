<?php
/**
 * Product Quantity for WooCommerce - Metaboxes
 *
 * @version 1.8.0
 * @since   1.0.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Metaboxes' ) ) :

class Alg_WC_PQ_Metaboxes {

	/**
	 * Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_pq_enabled', 'yes' ) ) {
			$this->is_section_enabled = array(
				'min'                  => ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) &&
					'yes' === apply_filters( 'alg_wc_pq_per_item_quantity_per_product', 'no', 'min' ) ),
				'max'                  => ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) &&
					'yes' === apply_filters( 'alg_wc_pq_per_item_quantity_per_product', 'no', 'max' ) ),
				'step'                 => ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) &&
					'yes' === apply_filters( 'alg_wc_pq_quantity_step_per_product', 'no' ) ),
				'exact_qty_allowed'    => ( 'yes' === get_option( 'alg_wc_pq_exact_qty_allowed_section_enabled', 'no' ) &&
					'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product', 'no', 'allowed' ) ),
				'exact_qty_disallowed' => ( 'yes' === get_option( 'alg_wc_pq_exact_qty_disallowed_section_enabled', 'no' ) &&
					'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product', 'no', 'disallowed' ) ),
				'dropdown'             => ( 'yes' === get_option( 'alg_wc_pq_qty_dropdown', 'no' ) &&
					'yes' === get_option( 'alg_wc_pq_qty_dropdown_label_template_is_per_product', 'no' ) ),
				'price_by_qty'         => ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_enabled', 'no' ) &&
					'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_unit_input_enabled', 'no' ) ),
				'default'         => ( 'yes' === get_option( 'alg_wc_pq_default_section_enabled', 'no' ) &&
					'yes' === apply_filters( 'alg_wc_pq_per_item_default_quantity_per_product', 'no', 'disallowed' ) ),
				'price_unit'    => ( 'yes' === get_option( 'alg_wc_pq_qty_price_unit_enabled', 'no' ) &&
					'yes' === get_option( 'alg_wc_pq_qty_price_unit_product_enabled', 'no' ) ),
				'allow_selling_below_stock'    => ( 'yes' === get_option( 'alg_wc_pq_min_per_item_quantity_per_product_allow_selling_below_stock', 'no' ) &&
					'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) && 'yes' === apply_filters( 'alg_wc_pq_per_item_quantity_per_product', 'no', 'min' ),
			);
			if (
				$this->is_section_enabled['min'] ||
				$this->is_section_enabled['max'] ||
				$this->is_section_enabled['step'] ||
				$this->is_section_enabled['exact_qty_allowed'] ||
				$this->is_section_enabled['exact_qty_disallowed'] ||
				$this->is_section_enabled['dropdown'] || 
				$this->is_section_enabled['price_by_qty'] || 
				$this->is_section_enabled['default'] || 
				$this->is_section_enabled['price_unit']
			) {
				add_action( 'add_meta_boxes',    array( $this, 'add_pq_metabox' ) );

				if('save_post_product' === get_option( 'alg_wc_pq_save_hook', 'save_post_product' )){
					add_action( 'save_post_product', array( $this, 'save_pq_meta_box' ), PHP_INT_MAX, 3 );
				}else{
					add_action( 'save_post', array( $this, 'save_pq_meta_box' ), PHP_INT_MAX, 3 );
				}
			}
		}
	}

	/**
	 * add_pq_metabox.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_pq_metabox() {
		add_meta_box(
			'alg-wc-product-quantity',
			__( 'Product Quantity', 'product-quantity-for-woocommerce' ),
			array( $this, 'display_pq_metabox' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * display_pq_metabox.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    [dev] `placeholder` for textarea
	 * @todo    [dev] `class` for all remaining types
	 */
	function display_pq_metabox() {
		$allow_sell_below_value = 'no';
		$current_post_id = get_the_ID();
		$html = '';
		$checked1 = '';
		$checked2 = '';
		$checked3 = '';
		$checked4 = '';
		$checked5 = '';
		$checked = '';
		
		$html .= '<table class="widefat striped">';
		foreach ( $this->get_meta_box_options() as $option ) {
			
			$option['desc'] = ( isset( $option['desc'] ) ? $option['desc'] : '' );
			$option['title'] = ( isset( $option['title'] ) ? $option['title'] : '' );
			
			$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
			if ( $is_enabled ) {
				if ( 'title' === $option['type'] ) {
					$html .= '<tr>';
					$html .= '<th colspan="3" style="text-align:left;font-weight:bold;">' . $option['title'] . '</th>';
					$html .= '</tr>';
				} else {
					$class = ( isset( $option['class'] ) ? $option['class'] : '' );
					$custom_attributes = '';
					$the_post_id   = ( isset( $option['product_id'] ) ) ? $option['product_id'] : $current_post_id;
					$the_meta_name = ( isset( $option['meta_name'] ) )  ? $option['meta_name']  : '_' . $option['name'];
					
					if ( get_post_meta( $the_post_id, $the_meta_name ) ) {
						$option_value = get_post_meta( $the_post_id, $the_meta_name, true );
					} else {
						$option_value = ( isset( $option['default'] ) ) ? $option['default'] : '';
					}

					$css = ( isset( $option['css'] ) ) ? $option['css']  : '';
					$input_ending = '';
					if ( 'select' === $option['type'] ) {
						if ( isset( $option['multiple'] ) ) {
							$custom_attributes = ' multiple';
							$option_name       = $option['name'] . '[]';
						} else {
							$option_name       = $option['name'];
						}
						if ( isset( $option['custom_attributes'] ) ) {
							$custom_attributes .= ' ' . $option['custom_attributes'];
						}
						$options = '';
						foreach ( $option['options'] as $select_option_key => $select_option_value ) {
							$selected = '';
							if ( is_array( $option_value ) ) {
								foreach ( $option_value as $single_option_value ) {
									if ( '' != ( $selected = selected( $single_option_value, $select_option_key, false ) ) ) {
										break;
									}
								}
							} else {
								$selected = selected( $option_value, $select_option_key, false );
							}
							$options .= '<option value="' . $select_option_key . '" ' . $selected . '>' . $select_option_value . '</option>';
						}
					} elseif ( 'textarea' === $option['type'] ) {
						if ( '' === $css ) {
							$css = 'min-width:300px;';
						}
					} else {
						$input_ending = ' id="' . $option['name'] . '" name="' . $option['name'] . '" value="' . $option_value . '">';
						if ( isset( $option['custom_attributes'] ) ) {
							$input_ending = ' ' . $option['custom_attributes'] . $input_ending;
						}
						if ( isset( $option['placeholder'] ) ) {
							$input_ending = ' placeholder="' . $option['placeholder'] . '"' . $input_ending;
						}
					}
					switch ( $option['type'] ) {
						case 'price':
							$field_html = '<input style="' . $css . '" class="short wc_input_price" type="number" step="0.0001"' . $input_ending;
							break;
						case 'date':
							$field_html = '<input style="' . $css . '" class="input-text" display="date" type="text"' . $input_ending;
							break;
						case 'textarea':
							$field_html = '<textarea style="' . $css . '" id="' . $option['name'] . '" name="' . $option['name'] . '">' .
								$option_value . '</textarea>';
							break;
						case 'select':
							$field_html = '<select' . $custom_attributes . ' style="' . $css . '" id="' . $option['name'] . '" name="' .
								$option_name . '">' . $options . '</select>';
							break;
						case 'checkbox':
							if($option['desc'] == '' && $option['meta_name']== '_alg_wc_pq_min_allow_selling_below_stock') {
								$allow_sell_below_value = $option_value;
								if($option_value == 'yes') {
									$checked = 'checked';
								}
								// $field_html = '<input style="' . $css . '" class="' . $class . '" type="' . $option['type'] . '" '.$checked.' value="yes" ' . $input_ending;
								}
							break;
						default:
							if($option['desc'] != 'Main variable product') {
								$field_html = '<input style="' . $css . '" class="' . $class . '" type="' . $option['type'] . '"' . $input_ending;
								if ( $this->is_section_enabled['allow_selling_below_stock'] ) {
									if($option['desc'] == '' && $option['meta_name']== '_alg_wc_pq_min') {
									
									if($allow_sell_below_value == 'yes') {
										$checked = 'checked';
									}else{
										$checked = '';
									}
									$maybe_tooltip = wc_help_tip( "Allow selling below this number if no stock is available to meet this number.", true );
									$field_html .= '<input style="' . $css . '" class="' . $class . '" type="checkbox" '.$checked.' value="yes" id="alg_wc_pq_min_allow_selling_below_stock_'.$the_post_id.'" name="alg_wc_pq_min_allow_selling_below_stock_'.$the_post_id.'" /><b>Allow selling below this number.</b>'.$maybe_tooltip;
									}
								}
							} else if($option['desc'] == 'Main variable product') {
								$field_html = '<input style="' . $css . '" class="' . $class . '" type="' . $option['type'] . '"' . $input_ending;
								if (strpos($option['name'], 'alg_wc_pq_min_') !== false) {
									$alg_wc_pq_min_name = 'alg_wc_pq_min_'.$the_post_id.'_to_all';
									$option_value5 = get_post_meta( $the_post_id, $alg_wc_pq_min_name, true );
									if($option_value5 == 'yes') {
										$checked5 = 'checked';
									}
									$maybe_tooltip = ( isset( $option['tooltip'] ) && '' != $option['tooltip'] ) ? wc_help_tip( "Add To All Min Variation", true ) : '';
									$field_html .= ' <input class="add_to_all_main" id="'.$option['name'].'_to_all" name="'.$option['name'].'_to_all" type="checkbox" '.$checked5.' value="'.$option_value5.'" /><b>Add To All</b>'.$maybe_tooltip;
								} else if(strpos($option['name'], 'alg_wc_pq_max_') !== false) {
									$alg_wc_pq_max_name = 'alg_wc_pq_max_'.$the_post_id.'_to_all';
									$option_value1 = get_post_meta( $the_post_id, $alg_wc_pq_max_name, true );
									if($option_value1 == 'yes') {
										$checked1 = 'checked';
									}
									$maybe_tooltip = ( isset( $option['tooltip'] ) && '' != $option['tooltip'] ) ? wc_help_tip( "Add To All Max Variation", true ) : '';
									$field_html .= ' <input class="add_to_all_main" id="'.$option['name'].'_to_all" name="'.$option['name'].'_to_all" type="checkbox" '.$checked1.' value="'.$option_value1.'" /><b>Add To All</b>'.$maybe_tooltip;
								} else if(strpos($option['name'], 'alg_wc_pq_step_') !== false) {
									$alg_wc_pq_step_name = 'alg_wc_pq_step_'.$the_post_id.'_to_all';
									$option_value2 = get_post_meta( $the_post_id, $alg_wc_pq_step_name, true );
									if($option_value2 == 'yes') {
										$checked2 = 'checked';
									}
									$maybe_tooltip = ( isset( $option['tooltip'] ) && '' != $option['tooltip'] ) ? wc_help_tip( "Add To All Step Variation", true ) : '';
									$field_html .= ' <input class="add_to_all_main" id="'.$option['name'].'_to_all" name="'.$option['name'].'_to_all" type="checkbox" '.$checked2.' value="'.$option_value2.'" /><b>Add To All</b>'.$maybe_tooltip;
								} else if(strpos($option['name'], 'alg_wc_pq_default_') !== false) {
									$alg_wc_pq_default_name = 'alg_wc_pq_default_'.$the_post_id.'_to_all';
									$option_value3 = get_post_meta( $the_post_id, $alg_wc_pq_default_name, true );
									if($option_value3 == 'yes') {
										$checked3 = 'checked';
									}
									$maybe_tooltip = ( isset( $option['tooltip'] ) && '' != $option['tooltip'] ) ? wc_help_tip( "Add To All Default Variation", true ) : '';
									$field_html .= ' <input class="add_to_all_main" id="'.$option['name'].'_to_all" name="'.$option['name'].'_to_all" type="checkbox" '.$checked3.' value="'.$option_value3.'" /><b>Add To All</b>'.$maybe_tooltip;
								} else if(strpos($option['name'], 'alg_wc_pq_exact_qty_allowed_') !== false) {
									$alg_wc_pq_default_name = 'alg_wc_pq_exact_qty_allowed_'.$the_post_id.'_to_all';
									$option_value4 = get_post_meta( $the_post_id, $alg_wc_pq_default_name, true );
									if($option_value4 == 'yes') {
										$checked4 = 'checked';
									}
									$maybe_tooltip = ( isset( $option['tooltip'] ) && '' != $option['tooltip'] ) ? wc_help_tip( "Add To All Exact Quantity Allowed Variation", true ) : '';
									$field_html .= ' <input class="add_to_all_main" id="'.$option['name'].'_to_all" name="'.$option['name'].'_to_all" type="checkbox" '.$checked4.' value="'.$option_value4.'" /><b>Add To All</b>'.$maybe_tooltip;
								}
							}
							/* $field_html = '<input style="' . $css . '" class="' . $class . '" type="' . $option['type'] . '"' . $input_ending; */
							break;
					}
					if(isset($option['meta_name']) && $option['meta_name']== '_alg_wc_pq_min_allow_selling_below_stock') {
						
					}else{
						$html .= '<tr>';
						$maybe_tooltip = ( isset( $option['tooltip'] ) && '' != $option['tooltip'] ) ? wc_help_tip( $option['tooltip'], true ) : '';
						$html .= '<th style="text-align:left;width:25%;">' . $option['title'] . $maybe_tooltip . '</th>';
						if ( isset( $option['desc'] ) && '' != $option['desc'] ) {
							$html .= '<td style="font-style:italic;width:25%;">' . $option['desc'] . '</td>';
						}
						$html .= '<td>' . $field_html . '</td>';
						$html .= '</tr>';
					}
				}
			}
		}
		$html .= '</table>';
		$html .= '<input type="hidden" name="alg_wc_pq_save_post" value="alg_wc_pq_save_post">';
		echo $html;
		do_action( 'alg_wc_pq_after_meta_box_settings' );
	}

	/**
	 * save_pq_meta_box.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function save_pq_meta_box( $post_id, $post, $update ) {
		global $post;
		$the_id = get_the_ID();
		if($the_id != $post_id){
			$pid = $post_id;
		}else{
			$pid = $post_id;
		}
		
		if('save_post_product' === get_option( 'alg_wc_pq_save_hook', 'save_post_product' )){
			if ($post && property_exists($post, 'post_type') && $post->post_type != 'product'){
				return;
			}
		}
		
		/*for language WPML support*/
		if(function_exists('icl_object_id') && function_exists('icl_get_languages')){
			$languages = icl_get_languages();
		}
		
		// Check that we are saving with current metabox displayed.
		if ( ! isset( $_POST[ 'alg_wc_pq_save_post' ] ) ) {
			return;
		}
		// Save options
		foreach ( $this->get_meta_box_options($pid) as $option ) {
			if ( 'title' === $option['type'] ) {
				continue;
			}
			$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
			if ( $is_enabled ) {
				if ( 'checkbox' === $option['type'] ) {
					$option_value = ( ( isset( $_POST[ $option['name'] ] ) && $_POST[ $option['name'] ]=='yes' ) ? 'yes' : 'no' );
				}else{
					$option_value  = ( isset( $_POST[ $option['name'] ] ) ? $_POST[ $option['name'] ] : $option['default'] );
				}
				$_post_id      = ( isset( $option['product_id'] )     ? $option['product_id']     : $post_id );
				$_meta_name    = ( isset( $option['meta_name'] )      ? $option['meta_name']      : '_' . $option['name'] );
				
				update_post_meta( $_post_id, $_meta_name, $option_value );
				
				/*for language WPML support*/
				if(function_exists('icl_object_id') && function_exists('icl_get_languages')){
					foreach ($languages as $lang) {
						if ($lang['code'] != 'en') {
							if($option['is_var'] == 'yes'){
								$lang_pid = icl_object_id($_post_id, 'product_variation', false, $lang['code']);
							}else{
								$lang_pid = icl_object_id($_post_id, 'product', false, $lang['code']);
							}
							if($lang_pid > 0){
								update_post_meta( $lang_pid, $_meta_name, $option_value );
							}
						}
					}				
				}
			}
		}
		
		// save exclude product id by category for new product
		if (false !== strpos($_POST['_wp_http_referer'], 'post-new.php')) {
			if ( '' != ( $disable_categories = get_option( 'alg_wc_pq_disable_by_category', '' ) ) ) {
				$d_cat_ids = get_option( 'alg_wc_pq_disable_category_excluded_pids', '' );
				if ( '' != $d_cat_ids ) {
					$d_cat_ids = array_map( 'trim', explode( ',', $d_cat_ids ) );
				}else{
					$d_cat_ids = array();
				}
				$category_terms = get_the_terms( $pid, 'product_cat' );
				if(!empty( $category_terms )){
					foreach ($category_terms as $term) {
						$product_cat_id = $term->term_id;
						if (in_array( $product_cat_id, $disable_categories )) {
							
							if ( !empty($d_cat_ids) ) {
								$d_cat_ids[] = $pid;
							}else{
								$d_cat_ids[] = $pid;
							}
							
							update_option('alg_wc_pq_disable_category_excluded_pids',implode(',',$d_cat_ids));
							
							break;
						}
					}
				}
				
			}
		}

		
	}

	/**
	 * get_product_formatted_variation.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_product_formatted_variation( $variation, $flat = false, $include_names = true ) {
		if ( ! isset( $this->is_wc_version_below_3 ) ) {
			$this->is_wc_version_below_3 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
		}
		if ( $this->is_wc_version_below_3 ) {
			return $variation->get_formatted_variation_attributes( $flat );
		} else {
			return wc_get_formatted_variation( $variation, $flat, $include_names );
		}
	}

	/**
	 * get_meta_box_options.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [dev] (maybe) add "Enable/Disable" option
	 */
	function get_meta_box_options($pid = 0) {
		if($pid <= 0){
			$main_product_id = get_the_ID();
		}else{
			$main_product_id = $pid;
		}
		$_product = wc_get_product( $main_product_id );
		if ( ! $_product ) {
			return array();
		}
		$products = array();
		$varids = array();

		if ( $_product->is_type( 'variable' ) ) {
			$available_variations = $_product->get_available_variations();
			foreach ( $available_variations as $variation ) {
				$variation_product = wc_get_product( $variation['variation_id'] );
				$products[ $variation['variation_id'] ] = $this->get_product_formatted_variation( $variation_product, true );
				$varids[] = $variation['variation_id'];
			}
			$products[ $main_product_id ] = __( 'Main variable product', 'product-quantity-for-woocommerce' );
		} else {
			$products[ $main_product_id ] = '';
		}
		$qty_step_settings = ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ? '0.000001' : '1' );
		$quantities = array();
		foreach ( $products as $product_id => $desc ) {
			$is_var = 'no';
			if(!empty($varids)){
				$is_var = (in_array($product_id, $varids) ? 'yes' : 'no');
			}
			if ( $this->is_section_enabled['min'] ) {
				
				if ( $this->is_section_enabled['allow_selling_below_stock'] ) {
				
					$quantities = array_merge( $quantities, array(
						array(
							'name'       => 'alg_wc_pq_min_allow_selling_below_stock' . '_' . $product_id,
							'default'    => 'no',
							'type'       => 'checkbox',
							'title'      => __( 'Allow selling below this number if no stock is available to meet this number', 'product-quantity-for-woocommerce' ),
							'desc'       => $desc,
							'is_var'     => $is_var,
							'product_id' => $product_id,
							'meta_name'  => '_' . 'alg_wc_pq_min_allow_selling_below_stock',
							'tooltip'    => __( 'Allow selling below this number if no stock is available to meet this number', 'product-quantity-for-woocommerce' ),
						),
					) );
					
				}
				
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_min' . '_' . $product_id,
						'default'    => '',
						'type'       => 'number',
						'title'      => __( 'Minimum quantity', 'product-quantity-for-woocommerce' ),
						'desc'       => $desc,
						'is_var'     => $is_var,
						'product_id' => $product_id,
						'meta_name'  => '_' . 'alg_wc_pq_min',
						'custom_attributes' => 'min="-1" step="' . $qty_step_settings . '"',
						'tooltip'    => __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ),
					),
					/*
					array(
						'name'       => 'alg_wc_pq_min_'.$product_id.'_to_all',
						'type'       => 'hidden',
						'meta_name'  => 'alg_wc_pq_min_'.$product_id.'_to_all',
					),
					*/
				) );
				
				
			}
			if ( $this->is_section_enabled['max'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_max' . '_' . $product_id,
						'default'    => '',
						'type'       => 'number',
						'title'      => __( 'Maximum quantity', 'product-quantity-for-woocommerce' ),
						'desc'       => $desc,
						'is_var'     => $is_var,
						'product_id' => $product_id,
						'meta_name'  => '_' . 'alg_wc_pq_max',
						'custom_attributes' => 'min="-1" step="' . $qty_step_settings . '"',
						'tooltip'    => __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ),
					),
					/*
					array(
						'name'       => 'alg_wc_pq_max_'.$product_id.'_to_all',
						'type'       => 'hidden',
						'meta_name'  => 'alg_wc_pq_max_'.$product_id.'_to_all',
					),
					*/
				) );
			}
			if ( $this->is_section_enabled['step'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_step' . '_' . $product_id,
						'default'    => '',
						'type'       => 'number',
						'title'      => __( 'Quantity step', 'product-quantity-for-woocommerce' ),
						'desc'       => $desc,
						'is_var'     => $is_var,
						'product_id' => $product_id,
						'meta_name'  => '_' . 'alg_wc_pq_step',
						'custom_attributes' => 'min="0" step="' . $qty_step_settings . '"',
						'tooltip'    => __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ),
					),
					/*
					array(
						'name'       => 'alg_wc_pq_step_'.$product_id.'_to_all',
						'type'       => 'hidden',
						'meta_name'  => 'alg_wc_pq_step_'.$product_id.'_to_all',
					),
					*/
				) );
			}
			if ( $this->is_section_enabled['exact_qty_allowed'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_exact_qty_allowed' . '_' . $product_id,
						'default'    => '',
						'type'       => 'text',
						'title'      => __( 'Exact quantity allowed', 'product-quantity-for-woocommerce' ),
						'desc'       => $desc,
						'is_var'     => $is_var,
						'product_id' => $product_id,
						'meta_name'  => '_' . 'alg_wc_pq_exact_qty_allowed',
						'tooltip'    => sprintf( __( 'Allowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ) . ' ' .
							__( 'Set blank to use global settings.', 'product-quantity-for-woocommerce' ),
						'css'        => 'width:50%;',
					),
					/*
					array(
						'name'       => 'alg_wc_pq_exact_qty_allowed_'.$product_id.'_to_all',
						'type'       => 'hidden',
						'meta_name'  => 'alg_wc_pq_exact_qty_allowed_'.$product_id.'_to_all',
					),
					*/
				) );
			}
			if ( $this->is_section_enabled['exact_qty_disallowed'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_exact_qty_disallowed' . '_' . $product_id,
						'default'    => '',
						'type'       => 'text',
						'title'      => __( 'Exact quantity disallowed', 'product-quantity-for-woocommerce' ),
						'desc'       => $desc,
						'is_var'     => $is_var,
						'product_id' => $product_id,
						'meta_name'  => '_' . 'alg_wc_pq_exact_qty_disallowed',
						'tooltip'    => sprintf( __( 'Disallowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ) . ' ' .
							__( 'Set blank to use global settings.', 'product-quantity-for-woocommerce' ),
						'css'        => 'width:100%;',
					),
				) );
			}
			
			if ( $this->is_section_enabled['default'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_default' . '_' . $product_id,
						'default'    => '',
						'type'       => 'number',
						'title'      => __( 'Default quantity', 'product-quantity-for-woocommerce' ),
						'desc'       => $desc,
						'is_var'     => $is_var,
						'product_id' => $product_id,
						'meta_name'  => '_' . 'alg_wc_pq_default',
						'custom_attributes' => 'min="-1" step="' . $qty_step_settings . '"',
						'tooltip'    => __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ),
					),
					/*
					array(
						'name'       => 'alg_wc_pq_default_'.$product_id.'_to_all',
						'type'       => 'hidden',
						'meta_name'  => 'alg_wc_pq_default_'.$product_id.'_to_all',
					),
					*/
				) );
			}
			
			if ( $this->is_section_enabled['price_unit'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_price_unit' . '_' . $product_id,
						'default'    => '',
						'type'       => 'text',
						'title'      => __( 'Price Unit', 'product-quantity-for-woocommerce' ),
						'desc'       => $desc,
						'is_var'     => $is_var,
						'product_id' => $product_id,
						'meta_name'  => '_' . 'alg_wc_pq_price_unit',
						'custom_attributes' => 'min="-1" step="' . $qty_step_settings . '"',
						'tooltip'    => __( 'Any string value.', 'product-quantity-for-woocommerce' ),
					),
					/*
					array(
						'name'       => 'alg_wc_pq_price_unit_'.$product_id.'_to_all',
						'type'       => 'hidden',
						'meta_name'  => 'alg_wc_pq_price_unit_'.$product_id.'_to_all',
					),
					*/
				) );
			}
		}
		if ( $this->is_section_enabled['dropdown'] ) {
			$quantities = array_merge( $quantities, array(
				array(
					'name'       => 'alg_wc_pq_qty_dropdown_label_template_singular',
					'default'    => '',
					'type'       => 'text',
					'title'      => __( 'Dropdown label template: Singular', 'product-quantity-for-woocommerce' ),
					'desc'       => ( count( $products ) > 1 ? __( 'All variations', 'product-quantity-for-woocommerce' ) : '' ),
					'is_var'     => $is_var,
					'tooltip'    => sprintf( __( 'Dropdown label template, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>%qty% item</em>' ) . ' ' .
						__( 'Set blank to use global settings.', 'product-quantity-for-woocommerce' ),
					'placeholder' => get_option( 'alg_wc_pq_qty_dropdown_label_template_singular', '%qty%' ),
				),
				array(
					'name'       => 'alg_wc_pq_qty_dropdown_label_template_plural',
					'default'    => '',
					'type'       => 'text',
					'title'      => __( 'Dropdown label template: Plural', 'product-quantity-for-woocommerce' ),
					'desc'       => ( count( $products ) > 1 ? __( 'All variations', 'product-quantity-for-woocommerce' ) : '' ),
					'is_var'     => $is_var,
					'tooltip'    => sprintf( __( 'Dropdown label template, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>%qty% items</em>' ) . ' ' .
						__( 'Set blank to use global settings.', 'product-quantity-for-woocommerce' ),
					'placeholder' => get_option( 'alg_wc_pq_qty_dropdown_label_template_plural', '%qty%' ),
				),
			) );
		}
		
		if ( $this->is_section_enabled['price_by_qty'] ) { 
			$quantities = array_merge( $quantities, array(
				array(
					'name'       => 'alg_wc_pq_qty_price_by_qty_unit_label_template_singular',
					'default'    => '',
					'type'       => 'text',
					'title'      => __( 'Unit label template: Singular', 'product-quantity-for-woocommerce' ),
					'desc'       => ( count( $products ) > 1 ? __( 'All variations', 'product-quantity-for-woocommerce' ) : '' ),
					'is_var'     => $is_var,
					'tooltip'    => sprintf( __( 'Unit label template, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>piece</em>' ) . ' ' .
						__( 'Set blank to use global settings.', 'product-quantity-for-woocommerce' ),
					'placeholder' => get_option( 'alg_wc_pq_qty_price_by_qty_unit_label_template_singular', 'piece' ),
				),
				array(
					'name'       => 'alg_wc_pq_qty_price_by_qty_unit_label_template_plural',
					'default'    => '',
					'type'       => 'text',
					'title'      => __( 'Unit label template: Plural', 'product-quantity-for-woocommerce' ),
					'desc'       => ( count( $products ) > 1 ? __( 'All variations', 'product-quantity-for-woocommerce' ) : '' ),
					'is_var'     => $is_var,
					'tooltip'    => sprintf( __( 'Unit label template, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>pieces</em>' ) . ' ' .
						__( 'Set blank to use global settings.', 'product-quantity-for-woocommerce' ),
					'placeholder' => get_option( 'alg_wc_pq_qty_price_by_qty_unit_label_template_plural', 'pieces' ),
				),
			) );
		}
		
		if ( $_product->is_type( 'variable' ) ) {
			if ( $this->is_section_enabled['min'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_min' . '_variable_qty',
						'default'    => '',
						'type'       => 'number',
						'title'      => __( 'Minimum quantity for all variations', 'product-quantity-for-woocommerce' ),
						'desc'       => ( count( $products ) > 1 ? __( 'All variations', 'product-quantity-for-woocommerce' ) : '' ),
						'is_var'     => $is_var,
						'meta_name'  => '_' . 'alg_wc_pq_min_variable_qty',
						'custom_attributes' => 'min="-1" step="' . $qty_step_settings . '"',
						'tooltip'    => __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ),
					)
				) );
			}

			if ( $this->is_section_enabled['max'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_max' . '_variable_qty',
						'default'    => '',
						'type'       => 'number',
						'title'      => __( 'Maximum quantity for all variations', 'product-quantity-for-woocommerce' ),
						'desc'       => ( count( $products ) > 1 ? __( 'All variations', 'product-quantity-for-woocommerce' ) : '' ),
						'is_var'     => $is_var,
						'meta_name'  => '_' . 'alg_wc_pq_max_variable_qty',
						'custom_attributes' => 'min="-1" step="' . $qty_step_settings . '"',
						'tooltip'    => __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ),
					)
				) );
			}
			
			if ( $this->is_section_enabled['exact_qty_allowed'] ) {
				$quantities = array_merge( $quantities, array(
					array(
						'name'       => 'alg_wc_pq_exact_qty_allowed' . '_variable_qty',
						'default'    => '',
						'type'       => 'text',
						'title'      => __( 'Exact quantity allowed for all variations', 'product-quantity-for-woocommerce' ),
						'desc'       => ( count( $products ) > 1 ? __( 'All variations', 'product-quantity-for-woocommerce' ) : '' ),
						'is_var'     => $is_var,
						'meta_name'  => '_' . 'alg_wc_pq_exact_qty_allowed_variable_qty',
						'custom_attributes' => '',
						'tooltip'    => __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ),
					)
				) );
			}
		}
		return $quantities;
	}

}

endif;

return new Alg_WC_PQ_Metaboxes();
