<?php
/*
Plugin Name: Min Max Default Quantity for WooCommerce
Plugin URI: https://wpfactory.com/item/product-quantity-for-woocommerce/
Description: Manage product quantity in WooCommerce, beautifully. Define a minimum / maximum / step quantity and more on WooCommerce products.
Version: 4.8.0
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: product-quantity-for-woocommerce
Domain Path: /langs
WC tested up to: 9.3
Requires Plugins: woocommerce
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
 * @version 4.8.0
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
	public $version = '4.8.0';

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
	 * @version 4.8.0
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
		load_plugin_textdomain( 'product-quantity-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Pro
		if ( 'product-quantity-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-pq-pro.php' );
		}else{
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

		// WC Settings tab as WPFactory submenu item
		$this->move_wc_settings_tab_to_wpfactory_menu();

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
	 * @version 4.8.0
	 * @since   4.8.0
	 */
	function move_wc_settings_tab_to_wpfactory_menu() {

		if ( ! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ) {
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

if ( ! function_exists( 'mp_sync_on_product_save' ) ) {
	add_action('woocommerce_update_product', 'mp_sync_on_product_save', 10, 1);
	function mp_sync_on_product_save( $post_id ) {
		/*for language WPML support*/
		if(function_exists('icl_object_id') && function_exists('icl_get_languages')){
			$languages = icl_get_languages();
		}

		$product = wc_get_product( $post_id );
		if ( $product->is_type( 'variable' ) ) {
			$main_product_meta = get_post_meta( $post_id );
			$main_product_min_quantity_to_all = get_post_meta( $post_id, 'main_product_min_quantity_to_all' , true );
			$main_product_max_quantity_to_all = get_post_meta( $post_id, 'main_product_max_quantity_to_all' , true );
			$main_product_step_quantity_to_all = get_post_meta( $post_id, 'main_product_step_quantity_to_all' , true );
			$main_product_default_quantity_to_all = get_post_meta( $post_id, 'main_product_default_quantity_to_all' , true );
			$main_product_exact_qty_allowed_quantity_to_all = get_post_meta( $post_id, 'main_product_exact_qty_allowed_quantity_to_all' , true );

			update_post_meta( $post_id, 'main_product_min_quantity_to_all', 'no' );
			update_post_meta( $post_id, 'main_product_max_quantity_to_all', 'no' );
			update_post_meta( $post_id, 'main_product_step_quantity_to_all', 'no' );
			update_post_meta( $post_id, 'main_product_default_quantity_to_all', 'no' );
			update_post_meta( $post_id, 'main_product_exact_qty_allowed_quantity_to_all', 'no' );

			$_alg_wc_pq_min = get_post_meta( $post_id, '_alg_wc_pq_min' , true );
			$_alg_wc_pq_max = get_post_meta( $post_id, '_alg_wc_pq_max' , true );
			$_alg_wc_pq_step = get_post_meta( $post_id, '_alg_wc_pq_step' , true );
			$_alg_wc_pq_default = get_post_meta( $post_id, '_alg_wc_pq_default' , true );
			$_alg_wc_pq_exact_qty_allowed = get_post_meta( $post_id, '_alg_wc_pq_exact_qty_allowed' , true );
			$available_variations = $product->get_available_variations();

			foreach($available_variations as $res) {
				$variation_id = $res['variation_id'];
				$variation_meta = get_post_meta( $variation_id );
				if($main_product_min_quantity_to_all == 'yes') {
					update_post_meta( $variation_id, '_alg_wc_pq_min', $_alg_wc_pq_min );
				}
				if($main_product_max_quantity_to_all == 'yes') {
					update_post_meta( $variation_id, '_alg_wc_pq_max', $_alg_wc_pq_max );
				}
				if($main_product_step_quantity_to_all == 'yes') {
					update_post_meta( $variation_id, '_alg_wc_pq_step', $_alg_wc_pq_step );
				}
				if($main_product_default_quantity_to_all == 'yes') {
					update_post_meta( $variation_id, '_alg_wc_pq_default', $_alg_wc_pq_default );
				}

				if($main_product_exact_qty_allowed_quantity_to_all == 'yes') {
					update_post_meta( $variation_id, '_alg_wc_pq_exact_qty_allowed', $_alg_wc_pq_exact_qty_allowed );
				}


				/*for language WPML support*/
				if(function_exists('icl_object_id') && function_exists('icl_get_languages')){
					foreach ($languages as $lang) {
						if ($lang['code'] != 'en') {
							$lang_vpid = icl_object_id($variation_id, 'product_variation', false, $lang['code']);
							if($lang_vpid > 0){
								if($main_product_min_quantity_to_all == 'yes') {
									update_post_meta( $lang_vpid, '_alg_wc_pq_min', $_alg_wc_pq_min );
								}
								if($main_product_max_quantity_to_all == 'yes') {
									update_post_meta( $lang_vpid, '_alg_wc_pq_max', $_alg_wc_pq_max );
								}
								if($main_product_step_quantity_to_all == 'yes') {
									update_post_meta( $lang_vpid, '_alg_wc_pq_step', $_alg_wc_pq_step );
								}
								if($main_product_default_quantity_to_all == 'yes') {
									update_post_meta( $lang_vpid, '_alg_wc_pq_default', $_alg_wc_pq_default );
								}

								if($main_product_exact_qty_allowed_quantity_to_all == 'yes') {
									update_post_meta( $lang_vpid, '_alg_wc_pq_exact_qty_allowed', $_alg_wc_pq_exact_qty_allowed );
								}
							}
						}
					}
				}


			}
		}
	}
}

if ( ! function_exists( 'misha_adv_product_options' ) ) {
	add_action( 'woocommerce_product_options_advanced', 'misha_adv_product_options');
	function misha_adv_product_options(){

		echo '<div class="options_group custom_quantity_options_group">';

		woocommerce_wp_checkbox( array(
			'id'      => 'main_product_min_quantity_to_all',
			'value'   => get_post_meta( get_the_ID(), 'main_product_min_quantity_to_all', true ),
			'label'   => 'Add Main product min quantity to all',
			'desc_tip' => true,
			'description' => 'Add Main product quantity for all variations',
		) );
		woocommerce_wp_checkbox( array(
			'id'      => 'main_product_max_quantity_to_all',
			'value'   => get_post_meta( get_the_ID(), 'main_product_max_quantity_to_all', true ),
			'label'   => 'Add Main product max quantity to all',
			'desc_tip' => true,
			'description' => 'Add Main product quantity for all variations',
		) );
		woocommerce_wp_checkbox( array(
			'id'      => 'main_product_step_quantity_to_all',
			'value'   => get_post_meta( get_the_ID(), 'main_product_step_quantity_to_all', true ),
			'label'   => 'Add Main product step quantity to all',
			'desc_tip' => true,
			'description' => 'Add Main product quantity for all variations',
		) );
		woocommerce_wp_checkbox( array(
			'id'      => 'main_product_default_quantity_to_all',
			'value'   => get_post_meta( get_the_ID(), 'main_product_default_quantity_to_all', true ),
			'label'   => 'Add Main product default quantity to all',
			'desc_tip' => true,
			'description' => 'Add Main product quantity for all variations',
		) );

		woocommerce_wp_checkbox( array(
			'id'      => 'main_product_exact_qty_allowed_quantity_to_all',
			'value'   => get_post_meta( get_the_ID(), 'main_product_exact_qty_allowed_quantity_to_all', true ),
			'label'   => 'Add Main product exact allowed quantity to all',
			'desc_tip' => true,
			'description' => 'Add Main product quantity for all variations',
		) );

		echo '</div>';

	}
}

if ( ! function_exists( 'misha_save_fields' ) ) {
	add_action( 'woocommerce_process_product_meta', 'misha_save_fields', 10, 2 );
	function misha_save_fields( $id, $post ){
		if( !empty( $_POST['main_product_min_quantity_to_all'] ) ) {
			$alg_wc_pq_min_name = 'alg_wc_pq_min_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_min_quantity_to_all', $_POST['main_product_min_quantity_to_all'] );
		}  else {
			$alg_wc_pq_min_name = 'alg_wc_pq_min_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_min_quantity_to_all', 'no' );
		}
		if( !empty( $_POST['main_product_max_quantity_to_all'] ) ) {
			$alg_wc_pq_max_name = 'alg_wc_pq_max_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_max_quantity_to_all', $_POST['main_product_max_quantity_to_all'] );
		} else {
			$alg_wc_pq_max_name = 'alg_wc_pq_max_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_max_quantity_to_all', 'no' );
		}
		if( !empty( $_POST['main_product_step_quantity_to_all'] ) ) {
			$alg_wc_pq_step_name = 'alg_wc_pq_step_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_step_quantity_to_all', $_POST['main_product_step_quantity_to_all'] );
		} else {
			$alg_wc_pq_step_name = 'alg_wc_pq_step_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_step_quantity_to_all', 'no' );
		}
		if( !empty( $_POST['main_product_default_quantity_to_all'] ) ) {
			$alg_wc_pq_default_name = 'alg_wc_pq_default_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_default_quantity_to_all', $_POST['main_product_default_quantity_to_all'] );
		} else {
			$alg_wc_pq_default_name = 'alg_wc_pq_default_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_default_quantity_to_all', 'no' );
		}
		if( !empty( $_POST['main_product_exact_qty_allowed_quantity_to_all'] ) ) {
			$alg_wc_pq_exact_qty_allowed_name = 'alg_wc_pq_exact_qty_allowed_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_exact_qty_allowed_quantity_to_all', $_POST['main_product_exact_qty_allowed_quantity_to_all'] );
		} else {
			$alg_wc_pq_exact_qty_allowed_name = 'alg_wc_pq_exact_qty_allowed_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_exact_qty_allowed_quantity_to_all', 'no' );
		}
	}
}

if ( ! function_exists( 'alg_wc_pq_update_closedate' ) ) {
	add_action( 'wp_ajax_alg_wc_pq_update_closedate', 'alg_wc_pq_update_closedate' );
	add_action( 'wp_ajax_nopriv_alg_wc_pq_update_closedate', 'alg_wc_pq_update_closedate' );

	function alg_wc_pq_update_closedate(){
		$user_id = get_current_user_id();
		if($user_id > 0){
			$phpdatetime  = time();
			update_user_meta($user_id, 'alg_wc_pq_closedate', $phpdatetime);
		}
		echo "ok";
		die;
	}
}

if ( ! function_exists( 'alg_wc_pg_admin_footer_js' ) ) {
	add_action('admin_footer', 'alg_wc_pg_admin_footer_js');
	function alg_wc_pg_admin_footer_js($data) {
		?>
			<script>
				jQuery(document).ready(function() {
					jQuery(".alg_wc_pq_close").on('click', function(){
						var closeData = {
							'action'  : 'alg_wc_pq_update_closedate'
						};

						jQuery.ajax({
							type   : 'POST',
							url    : <?php echo "'" . admin_url( 'admin-ajax.php' ) . "'"; ?>,
							data   : closeData,
							async  : true,
							success: function( response ) {
								if(response=='ok'){
									jQuery(".alg_wc_pq_right_ad").remove();
								}
							},
						});
					});
					is_checkedalg_wc_pqwp_role();
				});

				jQuery("#alg_wc_pq_enable_exclude_role_specofic").on("click", function(){
					is_checkedalg_wc_pqwp_role();
				});
				function is_checkedalg_wc_pqwp_role(){
					if(jQuery("#alg_wc_pq_enable_exclude_role_specofic").length > 0){
						var check = jQuery("#alg_wc_pq_enable_exclude_role_specofic").prop("checked");
						if(check) {
							 jQuery('#alg_wc_pq_required_user_roles').attr('disabled','disabled');
							 if (jQuery.isFunction(jQuery('#alg_wc_pq_required_user_roles').select2)){
								jQuery( '#alg_wc_pq_required_user_roles' ).select2();
							 }

							 jQuery('#alg_wc_pq_non_required_user_roles').removeAttr('disabled');
						} else {

							 jQuery('#alg_wc_pq_non_required_user_roles').attr('disabled','disabled');
							 if (jQuery.isFunction(jQuery('#alg_wc_pq_non_required_user_roles').select2)){
								jQuery( '#alg_wc_pq_non_required_user_roles' ).select2();
							 }

							 jQuery('#alg_wc_pq_required_user_roles').removeAttr('disabled');
						}
					}
				}
			</script>
		<?php
	}
}

if ( ! function_exists( 'quantity_to_all' ) ) {
	add_action('admin_head','quantity_to_all');

	function quantity_to_all() {
		?>
		<style>
		.alg_wc_pq_close{
			position: absolute;
			right:-13px;
			top: -26px;
			cursor: pointer;
			color: white;
			background: #000;
			width: 25px;
			height: 25px;
			text-align: center;
			border-radius: 50%;
			font-size: 32px;
		}
		.custom_quantity_options_group {
			display: none;
		}
		.alg_wc_pq_name_heading{
			position: relative;
		}
		.alg_wc_pq_right_ad{
			position: absolute;
			right:20px;
			padding: 16px;
			box-shadow: 0 1px 6px 0 rgb(0 0 0 / 30%);
			border: 1px solid #dcdcdc;
			background-color: #fff;
			margin: 0px 0 20px;
			width: 25em;
			z-index: 99;
			font-weight: 600;
			border-radius: 10px;

		}
		.alg_wc_pq-button-upsell{
			display:inline-flex;
			align-items:center;
			justify-content:center;
			box-sizing:border-box;
			min-height:48px;
			padding:8px 1em;
			font-size:16px;
			line-height:1.5;
			font-family:Arial,sans-serif;
			color:#000;
			border-radius:4px;
			box-shadow:inset 0 -4px 0 rgba(0,0,0,.2);
			filter:drop-shadow(0 2px 4px rgba(0,0,0,.2));
			text-decoration:none;
			background-color:#7ce577;
			font-weight: 600;
		}
		.alg_wc_pq-button-upsell:hover{
			background-color:#7ce577;
			color:#000;
			font-weight: 600;
		}
		.alg_wc_pq-sidebar__section li:before{
			content:"+";
			position:absolute;
			left:0;
			font-weight:700
		}
		.alg_wc_pq-sidebar__section li{
			list-style:none;
			margin-left:20px
		}
		.alg_wc_pq-sidebar__section{
			position: relative;
		}
		img.alg_wc_pq_resize{
			width: 60px;
			float: right;
			position: absolute;
			right: 0px;
			top: -15px;
			padding-left: 10px;
		}
		.alg_wc_pq_text{
			margin-right: 18%;
		}
		</style>
		<script>
		jQuery(document).ready(function() {
			var product_id = jQuery('#post_ID').val();
			var alg_wc_pq_min_name = 'alg_wc_pq_min_'+product_id+'_to_all';
			var alg_wc_pq_min_to_all = jQuery("input[type='checkbox'][name='main_product_min_quantity_to_all']");
			var alg_wc_pq_min = jQuery("input[type='checkbox'][name='"+alg_wc_pq_min_name+"']");

			if(alg_wc_pq_min_to_all.prop('checked')){
				alg_wc_pq_min.prop('checked',true);
			}

			alg_wc_pq_min.on('change', function(){
				alg_wc_pq_min_to_all.prop('checked',this.checked);
				if(this.checked){
					alg_wc_pq_min_to_all.val('yes');
				}else{
					alg_wc_pq_min_to_all.val('no');
				}
			});

			var alg_wc_pq_max_name = 'alg_wc_pq_max_'+product_id+'_to_all';
			var alg_wc_pq_max_to_all = jQuery("input[type='checkbox'][name='main_product_max_quantity_to_all']");
			var alg_wc_pq_max = jQuery("input[type='checkbox'][name='"+alg_wc_pq_max_name+"']");

			if(alg_wc_pq_max_to_all.prop('checked')){
				alg_wc_pq_max.prop('checked',true);
			}

			alg_wc_pq_max.on('change', function(){
				alg_wc_pq_max_to_all.prop('checked',this.checked);
				if(this.checked){
					alg_wc_pq_max_to_all.val('yes');
				}else{
					alg_wc_pq_max_to_all.val('no');
				}
			});
			var alg_wc_pq_step_name = 'alg_wc_pq_step_'+product_id+'_to_all';
			var alg_wc_pq_step_to_all = jQuery("input[type='checkbox'][name='main_product_step_quantity_to_all']");
			var alg_wc_pq_step = jQuery("input[type='checkbox'][name='"+alg_wc_pq_step_name+"']");

			if(alg_wc_pq_step_to_all.prop('checked')){
				alg_wc_pq_step.prop('checked',true);
			}

			alg_wc_pq_step.on('change', function(){
				alg_wc_pq_step_to_all.prop('checked',this.checked);
				if(this.checked){
					alg_wc_pq_step_to_all.val('yes');
				}else{
					alg_wc_pq_step_to_all.val('no');
				}
			});
			var alg_wc_pq_default_name = 'alg_wc_pq_default_'+product_id+'_to_all';
			var alg_wc_pq_default_to_all = jQuery("input[type='checkbox'][name='main_product_default_quantity_to_all']");
			var alg_wc_pq_default = jQuery("input[type='checkbox'][name='"+alg_wc_pq_default_name+"']");

			if(alg_wc_pq_default_to_all.prop('checked')){
				alg_wc_pq_default.prop('checked',true);
			}

			alg_wc_pq_default.on('change', function(){
				alg_wc_pq_default_to_all.prop('checked',this.checked);
				if(this.checked){
					alg_wc_pq_default_to_all.val('yes');
				}else{
					alg_wc_pq_default_to_all.val('no');
				}
			});

			var alg_wc_pq_exact_qty_allowed_name = 'alg_wc_pq_exact_qty_allowed_'+product_id+'_to_all';
			var alg_wc_pq_exact_qty_allowed_to_all = jQuery("input[type='checkbox'][name='main_product_exact_qty_allowed_quantity_to_all']");
			var alg_wc_pq_exact_qty_allowed = jQuery("input[type='checkbox'][name='"+alg_wc_pq_exact_qty_allowed_name+"']");

			if(alg_wc_pq_exact_qty_allowed_to_all.prop('checked')){
				alg_wc_pq_exact_qty_allowed.prop('checked',true);
			}

			alg_wc_pq_exact_qty_allowed.on('change', function(){
				alg_wc_pq_exact_qty_allowed_to_all.prop('checked',this.checked);
				if(this.checked){
					alg_wc_pq_exact_qty_allowed_to_all.val('yes');
				}else{
					alg_wc_pq_exact_qty_allowed_to_all.val('no');
				}
			});


		});
		</script>
		<?php
	}
}

if ( ! function_exists( 'pq_select_footer_scripts' ) ) :
add_action( 'wp_footer', 'pq_select_footer_scripts', 99 );
function pq_select_footer_scripts(){
	?>
	<script>
	jQuery( document ).ready( function () {
		var qty_select = jQuery( "select.qty" );
		if ( qty_select.length > 0 ) {
			jQuery( document ).on( 'change', 'select.qty:not(.disable_price_by_qty)', function () {
				var input = jQuery( this ).closest( 'div.quantity' ).find( 'input.qty' );
				if ( input.length > 0 ) {
					sync_classes( input );
					input.val( jQuery( this ).val() ).change();
				}

				var add_to_cart = jQuery( this ).closest( 'div.quantity' ).siblings( ".add-to-cart" );
				var add_cart = jQuery( this ).closest( 'div.quantity' ).siblings( ".add_to_cart_button" );
				if ( add_to_cart.length > 0 ) {
					add_to_cart.find( 'a.add_to_cart_button' ).attr( "data-quantity", jQuery( this ).val() );
				} else if ( add_cart.length > 0 ) {
					add_cart.attr( "data-quantity", jQuery( this ).val() );
				}
			} );

			qty_select.change();

		}

	} );

	jQuery( '[name="quantity"]' ).not( ".disable_price_by_qty" ).on( 'change', function(e) {
		var current_val = parseFloat(jQuery(this).val());
		if ( Number.isInteger( current_val ) === false )
		{
			current_val = current_val.toFixed(4);
			current_val = parseFloat(current_val);
			jQuery(this).val( current_val );
		} else {
			current_val = parseInt( current_val );
			jQuery(this).val( current_val );
		}
	});

	function sync_classes( input ) {
		var classList = input.attr('class').split(/\s+/);
		jQuery(classList).each(function( index, item){
			if( !jQuery("select.qty").hasClass(item) ) {
				jQuery("select.qty").addClass(item);
			}
		});
	}
	</script>
	<?php
}
endif;

if ( ! function_exists( 'pq_custom_admin_js_add_order' ) ) {
	function pq_custom_admin_js_add_order() {
		if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_admin_order_enabled', 'no' ) ) {
		?>
		<script>
		jQuery( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) {
			if( target == 'wc-modal-add-products') {
				jQuery('.wc-backbone-modal-content').find('input.quantity').attr('step','0.00001');
				jQuery('.wc-backbone-modal-content').find('input.quantity').val('1');
			}
		});
		jQuery( document.body ).on( 'wc-enhanced-select-init', function( evt ) {
				jQuery('.wc-backbone-modal-content').find('input.quantity').attr('step','0.00001');
				jQuery('.wc-backbone-modal-content').find('input.quantity').val('1');
		});
		</script>
		<?php }
	}
	add_action('admin_footer', 'pq_custom_admin_js_add_order');
}

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
