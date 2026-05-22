<?php
/**
 * Product Quantity for WooCommerce - Pro Class
 *
 * @version 5.3.9
 * @since   1.8.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Free' ) ) :

	class WPFMMSQ_Free {

		public $attribute_taxonomies = array();

		/**
		 * Constructor.
		 *
		 * @version 5.3.9
		 * @since   1.8.0
		 *
		 * @todo    [dev] maybe move here: `require_once( 'includes/settings/class-wpfmmsq-metaboxes.php' );`
		 */
		function __construct() {

			add_filter( 'wpfmmsq_quantity_step_per_product', array( $this, 'quantity_step_per_product' ) );
			add_filter( 'wpfmmsq_quantity_step_per_product_value', array( $this, 'quantity_step_per_product_value' ), 10, 3 );


			add_filter( 'wpfmmsq_per_item_qty_per_product', array( $this, 'per_item_quantity_per_product' ), 10, 2 );
			add_filter( 'wpfmmsq_per_item_qty_per_product_value', array( $this, 'per_item_quantity_per_product_value' ), 10, 3 );

		}

		/**
		 * quantity_step_per_product.
		 *
		 * @version 5.3.4
		 * @since   1.8.0
		 */
		function quantity_step_per_product( $value ) {
			return get_option( 'wpfmmsq_step_per_product_enabled', 'no' );
		}

		/**
		 * per_item_quantity_per_product.
		 *
		 * @version 5.3.4
		 * @since   1.8.0
		 */
		function per_item_quantity_per_product( $value, $min_or_max ) {
			return get_option( 'wpfmmsq_' . $min_or_max . '_per_item_quantity_per_product', 'no' );
		}

		/**
		 * quantity_step_per_product_value.
		 *
		 * @version 5.3.9
		 * @since   1.8.0
		 */
		function quantity_step_per_product_value( $value, $product_id, $from_shortcode = false ) {
			$product = wc_get_product( $product_id );
			$step = get_post_meta( $product_id, '_' . 'wpfmmsq_step', true );

			if ( empty( $step ) ) {
				return 0;
			}

			return $step;
		}

		/**
		 * per_item_quantity_per_product_value.
		 *
		 * @version 5.3.9
		 * @since   1.8.0
		 */
		function per_item_quantity_per_product_value( $value, $product_id, $min_or_max ) {
			return (float) get_post_meta( $product_id, '_' . 'wpfmmsq_' . $min_or_max, true );
		}

	}

endif;

return new WPFMMSQ_Free();
