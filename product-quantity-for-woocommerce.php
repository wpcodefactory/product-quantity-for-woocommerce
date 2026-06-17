<?php
/*
Plugin Name: Min Max Step Quantity Limits Manager for WooCommerce
Plugin URI: https://wpfactory.com/item/product-quantity-for-woocommerce/
Description: Manage product quantity in WooCommerce, beautifully. Define a minimum / maximum / step quantity and more on WooCommerce products.
Version: 5.4.3
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: product-quantity-for-woocommerce
Domain Path: /langs
WC tested up to: 10.8
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

/**
 * wpfmmsq_check_free_active.
 *
 * @version 5.3.1
 */
if ( ! function_exists( 'wpfmmsq_check_free_active' ) ) :
	function wpfmmsq_check_free_active() {

		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

		if ( wpfmmsq_check_if_active_plugin( 'product-quantity-for-woocommerce', 'product-quantity-for-woocommerce.php', $active_plugins ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			/* translators: 1: Opening link tag to plugins list, 2: Closing link tag. */
			$message = sprintf(
				__( 'You need to deactivate Product Quantity Control for WooCommerce. <br/> %1$s back to plugins. %2$s', 'product-quantity-for-woocommerce' ),
				'<a href="' . esc_url( wp_nonce_url( 'plugins.php?plugin_status=all' ) ) . '">',
				'</a>'
			);
			wp_die( wp_kses_post( $message ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}

	}
endif;

/**
 * register_activation_hook.
 */
register_activation_hook( __FILE__, 'wpfmmsq_check_free_active' );

/**
 * functions.
 */
require_once( 'includes/functions/wpfmmsq-core-functions.php' );

/**
 * deprecated.
 *
 * @version 5.3.1
 */
require_once( 'includes/functions/wpfmmsq-deprecated-functions.php' );

/**
 * deprecated hooks.
 *
 * @version 5.3.1
 */
require_once( 'includes/functions/wpfmmsq-deprecated-hooks.php' );

/**
 * deprecated shortcodes.
 *
 * @version 5.3.1
 */
require_once( 'includes/functions/wpfmmsq-deprecated-shortcodes.php' );

/**
 * do_disable.
 */
if ( wpfmmsq_do_disable( basename( __FILE__ ) ) ) {
	return;
}

/**
 * Main WPFMMSQ Class.
 *
 * @class   WPFMMSQ
 *
 * @version 5.3.1
 * @since   1.0.0
 */
if ( ! class_exists( 'WPFMMSQ' ) ) :

	final class WPFMMSQ {

		/**
		 * Plugin version.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		public $version = '5.4.3';

		/**
		 * core.
		 *
		 * @since 4.6.0
		 * @var   string
		 */
		public $core = null;

		/**
		 * settings.
		 *
		 * @since 4.6.0
		 * @var   string
		 */
		public $settings = null;

		/**
		 * @since 1.0.0
		 * @var   WPFMMSQ The single instance of the class
		 */
		protected static $_instance = null;

		/**
		 * Main WPFMMSQ Instance.
		 *
		 * Ensures only one instance of WPFMMSQ is loaded or can be loaded.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @static
		 * @return  WPFMMSQ - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * WPFMMSQ Constructor.
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
				require_once( 'includes/pro/class-wpfmmsq-pro.php' );
			} else {
				require_once( 'includes/class-wpfmmsq-free.php' );
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
		 * @version 5.3.4
		 * @since   1.0.0
		 */
		function includes() {
			require_once( 'includes/class-wpfmmsq-migration.php' );

			// Migration is now handled in version_updated().

			// Core
			$this->core = require_once( 'includes/class-wpfmmsq-core.php' );
		}

		/**
		 * admin.
		 *
		 * @version 5.3.9
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
			require_once( 'includes/settings/class-wpfmmsq-metaboxes.php' );
			require_once( 'includes/settings/class-wpfmmsq-settings-section.php' );
			$this->settings                 = array();
			$this->settings['general']      = require_once( 'includes/settings/class-wpfmmsq-settings-general.php' );
			$this->settings['min']          = require_once( 'includes/settings/class-wpfmmsq-settings-min.php' );
			$this->settings['max']          = require_once( 'includes/settings/class-wpfmmsq-settings-max.php' );
			$this->settings['default']      = require_once( 'includes/settings/class-wpfmmsq-settings-default.php' );
			$this->settings['step']         = require_once( 'includes/settings/class-wpfmmsq-settings-step.php' );
			$this->settings['fixed']        = require_once( 'includes/settings/class-wpfmmsq-settings-fixed.php' );
			$this->settings['dropdown']     = require_once( 'includes/settings/class-wpfmmsq-settings-dropdown.php' );
			$this->settings['price_by_qty'] = require_once( 'includes/settings/class-wpfmmsq-settings-price-by-qty.php' );
			$this->settings['price_unit']   = require_once( 'includes/settings/class-wpfmmsq-settings-price-unit.php' );
			$this->settings['qty_info']     = require_once( 'includes/settings/class-wpfmmsq-settings-qty-info.php' );
			$this->settings['styling']      = require_once( 'includes/settings/class-wpfmmsq-settings-styling.php' );
			$this->settings['admin']        = require_once( 'includes/settings/class-wpfmmsq-settings-admin.php' );
			$this->settings['advanced']     = require_once( 'includes/settings/class-wpfmmsq-settings-advanced.php' );

			// Version updated
			if ( get_option( 'wpfmmsq_version', '' ) !== $this->version ) {
				add_action( 'admin_init', array( $this, 'version_updated' ) );
			}

		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @version 4.8.0
		 * @since   1.0.0
		 *
		 * @param   mixed  $links
		 *
		 * @return  array
		 */
		function action_links( $links ) {
			$custom_links = array();

			$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=wpfmmsq' ) . '">' .
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
		 * @version 5.1.2
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
				'wc_settings_tab_id' => 'wpfmmsq',
				'menu_title'         => __( 'Quantity Manager', 'product-quantity-for-woocommerce' ),
				'page_title'         => __( 'Min Max Step Quantity Limits Manager', 'product-quantity-for-woocommerce' ),
				'plugin_icon'        => array(
					'url'   => 'https://ps.w.org/product-quantity-for-woocommerce/assets/icon.svg?rev=2971558',
					'style' => 'margin-top:-5px;margin-left:-4px',
				)
			) );
		}

		/**
		 * Add Product Quantity settings tab to WooCommerce settings.
		 *
		 * @version 5.3.1
		 * @since   1.0.0
		 */
		function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once( 'includes/settings/class-wpfmmsq-settings.php' );

			return $settings;
		}

		/**
		 * version_updated.
		 *
		 * @version 5.3.4
		 * @since   1.2.0
		 */
		function version_updated() {
			// Run migration if needed
			require_once( 'includes/class-wpfmmsq-migration.php' );
			$migration_version = WPFMMSQ_Migration::get_version();
			if ( get_option( 'wpfmmsq_migration_version', '' ) !== $migration_version ) {
				try {
					WPFMMSQ_Migration::run();
				}
				catch ( Exception $e ) {
					error_log( 'WPFMMSQ Migration failed: ' . $e->getMessage() );
				}
			}
			update_option( 'wpfmmsq_version', $this->version );
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

if ( ! function_exists( 'wpfmmsq' ) ) {
	/**
	 * Returns the main instance of WPFMMSQ to prevent the need to use globals.
	 *
	 * @version 5.3.1
	 * @since   5.3.1
	 *
	 * @return  WPFMMSQ
	 */
	function wpfmmsq() {
		return WPFMMSQ::instance();
	}
}

/**
 * plugins_loaded.
 *
 * @version 5.3.1
 * @since   4.9.0
 */
add_action( 'plugins_loaded', 'wpfmmsq' );
