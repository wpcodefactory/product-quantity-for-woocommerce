<?php
/**
 * Product Quantity for WooCommerce - Admin Class
 *
 * @version 5.3.9
 * @since   1.6.1
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Admin' ) ) :

	class WPFMMSQ_Admin {

		/**
		 * Constructor.
		 *
		 * @version 5.3.9
		 * @since   1.6.1
		 */
		function __construct() {
			if ( 'yes' === get_option( 'wpfmmsq_admin_columns_enabled', 'no' ) ) {
				add_filter( 'manage_edit-product_columns', array( $this, 'add_admin_product_columns' ), PHP_INT_MAX );
				add_action( 'manage_product_posts_custom_column', array( $this, 'render_admin_product_columns' ), PHP_INT_MAX );
			}
		}

		/**
		 * add_admin_product_columns.
		 *
		 * @version 5.3.9
		 * @since   1.6.1
		 * @todo    [dev] `[alg_wc_pq_min_exact_allowed_product_qty]`
		 * @todo    [dev] `[alg_wc_pq_min_exact_disallowed_product_qty]`
		 */
		function add_admin_product_columns( $columns ) {
			$all_columns    = array(
				'wpfmmsq_min_qty'  => __( 'Min Qty', 'product-quantity-for-woocommerce' ),
				'wpfmmsq_max_qty'  => __( 'Max Qty', 'product-quantity-for-woocommerce' ),
				'wpfmmsq_qty_step' => __( 'Qty Step', 'product-quantity-for-woocommerce' ),
			);
			$columns_to_add = get_option( 'wpfmmsq_admin_columns', array() );
			if ( empty( $columns_to_add ) ) {
				$columns_to_add = array_keys( $all_columns );
			}
			foreach ( $columns_to_add as $column_to_add ) {
				if ( isset( $all_columns[ $column_to_add ] ) ) {
					$columns[ $column_to_add ] = $all_columns[ $column_to_add ];
				}
			}

			return $columns;
		}

		/**
		 * render_admin_product_columns.
		 *
		 * @version 5.3.9
		 * @since   1.6.1
		 */
		function render_admin_product_columns( $column ) {
			switch ( $column ) {
				case 'wpfmmsq_min_qty':
					echo do_shortcode( '[wpfmmsq_min_product_qty]' );
					break;
				case 'wpfmmsq_max_qty':
					echo do_shortcode( '[wpfmmsq_max_product_qty]' );
					break;
				case 'wpfmmsq_qty_step':
					echo do_shortcode( '[wpfmmsq_product_qty_step]' );
					break;
			}
		}

	}

endif;

return new WPFMMSQ_Admin();
