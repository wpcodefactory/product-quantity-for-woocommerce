<?php
/**
 * Product Quantity for WooCommerce - Functions - Core
 *
 * @version 5.3.8
 * @since   1.6.3
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wpfmmsq_check_if_active_plugin' ) ) {
	/**
	 * wpfmmsq_check_if_active_plugin
	 *
	 * @version 5.3.1
	 * @since   1.6.3
	 * @todo    [dev] add fallback for multisite
	 */
	function wpfmmsq_check_if_active_plugin( $plugin_dir, $plugin_file, $active_plugins ) {
		$plugin = $plugin_dir . '/' . $plugin_file;
		if ( ! in_array( $plugin, $active_plugins ) && ! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) ) ) {
			// Fallback check
			foreach ( $active_plugins as $active_plugin ) {
				$active_plugin = explode( '/', $active_plugin );
				$active_plugin = $active_plugin[ count( $active_plugin ) - 1 ];
				if ( $plugin_file === $active_plugin ) {
					// Active
					return true;
				}
			}

			// Not active
			return false;
		}

		// Active
		return true;
	}
}

if ( ! function_exists( 'wpfmmsq_do_disable' ) ) {
	/**
	 * wpfmmsq_do_disable
	 *
	 * @version 5.3.1
	 * @since   1.6.3
	 */
	function wpfmmsq_do_disable( $basename ) {
		// Get active plugins
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
		// Check if WooCommerce is active, if not - disable
		if ( ! wpfmmsq_check_if_active_plugin( 'woocommerce', 'woocommerce.php', $active_plugins ) ) {
			return true;
		}
		if ( 'product-quantity-for-woocommerce.php' === $basename ) {
			// Check if Pro is active, if yes - disable
			if ( wpfmmsq_check_if_active_plugin( 'product-quantity-for-woocommerce-pro', 'product-quantity-for-woocommerce-pro.php', $active_plugins ) ) {
				return true;
			}
		}

		// Do not disable
		return false;
	}
}

if ( ! function_exists( 'wpfmmsq_wc_get_attribute_taxonomies' ) ) {
	/**
	 * wpfmmsq_wc_get_attribute_taxonomies
	 *
	 * @version 5.3.4
	 * @since   1.6.3
	 */
	function wpfmmsq_wc_get_attribute_taxonomies() {
		global $wpdb;

		$cache_key            = 'wpfmmsq_attribute_taxonomies';
		$attribute_taxonomies = get_transient( $cache_key );

		if ( false === $attribute_taxonomies ) {
			$raw_attribute_taxonomies = $wpdb->get_results(
				"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC"
			);

			$attribute_taxonomies = array();

			foreach ( $raw_attribute_taxonomies as $result ) {
				$attribute_taxonomies[ $result->attribute_id ] = $result;
			}

			set_transient( $cache_key, $attribute_taxonomies, DAY_IN_SECONDS );
		}

		return $attribute_taxonomies;
	}
}

if ( ! function_exists( 'wpfmmsq_wc_attribute_taxonomy_name' ) ) {
	/**
	 * Get a product attribute name.
	 *
	 * @version 5.3.1
	 * @since   5.3.1
	 *
	 * @param   string  $attribute_name  Attribute name.
	 *
	 * @return string
	 */
	function wpfmmsq_wc_attribute_taxonomy_name( $attribute_name ) {
		return $attribute_name ? 'pa_' . wpfmmsq_wc_sanitize_taxonomy_name( $attribute_name ) : '';
	}
}

if ( ! function_exists( 'wpfmmsq_wc_sanitize_taxonomy_name' ) ) {
	/**
	 * Sanitize taxonomy names. Slug format (no spaces, lowercase).
	 * Urldecode is used to reverse munging of UTF8 characters.
	 *
	 * @version 5.3.1
	 * @since   5.3.1
	 *
	 * @param   string  $taxonomy  Taxonomy name.
	 *
	 * @return string
	 */
	function wpfmmsq_wc_sanitize_taxonomy_name( $taxonomy ) {
		return apply_filters( 'sanitize_taxonomy_name', urldecode( sanitize_title( urldecode( $taxonomy ) ) ), $taxonomy );
	}
}

if ( ! function_exists( 'wpfmmsq_enqueue_script' ) ) {
	/**
	 * wpfmmsq_enqueue_script.
	 *
	 * @version 5.3.1
	 * @since   5.1.4
	 */
	function wpfmmsq_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $args = array() ) {
		if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
			$src = str_replace( '.js', '.min.js', $src );
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $args );
	}
}

if ( ! function_exists( 'wpfmmsq_get_term_meta_value' ) ) {
	/**
	 * Get a term meta array value safely.
	 *
	 * @version 5.3.8
	 * @since   5.3.8
	 *
	 * @param   array   $term_meta  Term meta array.
	 * @param   string  $key        Meta key.
	 * @param   string  $default    Fallback value.
	 *
	 * @return string
	 */
	function wpfmmsq_get_term_meta_value( $term_meta, $key, $default = '' ) {
		return ( is_array( $term_meta ) && isset( $term_meta[ $key ] ) ? (string) $term_meta[ $key ] : $default );
	}
}