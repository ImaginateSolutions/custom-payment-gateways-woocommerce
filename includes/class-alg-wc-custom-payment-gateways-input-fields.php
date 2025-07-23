<?php
/**
 * Custom Payment Gateways for WooCommerce - Input Fields Class
 *
 * @version 1.6.1
 * @since   1.3.0
 * @author  Imaginate Solutions
 * @package cpgw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Utilities\OrderUtil;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

if ( ! class_exists( 'Alg_WC_Custom_Payment_Gateways_Input_Fields' ) ) :

	/**
	 * Input Fields Class.
	 */
	class Alg_WC_Custom_Payment_Gateways_Input_Fields {

		/**
		 * Constructor.
		 *
		 * @version 1.6.1
		 * @since   1.3.0
		 * @todo    [dev] add option to pre-fill input fields on checkout with previous customer values (i.e. save it in customer meta)
		 */
		public function __construct() {
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'check_required_input_fields' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'add_input_fields_to_order_meta' ), PHP_INT_MAX, 2 );
			add_action( 'add_meta_boxes', array( $this, 'add_input_fields_meta_box' ), 10, 2 );
			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_input_fields_to_order_details' ) );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'add_input_fields_to_emails' ), 10, 4 );
			if ( 'yes' === get_option( 'alg_wc_cpg_input_fields_woe_enabled', 'no' ) ) {
				add_filter( 'woe_get_order_value__alg_wc_cpg_input_fields', array( $this, 'woe_process_input_fields' ), 10, 3 );
			}
			add_action( 'wp_enqueue_scripts', array( $this, 'input_scripts' ) );
			add_action( 'wp_ajax_custom_gateway_upload_file', array( $this, 'wp_ajax_custom_gateway_upload_file_callback' ) );
			add_action( 'wp_ajax_nopriv_custom_gateway_upload_file', array( $this, 'wp_ajax_custom_gateway_upload_file_callback' ) );
		}

		/**
		 * Input scripts.
		 *
		 * @version 1.6.0
		 * @since   1.6.0
		 */
		public function input_scripts() {
			wp_enqueue_script(
				'alg-wc-custom-payment-gateways-input',
				alg_wc_custom_payment_gateways()->plugin_url() . '/includes/js/alg-wc-custom-payment-gateways-input.js',
				array( 'jquery' ),
				alg_wc_custom_payment_gateways()->version,
				true
			);

			wp_localize_script(
				'alg-wc-custom-payment-gateways-input',
				'alg_wc_custom_payment_gateways_data',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'file_upload_nonce' ),
				)
			);
		}

		/**
		 * Ajax function for file upload.
		 *
		 * @version 1.6.0
		 * @since   1.6.0
		 */
		public function wp_ajax_custom_gateway_upload_file_callback() {
			check_ajax_referer( 'file_upload_nonce', 'security' );

			if ( ! empty( $_FILES['custom_file'] ) ) {
				$file_upload_size  = isset( $_POST['file_upload_size'] ) ? floatval( $_POST['file_upload_size'] ) : 2; // MB
				$file_upload_types = isset( $_POST['file_upload_types'] ) ? explode( ',', $_POST['file_upload_types'] ) : array( 'jpg', 'png', 'pdf' );

				$file_info    = $_FILES['custom_file'];
				$file_ext     = strtolower( pathinfo( $file_info['name'], PATHINFO_EXTENSION ) );
				$file_size_mb = $file_info['size'] / ( 1024 * 1024 );

				// Validate file type.
				if ( ! in_array( $file_ext, array_map( 'trim', $file_upload_types ), true ) ) {
					wp_send_json_error( array( 'message' => 'Invalid file type. Allowed files type: ' . get_option( 'alg_wc_cpg_upload_allowed_file_types', 'jpg,png,pdf' ) ) );
				}

				// Validate file size.
				if ( $file_size_mb > $file_upload_size ) {
					wp_send_json_error( array( 'message' => 'File size exceeds the allowed limit. Upload file size below ' . $file_upload_size . ' MB' ) );
				}

				$uploaded = wp_handle_upload( $file_info, array( 'test_form' => false ) );
				if ( isset( $uploaded['url'] ) ) {
					wp_send_json_success( array( 'file_url' => $uploaded['url'] ) );
				} else {
					wp_send_json_error( array( 'message' => 'Upload failed.' ) );
				}
			}
			wp_send_json_error( array( 'message' => 'No file uploaded.' ) );
		}

		/**
		 * Process input fields.
		 *
		 * @param mixed    $value Value.
		 * @param WC_Order $order Order Object.
		 * @param string   $field Field.
		 * @return mixed
		 * @version 1.6.1
		 * @since   1.6.1
		 */
		public function woe_process_input_fields( $value, $order, $field ) {
			$order_id = $order->get_id();
			if ( $order_id ) {
				$input_fields = $order->get_meta( '_alg_wc_cpg_input_fields', true );
				// $input_fields = get_post_meta( $order_id, '_alg_wc_cpg_input_fields', true );
				if ( is_array( $input_fields ) ) {
					$template = get_option( 'alg_wc_cpg_input_fields_woe_template', '%title%: %value%' );
					$glue     = get_option( 'alg_wc_cpg_input_fields_woe_glue', ' | ' );
					$output   = array();
					foreach ( $input_fields as $field_title => $field_value ) {
						$output[] = str_replace( array( '%title%', '%value%' ), array( $field_title, $field_value ), $template );
					}
					return implode( $glue, $output );
				} else {
					return $input_fields;
				}
			}
			return $value;
		}

		/**
		 * Get input fields output.
		 *
		 * @param array $fields Fields Array.
		 * @param array $templates Templates.
		 * @return array
		 * @version 1.4.2
		 * @since   1.4.0
		 * @todo    [dev] (optionally) do not output on empty value
		 */
		public function get_input_fields_output( $fields, $templates ) {
			$fields_html = '';
			foreach ( $fields as $title => $value ) {
				if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
					// Get filename from URL.
					$filename = basename( parse_url( $value, PHP_URL_PATH ) );

					$value = '<a href="' . esc_url( $value ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $filename ) . '</a>';
				} else {
					$value = wpautop( $value );
				}
				$fields_html .= str_replace( array( '%title%', '%value%' ), array( $title, $value ), $templates['item'] );
			}
			return $templates['start'] . $fields_html . $templates['end'];
		}

		/**
		 * Add input fields to emails.
		 *
		 * @param WC_Order $order Order Object.
		 * @param bool     $sent_to_admin Is sent to admin.
		 * @param bool     $plain_text Is plain text.
		 * @param WC_Email $email Email Object.
		 * @version 1.4.0
		 * @since   1.4.0
		 * @todo    [dev] customizable position (same in `add_input_fields_to_order_details()`)
		 * @todo    [dev] enable/disable per input field or per payment gateway (same in `add_input_fields_to_order_details()`)
		 * @todo    [dev] enable/disable per `$email`
		 */
		public function add_input_fields_to_emails( $order, $sent_to_admin, $plain_text, $email ) {
			if ( 'no' === get_option( 'alg_wc_cpg_input_fields_add_to_emails', 'no' ) ) {
				return;
			}
			if (
			'customer' === get_option( 'alg_wc_cpg_input_fields_add_to_emails_sent_to', 'all' ) && $sent_to_admin ||
			'admin' === get_option( 'alg_wc_cpg_input_fields_add_to_emails_sent_to', 'all' ) && ! $sent_to_admin
			) {
				return;
			}
			$input_fields_meta = $order->get_meta( '_alg_wc_cpg_input_fields', true );
			// $input_fields_meta = get_post_meta( $order->get_id(), '_alg_wc_cpg_input_fields', true );
			if ( ! empty( $input_fields_meta ) ) {
				$templates = ( $plain_text ?
				get_option( 'alg_wc_cpg_input_fields_add_to_emails_template_plain', array() ) :
				get_option( 'alg_wc_cpg_input_fields_add_to_emails_template', array() ) );
				$start     = ( isset( $templates['header'] ) ? $templates['header'] : '' );
				$item      = ( isset( $templates['field'] ) ? $templates['field'] : ( $plain_text ? '%title%: %value%' . "\n" : '<p>%title%: %value%</p>' ) );
				$end       = ( isset( $templates['footer'] ) ? $templates['footer'] : '' );
				echo $this->get_input_fields_output(
					$input_fields_meta,
					array(
						'start' => $start,
						'item'  => $item,
						'end'   => $end,
					)
				);
			}
		}

		/**
		 * Add input fields to order details.
		 *
		 * @param WC_Order $order Order Object.
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function add_input_fields_to_order_details( $order ) {
			if ( 'no' === get_option( 'alg_wc_cpg_input_fields_add_to_order_details', 'no' ) ) {
				return;
			}
			$input_fields_meta = $order->get_meta( '_alg_wc_cpg_input_fields', true );
			// $input_fields_meta = get_post_meta( $order->get_id(), '_alg_wc_cpg_input_fields', true );
			if ( ! empty( $input_fields_meta ) ) {
				$templates = get_option( 'alg_wc_cpg_input_fields_add_to_order_details_template', array() );
				$start     = ( isset( $templates['header'] ) ? $templates['header'] : '<table class="widefat striped"><tbody>' );
				$item      = ( isset( $templates['field'] ) ? $templates['field'] : '<tr><th>%title%</th><td>%value%</td></tr>' );
				$end       = ( isset( $templates['footer'] ) ? $templates['footer'] : '</tbody></table>' );
				echo $this->get_input_fields_output(
					$input_fields_meta,
					array(
						'start' => $start,
						'item'  => $item,
						'end'   => $end,
					)
				);
			}
		}

		/**
		 * Check required input fields.
		 *
		 * @param array $data Data.
		 * @param array $errors Errors.
		 * @version 1.3.0
		 * @since   1.3.0
		 * @todo    [dev] customizable error message
		 */
		public function check_required_input_fields( $data, $errors ) {
			if ( ! empty( $data['payment_method'] ) ) {
				if ( isset( $_POST['alg_wc_cpg_input_fields_required'][ $data['payment_method'] ] ) ) {
					foreach ( $_POST['alg_wc_cpg_input_fields_required'][ $data['payment_method'] ] as $required_field_name => $is_required ) {
						if (
						! isset( $_POST['alg_wc_cpg_input_fields'][ $data['payment_method'] ][ $required_field_name ] ) ||
						'' === $_POST['alg_wc_cpg_input_fields'][ $data['payment_method'] ][ $required_field_name ]
						) {
							$errors->add(
								'alg_wc_custom_payment_gateways',
								// translators: %s Required field name.
								sprintf( __( '%s is a required field.', 'custom-payment-gateways-woocommerce' ), '<strong>' . $required_field_name . '</strong>' )
							);
						}
					}
				}
			}
		}

		/**
		 * Add input fields meta box.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 * @todo    [dev] customizable context (i.e. `side`, `normal`, `advanced`) and priority (i.e. `default`, `low`, `high`)
		 */
		public function add_input_fields_meta_box( $post_type, $post ) {
			if ( 'woocommerce_page_wc-orders' === $post_type || 'shop_order' === $post_type ) {
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					// HPOS usage is enabled.
					global $theorder;
					if ( ! $theorder ) {
						return;
					}
					$input_fields_meta = $theorder->get_meta( '_alg_wc_cpg_input_fields', true );
				} else {
					// Traditional CPT-based orders are in use.
					$input_fields_meta = get_post_meta( get_the_ID(), '_alg_wc_cpg_input_fields', true );
				}

				if ( ! empty( $input_fields_meta ) ) {
					$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
							? wc_get_page_screen_id( 'shop-order' )
							: 'shop_order';

					add_meta_box(
						'alg-wc-cpg-input-fields',
						__( 'Payment gateway input fields', 'custom-payment-gateways-woocommerce' ),
						array( $this, 'display_input_fields_meta_box' ),
						$screen,
						'side'
					);
				}
			}
		}

		/**
		 * Display input fields meta box.
		 *
		 * @param mixed $post_or_order_object Post object.
		 * @version 1.3.0
		 * @since   1.3.0
		 * @todo    [dev] add "Delete data" button
		 */
		public function display_input_fields_meta_box( $post_or_order_object ) {
			$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

			$input_fields_meta = $order->get_meta( '_alg_wc_cpg_input_fields', true );

			echo $this->get_input_fields_output(
				// get_post_meta( get_the_ID(), '_alg_wc_cpg_input_fields', true ),
				$input_fields_meta,
				array(
					'start' => '<table class="widefat striped"><tbody>',
					'item'  => '<tr><th>%title%</th><td>%value%</td></tr>',
					'end'   => '</tbody></table>',
				)
			);
		}

		/**
		 * Add input fields to order meta.
		 *
		 * @param int  $order_id Order ID.
		 * @param bool $posted Posted.
		 * @version 1.5.0
		 * @since   1.3.0
		 * @todo    [dev] (maybe) optional `sanitize_textarea_field` (e.g. `sanitize_text_field` or no sanitization at all)
		 * @todo    [dev] (maybe) get `payment_method` from `$order->get_payment_method()` (as a fallback?)
		 */
		public function add_input_fields_to_order_meta( $order_id, $posted ) {
			if ( ! empty( $_POST['payment_method'] ) && isset( $_POST['alg_wc_cpg_input_fields'][ $_POST['payment_method'] ] ) ) {
				$values = array_map( 'sanitize_textarea_field', $_POST['alg_wc_cpg_input_fields'][ $_POST['payment_method'] ] );
				$order  = wc_get_order( $order_id );
				$order->update_meta_data( '_alg_wc_cpg_input_fields', $values );
				$order->save();
				// update_post_meta( $order_id, '_alg_wc_cpg_input_fields', $values );
				if ( 'yes' === get_option( 'alg_wc_cpg_input_fields_add_order_note', 'no' ) ) {
					$note   = array();
					$note[] = __( 'Payment gateway input fields', 'custom-payment-gateways-woocommerce' ) . ':';
					// $order  = wc_get_order( $order_id );
					foreach ( $values as $title => $value ) {
						$note[] = ( $title . ': ' . $value );
					}
					$order->add_order_note( implode( PHP_EOL, $note ) );
				}
			}

			if ( ! empty( $_POST['payment_method'] ) && 'alg_custom_gateway_1' === $_POST['payment_method'] ) {
				$total_orders = (int) get_option( 'img_cpg_orders', 0 );
				++$total_orders;
				update_option( 'img_cpg_orders', $total_orders );
			}
		}
	}

endif;

return new Alg_WC_Custom_Payment_Gateways_Input_Fields();
