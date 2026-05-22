<?php
/**
 * Product Quantity for WooCommerce - Settings
 *
 * @version 5.3.9
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Settings' ) ) :

	class WPFMMSQ_Settings extends WC_Settings_Page {

		/**
		 * Constructor.
		 *
		 * @version 5.3.4
		 * @since   1.0.0
		 */
		function __construct() {
			$this->id    = 'wpfmmsq';
			$this->label = __( 'Product Quantity', 'product-quantity-for-woocommerce' );
			parent::__construct();

			add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unsanitize_option' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'save_empty_option_value' ), PHP_INT_MAX, 2 );
		}

		/**
		 * maybe_unsanitize_option.
		 *
		 * @version 5.3.4
		 * @since   1.2.0
		 */
		function maybe_unsanitize_option( $value, $option, $raw_value ) {
			$has_raw_flag = ! empty( $option['wpfmmsq_raw'] ) || ! empty( $option['alg_wc_pq_raw'] );

			return ( $has_raw_flag ? $raw_value : $value );
		}

		/**
		 * Save an empty option value.
		 *
		 * @version 5.3.4
		 * @since   4.7.0
		 */
		function save_empty_option_value( $value, $option ) {

			if ( ! isset( $option['alg_empty_value'] ) ) {
				return $value;
			}

			$sanitized_value = sanitize_text_field( $value );

			// If the sanitized value is not empty, return the value
			if ( '' !== $sanitized_value ) {
				return $value;
			}

			return sanitize_text_field( $option['alg_empty_value'] );
		}

		/**
		 * get_advertisement.
		 *
		 * @version 5.3.4
		 * @since   4.9.7
		 */
		function get_advertisement() {
			ob_start();
			?>
			<div class="wpfmmsq_right_ad">
				<div class="wpfmmsq-sidebar__section">
					<div class="wpfmmsq_name_heading">
						<img class="wpfmmsq_resize" src="<?php echo esc_url( plugins_url( '../../assets/images/icon-128x128.png', __FILE__ ) ); ?>">
						<p class="wpfmmsq_text">Enjoying the plugin? Unleash
							its
							full potential with the premium version, it allows
							you
							to: </p>
					</div>
					<ul>
						<li>
							<strong>Set Min/Max/Step quantity values per
								category or
								product, define values the way you
								want!</strong>
						</li>
						<li>
							<strong>Define Allowed/Disallowed quantities per
								category or product.</strong>
						</li>
						<li>
							<strong>Customize labels per product on quantity
								dropdown menu.</strong>
						</li>
						<li>
							<strong>Give products a “Green Pass” to bypass all
								plugin settings!</strong>
						</li>
						<li>
							<strong>And much more!</strong>
						</li>
					</ul>
					<p style="text-align:center">
						<a id="wpfmmsq-premium-button" class="wpfmmsq-button-upsell" href="https://wpfactory.com/item/product-quantity-for-woocommerce" target="_blank">Get
							Min Max Step Quantity Limits Manager for WooCommerce
							Pro</a>
					</p>
					<br>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * get_settings.
		 *
		 * @version 5.3.4
		 * @since   1.0.0
		 */
		function get_settings() {
			global $current_section;

			$advertisement = array(
				array(
					'type' => 'title',
					'desc' => apply_filters( 'wpfmmsq_advertise', $this->get_advertisement() ),
					'id'   => $this->id . '_' . $current_section . '_options_ad_section',
				)
			);

			$return = array_merge(
				apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ),
				array(
					array(
						'title' => __( 'Reset Section', 'product-quantity-for-woocommerce' ),
						'type'  => 'title',
						'id'    => $this->id . '_' . $current_section . '_reset_options',
					),
					array(
						'title'   => __( 'Reset section settings', 'product-quantity-for-woocommerce' ),
						'desc'    => '<strong>' . __( 'Reset', 'product-quantity-for-woocommerce' ) . '</strong>',
						'id'      => $this->id . '_' . $current_section . '_reset',
						'default' => 'no',
						'type'    => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => $this->id . '_' . $current_section . '_reset_options',
					),
				)
			);

			return array_merge( $advertisement, $return );
		}

		/**
		 * maybe_reset_settings.
		 *
		 * @version 5.3.9
		 * @since   1.0.0
		 */
		function maybe_reset_settings() {
			global $current_section;
			if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
				foreach ( $this->get_settings() as $value ) {
					if ( isset( $value['id'] ) ) {
						$id = explode( '[', $value['id'] );
						delete_option( $id[0] );
					}
				}
				add_action( 'admin_notices', array( $this, 'admin_notice_settings_reset' ) );
			}

			if ( count( $this->get_settings() ) > 0 ) {
				foreach ( $this->get_settings() as $value ) {
					if ( isset( $value['id'] ) && 'wpfmmsq_disable_urls' == $value['id'] ) {
						$ids  = [];
						$urls = ( isset( $_POST['wpfmmsq_disable_urls'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpfmmsq_disable_urls'] ) ) : '' );
						$urls = array_map( 'trim', explode( PHP_EOL, $urls ) );
						if ( ! empty( $urls ) && count( $urls ) ) {
							foreach ( $urls as $url ) {
								$id = url_to_postid( $url );
								if ( ! empty( $id ) ) {
									$ids[]              = $id;
									$main_product       = wc_get_product( $id );
									$variation_products = $main_product->get_children();
									if ( ! empty( $variation_products ) && count( $variation_products ) > 0 ) {
										foreach ( $variation_products as $var ) {
											$ids[] = $var;
										}
									}
								}
							}
							array_unique( $ids );
						}
						if ( count( $ids ) > 0 ) {
							update_option( 'wpfmmsq_disable_urls_excluded_pids', implode( ',', $ids ) );
						} else {
							update_option( 'wpfmmsq_disable_urls_excluded_pids', '' );
						}
					}

					if ( isset( $value['id'] ) && 'wpfmmsq_disable_by_category' == $value['id'] ) {
						$c_p_ids      = [];
						$category_ids = ( isset( $_POST['wpfmmsq_disable_by_category'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['wpfmmsq_disable_by_category'] ) ) : array() );

						$pids = $this->get_product_ids( $category_ids );

						if ( $pids && ! empty( $pids ) && count( $pids ) ) {
							foreach ( $pids as $pid ) {
								if ( ! empty( $pid ) ) {
									$c_p_ids[]          = $pid;
									$main_product       = wc_get_product( $pid );
									$variation_products = $main_product->get_children();
									if ( ! empty( $variation_products ) && count( $variation_products ) > 0 ) {
										foreach ( $variation_products as $var ) {
											$c_p_ids[] = $var;
										}
									}
								}
							}
							array_unique( $c_p_ids );
						}
						if ( count( $c_p_ids ) > 0 ) {
							update_option( 'wpfmmsq_disable_category_excluded_pids', implode( ',', $c_p_ids ) );
						} else {
							update_option( 'wpfmmsq_disable_category_excluded_pids', '' );
						}
					}

				}
			}
		}

		/**
		 * admin_notice_settings_reset.
		 *
		 * @version 5.3.4
		 * @since   1.3.0
		 */
		function admin_notice_settings_reset() {
			echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
				 esc_html__( 'Your settings have been reset.', 'product-quantity-for-woocommerce' ) . '</strong></p></div>';
		}

		/**
		 * get_product_ids.
		 *
		 * @version 5.3.4
		 * @since   1.3.0
		 */
		function get_product_ids( $category_ids ) {
			if ( empty( $category_ids ) || ! is_array( $category_ids ) ) {
				return false;
			}
			$exclude_id_args = array(
				'post_status'    => 'publish',
				'post_type'      => 'product',
				'fields'         => 'ids',
				'posts_per_page' => '-1',
				'tax_query'      => array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $category_ids,
						'operator' => 'IN'
					)
				)
			);

			$exclude_id_query = new WP_Query( $exclude_id_args );
			wp_reset_postdata();
			if ( ! empty( $exclude_id_query ) && $exclude_id_query->found_posts > 0 ) {
				return $exclude_id_query->posts;
			}

			return false;
		}

		/**
		 * Save settings.
		 *
		 * @version 5.3.4
		 * @since   1.0.0
		 */
		function save() {
			parent::save();
			global $current_section;
			do_action( 'wpfmmsq_settings_after_save', $current_section );
			$this->maybe_reset_settings();
		}

	}

endif;

return new WPFMMSQ_Settings();
