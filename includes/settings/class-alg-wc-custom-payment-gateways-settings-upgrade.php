<?php
/**
 * Custom Payment Gateways for WooCommerce - Advanced Section Settings
 *
 * @version 1.4.0
 * @since   1.4.0
 * @author  Imaginate Solutions
 * @package cpgw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Custom_Payment_Gateways_Settings_Upgrade' ) ) :

	/**
	 * Advanced Settings class.
	 */
	class Alg_WC_Custom_Payment_Gateways_Settings_Upgrade extends Alg_WC_Custom_Payment_Gateways_Settings_Section {

		public $id = '';

		public $desc = '';

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function __construct() {
			$this->id   = 'upgrade';
			$this->desc = __( 'Lite vs Pro', 'custom-payment-gateways-woocommerce' );
			parent::__construct();
		}

		/**
		 * Get settings.
		 *
		 * @return array Settings Array.
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function get_settings() {
			return array();
		}

	}

endif;

return new Alg_WC_Custom_Payment_Gateways_Settings_Upgrade();
