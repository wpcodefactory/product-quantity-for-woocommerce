<?php
/**
 * Product Quantity for WooCommerce - Styling Section Settings
 *
 * @version 5.0.3
 * @since   1.7.0
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Settings_Styling' ) ) :

	class WPFMMSQ_Settings_Styling extends WPFMMSQ_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 5.0.3
		 * @since   1.7.0
		 */
		function __construct() {
			$this->id = 'styling';
			parent::__construct();
		}

		/**
		 * set_section_variables.
		 *
		 * @version 5.0.3
		 * @since   5.0.3
		 *
		 * @return void
		 */
		public function set_section_variables() {
			parent::set_section_variables();
			$this->desc = __( 'Styling', 'product-quantity-for-woocommerce' );
		}

		/**
		 * get_settings.
		 *
		 * @version 1.7.0
		 * @since   1.7.0
		 * @todo    [dev] (maybe) add "Enable section" option
		 */
		function get_settings() {
			return array(
				array(
					'title' => __( 'Styling Options', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'wpfmmsq_qty_styling_options',
				),
				array(
					'title'       => __( 'Quantity input style', 'product-quantity-for-woocommerce' ),
					'desc_tip'    => __( 'Ignored if empty.', 'product-quantity-for-woocommerce' ),
					'desc'        => sprintf( __( 'E.g.: %s', 'product-quantity-for-woocommerce' ), '<code>width: 100px !important; max-width: 100px !important;</code>' ),
					'id'          => 'wpfmmsq_qty_input_style',
					'default'     => '',
					'type'        => 'textarea',
					'css'         => 'width:70%;min-height:100px;',
					'wpfmmsq_raw' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_qty_styling_options',
				),
			);
		}

	}

endif;

return new WPFMMSQ_Settings_Styling();
