<?php
/*
Plugin Name: Product Quantity for WooCommerce
Plugin URI: https://wpfactory.com/item/product-quantity-for-woocommerce/
Description: Manage product quantity in WooCommerce, beautifully.
Version: 1.8.1
Author: WPWhale
Author URI: https://wpwhale.com
Text Domain: product-quantity-for-woocommerce
Domain Path: /langs
Copyright: © 2019 WPWhale
WC tested up to: 3.8
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'includes/functions/alg-wc-pq-core-functions.php' );

if ( alg_wc_pq_do_disable( basename( __FILE__ ) ) ) {
	return;
}

if ( ! class_exists( 'Alg_WC_PQ' ) ) :

/**
 * Main Alg_WC_PQ Class
 *
 * @class   Alg_WC_PQ
 * @version 1.8.0
 * @since   1.0.0
 */
final class Alg_WC_PQ {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '1.8.1';

	/**
	 * @var   Alg_WC_PQ The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_PQ Instance
	 *
	 * Ensures only one instance of Alg_WC_PQ is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_PQ - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_PQ Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'product-quantity-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Pro
		if ( 'product-quantity-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-pq-pro.php' );
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function includes() {
		// Core
		$this->core = require_once( 'includes/class-alg-wc-pq-core.php' );
	}

	/**
	 * admin.
	 *
	 * @version 1.7.0
	 * @since   1.3.0
	 */
	function admin() {
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once( 'includes/settings/class-alg-wc-pq-metaboxes.php' );
		require_once( 'includes/settings/class-alg-wc-pq-settings-section.php' );
		$this->settings = array();
		$this->settings['general']      = require_once( 'includes/settings/class-alg-wc-pq-settings-general.php' );
		$this->settings['min']          = require_once( 'includes/settings/class-alg-wc-pq-settings-min.php' );
		$this->settings['max']          = require_once( 'includes/settings/class-alg-wc-pq-settings-max.php' );
		$this->settings['step']         = require_once( 'includes/settings/class-alg-wc-pq-settings-step.php' );
		$this->settings['fixed']        = require_once( 'includes/settings/class-alg-wc-pq-settings-fixed.php' );
		$this->settings['dropdown']     = require_once( 'includes/settings/class-alg-wc-pq-settings-dropdown.php' );
		$this->settings['price_by_qty'] = require_once( 'includes/settings/class-alg-wc-pq-settings-price-by-qty.php' );
		$this->settings['qty_info']     = require_once( 'includes/settings/class-alg-wc-pq-settings-qty-info.php' );
		$this->settings['styling']      = require_once( 'includes/settings/class-alg-wc-pq-settings-styling.php' );
		$this->settings['admin']        = require_once( 'includes/settings/class-alg-wc-pq-settings-admin.php' );
		$this->settings['advanced']     = require_once( 'includes/settings/class-alg-wc-pq-settings-advanced.php' );
		// Version updated
		if ( get_option( 'alg_wc_pq_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pq' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'product-quantity-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a href="https://wpfactory.com/item/product-quantity-for-woocommerce/">' .
				__( 'Unlock All', 'product-quantity-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Add Product Quantity settings tab to WooCommerce settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-alg-wc-settings-pq.php' );
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.3.0
	 * @since   1.2.0
	 */
	function version_updated() {
		update_option( 'alg_wc_pq_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_pq' ) ) {
	/**
	 * Returns the main instance of Alg_WC_PQ to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_PQ
	 */
	function alg_wc_pq() {
		return Alg_WC_PQ::instance();
	}
}

alg_wc_pq();
