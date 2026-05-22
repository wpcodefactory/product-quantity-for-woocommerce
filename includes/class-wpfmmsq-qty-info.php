<?php
/**
 * Product Quantity for WooCommerce - Quantity Info Class
 *
 * @version 5.3.4
 * @since   1.7.0
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Quantity_Info' ) ) :

	class WPFMMSQ_Quantity_Info {

		/**
		 * qty_info_default_content
		 *
		 * @since 4.6.0
		 * @var   string
		 */
		public $qty_info_default_content = null;

		/**
		 * Constructor.
		 *
		 * @version 5.3.9
		 * @since   1.7.0
		 */
		function __construct() {

			if ( ! isset( $_GET['et_fb'] ) && ! isset( $_GET['et_bfb'] ) ) {

				// Quantity info on single product page
				if ( 'yes' === get_option( 'wpfmmsq_qty_info_on_single_product', 'no' ) || 'yes' === get_option( 'wpfmmsq_qty_info_on_single_product_custom_hook', 'no' ) ) {
					if ( 'yes' === get_option( 'wpfmmsq_qty_info_on_single_product', 'no' ) ) {
						add_action( 'woocommerce_single_product_summary', array( $this, 'output_qty_info_on_single_product' ), 31 );
					}

					if ( 'yes' === get_option( 'wpfmmsq_qty_info_on_single_product_custom_hook', 'no' ) ) {
						$hook          = get_option( 'wpfmmsq_qty_info_on_single_product_custom_hook_name', 'woocommerce_single_product_summary' );
						$hook_priority = get_option( 'wpfmmsq_qty_info_on_single_product_custom_hook_priority', 31 );
						add_action( $hook, array( $this, 'output_qty_info_on_single_product' ), $hook_priority );
					}
				}

				// Quantity info on archives
				if ( 'yes' === get_option( 'wpfmmsq_qty_info_on_loop', 'no' ) ) {
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'output_qty_info_on_loop' ), 11 );
				}

				// Qty info default content
				$this->qty_info_default_content = '<p>' .
				                                  '[wpfmmsq_min_product_qty before="Minimum quantity is <strong>" after="</strong><br>"]' .
				                                  '[wpfmmsq_max_product_qty before="Maximum quantity is <strong>" after="</strong><br>"]' .
				                                  '[wpfmmsq_product_qty_step before="Step is <strong>" after="</strong><br>"]' .
				                                  '</p>';
			}
		}

		/**
		 * output_qty_info_on_single_product.
		 *
		 * @version 5.3.4
		 * @since   1.6.0
		 * @todo    [dev] (important) info: position & priority (same for `loop`)
		 * @todo    [dev] info: variations (same for `loop`)
		 */
		function output_qty_info_on_single_product() {
			if ( ! $this->wpfmmsq_qty_info_is_disable() ) {
				echo wp_kses_post( do_shortcode( get_option( 'wpfmmsq_qty_info_on_single_product_content', $this->qty_info_default_content ) ) );
			}
		}

		/**
		 * output_qty_info_on_loop.
		 *
		 * @version 5.3.4
		 * @since   1.6.0
		 */
		function output_qty_info_on_loop() {
			if ( ! $this->wpfmmsq_qty_info_is_disable() ) {
				echo wp_kses_post( do_shortcode( get_option( 'wpfmmsq_qty_info_on_loop_content', $this->qty_info_default_content ) ) );
			}
		}

		/**
		 * wpfmmsq_qty_info_is_disable.
		 *
		 * @version 5.3.4
		 * @since   1.3.3
		 */
		function wpfmmsq_qty_info_is_disable() {
			global $product;
			if ( ! $product ) {
				return false;
			}

			if ( wpfmmsq()->core->disable_product_id_by_url_option( $product->get_id() ) ) {
				return true;
			}

			$alg_wc_pq_qty_info_enable_per_category     = get_option( 'wpfmmsq_qty_info_enable_per_category', 'no' );
			$alg_wc_pq_qty_info_per_category_categories = get_option( 'wpfmmsq_qty_info_per_category_categories', array() );

			if ( $alg_wc_pq_qty_info_enable_per_category == 'yes' ) {
				if ( ! empty( $alg_wc_pq_qty_info_per_category_categories ) ) {
					if ( ! empty( $product ) && $product->get_id() > 0 && ! is_admin() ) {
						$product_id       = $product->get_id();
						$product_cats_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
						if ( ! empty( $product_cats_ids ) ) {
							foreach ( $product_cats_ids as $cat_id ) {
								if ( in_array( $cat_id, $alg_wc_pq_qty_info_per_category_categories ) ) {
									return true;
								}
							}
						}

					}
				}
			}

			return false;
		}

	}

endif;

return new WPFMMSQ_Quantity_Info();
