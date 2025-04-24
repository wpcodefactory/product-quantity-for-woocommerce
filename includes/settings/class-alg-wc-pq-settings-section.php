<?php
/**
 * Product Quantity for WooCommerce - Section Settings
 *
 * @version 5.0.3
 * @since   1.0.0
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_PQ_Settings_Section' ) ) :

class Alg_WC_PQ_Settings_Section {

	/**
	 * id.
	 *
	 * @var   string
	 * @since 5.0.3
	 */
	public $id = null;

	/**
	 * desc.
	 *
	 * @var   string
	 * @since 5.0.3
	 */
	public $desc = null;

	/**
	 * qty_step_settings
	 *
	 * @var   string
	 * @since 4.6.8
	 */
	public $qty_step_settings = 1;

	/**
	 * Constructor.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_pq',              array( $this, 'set_section_variables' ), 9 );
		add_filter( 'woocommerce_get_sections_alg_wc_pq',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_pq_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
		add_action( 'admin_head', array( $this, 'custom_admin_inline_styles' ) );
	}

	/**
	 * set_section_variables.
	 *
	 * @version 5.0.3
	 * @since   5.0.3
	 *
	 * @return void
	 */
	function set_section_variables(){

	}

	/**
	 * custom_admin_inline_styles.
	 *
	 * @version 4.9.4
	 * @since   4.9.4
	 *
	 * @return void
	 */
	function custom_admin_inline_styles() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === 'alg_wc_pq' ) {
			?>
			<style>
				.alg-wc-pq-array-to-list {
					list-style: inside;
					margin-top: 0;
					margin-bottom:25px;
					background:#fff;
					padding:12px 12px 6px 12px;
					border:1px solid #c3c4c7
				}

				.alg-wc-pq-array-to-list-header {
					background: #fff;
					border-left: 1px solid #c3c4c7;
					border-top: 1px solid #c3c4c7;
					border-right: 1px solid #c3c4c7;
					padding: 7px 6px 7px 7px;
					font-size: 14px;
					line-height: 1.4;
					font-weight: 600;
					margin: 25px 0 0 0;
				}

				.alg-wc-pq-array-to-list-header .dashicons {
					margin: 0 2px 0 0;
				}
			</style>
			<?php
		}
	}

	/**
	 * message_replaced_values.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function message_replaced_values( $values ) {
		$message_template = ( 1 == count( $values ) ?
			__( 'Replaced value: %s.', 'product-quantity-for-woocommerce' ) : __( 'Replaced values: %s.', 'product-quantity-for-woocommerce' ) );
		return sprintf( $message_template, '<code>' . implode( '</code>, <code>', $values ) . '</code>' );
	}

	/**
	 * get_qty_step_settings.
	 *
	 * @version 4.6.8
	 * @since   1.6.0
	 * @todo    [dev] customizable `$qty_step_settings` (i.e. instead of always `0.000001`)
	 */
	function get_qty_step_settings() {
		/*if ( ! isset( $this->qty_step_settings ) ) {*/
			$this->qty_step_settings = ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ? 0.000001 : 1 );
		/*}*/
		return $this->qty_step_settings;
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

	/**
	 * array_to_html_list_items.
	 *
	 * @version 4.9.4
	 * @since   4.9.3
	 *
	 * @param         $items
	 * @param   bool  $wrap_on_ul
	 *
	 * @return string
	 */
	function array_to_html_list_items( $items, $args = null ) {
		$args       = wp_parse_args( $args, array(
			'wrap_on_ul' => true,
			'ul_style'   => ''
		) );
		$wrap_on_ul = $args['wrap_on_ul'];
		$ul_style   = $args['ul_style'];
		$output     = '';
		if ( is_array( $items ) ) {
			$output .= $wrap_on_ul ? '<ul class="alg-wc-pq-array-to-list" style="' . wp_kses_post( $ul_style ) . '">' : '';
			$output .= '<li>' . implode( '</li><li>', array_map( 'wp_kses_post', $items ) ) . '</li>';
			$output .= $wrap_on_ul ? '</ul>' : '';
		}

		return $output;
	}

	/**
	 * section_notes.
	 *
	 * @version 4.9.4
	 * @since   4.9.4
	 *
	 * @param $items
	 * @param $args
	 *
	 * @return string
	 */
	function section_notes( $items, $args = null ) {
		$output = '<div class="alg-wc-pq-array-to-list-header"><span class="dashicons dashicons-info"></span> '.__('Notes','product-quantity-for-woocommerce').'</div>';
		$output .= $this->array_to_html_list_items( $items, array(
			'wrap_on_ul' => true,
			'ul_style'   => ''
		) );
		return $output;
	}

}

endif;
