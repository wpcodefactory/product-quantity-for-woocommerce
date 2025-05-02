<?php
/*
Plugin Name: Min Max Step Quantity Limits Manager for WooCommerce
Plugin URI: https://wpfactory.com/item/product-quantity-for-woocommerce/
Description: Manage product quantity in WooCommerce, beautifully. Define a minimum / maximum / step quantity and more on WooCommerce products.
Version: 5.0.5
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: product-quantity-for-woocommerce
Domain Path: /langs
WC tested up to: 9.8
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

/**
 * alg_wc_pq_check_free_active.
 */
if ( ! function_exists( 'alg_wc_pq_check_free_active' ) ) :
function alg_wc_pq_check_free_active() {

	$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

	if ( alg_wc_pq_check_if_active_plugin( 'product-quantity-for-woocommerce', 'product-quantity-for-woocommerce.php', $active_plugins ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( sprintf(
			__( 'You need to deactivate Product Quantity Control for WooCommerce. <br/> %s back to plugins. %s', 'product-quantity-for-woocommerce' ),
			'<a href="' . wp_nonce_url( 'plugins.php?plugin_status=all' ) . '">',
			'</a>'
		) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

}
endif;

/**
 * register_activation_hook.
 */
register_activation_hook( __FILE__, 'alg_wc_pq_check_free_active' );

/**
 * functions.
 */
require_once( 'includes/functions/alg-wc-pq-core-functions.php' );

/**
 * do_disable.
 */
if ( alg_wc_pq_do_disable( basename( __FILE__ ) ) ) {
	return;
}

/**
 * Main Alg_WC_PQ Class.
 *
 * @class   Alg_WC_PQ
 *
 * @version 4.9.2
 * @since   1.0.0
 */
if ( ! class_exists( 'Alg_WC_PQ' ) ) :

final class Alg_WC_PQ {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '5.0.5';

	/**
	 * core.
	 *
	 * @var   string
	 * @since 4.6.0
	 */
	public $core = null;

	/**
	 * settings.
	 *
	 * @var   string
	 * @since 4.6.0
	 */
	public $settings = null;

	/**
	 * @var   Alg_WC_PQ The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_PQ Instance.
	 *
	 * Ensures only one instance of Alg_WC_PQ is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
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
	 * @version 5.0.0
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
		}

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Pro
		if ( 'product-quantity-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-pq-pro.php' );
		} else {
			require_once( 'includes/class-alg-wc-pq-free.php' );
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}
	}

	/**
	 * localize.
	 *
	 * @version 4.9.1
	 * @since   4.9.1
	 */
	function localize() {
		load_plugin_textdomain(
			'product-quantity-for-woocommerce',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/langs/'
		);
	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 4.9.0
	 * @since   4.5.10
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 */
	function wc_declare_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				__FILE__,
				true
			);
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
	 * @version 4.8.0
	 * @since   1.3.0
	 */
	function admin() {

		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

		// "Recommendations" page
		$this->add_cross_selling_library();

		// WC Settings tab as WPFactory submenu item.
		add_action( 'init', array( $this, 'move_wc_settings_tab_to_wpfactory_menu' ) );

		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once( 'includes/settings/class-alg-wc-pq-metaboxes.php' );
		require_once( 'includes/settings/class-alg-wc-pq-category-metaboxes.php' );
		require_once( 'includes/settings/class-alg-wc-pq-attribute-item-metaboxes.php' );
		require_once( 'includes/settings/class-alg-wc-pq-settings-section.php' );
		$this->settings = array();
		$this->settings['general']      = require_once( 'includes/settings/class-alg-wc-pq-settings-general.php' );
		$this->settings['min']          = require_once( 'includes/settings/class-alg-wc-pq-settings-min.php' );
		$this->settings['max']          = require_once( 'includes/settings/class-alg-wc-pq-settings-max.php' );
		$this->settings['default']      = require_once( 'includes/settings/class-alg-wc-pq-settings-default.php' );
		$this->settings['step']         = require_once( 'includes/settings/class-alg-wc-pq-settings-step.php' );
		$this->settings['fixed']        = require_once( 'includes/settings/class-alg-wc-pq-settings-fixed.php' );
		$this->settings['dropdown']     = require_once( 'includes/settings/class-alg-wc-pq-settings-dropdown.php' );
		$this->settings['price_by_qty'] = require_once( 'includes/settings/class-alg-wc-pq-settings-price-by-qty.php' );
		$this->settings['price_unit']   = require_once( 'includes/settings/class-alg-wc-pq-settings-price-unit.php' );
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
	 * @version 4.8.0
	 * @since   1.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pq' ) . '">' .
			__( 'Settings', 'product-quantity-for-woocommerce' ) .
		'</a>';

		$custom_links[] = '<a style=" font-weight: bold;" target="_blank" href="' . esc_url( 'https://wordpress.org/support/plugin/product-quantity-for-woocommerce/reviews/#new-post"' ) . '">' .
			__( 'Review Us', 'product-quantity-for-woocommerce' ) .
		'</a>';

		if ( 'product-quantity-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a style="color: green; font-weight: bold;" target="_blank" href="' . esc_url( 'https://wpfactory.com/item/product-quantity-for-woocommerce/"' ) . '">' .
				__( 'Go Pro', 'product-quantity-for-woocommerce' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 4.8.0
	 * @since   4.8.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => __FILE__ ) );
		$cross_selling->init();

	}

	/**
	 * move_wc_settings_tab_to_wpfactory_menu.
	 *
	 * @version 5.0.0
	 * @since   4.8.0
	 */
	function move_wc_settings_tab_to_wpfactory_menu() {
		if (
			! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ||
			! is_admin()
		) {
			return;
		}

		$wpfactory_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();

		if ( ! method_exists( $wpfactory_admin_menu, 'move_wc_settings_tab_to_wpfactory_menu' ) ) {
			return;
		}

		$wpfactory_admin_menu->move_wc_settings_tab_to_wpfactory_menu( array(
			'wc_settings_tab_id' => 'alg_wc_pq',
			'menu_title'         => __( 'Product Quantity', 'product-quantity-for-woocommerce' ),
			'page_title'         => __( 'Product Quantity', 'product-quantity-for-woocommerce' ),
		) );
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
	 *
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
	 *
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
	 *
	 * @return  Alg_WC_PQ
	 */
	function alg_wc_pq() {
		return Alg_WC_PQ::instance();
	}
}

/**
 * plugins_loaded.
 *
 * @version 4.9.0
 * @since   4.9.0
 */
add_action( 'plugins_loaded', 'alg_wc_pq' );
