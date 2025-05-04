<?php
/**
 * WC_Gateway_Blocks_Support class for support block.
 *
 * @package WooCommerce\Blocks\Payments\Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * WC_Gateway_Blocks_Support class.
 *
 * This class provides a custom payment gateway integration for WooCommerce Blocks, enabling compatibility
 * with the block-based checkout experience. Extending WooCommerce's AbstractPaymentMethodType, this class
 * handles both frontend and backend operations for the custom gateway, such as initializing settings,
 * enqueuing scripts, and processing custom fields in the checkout context.
 *
 * @package WooCommerce\Blocks\Payments\Integrations
 */
final class WC_Gateway_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * The unique identifier for the custom payment gateway.
	 *
	 * This ID is used to register and manage the gateway within WooCommerce.
	 * It is also utilized in settings retrieval and script handles specific to this gateway.
	 *
	 * @var string
	 */
	protected $name = 'alg_custom_gateway_1'; // payment gateway id.

	/**
	 * Constructor.
	 *
	 * Sets up the gateway by adding the necessary action for processing payments with context.
	 */
	public function __construct() {
		add_action( 'woocommerce_rest_checkout_process_payment_with_context', array( $this, 'checkout_process_payment_with_context' ), 10, 2 );
	}

	/**
	 * Initializes the payment method.
	 *
	 * This function will get called during the server side initialization process and is a good place to put any settings
	 * population etc. Basically anything you need to do to initialize your gateway.
	 *
	 * Note, this will be called on every request so don't put anything expensive here.
	 */
	public function initialize() {
		// get payment gateway settings.
		$this->settings = get_option( "woocommerce_{$this->name}_settings", array() );
	}

	/**
	 * This should return whether the payment method is active or not.
	 *
	 * If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return filter_var( $this->get_setting( 'enabled', false ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {

		$asset_path   = plugin_dir_path( __DIR__ ) . 'build/index.asset.php';
		$version      = null;
		$dependencies = array();
		if ( file_exists( $asset_path ) ) {
			$asset        = require $asset_path;
			$version      = isset( $asset['version'] ) ? $asset['version'] : $version;
			$dependencies = isset( $asset['dependencies'] ) ? $asset['dependencies'] : $dependencies;
		}

		wp_register_script(
			'wc-blocks-integration',
			plugin_dir_url( __DIR__ ) . 'build/index.js',
			$dependencies,
			$version,
			true
		);

		wp_enqueue_style(
			'wc-blocks-integration-css',
			plugin_dir_url( __DIR__ ) . 'build/style-index.css',
			array(),
			$version,
			'all'
		);

		return array( 'wc-blocks-integration' );
	}

	/**
	 * Returns an array of script handles to be enqueued for the admin.
	 *
	 * Include this if your payment method has a script you _only_ want to load in the editor context for the checkout block.
	 * Include here any script from `get_payment_method_script_handles` that is also needed in the admin.
	 */
	public function get_payment_method_script_handles_for_admin() {
		return $this->get_payment_method_script_handles();
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script client side.
	 *
	 * This data will be available client side via `wc.wcSettings.getSetting`. So for instance if you assigned `stripe` as the
	 * value of the `name` property for this class, client side you can access any data via:
	 * `wc.wcSettings.getSetting( 'stripe_data' )`. That would return an object matching the shape of the associative array
	 * you returned from this function.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$require = $this->get_setting( 'input_fields_required_1' );
		$title   = $this->get_setting( 'input_fields_title_1' );
	
		//  Return no fields if title is empty
		if ( empty( $title ) ) {
			return array(
				'icon'           => $this->get_setting( 'icon' ),
				'title'          => $this->get_setting( 'title' ),
				'description'    => $this->get_setting( 'description' ),
				'total_gateways' => apply_filters( 'alg_wc_custom_payment_gateways_values', 2, 'total_gateways' ),
				'supports'       => $this->get_supported_features(),
				'fields'         => [], // ← empty array
			);
		}
	
		//  Otherwise, return full data
		return array(
			'icon'           => $this->get_setting( 'icon' ),
			'title'          => $this->get_setting( 'title' ),
			'description'    => $this->get_setting( 'description' ),
			'total_gateways' => apply_filters( 'alg_wc_custom_payment_gateways_values', 2, 'total_gateways' ),
			'supports'       => $this->get_supported_features(),
			'fields'         => array(
				array(
					'label'       => 'yes' === $require ? $title . '*' : $title,
					'required'    => 'yes' === $require ? true : false,
					'type'        => $this->get_setting( 'input_fields_type_1' ),
					'placeholder' => $this->get_setting( 'input_fields_placeholder_1' ),
					'inclass'     => $this->get_setting( 'input_fields_class_1' ),
					'name'        => 'field-1',
					'options'     => $this->convert_newline_to_array( $this->get_setting( 'input_fields_options_1' ) ),
				),
			),
		);
	}

	/**
	 * Processes the payment within the checkout context.
	 *
	 * This function is hooked to the WooCommerce REST API for processing payments. It checks if the custom
	 * gateway data exists, then combines the data into metadata and saves it with the order.
	 *
	 * @param object $context The checkout context.
	 * @param array  $result  The result data from the payment process.
	 */
	public function checkout_process_payment_with_context( $context, $result ) {

		if ( property_exists( $context, 'payment_data' )
			&& isset( $context->payment_data['customgatewayis'] )
			&& ! empty( $context->payment_data['customgatewayis'] )
			&& ! empty( $context->payment_data['gatewayisdata'] ) ) :
			$keys   = explode( ',', $context->payment_data['gatewayisnames'] );
			$values = explode( ',', $context->payment_data['gatewayisdata'] );
			if ( count( $keys ) === count( $values ) ) :
				$metadata = array_combine( $keys, $values );
				$context->order->update_meta_data( '_alg_wc_cpg_input_fields', $metadata );
				$context->order->save();
			endif;
		endif;

		if ( property_exists( $context, 'order' ) && 'alg_custom_gateway_1' === $context->order->get_payment_method() ) {
			$total_orders = (int)get_option( 'img_cpg_orders', 0 );
			$total_orders++;
			update_option( 'img_cpg_orders', $total_orders );
		}
	}

	/**
	 * Converts a multi-line string into an array, with each line as an array element.
	 *
	 * This function takes a string containing multiple lines, splits it by newline characters,
	 * and returns an array where each element corresponds to a line. Empty lines are removed.
	 *
	 * @param string $stringdata The input string with lines separated by new line characters.
	 * @return array An array of lines from the input string, with empty lines removed.
	 */
	public function convert_newline_to_array( $stringdata ) {

		if ( empty( $stringdata ) ) :
			return array();
		endif;

		$normalized_string = str_replace( array( "\r\n", "\r" ), "\n", $stringdata );
		// Trim any extra spaces or new lines from the beginning and end of the string.
		$trimmed_string = trim( $normalized_string );
		// Split the string by new line into an array.
		$array = explode( PHP_EOL, $trimmed_string );
		// Filter out any empty lines.
		return array_filter( $array, 'strlen' );
	}
}
