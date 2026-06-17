<?php
/**
 * Product Quantity for WooCommerce - Category Metaboxes
 *
 * @version 5.4.3
 * @since   5.4.3
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Category_Metaboxes' ) ) :

	class WPFMMSQ_Category_Metaboxes {

		/**
		 * is_section_enabled
		 *
		 * @since 5.4.3
		 * @var   array
		 */
		public $is_section_enabled = array();

		/**
		 * Constructor.
		 *
		 * @version 5.4.3
		 * @since   5.4.3
		 */
		function __construct() {
			$this->is_section_enabled = array(
				'category_price_unit' => ( 'yes' === get_option( 'wpfmmsq_qty_price_unit_enabled', 'no' ) &&
					'yes' === get_option( 'wpfmmsq_qty_price_unit_category_enabled', 'no' ) ),
			);

			if (
				$this->is_section_enabled['category_price_unit']
			) {
				add_action( 'product_cat_edit_form_fields', array( $this, 'pq_taxonomy_edit_custom_meta_field' ), 10, 2 );
				add_action( 'edited_product_cat', array( $this, 'pq_save_taxonomy_custom_meta_field' ), 10, 2 );
			}
		}

		/**
		 * pq_taxonomy_edit_custom_meta_field.
		 *
		 * @version 5.4.3
		 * @since   5.4.3
		 *
		 * @param WP_Term $term Current taxonomy term object.
		 */
		public function pq_taxonomy_edit_custom_meta_field( $term ) {
			$t_id      = $term->term_id;
			$term_meta = get_option( "wpfmmsq_taxonomy_product_cat_$t_id", array() );
			?>
			<?php if ( $this->is_section_enabled['category_price_unit'] ) { ?>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="term_meta[wpfmmsq_category_price_unit]"><?php esc_html_e( 'Price Unit', 'product-quantity-for-woocommerce' ); ?></label>
					</th>
					<td>
						<?php
						$cat_unit = wpfmmsq_get_term_meta_value( $term_meta, 'wpfmmsq_category_price_unit' );
						?>
						<input type="text" name="term_meta[wpfmmsq_category_price_unit]" id="term_meta[wpfmmsq_category_price_unit]" value="<?php echo esc_attr( $cat_unit ); ?>">
						<p class="description"><?php esc_html_e( 'Specify a string for this category, this is controlled by Product Quantity plugin', 'product-quantity-for-woocommerce' ); ?></p>
					</td>
				</tr>
			<?php } ?>
			<?php
		}

		/**
		 * pq_save_taxonomy_custom_meta_field.
		 *
		 * @version 5.4.3
		 * @since   5.4.3
		 *
		 * @param int $term_id Term ID.
		 */
		public function pq_save_taxonomy_custom_meta_field( $term_id ) {
			if ( ! isset( $_POST['_wpnonce'] ) ) {
				return;
			}

			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
			if ( ! wp_verify_nonce( $nonce, 'update-tag_' . $term_id ) ) {
				return;
			}

			$taxonomy = get_taxonomy( 'product_cat' );
			if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->edit_terms ) ) {
				return;
			}

			$posted_term_meta = ( isset( $_POST['term_meta'] ) ? map_deep( wp_unslash( (array) $_POST['term_meta'] ), 'sanitize_text_field' ) : array() );
			if ( ! empty( $posted_term_meta ) ) {

				$t_id      = $term_id;
				$term_meta = get_option( "wpfmmsq_taxonomy_product_cat_$t_id", array() );
				$cat_keys  = array_keys( $posted_term_meta );
				foreach ( $cat_keys as $key ) {
					if ( isset( $posted_term_meta[ $key ] ) ) {
						$term_meta[ $key ] = sanitize_text_field( $posted_term_meta[ $key ] );
					}
				}
				// Save the option array.
				update_option( "wpfmmsq_taxonomy_product_cat_$t_id", $term_meta );
			}

		}

	}

endif;

return new WPFMMSQ_Category_Metaboxes();