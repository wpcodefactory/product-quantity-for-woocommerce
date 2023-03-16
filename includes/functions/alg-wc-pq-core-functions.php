<?php
/**
 * Product Quantity for WooCommerce - Functions - Core
 *
 * @version 1.6.3
 * @since   1.6.3
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'alg_wc_pq_check_if_active_plugin' ) ) {
	/**
	 * alg_wc_pq_check_if_active_plugin
	 *
	 * @version 1.6.3
	 * @since   1.6.3
	 * @todo    [dev] add fallback for multisite
	 */
	function alg_wc_pq_check_if_active_plugin( $plugin_dir, $plugin_file, $active_plugins ) {
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

if ( ! function_exists( 'alg_wc_pq_do_disable' ) ) {
	/**
	 * alg_wc_pq_do_disable
	 *
	 * @version 1.6.3
	 * @since   1.6.3
	 */
	function alg_wc_pq_do_disable( $basename ) {
		// Get active plugins
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
		// Check if WooCommerce is active, if not - disable
		if ( ! alg_wc_pq_check_if_active_plugin( 'woocommerce', 'woocommerce.php', $active_plugins ) ) {
			return true;
		}
		if ( 'product-quantity-for-woocommerce.php' === $basename ) {
			// Check if Pro is active, if yes - disable
			if ( alg_wc_pq_check_if_active_plugin( 'product-quantity-for-woocommerce-pro', 'product-quantity-for-woocommerce-pro.php', $active_plugins ) ) {
				return true;
			}
		}
		// Do not disable
		return false;
	}
}

if ( ! function_exists( 'alg_wc_pq_wc_get_attribute_taxonomies' ) ) {
	/**
	 * alg_wc_pq_wc_get_attribute_taxonomies
	 *
	 * @version 1.6.3
	 * @since   1.6.3
	 */
	function alg_wc_pq_wc_get_attribute_taxonomies() {
		global $wpdb;

		$raw_attribute_taxonomies = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC;" );
		
		// Index by ID for easer lookups.
		$attribute_taxonomies = array();

		foreach ( $raw_attribute_taxonomies as $result ) {
			$attribute_taxonomies[ $result->attribute_id ] = $result;
		}
		
		return $attribute_taxonomies;
	}
}

if ( ! function_exists( 'alg_pq_wc_attribute_taxonomy_name' ) ) {
	/**
	 * Get a product attribute name.
	 *
	 * @param string $attribute_name Attribute name.
	 * @return string
	 */
	function alg_pq_wc_attribute_taxonomy_name( $attribute_name ) {
		return $attribute_name ? 'pa_' . alg_pq_wc_sanitize_taxonomy_name( $attribute_name ) : '';
	}
}

if ( ! function_exists( 'alg_pq_wc_sanitize_taxonomy_name' ) ) {
	/**
	 * Sanitize taxonomy names. Slug format (no spaces, lowercase).
	 * Urldecode is used to reverse munging of UTF8 characters.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return string
	 */
	function alg_pq_wc_sanitize_taxonomy_name( $taxonomy ) {
		return apply_filters( 'sanitize_taxonomy_name', urldecode( sanitize_title( urldecode( $taxonomy ) ) ), $taxonomy );
	}
}