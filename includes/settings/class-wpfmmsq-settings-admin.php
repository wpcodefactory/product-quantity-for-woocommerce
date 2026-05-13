<?php
/**
 * Product Quantity for WooCommerce - Admin Section Settings
 *
 * @version 5.0.3
 * @since   1.7.0
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFMMSQ_Settings_Admin' ) ) :

	class WPFMMSQ_Settings_Admin extends WPFMMSQ_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 5.0.3
		 * @since   1.7.0
		 */
		function __construct() {
			$this->id = 'admin';
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
			$this->desc = __( 'Admin', 'product-quantity-for-woocommerce' );
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
					'title' => __( 'Admin Options', 'product-quantity-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'wpfmmsq_admin_options',
				),
				array(
					'title'    => __( 'Admin columns', 'product-quantity-for-woocommerce' ),
					'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
					'desc_tip' => __( 'Will add quantity columns to admin products list.', 'product-quantity-for-woocommerce' ),
					'id'       => 'wpfmmsq_admin_columns_enabled',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'desc'     => __( 'Columns', 'product-quantity-for-woocommerce' ),
					'desc_tip' => __( 'Leave blank to add all available columns.', 'product-quantity-for-woocommerce' ),
					'id'       => 'wpfmmsq_admin_columns',
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => array(
						'wpfmmsq_min_qty'  => __( 'Min Qty', 'product-quantity-for-woocommerce' ),
						'wpfmmsq_max_qty'  => __( 'Max Qty', 'product-quantity-for-woocommerce' ),
						'wpfmmsq_qty_step' => __( 'Qty Step', 'product-quantity-for-woocommerce' ),
					),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wpfmmsq_admin_options',
				),
			);
		}

	}

endif;

return new WPFMMSQ_Settings_Admin();
