<?php
/**
 * Product Quantity for WooCommerce - Metaboxes
 *
 * @version 5.3.4
 * @since   1.0.0
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Attribute_Item_Metaboxes' ) ) :

	class WPFMMSQ_Attribute_Item_Metaboxes {

		/**
		 * is_section_enabled
		 *
		 * @since 4.6.0
		 * @var   string
		 */
		public $is_section_enabled = null;

		/**
		 * attribute_taxonomies
		 *
		 * @since 4.6.0
		 * @var   array
		 */
		public $attribute_taxonomies = array();

		/**
		 * Constructor.
		 *
		 * @version 5.3.4
		 * @since   1.0.0
		 */
		function __construct() {
			$this->is_section_enabled = array(
				'exact_qty_allowed'  => ( 'yes' === get_option( 'wpfmmsq_exact_qty_allowed_section_enabled', 'no' ) &&
										  'yes' === get_option( 'wpfmmsq_exact_qty_allowed_per_attribute_item_quantity', 'no' ) ),
				'min'                => ( 'yes' === get_option( 'wpfmmsq_min_section_enabled', 'no' ) &&
										  'yes' === get_option( 'wpfmmsq_min_per_attribute_item_quantity', 'no' ) ),
				'step'               => ( 'yes' === get_option( 'wpfmmsq_step_section_enabled', 'no' ) &&
										  'yes' === get_option( 'wpfmmsq_step_per_attribute_item_quantity', 'no' ) ),
				'max'                => ( 'yes' === get_option( 'wpfmmsq_max_section_enabled', 'no' ) &&
										  'yes' === get_option( 'wpfmmsq_max_per_attribute_item_quantity', 'no' ) ),
				'total_price_by_qty' => ( 'yes' === get_option( 'wpfmmsq_qty_price_by_qty_enabled', 'no' ) &&
										  'yes' === get_option( 'wpfmmsq_qty_price_by_attribute_qty_unit_input_enabled', 'no' ) ),
			);

			if (
				$this->is_section_enabled['exact_qty_allowed'] ||
				$this->is_section_enabled['min'] ||
				$this->is_section_enabled['step'] ||
				$this->is_section_enabled['max'] ||
				$this->is_section_enabled['total_price_by_qty']
			) {
				$this->attribute_taxonomies = wpfmmsq_wc_get_attribute_taxonomies();
				// $product_attributes_selected = get_option( 'wpfmmsq_exact_qty_allowed_per_attributes_selected', array() );
				if ( ! empty( $product_attributes_selected ) && ! empty( $this->attribute_taxonomies ) > 0 ) {
					foreach ( $product_attributes_selected as $selected_id ) {
						$tax  = $this->attribute_taxonomies[ $selected_id ];
						$name = wpfmmsq_wc_attribute_taxonomy_name( $tax->attribute_name );
						add_action( $name . '_edit_form_fields', array( $this, 'pq_taxonomy_edit_custom_meta_field' ), 10, 2 );
						add_action( 'edited_' . $name, array( $this, 'pq_save_taxonomy_custom_meta_field' ), 10, 2 );
					}
				} else if ( ! empty( $this->attribute_taxonomies ) ) {
					foreach ( $this->attribute_taxonomies as $tax ) {
						$name = wpfmmsq_wc_attribute_taxonomy_name( $tax->attribute_name );
						add_action( $name . '_edit_form_fields', array( $this, 'pq_taxonomy_edit_custom_meta_field' ), 10, 2 );
						add_action( 'edited_' . $name, array( $this, 'pq_save_taxonomy_custom_meta_field' ), 10, 2 );
					}
				}
			}
		}

		/**
		 * pq_taxonomy_edit_custom_meta_field.
		 *
		 * @version 5.3.4
		 * @since   4.6.0
		 */
		public function pq_taxonomy_edit_custom_meta_field( $term ) {
			$taxonomy                                               = $term->taxonomy;
			$t_id                                                   = $term->term_id;
			$term_meta                                              = get_option( "wpfmmsq_taxonomy_product_attribute_item_$t_id" );
			$product_attributes_selected                            = get_option( 'wpfmmsq_exact_qty_allowed_per_attributes_selected', array() );
			$wpfmmsq_min_per_attribute_selected                     = get_option( 'wpfmmsq_min_per_attribute_selected', array() );
			$wpfmmsq_max_per_attribute_selected                     = get_option( 'wpfmmsq_max_per_attribute_selected', array() );
			$wpfmmsq_step_per_attribute_selected                    = get_option( 'wpfmmsq_step_per_attribute_selected', array() );
			$wpfmmsq_qty_price_by_attribute_qty_unit_input_selected = get_option( 'wpfmmsq_qty_price_by_attribute_qty_unit_input_selected', array() );
			?>
			<?php if ( $this->is_section_enabled['min'] && $this->is_field_allowed( $taxonomy, $wpfmmsq_min_per_attribute_selected ) ) { ?>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_min]"><?php esc_html_e( 'Minimum quantity (grouped for this attribute item)', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
					</th>
					<td>
						<input type="number" step="0.000001" min="0" name="term_meta[wpfmmsq_min]" id="term_meta[wpfmmsq_min]" value="<?php echo esc_attr( $term_meta['wpfmmsq_min'] ) ? esc_attr( $term_meta['wpfmmsq_min'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify a number of minimum allowed quantity for this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_min_all_product]"><?php esc_html_e( 'Minimum quantity for all product (apply to all products in this attribute item)', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
					</th>
					<td>
						<input type="number" step="0.000001" min="0" name="term_meta[wpfmmsq_min_all_product]" id="term_meta[wpfmmsq_min_all_product]" value="<?php echo esc_attr( $term_meta['wpfmmsq_min_all_product'] ) ? esc_attr( $term_meta['wpfmmsq_min_all_product'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify a number of minimum allowed quantity for all product in this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
			<?php } ?>
			<?php if ( $this->is_section_enabled['max'] && $this->is_field_allowed( $taxonomy, $wpfmmsq_max_per_attribute_selected ) ) { ?>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_max]"><?php esc_html_e( 'Maximum quantity (grouped for this attribute item)', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
					</th>
					<td>
						<input type="number" step="0.000001" min="0" name="term_meta[wpfmmsq_max]" id="term_meta[wpfmmsq_max]" value="<?php echo esc_attr( $term_meta['wpfmmsq_max'] ) ? esc_attr( $term_meta['wpfmmsq_max'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify a number of maximum allowed quantity for this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_max_all_product]"><?php esc_html_e( 'Maximum quantity for all product (apply to all products in this attribute item)', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
					</th>
					<td>
						<input type="number" step="0.000001" min="0" name="term_meta[wpfmmsq_max_all_product]" id="term_meta[wpfmmsq_max_all_product]" value="<?php echo esc_attr( $term_meta['wpfmmsq_max_all_product'] ) ? esc_attr( $term_meta['wpfmmsq_max_all_product'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify a number of maximum allowed quantity for all product in this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
			<?php } ?>

			<?php if ( $this->is_section_enabled['step'] && $this->is_field_allowed( $taxonomy, $wpfmmsq_step_per_attribute_selected ) ) { ?>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_step]"><?php esc_html_e( 'Step quantity', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
					</th>
					<td>
						<input type="number" step="0.000001" min="0" name="term_meta[wpfmmsq_step]" id="term_meta[wpfmmsq_step]" value="<?php echo esc_attr( $term_meta['wpfmmsq_step'] ) ? esc_attr( $term_meta['wpfmmsq_step'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify a number of step quantity for this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
				<?php /*
			<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[wpfmmsq_step_all_product]"><?php esc_html_e( 'Step quantity for all product', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
				<td>
					<input type="number" step="0.000001" min="0" name="term_meta[wpfmmsq_step_all_product]" id="term_meta[wpfmmsq_step_all_product]" value="<?php echo esc_attr( $term_meta['wpfmmsq_step_all_product'] ) ? esc_attr( $term_meta['wpfmmsq_step_all_product'] ) : ''; ?>">
					<p class="description"><?php esc_html_e( 'Specify a number of step quantity for all product in this attribute item, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
				</td>
			</tr>
			*/ ?>
			<?php } ?>

			<?php if ( $this->is_section_enabled['exact_qty_allowed'] && $this->is_field_allowed( $taxonomy, $product_attributes_selected ) ) { ?>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_exact_qty_allowed]"><?php esc_html_e( 'Exact quantity allowed (grouped for this attribute item)', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( sprintf( __( 'Allowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ), true ); ?></label>
					</th>
					<td>
						<input type="text" name="term_meta[wpfmmsq_exact_qty_allowed]" id="term_meta[wpfmmsq_exact_qty_allowed]" value="<?php echo esc_attr( $term_meta['wpfmmsq_exact_qty_allowed'] ) ? esc_attr( $term_meta['wpfmmsq_exact_qty_allowed'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify numbers of exact allowed quantity for this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_exact_qty_allowed_all_product]"><?php esc_html_e( 'Exact quantity allowed for all product (apply to all products in this attribute item)', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( sprintf( __( 'Allowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ), true ); ?></label>
					</th>
					<td>
						<input type="text" name="term_meta[wpfmmsq_exact_qty_allowed_all_product]" id="term_meta[wpfmmsq_exact_qty_allowed_all_product]" value="<?php echo esc_attr( $term_meta['wpfmmsq_exact_qty_allowed_all_product'] ) ? esc_attr( $term_meta['wpfmmsq_exact_qty_allowed_all_product'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify numbers of exact allowed quantity for all product in this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
			<?php } ?>

			<?php if ( $this->is_section_enabled['total_price_by_qty'] && $this->is_field_allowed( $taxonomy, $wpfmmsq_qty_price_by_attribute_qty_unit_input_selected ) ) { ?>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_price_by_qty_attribute_unit_singular]"><?php esc_html_e( 'Unit label template: Singular', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label>
					</th>
					<td>
						<input type="text" name="term_meta[wpfmmsq_price_by_qty_attribute_unit_singular]" id="term_meta[wpfmmsq_price_by_qty_attribute_unit_singular]" value="<?php echo esc_attr( $term_meta['wpfmmsq_price_by_qty_attribute_unit_singular'] ) ? esc_attr( $term_meta['wpfmmsq_price_by_qty_attribute_unit_singular'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify a singular string for this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_price_by_qty_attribute_unit_plural]"><?php esc_html_e( 'Unit label template: Plural', 'product-quantity-for-woocommerce' ); ?><?php //echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label>
					</th>
					<td>
						<input type="text" name="term_meta[wpfmmsq_price_by_qty_attribute_unit_plural]" id="term_meta[wpfmmsq_price_by_qty_attribute_unit_plural]" value="<?php echo esc_attr( $term_meta['wpfmmsq_price_by_qty_attribute_unit_plural'] ) ? esc_attr( $term_meta['wpfmmsq_price_by_qty_attribute_unit_plural'] ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Specify a plural string for this attribute item, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
			<?php } ?>


			<?php
		}

		/**
		 * pq_save_taxonomy_custom_meta_field.
		 *
		 * @version 5.3.4
		 * @since   4.6.0
		 */
		public function pq_save_taxonomy_custom_meta_field( $term_id ) {
			$posted_term_meta = ( isset( $_POST['term_meta'] ) ? map_deep( wp_unslash( (array) $_POST['term_meta'] ), 'sanitize_text_field' ) : array() );
			if ( ! empty( $posted_term_meta ) ) {

				$t_id      = $term_id;
				$term_meta = get_option( "wpfmmsq_taxonomy_product_attribute_item_$t_id" );
				$cat_keys  = array_keys( $posted_term_meta );
				foreach ( $cat_keys as $key ) {
					if ( isset( $posted_term_meta[ $key ] ) ) {
						$term_meta[ $key ] = $posted_term_meta[ $key ];
					}
				}
				// Save the option array.
				update_option( "wpfmmsq_taxonomy_product_attribute_item_$t_id", $term_meta );
			}

		}

		public function is_field_allowed( $taxonomy, $allowed_attribute ) {
			if ( ! empty( $allowed_attribute ) ) {
				foreach ( $allowed_attribute as $selected_id ) {
					$tax  = $this->attribute_taxonomies[ $selected_id ];
					$name = wpfmmsq_wc_attribute_taxonomy_name( $tax->attribute_name );
					if ( $taxonomy == $name ) {
						return true;
					}
				}

				return false;
			}

			return true;
		}

	}

endif;

return new WPFMMSQ_Attribute_Item_Metaboxes();


