<?php
/**
 * Custom Payment Gateways for WooCommerce - Upgrade options class
 *
 * @version 2.1.0
 * @since   2.1.0
 * @author  Imaginate Solutions
 * @package cpgw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Custom_Payment_Upgrades' ) ) :

	/**
	 * Input Fields Class.
	 */
	class Alg_WC_Custom_Payment_Upgrades {

		/**
		 * Constructor.
		 *
		 * @version 2.1.0
		 * @since   2.1.0
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'save_options' ) );

			add_action( 'admin_notices', array( $this, 'show_review_notice' ) );

			add_action( 'wp_ajax_cpgw_dismiss_review_notice', array( $this, 'cpgw_dismiss_review_notice' ) );

			add_action( 'alg_cpg_upgrade_content', array( $this, 'show_content' ) );
		}

		public function save_options() {
			if ( ! get_option( 'img_cpg_install_date' ) ) {
				update_option( 'img_cpg_install_date', current_time( 'timestamp' ) );
			}

			if ( ! get_option( 'img_cpg_orders' ) ) {
				update_option( 'img_cpg_orders', 0 );
			}
		}

		public function show_review_notice() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( isset( $_GET['page'] ) && ( 'wc-orders' === $_GET['page'] || 'wc-settings' === $_GET['page'] ) ) {
				// Skip if already dismissed
				if ( get_option( 'cpgw_review_notice_dismissed' ) ) {
					return;
				}

				if ( get_option( 'img_cpg_orders', 0 ) < 10 ) {
					return;
				}

				$nonce = wp_create_nonce( 'cpgw_dismiss_notice_nonce' );

				?>

				<div class="notice notice-success is-dismissible cpgw-review-notice">
					<p>
						🎉 <strong>Congratulations!</strong> You have successfully received 10 orders using <strong>Custom Payment Gateways for WooCommerce</strong> 🎉<br><br>
						We hope it has been helpful! Would you consider taking a moment to <a href="https://wordpress.org/support/plugin/custom-payment-gateways-woocommerce/reviews/?rate=5#new-post" target="_blank">leave us a 5-star review</a>?<br>
						Your feedback keeps us going 💖
					</p>
				</div>
				<script>
					(function($) {
						$(document).on('click', '.cpgw-review-notice .notice-dismiss', function() {
							$.post(ajaxurl, {
								action: 'cpgw_dismiss_review_notice',
								_nonce: '<?php echo esc_js($nonce); ?>'
							});
						});
					})(jQuery);
				</script>

				<?php
			}
		}

		public function cpgw_dismiss_review_notice() {
			if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], 'cpgw_dismiss_notice_nonce' ) ) {
				wp_send_json_error( 'Invalid nonce', 403 );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Unauthorized', 403 );
			}

			update_option( 'cpgw_review_notice_dismissed', 1 );
			wp_send_json_success( 'Notice dismissed' );
		}

		public function show_content() {
			?>
			<div class="cpgw-upgrade-page">
				<h1>🚀 Upgrade to Custom Payment Gateways for WooCommerce Pro & Supercharge Your Checkout</h1>
				<p>Enjoy more powerful features, priority support, and exclusive Pro-only capabilities.</p>

				<h2>🔍 Lite vs Pro Comparison</h2>
				<table class="cpgw-comparison">
					<thead>
						<tr>
							<th>Feature</th>
							<th>Lite</th>
							<th>Pro</th>
						</tr>
					</thead>
					<tbody>
						<tr><td>Number of Custom Payment Gateways</td><td>1</td><td>Unlimited</td></tr>
						<tr><td>Custom Fields per Gateway</td><td>1</td><td>Unlimited</td></tr>
						<tr><td>Fees per Gateway</td><td>1</td><td>Unlimited</td></tr>
						<tr><td>Conditional logic based Order amount</td><td>❌</td><td>✅</td></tr>
						<tr><td>Additional placeholders for dynamic data</td><td>❌</td><td>✅</td></tr>
						<tr><td>Priority support</td><td>❌</td><td>✅</td></tr>
					</tbody>
				</table>

				<h2>🎁 Get 15% Off Pro</h2>
				<p><strong>Use coupon <code>CPG15</code> at checkout!</strong></p>
				<p>
					<a href="https://imaginate-solutions.com/downloads/custom-payment-gateways-for-woocommerce/" target="_blank" class="button button-primary button-hero">
						Upgrade to Pro Now →
					</a>
				</p>

				<h2>🌟 What Users Are Saying</h2>
				<div class="cpgw-testimonials">
					<div class="cpgw-testimonial">
						<p>“It simply works. Have a whooping <strong>5 Custom Payment Gateways</strong> installed on our website. This not only gives the end users <strong>more options to pay</strong> but also <strong>more orders</strong> for us.”</p>
						<p><strong>– Noh Balcha</strong></p>
					</div>
					<div class="cpgw-testimonial">
						<p>“I used this to create a gateway using <strong>Zelle, Apple Pay, Gpay</strong> and many others. <strong>Worked like a charm</strong>. Awesome plugin for my WooCommerce Store.”</p>
						<p><strong>– Andrew Bossola</strong></p>
					</div>
				</div>

				<h2><span class="dashicons dashicons-admin-plugins" style="color: #2271b1; display: inline-block;"></span> More Plugins by Us</h2>
				<h4>Trusted by over 25,000 WooCommerce users - discover more plugins to power up your store.</h4>
				<div class="cpgw-plugin-grid">
					<?php
					$plugins = array(
						array(
							'name' => 'File Uploads Addon for WooCommerce',
							'desc' => 'A lightweight WooCommerce plugin to upload different types of files from your WooCommerce Product pages. Save time and capture all the additional information in the order itself.',
							'link' => 'https://imaginate-solutions.com/downloads/woocommerce-addon-uploads/?utm_source=cpgupgrade&utm_medium=litepage&utm_campaign=litevspro',
						),
						array(
							'name' => 'Custom Shipping Methods for WooCommerce',
							'desc' => 'Add customized and conditional shipping methods to your WooCommerce Store. Have more control over the Shipping Methods and set various rules.',
							'link' => 'https://imaginate-solutions.com/downloads/custom-shipping-methods-for-woocommerce/?utm_source=cpgupgrade&utm_medium=litepage&utm_campaign=litevspro',
						),
						array(
							'name' => 'Payment Gateways by User Roles for WooCommerce',
							'desc' => 'Allow rules for WordPress User Roles for your WooCommerce Payment Gateways to show up on your WooCommerce Store.',
							'link' => 'https://imaginate-solutions.com/downloads/payment-gateways-by-user-roles-for-woocommerce/?utm_source=cpgupgrade&utm_medium=litepage&utm_campaign=litevspro',
						),
						array(
							'name' => 'Variations Radio Buttons for WooCommerce',
							'desc' => 'Replace the standard WooCommerce Variable Products drop down box template with radio buttons.',
							'link' => 'https://imaginate-solutions.com/downloads/variations-radio-buttons-for-woocommerce/?utm_source=cpgupgrade&utm_medium=litepage&utm_campaign=litevspro'
						),
					);

					foreach ($plugins as $plugin) {
						echo '<div class="cpgw-plugin-card">';
						echo '<h4>' . esc_html($plugin['name']) . '</h4>';
						echo '<p>' . esc_html($plugin['desc']) . '</p>';
						echo '<a href="' . esc_url($plugin['link']) . '" target="_blank">View Plugin →</a>';
						echo '</div>';
					}
					?>
				</div>
			</div>
			<style>
			.cpgw-upgrade-page { max-width: 1080px; padding: 20px 0; }
			/*.cpgw-upgrade-page h1, .cpgw-upgrade-page h2 { margin-top: 1.5em; }*/
			.cpgw-comparison {
				border-collapse: collapse;
				width: 100%;
				margin-top: 1em;
			}
			.cpgw-comparison th, .cpgw-comparison td {
				border: 1px solid #ccc;
				padding: 10px;
				text-align: center;
			}
			.cpgw-comparison th {
				background: #f7f7f7;
			}
			.cpgw-testimonials {
				display: flex;
				gap: 20px;
				flex-wrap: wrap;
			}
			.cpgw-testimonial {
				background: #fff;
				border: 1px solid #ddd;
				padding: 15px;
				flex: 1 1 45%;
				border-left: 4px solid #2271b1;
			}
			.cpgw-plugin-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
				gap: 20px;
				margin-top: 1em;
			}
			.cpgw-plugin-card {
				background: #f9f9f9;
				padding: 15px;
				border: 1px solid #ccc;
			}
			.cpgw-plugin-card h4 {
				margin-top: 0;
			}
			.button.button-hero {
				font-size: 1.2em;
				padding: 0.8em 2em;
			}
			.woocommerce-save-button {
				display: none;
			}
			</style>
			<?php
		}

	}

endif;

return new Alg_WC_Custom_Payment_Upgrades();