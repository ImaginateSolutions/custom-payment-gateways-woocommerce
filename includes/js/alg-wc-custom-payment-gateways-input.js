/**
 * alg-wc-custom-payment-gateways.js
 *
 * @version 1.6.0
 * @since   1.6.0
 * @author  Imaginate Solutions
 */

jQuery(
	function() {
		// jQuery( 'body' ).on(
		// 	'change',
		// 	'input[name="payment_method"]',
		// 	function() {
		// 		jQuery( 'body' ).trigger( 'update_checkout' );
		// 	}
		// );


		jQuery('body')
		.off('change', '.alg-wc-cpg-file-fields') // remove previous
		.on('change', '.alg-wc-cpg-file-fields', function () {

			if ( ! this.files.length ) {
				jQuery('.alg-wc-cpg-file-fields-value').val('');
			} else {

				// we need only the only one for now, right?
				const file = this.files[0];
				const fileSizeLimit = parseInt(jQuery(this).attr('file_size'), 10);
				const fileTypes = jQuery(this).attr('file_types');
				//jQuery( '#misha_filelist' ).html( '<img width="50" height="50" src="' + URL.createObjectURL( file ) + '"><span>' + file.name + '</span>' );

				const formData = new FormData();
				formData.append('custom_file', file);
				formData.append('action', 'custom_gateway_upload_file');
				formData.append('security', alg_wc_custom_payment_gateways_data.nonce); // nonce from localized script
				formData.append('file_upload_size', fileSizeLimit); // nonce from localized script
				formData.append('file_upload_types', fileTypes); // nonce from localized script
				jQuery.ajax({
					url: alg_wc_custom_payment_gateways_data.ajax_url,
					type: 'POST',
					data: formData,
					contentType: false,
					enctype: 'multipart/form-data',
					processData: false,
					success: function ( response ) {
						jQuery('.woocommerce-error-upload').remove(); // remove old error

						if (response.success && response.data?.file_url) {
							jQuery('.alg-wc-cpg-file-fields-value').val(response.data.file_url);
						} else {
							const message = response.data?.message || 'Unknown upload error.';
							const errorHtml = `
								<ul class="woocommerce-error woocommerce-error-upload" role="alert">
									<li>${message}</li>
								</ul>
							`;

							// target only the checkout form's notices wrapper
							const $wrapper = jQuery('.alg-wc-cpg-file-fields').closest('form.checkout').find('.woocommerce-notices-wrapper').first();

							if ($wrapper.length) {
								$wrapper.prepend(errorHtml);
							} else {
								jQuery('form.checkout').before('<div class="woocommerce-notices-wrapper">' + errorHtml + '</div>');
							}

							jQuery('html, body').animate({
								scrollTop: jQuery('.woocommerce-notices-wrapper').offset().top - 50
							}, 300);
						}
					}
				});

			}

		} );
	}
);
