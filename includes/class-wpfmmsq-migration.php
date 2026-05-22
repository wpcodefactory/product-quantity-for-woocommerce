<?php
/**
 * Product Quantity for WooCommerce - Migration class.
 *
 * @version 5.3.9
 * @since   5.3.4
 * @package WPFMMSQ
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Migration' ) ) :

	class WPFMMSQ_Migration {

		/**
		 * Migration version.
		 *
		 * Bump this when adding new migrations. The stored option
		 * will be compared against this to determine if migration should run.
		 *
		 * @version 5.3.9
		 * @since   5.3.4
		 *
		 * @var string
		 */
		private static $version = '5.3.9';

		/**
		 * Get migration version.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @return string
		 */
		public static function get_version() {
			return self::$version;
		}

		/**
		 * Run all migrations.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 */
		public static function run() {
			if ( get_transient( 'wpfmmsq_migrating' ) ) {
				return;
			}

			set_transient( 'wpfmmsq_migrating', 1, 300 );

			try {
				self::migrate_options();
				self::migrate_post_metas();
				self::migrate_user_metas();
				self::migrate_term_metas();

				// Only update version if all migrations succeed
				update_option( 'wpfmmsq_migration_version', self::$version );
			} finally {
				delete_transient( 'wpfmmsq_migrating' );
			}
		}

		/**
		 * Legacy option keys mapped to new option keys.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @var array<string,string>
		 */
		private static $option_map = array(
			'alg_wc_pq_enabled'                                         => 'wpfmmsq_enabled',
			'alg_wc_pq_version'                                         => 'wpfmmsq_version',
			'alg_wc_pq_min_section_enabled'                             => 'wpfmmsq_min_section_enabled',
			'alg_wc_pq_min'                                             => 'wpfmmsq_min',
			'alg_wc_pq_min_per_item_quantity'                           => 'wpfmmsq_min_per_item_quantity',
			'alg_wc_pq_min_per_item_quantity_per_product'               => 'wpfmmsq_min_per_item_quantity_per_product',
			'alg_wc_pq_min_per_cat_item_quantity_per_product'           => 'wpfmmsq_min_per_cat_item_quantity_per_product',
			'alg_wc_pq_min_per_attribute_item_quantity'                 => 'wpfmmsq_min_per_attribute_item_quantity',
			'alg_wc_pq_max_section_enabled'                             => 'wpfmmsq_max_section_enabled',
			'alg_wc_pq_max'                                             => 'wpfmmsq_max',
			'alg_wc_pq_max_per_item_quantity'                           => 'wpfmmsq_max_per_item_quantity',
			'alg_wc_pq_max_per_item_quantity_per_product'               => 'wpfmmsq_max_per_item_quantity_per_product',
			'alg_wc_pq_max_per_cat_item_quantity_per_product'           => 'wpfmmsq_max_per_cat_item_quantity_per_product',
			'alg_wc_pq_max_per_attribute_item_quantity'                 => 'wpfmmsq_max_per_attribute_item_quantity',
			'alg_wc_pq_step_section_enabled'                            => 'wpfmmsq_step_section_enabled',
			'alg_wc_pq_step'                                            => 'wpfmmsq_step',
			'alg_wc_pq_step_per_product_enabled'                        => 'wpfmmsq_step_per_product_enabled',
			'alg_wc_pq_default_section_enabled'                         => 'wpfmmsq_default_section_enabled',
			'alg_wc_pq_default_per_item_quantity'                       => 'wpfmmsq_default_per_item_quantity',
			'alg_wc_pq_exact_qty_allowed_section_enabled'               => 'wpfmmsq_exact_qty_allowed_section_enabled',
			'alg_wc_pq_exact_qty_allowed'                               => 'wpfmmsq_exact_qty_allowed',
			'alg_wc_pq_exact_qty_disallowed_section_enabled'            => 'wpfmmsq_exact_qty_disallowed_section_enabled',
			'alg_wc_pq_exact_qty_disallowed'                            => 'wpfmmsq_exact_qty_disallowed',
			'alg_wc_pq_qty_dropdown'                                    => 'wpfmmsq_qty_dropdown',
			'alg_wc_pq_replace_woocommerce_quantity_field'              => 'wpfmmsq_replace_woocommerce_quantity_field',
			'alg_wc_pq_qty_dropdown_max_value_fallback'                 => 'wpfmmsq_qty_dropdown_max_value_fallback',
			'alg_wc_pq_qty_dropdown_search_filter'                      => 'wpfmmsq_qty_dropdown_search_filter',
			'alg_wc_pq_qty_dropdown_template_before'                    => 'wpfmmsq_qty_dropdown_template_before',
			'alg_wc_pq_qty_dropdown_template_after'                     => 'wpfmmsq_qty_dropdown_template_after',
			'alg_wc_pq_qty_dropdown_label_template_is_per_product'      => 'wpfmmsq_qty_dropdown_label_template_is_per_product',
			'alg_wc_pq_qty_dropdown_label_template_singular'            => 'wpfmmsq_qty_dropdown_label_template_singular',
			'alg_wc_pq_qty_dropdown_label_template_plural'              => 'wpfmmsq_qty_dropdown_label_template_plural',
			'alg_wc_pq_qty_dropdown_enable_per_category'                => 'wpfmmsq_qty_dropdown_enable_per_category',
			'alg_wc_pq_qty_dropdown_per_category_categories'            => 'wpfmmsq_qty_dropdown_per_category_categories',
			'alg_wc_pq_qty_dropdown_qty_archive_enabled'                => 'wpfmmsq_qty_dropdown_qty_archive_enabled',
			'alg_wc_pq_qty_dropdown_disable_dropdown_on_cart'           => 'wpfmmsq_qty_dropdown_disable_dropdown_on_cart',
			'alg_wc_pq_qty_price_by_qty_enabled'                        => 'wpfmmsq_qty_price_by_qty_enabled',
			'alg_wc_pq_qty_price_by_qty_enabled_variable'               => 'wpfmmsq_qty_price_by_qty_enabled_variable',
			'alg_wc_pq_qty_price_by_qty_position'                       => 'wpfmmsq_qty_price_by_qty_position',
			'alg_wc_pq_qty_price_by_qty_variation_price'                => 'wpfmmsq_qty_price_by_qty_variation_price',
			'alg_wc_pq_qty_price_by_qty_unit_singular'                  => 'wpfmmsq_qty_price_by_qty_unit_singular',
			'alg_wc_pq_qty_price_by_qty_unit_plural'                    => 'wpfmmsq_qty_price_by_qty_unit_plural',
			'alg_wc_pq_qty_price_by_qty_unit_input_enabled'             => 'wpfmmsq_qty_price_by_qty_unit_input_enabled',
			'alg_wc_pq_qty_price_by_qty_template'                       => 'wpfmmsq_qty_price_by_qty_template',
			'alg_wc_pq_qty_price_by_qty_qty_archive_enabled'            => 'wpfmmsq_qty_price_by_qty_qty_archive_enabled',
			'alg_wc_pq_qty_price_by_cat_qty_unit_input_enabled'         => 'wpfmmsq_qty_price_by_cat_qty_unit_input_enabled',
			'alg_wc_pq_qty_price_by_attribute_qty_unit_input_enabled'   => 'wpfmmsq_qty_price_by_attribute_qty_unit_input_enabled',
			'alg_wc_pq_qty_price_by_attribute_qty_unit_input_selected'  => 'wpfmmsq_qty_price_by_attribute_qty_unit_input_selected',
			'alg_wc_pq_qty_price_unit_enabled'                          => 'wpfmmsq_qty_price_unit_enabled',
			'alg_wc_pq_qty_price_unit'                                  => 'wpfmmsq_qty_price_unit',
			'alg_wc_pq_qty_price_unit_category_enabled'                 => 'wpfmmsq_qty_price_unit_category_enabled',
			'alg_wc_pq_qty_price_unit_product_enabled'                  => 'wpfmmsq_qty_price_unit_product_enabled',
			'alg_wc_pq_qty_price_unit_show_archive_enabled'             => 'wpfmmsq_qty_price_unit_show_archive_enabled',
			'alg_wc_pq_qty_price_unit_email_order_item_enabled'         => 'wpfmmsq_qty_price_unit_email_order_item_enabled',
			'alg_wc_pq_qty_info_on_single_product'                      => 'wpfmmsq_qty_info_on_single_product',
			'alg_wc_pq_qty_info_on_single_product_custom_hook'          => 'wpfmmsq_qty_info_on_single_product_custom_hook',
			'alg_wc_pq_qty_info_on_single_product_custom_hook_name'     => 'wpfmmsq_qty_info_on_single_product_custom_hook_name',
			'alg_wc_pq_qty_info_on_single_product_custom_hook_priority' => 'wpfmmsq_qty_info_on_single_product_custom_hook_priority',
			'alg_wc_pq_qty_info_on_single_product_content'              => 'wpfmmsq_qty_info_on_single_product_content',
			'alg_wc_pq_qty_info_on_loop'                                => 'wpfmmsq_qty_info_on_loop',
			'alg_wc_pq_qty_info_on_loop_content'                        => 'wpfmmsq_qty_info_on_loop_content',
			'alg_wc_pq_qty_info_enable_per_category'                    => 'wpfmmsq_qty_info_enable_per_category',
			'alg_wc_pq_qty_info_per_category_categories'                => 'wpfmmsq_qty_info_per_category_categories',
			'alg_wc_pq_decimal_quantities_enabled'                      => 'wpfmmsq_decimal_quantities_enabled',
			'alg_wc_pq_decimal_quantities_admin_order_enabled'          => 'wpfmmsq_decimal_quantities_admin_order_enabled',
			'alg_wc_pq_qty_input_style'                                 => 'wpfmmsq_qty_input_style',
			'alg_wc_pq_qty_hide_update_cart'                            => 'wpfmmsq_qty_hide_update_cart',
			'alg_wc_pq_add_to_cart_validation'                          => 'wpfmmsq_add_to_cart_validation',
			'alg_wc_pq_add_to_cart_validation_step_auto_correct'        => 'wpfmmsq_add_to_cart_validation_step_auto_correct',
			'alg_wc_pq_validate_on_checkout'                            => 'wpfmmsq_validate_on_checkout',
			'alg_wc_pq_stop_from_seeing_checkout'                       => 'wpfmmsq_stop_from_seeing_checkout',
			'alg_wc_pq_cart_notice_enabled'                             => 'wpfmmsq_cart_notice_enabled',
			'alg_wc_pq_cart_notice_type'                                => 'wpfmmsq_cart_notice_type',
			'alg_wc_pq_round_on_add_to_cart'                            => 'wpfmmsq_round_on_add_to_cart',
			'alg_wc_pq_round_with_js'                                   => 'wpfmmsq_round_with_js',
			'alg_wc_pq_force_on_single'                                 => 'wpfmmsq_force_on_single',
			'alg_wc_pq_force_on_loop'                                   => 'wpfmmsq_force_on_loop',
			'alg_wc_pq_force_js_check_min_max'                          => 'wpfmmsq_force_js_check_min_max',
			'alg_wc_pq_force_js_check_min_max_periodically'             => 'wpfmmsq_force_js_check_min_max_periodically',
			'alg_wc_pq_variation_do_load_all'                           => 'wpfmmsq_variation_do_load_all',
			'alg_wc_pq_variation_change'                                => 'wpfmmsq_variation_change',
			'alg_wc_pq_variation_force_on_add_to_cart'                  => 'wpfmmsq_variation_force_on_add_to_cart',
			'alg_wc_pq_all_sold_individually_enabled'                   => 'wpfmmsq_all_sold_individually_enabled',
			'alg_wc_pq_disable_by_order_per_user'                       => 'wpfmmsq_disable_by_order_per_user',
			'alg_wc_pq_disable_urls'                                    => 'wpfmmsq_disable_urls',
			'alg_wc_pq_disable_urls_excluded_pids'                      => 'wpfmmsq_disable_urls_excluded_pids',
			'alg_wc_pq_disable_by_category'                             => 'wpfmmsq_disable_by_category',
			'alg_wc_pq_disable_category_excluded_pids'                  => 'wpfmmsq_disable_category_excluded_pids',
			'alg_wc_pq_save_qty_in_order_item_meta'                     => 'wpfmmsq_save_qty_in_order_item_meta',
			'alg_wc_pq_save_qty_in_order_item_meta_key'                 => 'wpfmmsq_save_qty_in_order_item_meta_key',
			'alg_wc_pq_add_quantity_archive_enabled'                    => 'wpfmmsq_add_quantity_archive_enabled',
			'alg_wc_pq_false_ajax_async'                                => 'wpfmmsq_false_ajax_async',
			'alg_wc_pq_advance_wc_block_api'                            => 'wpfmmsq_advance_wc_block_api',
			'alg_wc_pq_advance_wpc_product_bundle'                      => 'wpfmmsq_advance_wpc_product_bundle',
			'alg_wc_pq_sum_variations'                                  => 'wpfmmsq_sum_variations',
			'alg_wc_pq_admin_columns_enabled'                           => 'wpfmmsq_admin_columns_enabled',
			'alg_wc_pq_admin_columns'                                   => 'wpfmmsq_admin_columns',
			'alg_wc_pq_buy_all_stock_button_enabled'                    => 'wpfmmsq_buy_all_stock_button_enabled',
			'alg_wc_pq_buy_all_stock_button_label'                      => 'wpfmmsq_buy_all_stock_button_label',
			'alg_wc_pq_buy_all_stock_button_class'                      => 'wpfmmsq_buy_all_stock_button_class',
			'alg_wc_pq_buy_all_stock_button_alert_msg'                  => 'wpfmmsq_buy_all_stock_button_alert_msg',
			'alg_wc_pq_admin_options'                                   => 'wpfmmsq_admin_options',
			'alg_wc_pq_min_qty'                                         => 'wpfmmsq_min_qty',
			'alg_wc_pq_max_qty'                                         => 'wpfmmsq_max_qty',
			'alg_wc_pq_qty_step'                                        => 'wpfmmsq_qty_step',
			'alg_wc_pq_max_options'                                     => 'wpfmmsq_max_options',
			'alg_wc_pq_max_cart_total_quantity_options'                 => 'wpfmmsq_max_cart_total_quantity_options',
			'alg_wc_pq_max_cart_total_quantity'                         => 'wpfmmsq_max_cart_total_quantity',
			'alg_wc_pq_max_cart_total_message'                          => 'wpfmmsq_max_cart_total_message',
			'alg_wc_pq_max_per_item_quantity_options'                   => 'wpfmmsq_max_per_item_quantity_options',
			'alg_wc_pq_max_per_item_message'                            => 'wpfmmsq_max_per_item_message',
			'alg_wc_pq_max_cat_cart_total_quantity_options'             => 'wpfmmsq_max_cat_cart_total_quantity_options',
			'alg_wc_pq_max_per_cat_item_quantity_options'               => 'wpfmmsq_max_per_cat_item_quantity_options',
			'alg_wc_pq_max_cat_message'                                 => 'wpfmmsq_max_cat_message',
			'alg_wc_pq_max_per_attribute_quantity_options'              => 'wpfmmsq_max_per_attribute_quantity_options',
			'alg_wc_pq_max_per_attribute_item_quantity_options'         => 'wpfmmsq_max_per_attribute_item_quantity_options',
			'alg_wc_pq_max_per_attribute_selected'                      => 'wpfmmsq_max_per_attribute_selected',
			'alg_wc_pq_max_per_attribute_message'                       => 'wpfmmsq_max_per_attribute_message',
			'alg_wc_pq_max_useful_options'                              => 'wpfmmsq_max_useful_options',
			'alg_wc_pq_default_options'                                 => 'wpfmmsq_default_options',
			'alg_wc_pq_qty_dropdown_options'                            => 'wpfmmsq_qty_dropdown_options',
			'alg_wc_pq_advanced_force_js_check_options'                 => 'wpfmmsq_advanced_force_js_check_options',
			'alg_wc_pq_force_js_check_step'                             => 'wpfmmsq_force_js_check_step',
			'alg_wc_pq_force_js_check_step_periodically'                => 'wpfmmsq_force_js_check_step_periodically',
			'alg_wc_pq_force_js_check_period_ms'                        => 'wpfmmsq_force_js_check_period_ms',
			'alg_wc_pq_advanced_order_item_meta_options'                => 'wpfmmsq_advanced_order_item_meta_options',
			'alg_wc_pq_rounding_options'                                => 'wpfmmsq_rounding_options',
			'alg_wc_pq_cart_options'                                    => 'wpfmmsq_cart_options',
			'alg_wc_pq_cart_advanced_options'                           => 'wpfmmsq_cart_advanced_options',
			'alg_wc_pq_enable_exclude_role_specofic'                    => 'wpfmmsq_enable_exclude_role_specofic',
			'alg_wc_pq_required_user_roles'                             => 'wpfmmsq_required_user_roles',
			'alg_wc_pq_non_required_user_roles'                         => 'wpfmmsq_non_required_user_roles',
			'alg_wc_pq_save_hook'                                       => 'wpfmmsq_save_hook',
		);

		/**
		 * Legacy post meta keys mapped to new post meta keys.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @var array<string,string>
		 */
		private static $post_meta_map = array(
			'_alg_wc_pq_min'                                  => '_wpfmmsq_min',
			'_alg_wc_pq_min_allow_selling_below_stock'        => '_wpfmmsq_min_allow_selling_below_stock',
			'_alg_wc_pq_max'                                  => '_wpfmmsq_max',
			'_alg_wc_pq_step'                                 => '_wpfmmsq_step',
			'_alg_wc_pq_default'                              => '_wpfmmsq_default',
			'_alg_wc_pq_exact_qty_allowed'                    => '_wpfmmsq_exact_qty_allowed',
			'_alg_wc_pq_exact_qty_disallowed'                 => '_wpfmmsq_exact_qty_disallowed',
			'_alg_wc_pq_qty_dropdown_label_template_singular' => '_wpfmmsq_qty_dropdown_label_template_singular',
			'_alg_wc_pq_qty_dropdown_label_template_plural'   => '_wpfmmsq_qty_dropdown_label_template_plural',
			'_alg_wc_pq_qty_price_unit'                       => '_wpfmmsq_qty_price_unit',
			'_alg_wc_pq_qty_price_by_qty_unit_singular'       => '_wpfmmsq_qty_price_by_qty_unit_singular',
			'_alg_wc_pq_qty_price_by_qty_unit_plural'         => '_wpfmmsq_qty_price_by_qty_unit_plural',
		);

		/**
		 * Legacy user meta keys mapped to new user meta keys.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @var array<string,string>
		 */
		private static $user_meta_map = array(
			'alg_wc_pq_closedate' => 'wpfmmsq_closedate',
		);

		/**
		 * Legacy term option bucket prefixes mapped to new prefixes.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @var array<string,string>
		 */
		private static $term_option_prefix_map = array(
			'taxonomy_product_cat_'            => 'wpfmmsq_taxonomy_product_cat_',
			'taxonomy_product_attribute_item_' => 'wpfmmsq_taxonomy_product_attribute_item_',
		);

		/**
		 * Legacy term meta keys mapped to new term meta keys.
		 *
		 * @version 5.3.9
		 * @since   5.3.9
		 *
		 * @var array<string,string>
		 */
		private static $term_meta_key_map = array(
			'alg_wc_pq_min'                                  => 'wpfmmsq_min',
			'alg_wc_pq_max'                                  => 'wpfmmsq_max',
			'alg_wc_pq_step'                                 => 'wpfmmsq_step',
			'alg_wc_pq_step_all_product'                     => 'wpfmmsq_step_all_product',
			'alg_wc_pq_default'                              => 'wpfmmsq_default',
			'alg_wc_pq_min_all_product'                      => 'wpfmmsq_min_all_product',
			'alg_wc_pq_max_all_product'                      => 'wpfmmsq_max_all_product',
			'alg_wc_pq_exact_qty_allowed'                    => 'wpfmmsq_exact_qty_allowed',
			'alg_wc_pq_exact_qty_disallowed'                 => 'wpfmmsq_exact_qty_disallowed',
			'alg_wc_pq_exact_qty_allowed_all_product'        => 'wpfmmsq_exact_qty_allowed_all_product',
			'alg_wc_pq_exact_qty_disallowed_all_product'     => 'wpfmmsq_exact_qty_disallowed_all_product',
			'alg_wc_pq_category_unit_singular'               => 'wpfmmsq_category_unit_singular',
			'alg_wc_pq_category_unit_plural'                 => 'wpfmmsq_category_unit_plural',
			'alg_wc_pq_price_by_qty_attribute_unit_singular' => 'wpfmmsq_price_by_qty_attribute_unit_singular',
			'alg_wc_pq_price_by_qty_attribute_unit_plural'   => 'wpfmmsq_price_by_qty_attribute_unit_plural',
			'alg_wc_pq_category_price_unit'                  => 'wpfmmsq_category_price_unit',
		);

		/**
		 * Get migration version.
		 *
		 * @version 5.3.6
		 * @since   5.3.4
		 *
		 * @return string
		 */
		private static function migrate_options() {
			global $wpdb;
			$old_keys_to_delete = array();
			foreach ( self::get_option_mapping() as $old_key => $new_key ) {
				$old_row    = $wpdb->get_row( $wpdb->prepare(
					"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
					$old_key
				) );
				$old_exists = ( $old_row !== null );
				$old_val    = $old_exists ? maybe_unserialize( $old_row->option_value ) : null;
				$new_exists = $wpdb->get_var( $wpdb->prepare(
					"SELECT option_id FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
					$new_key
				) );
				if ( $old_exists && ! $new_exists ) {
					update_option( $new_key, $old_val );
					$old_keys_to_delete[] = $old_key;
				}
			}

			$generic_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
					'alg_wc_pq_%'
				)
			);

			foreach ( $generic_rows as $row ) {
				$old_key = $row->option_name;
				$new_key = 'wpfmmsq_' . substr( $old_key, strlen( 'alg_wc_pq_' ) );
				if ( empty( $new_key ) || $new_key === $old_key ) {
					continue;
				}
				$new_exists = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT option_id FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
						$new_key
					)
				);
				if ( ! $new_exists ) {
					update_option( $new_key, maybe_unserialize( $row->option_value ) );
					$old_keys_to_delete[] = $old_key;
				}
			}

			// Delete old options after migration
			foreach ( $old_keys_to_delete as $old_key ) {
				delete_option( $old_key );
			}
		}

		/**
		 * Migrate post meta.
		 *
		 * @version 5.3.6
		 * @since   5.3.4
		 */
		private static function migrate_post_metas() {
			global $wpdb;
			$to_delete = array();
			foreach ( self::get_post_meta_mapping() as $old_key => $new_key ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
						$old_key
					)
				);
				foreach ( $results as $row ) {
					$exists = $wpdb->get_var( $wpdb->prepare(
						"SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s LIMIT 1",
						$row->post_id, $new_key
					) );
					if ( ! $exists ) {
						update_post_meta( $row->post_id, $new_key, maybe_unserialize( $row->meta_value ) );
						$to_delete[] = array( 'post_id' => $row->post_id, 'meta_key' => $old_key );
					}
				}
			}
			// Delete old post meta after migration
			foreach ( $to_delete as $item ) {
				delete_post_meta( $item['post_id'], $item['meta_key'] );
			}
		}

		/**
		 * Migrate user meta.
		 *
		 * @version 5.3.6
		 * @since   5.3.4
		 */
		private static function migrate_user_metas() {
			global $wpdb;
			$to_delete = array();
			foreach ( self::get_user_meta_mapping() as $old_key => $new_key ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s",
						$old_key
					)
				);
				foreach ( $results as $row ) {
					$exists = $wpdb->get_var( $wpdb->prepare(
						"SELECT umeta_id FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s LIMIT 1",
						$row->user_id, $new_key
					) );
					if ( ! $exists ) {
						update_user_meta( $row->user_id, $new_key, maybe_unserialize( $row->meta_value ) );
						$to_delete[] = array( 'user_id' => $row->user_id, 'meta_key' => $old_key );
					}
				}
			}
			// Delete old user meta after migration
			foreach ( $to_delete as $item ) {
				delete_user_meta( $item['user_id'], $item['meta_key'] );
			}
		}

		/**
		 * Migrate term option buckets.
		 *
		 * @version 5.3.9
		 * @since   5.3.4
		 */
		private static function migrate_term_metas() {
			global $wpdb;
			$to_delete = array();
			foreach ( self::get_term_option_prefix_map() as $old_prefix => $new_prefix ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
						$old_prefix . '%'
					)
				);
				foreach ( $results as $row ) {
					$suffix = substr( $row->option_name, strlen( $old_prefix ) );
					if ( '' === $suffix ) {
						continue;
					}
					$new_key = $new_prefix . $suffix;
					if ( false === get_option( $new_key, false ) ) {
						update_option( $new_key, maybe_unserialize( $row->option_value ) );
						$to_delete[] = $row->option_name;
					}
				}
			}
			// Delete old term options after migration
			foreach ( $to_delete as $old_key ) {
				delete_option( $old_key );
			}

			// Migrate legacy keys inside term meta option buckets.
			foreach ( self::get_term_option_prefix_map() as $new_prefix ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
						$new_prefix . '%'
					)
				);

				foreach ( $results as $row ) {
					$term_meta = maybe_unserialize( $row->option_value );
					if ( ! is_array( $term_meta ) ) {
						continue;
					}

					$updated = false;
					foreach ( self::get_term_meta_key_mapping() as $old_meta_key => $new_meta_key ) {
						if ( isset( $term_meta[ $old_meta_key ] ) && ! isset( $term_meta[ $new_meta_key ] ) ) {
							$term_meta[ $new_meta_key ] = $term_meta[ $old_meta_key ];
							unset( $term_meta[ $old_meta_key ] );
							$updated = true;
						}
					}

					if ( $updated ) {
						update_option( $row->option_name, $term_meta );
					}
				}
			}
		}

		/**
		 * Legacy-to-new option mapping.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @return array<string,string>
		 */
		private static function get_option_mapping() {
			return self::$option_map;
		}

		/**
		 * Legacy-to-new post meta mapping.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @return array<string,string>
		 */
		private static function get_post_meta_mapping() {
			return self::$post_meta_map;
		}

		/**
		 * Legacy-to-new user meta mapping.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @return array<string,string>
		 */
		private static function get_user_meta_mapping() {
			return self::$user_meta_map;
		}

		/**
		 * Legacy-to-new term option prefix mapping.
		 *
		 * @version 5.3.4
		 * @since   5.3.4
		 *
		 * @return array<string,string>
		 */
		private static function get_term_option_prefix_map() {
			return self::$term_option_prefix_map;
		}

		/**
		 * Legacy-to-new term meta key mapping.
		 *
		 * @version 5.3.9
		 * @since   5.3.8
		 *
		 * @return array<string,string>
		 */
		private static function get_term_meta_key_mapping() {
			return self::$term_meta_key_map;
		}

	}

endif;