<?php
/**
 * Product Quantity for WooCommerce - Metaboxes
 *
 * @version 1.8.0
 * @since   1.0.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Category_Metaboxes' ) ) :

class Alg_WC_PQ_Category_Metaboxes {

	/**
	 * Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->is_section_enabled = array(
			'min'                  => ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) &&
				'yes' === get_option( 'alg_wc_pq_min_per_cat_item_quantity_per_product', 'no' ) ),
			'max'                  => ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) &&
				'yes' === get_option( 'alg_wc_pq_max_per_cat_item_quantity_per_product', 'no' ) ),
			'step'                 => ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) &&
				'yes' === get_option( 'alg_wc_pq_step_per_cat_item_quantity_per_product', 'no' ) ),
			'default'                 => ( 'yes' === get_option( 'alg_wc_pq_default_section_enabled', 'no' ) &&
				'yes' === get_option( 'alg_wc_pq_default_per_cat_item_quantity_per_product', 'no' ) ),
			'exact_qty_allowed'    => ( 'yes' === get_option( 'alg_wc_pq_exact_qty_allowed_section_enabled', 'no' ) &&
				'yes' === get_option( 'alg_wc_pq_exact_qty_allowed_per_cat_item_quantity_per_product', 'no' ) ),
			'exact_qty_disallowed'    => ( 'yes' === get_option( 'alg_wc_pq_exact_qty_disallowed_section_enabled', 'no' ) &&
				'yes' === get_option( 'alg_wc_pq_exact_qty_disallowed_per_cat_item_quantity_per_product', 'no' ) ),
			'category_unit'    => ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_enabled', 'no' ) &&
				'yes' === get_option( 'alg_wc_pq_qty_price_by_cat_qty_unit_input_enabled', 'no' ) ),
			'category_price_unit'    => ( 'yes' === get_option( 'alg_wc_pq_qty_price_unit_enabled', 'no' ) &&
				'yes' === get_option( 'alg_wc_pq_qty_price_unit_category_enabled', 'no' ) )
		);
		if (
			$this->is_section_enabled['min'] ||
			$this->is_section_enabled['max'] ||
			$this->is_section_enabled['step'] ||
			$this->is_section_enabled['default'] ||
			$this->is_section_enabled['exact_qty_allowed'] ||
			$this->is_section_enabled['exact_qty_disallowed'] ||
			$this->is_section_enabled['category_unit'] ||
			$this->is_section_enabled['category_price_unit']
		) {
			/* add_action( 'product_cat_add_form_fields', array( $this, 'pq_taxonomy_add_custom_meta_field' ), 10, 2 ); */
			add_action( 'product_cat_edit_form_fields', array( $this, 'pq_taxonomy_edit_custom_meta_field' ), 10, 2 );
			add_action( 'edited_product_cat', array( $this, 'pq_save_taxonomy_custom_meta_field' ), 10, 2 );  
			/* add_action( 'create_product_cat', array( $this, 'pq_save_taxonomy_custom_meta_field' ), 10, 2 ); */
		}
	}
	
	public function pq_taxonomy_add_custom_meta_field() {
		?>
		<?php if ( $this->is_section_enabled['min'] ) { ?>
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_min]"><?php _e( 'Minimum quantity (grouped for this category)', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
			<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_min]" id="term_meta[alg_wc_pq_min]" value="" >
			<p class="description"><?php _e( 'Specify a number of minimum allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
		</div>
		
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_min_all_product]"><?php _e( 'Minimum quantity for all product (apply to all products in this category)', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
			<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_min_all_product]" id="term_meta[alg_wc_pq_min_all_product]" value="" >
			<p class="description"><?php _e( 'Specify a number of minimum allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
		</div>
		<?php } ?>
		<?php if ( $this->is_section_enabled['max'] ) { ?>
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_max]"><?php _e( 'Maximum quantity (grouped for this category)', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
			<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_max]" id="term_meta[alg_wc_pq_max]" value="">
			<p class="description"><?php _e( 'Specify a number of maximum allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
		</div>
		
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_max_all_product]"><?php _e( 'Maximum quantity for all product (apply to all products in this category)', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label>
			<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_max_all_product]" id="term_meta[alg_wc_pq_max_all_product]" value="">
			<p class="description"><?php _e( 'Specify a number of maximum allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
		</div>
		<?php } ?>
		<?php if ( $this->is_section_enabled['step'] ) { ?>
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_step]"><?php _e( 'Quantity step', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label>
			<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_step]" id="term_meta[alg_wc_pq_step]" value="">
			<p class="description"><?php _e( '','product-quantity-for-woocommerce' ); ?></p>
		</div>
		
		<?php } ?>
		<?php if ( $this->is_section_enabled['default'] ) { ?>
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_default]"><?php _e( 'Quantity default', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label>
			<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_default]" id="term_meta[alg_wc_pq_default]" value="">
			<p class="description"><?php _e( '','product-quantity-for-woocommerce' ); ?></p>
		</div>
		<?php } ?>
		<?php if ( $this->is_section_enabled['exact_qty_allowed'] ) { ?>
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_exact_qty_allowed]"><?php _e( 'Exact quantity allowed (grouped for this category)', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( sprintf( __( 'Allowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ), true ); ?></label>
			<input type="text" name="term_meta[alg_wc_pq_exact_qty_allowed]" id="term_meta[alg_wc_pq_exact_qty_allowed]" value="">
			<p class="description"><?php _e( 'Specify numbers of exact allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
		</div>
		
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_exact_qty_allowed_all_product]"><?php _e( 'Exact quantity allowed for all product (apply to all products in this category)', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( sprintf( __( 'Allowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ), true ); ?></label>
			<input type="text" name="term_meta[alg_wc_pq_exact_qty_allowed_all_product]" id="term_meta[alg_wc_pq_exact_qty_allowed_all_product]" value="">
			<p class="description"><?php _e( 'Specify numbers of exact allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
		</div>
		<?php } ?>
		<?php /*if ( $this->is_section_enabled['exact_qty_disallowed'] ) { ?>
		<div class="form-field">
			<label for="term_meta[alg_wc_pq_exact_qty_disallowed]"><?php _e( 'Exact quantity disallowed', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( sprintf( __( 'Disallowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ), true ); ?></label>
			<input type="text" name="term_meta[alg_wc_pq_exact_qty_disallowed]" id="term_meta[alg_wc_pq_exact_qty_disallowed]" value="">
			<p class="description"><?php _e( '','product-quantity-for-woocommerce' ); ?></p>
		</div>
		<?php }*/ ?>
	<?php
	}
	
	public function pq_taxonomy_edit_custom_meta_field($term) {
	    $t_id = $term->term_id;
	    $term_meta = get_option( "taxonomy_product_cat_$t_id" );
	   ?>
	   <?php if ( $this->is_section_enabled['min'] ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_min]"><?php _e( 'Minimum quantity (grouped for this category)', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_min]" id="term_meta[alg_wc_pq_min]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_min'] ) ? esc_attr( $term_meta['alg_wc_pq_min'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a number of minimum allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_min_all_product]"><?php _e( 'Minimum quantity for all product (apply to all products in this category)', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_min_all_product]" id="term_meta[alg_wc_pq_min_all_product]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_min_all_product'] ) ? esc_attr( $term_meta['alg_wc_pq_min_all_product'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a number of minimum allowed quantity for all product in this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<?php } ?>
		<?php if ( $this->is_section_enabled['max'] ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_max]"><?php _e( 'Maximum quantity (grouped for this category)', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_max]" id="term_meta[alg_wc_pq_max]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_max'] ) ? esc_attr( $term_meta['alg_wc_pq_max'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a number of maximum allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_max_all_product]"><?php _e( 'Maximum quantity for all product (apply to all products in this category)', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings. Set -1 to disable.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_max_all_product]" id="term_meta[alg_wc_pq_max_all_product]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_max_all_product'] ) ? esc_attr( $term_meta['alg_wc_pq_max_all_product'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a number of maximum allowed quantity for all product in this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<?php } ?>
		<?php if ( $this->is_section_enabled['step'] ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_step]"><?php _e( 'Quantity step', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_step]" id="term_meta[alg_wc_pq_step]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_step'] ) ? esc_attr( $term_meta['alg_wc_pq_step'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a number of step quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<?php } ?>
		
		<?php if ( $this->is_section_enabled['default'] ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_default]"><?php _e( 'Quantity default', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="number" step="0.000001" min="0" name="term_meta[alg_wc_pq_default]" id="term_meta[alg_wc_pq_default]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_default'] ) ? esc_attr( $term_meta['alg_wc_pq_default'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a number of default quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<?php } ?>
		<?php if ( $this->is_section_enabled['exact_qty_allowed'] ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_exact_qty_allowed]"><?php _e( 'Exact quantity allowed (grouped for this category)', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( sprintf( __( 'Allowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ), true ); ?></label></th>
			<td>
				<input type="text" name="term_meta[alg_wc_pq_exact_qty_allowed]" id="term_meta[alg_wc_pq_exact_qty_allowed]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_exact_qty_allowed'] ) ? esc_attr( $term_meta['alg_wc_pq_exact_qty_allowed'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify numbers of exact allowed quantity for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_exact_qty_allowed_all_product]"><?php _e( 'Exact quantity allowed for all product (apply to all products in this category)', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( sprintf( __( 'Allowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ), true ); ?></label></th>
			<td>
				<input type="text" name="term_meta[alg_wc_pq_exact_qty_allowed_all_product]" id="term_meta[alg_wc_pq_exact_qty_allowed_all_product]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_exact_qty_allowed_all_product'] ) ? esc_attr( $term_meta['alg_wc_pq_exact_qty_allowed_all_product'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify numbers of exact allowed quantity for all product in this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<?php } ?>
		
		<?php /*if ( $this->is_section_enabled['exact_qty_disallowed'] ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_exact_qty_disallowed]"><?php _e( 'Exact quantity disallowed', 'product-quantity-for-woocommerce' ); ?> <?php echo wc_help_tip( sprintf( __( 'Disallowed quantities as comma separated list, e.g.: %s.', 'product-quantity-for-woocommerce' ), '<em>3,7,9</em>' ), true ); ?></label></th>
			<td>
				<input type="text" name="term_meta[alg_wc_pq_exact_qty_disallowed]" id="term_meta[alg_wc_pq_exact_qty_disallowed]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_exact_qty_disallowed'] ) ? esc_attr( $term_meta['alg_wc_pq_exact_qty_disallowed'] ) : ''; ?>">
				<p class="description"><?php _e( '','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<?php }*/ ?>
		<?php if ( $this->is_section_enabled['category_unit'] ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_category_unit_singular]"><?php _e( 'Unit label template: Singular', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="text"  name="term_meta[alg_wc_pq_category_unit_singular]" id="term_meta[alg_wc_pq_category_unit_singular]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_category_unit_singular'] ) ? esc_attr( $term_meta['alg_wc_pq_category_unit_singular'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a singular string for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_category_unit_plural]"><?php _e( 'Unit label template: Plural', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="text"  name="term_meta[alg_wc_pq_category_unit_plural]" id="term_meta[alg_wc_pq_category_unit_plural]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_category_unit_plural'] ) ? esc_attr( $term_meta['alg_wc_pq_category_unit_plural'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a plural string for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<?php } ?>
		
		<?php if ( $this->is_section_enabled['category_price_unit'] ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[alg_wc_pq_category_price_unit]"><?php _e( 'Price Unit', 'product-quantity-for-woocommerce' ); ?> <?php //echo wc_help_tip( __( 'Set 0 to use global settings.', 'product-quantity-for-woocommerce' ), true ); ?></label></th>
			<td>
				<input type="text"  name="term_meta[alg_wc_pq_category_price_unit]" id="term_meta[alg_wc_pq_category_price_unit]" value="<?php echo esc_attr( $term_meta['alg_wc_pq_category_price_unit'] ) ? esc_attr( $term_meta['alg_wc_pq_category_price_unit'] ) : ''; ?>">
				<p class="description"><?php _e( 'Specify a string for this category, this is controlled by Product Quantity plugin','product-quantity-for-woocommerce' ); ?></p>
			</td>
		</tr>
		<?php } ?>
	<?php
	}
	public function pq_save_taxonomy_custom_meta_field( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {
			
			$t_id = $term_id;
			$term_meta = get_option( "taxonomy_product_cat_$t_id" );
			$cat_keys = array_keys( $_POST['term_meta'] );
			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['term_meta'][$key] ) ) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}
			// Save the option array.
			update_option( "taxonomy_product_cat_$t_id", $term_meta );
		}
		
	}

}

endif;

return new Alg_WC_PQ_Category_Metaboxes();
