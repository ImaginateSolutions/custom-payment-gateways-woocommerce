import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { useEffect, useState, useCallback } from '@wordpress/element';

import Fields from './fields';
import './style.scss';

const { extensionCartUpdate } = wc.blocksCheckout;
const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;
const { PAYMENT_STORE_KEY } = window.wc.wcBlocksData;

[...Array(1)].map((e, i) => {
    const settings = getSetting(`alg_custom_gateway_${i + 1}_data`, {});
    const label = decodeEntities(settings.title);
    
    var newlabels = '';
    var newvalues = '';
    var newobject = [];

    const Content = (props) => {
        const { eventRegistration, emitResponse, useState } = props;
        const { onPaymentSetup } = eventRegistration;
        const [fieldValues, setFieldValues] = useState([{}]);

        const onInputsChanges = useCallback(
            (value, index, label, type) => {
                setFieldValues((prevValues) => {
                    const newValues = [...prevValues];
                    if (!newValues[index]) newValues[index] = {};
                    // For file input, store file object or file name
                    if (type === 'file') {
                        newValues[index] = { ...newValues[index], label: label, value: value, file: value };
                    } else {
                        newValues[index] = { ...newValues[index], label: label, value: value };
                    }
                    return newValues;
                });
            },
            [setFieldValues]
        );

        newobject = fieldValues;
        newlabels = fieldValues.map(item => item.label);
        newvalues = fieldValues.map(item => item.value);

        useEffect(() => {

            const unsubscribe = onPaymentSetup(async () => {
                // Here we can do any processing we need, and then emit a response.
                // For example, we might validate a custom field, or perform an AJAX request, and then emit a response indicating it is valid or not.

                const missingRequiredFields = settings.fields.some((field, index) => {
                    if ('checkbox' === field.type) {
                        return field.required && (!newobject[index]?.value);
                    } else if ('file' === field.type) {
                        // File is required and not uploaded
                        return field.required && (!newobject[index]?.file || (newobject[index]?.file instanceof File && !newobject[index]?.file.name));
                    } else {
                        return field.required && (!newobject[index]?.value || newobject[index]?.value.trim() === '');
                    }
                });
               
                if (missingRequiredFields) {
                    return {
                        type: emitResponse.responseTypes.ERROR,
                        message: __('Please fill in all required fields.', 'custom-payment-gateways-woocommerce'),
                    };
                }

                if (newobject[i]?.file && newobject[i]?.file instanceof File) {

                    const fileField = settings.fields.find(f => f.type === 'file');
                    const fileSizeLimit = fileField?.file_size;
                    const fileTypes = fileField?.file_types;

					const formData = new FormData();
					formData.append('custom_file', newobject[i]?.file);
					formData.append('action', 'custom_gateway_upload_file');
					formData.append('security', alg_wc_custom_payment_gateways_data.nonce); // nonce from localized script
                    formData.append('file_upload_size', fileSizeLimit); // nonce from localized script
				    formData.append('file_upload_types', fileTypes); // nonce from localized script
					try {
						const response = await fetch(alg_wc_custom_payment_gateways_data.ajax_url, {
							method: 'POST',
							body: formData,
						});
						const result = await response.json();
                        
						if (result.success && result.data?.file_url) {
							newvalues = [result.data.file_url];
						} else {
							return {
								type: emitResponse.responseTypes.ERROR,
								message: __(  result.data.message, 'custom-payment-gateways-woocommerce'),
							};
						}
					} catch (e) {
						return {
							type: emitResponse.responseTypes.ERROR,
							message: __('File upload failed due to error.', 'custom-payment-gateways-woocommerce'),
						};
					}
				}
               

                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            customGatewayIS: true,
                            GatewayISData: newvalues.join(','),
                            GatewayISNames: newlabels.join(','),
                        },
                    },
                };
                
            });
            // Unsubscribes when this component is unmounted.
            return () => {
                unsubscribe();
            };
        }, [emitResponse.responseTypes, onPaymentSetup, setFieldValues]);

        return (
            <>
                <div dangerouslySetInnerHTML={{ __html: decodeEntities(settings.description || '') }} />
                {settings?.fields?.map((item, index) => (
                    <Fields
                        key={item.name || index}
                        attributes={{
                            ...item,
                            errorId: `${item.name}-error`,
                            placeholder: undefined,
                            ...(item.type === 'checkbox' ? { checked: fieldValues[index]?.['value'] } : { value: fieldValues[index]?.['value'] || '' }),
                            ...(item.type === 'select' ? {
                                options: item.options?.map((label, index) => ({
                                    key: label,
                                    label: label
                                }))
                            } : {}),
                            ...(item.type === 'file' ? {
                                // For file input, value should be undefined, let browser handle file input internally
                                value: undefined
                            } : {})
                        }}
                        event={(valueOrEvent) => {
                            // For file input, value is event.target.files[0]
                            if (item.type === 'file') {
                                // For file input, valueOrEvent is the event object
                                const fileObj = valueOrEvent?.target?.files?.[0] || null;
                                onInputsChanges(fileObj, index, item.label, item.type);
                            } else if (item.type === 'select') {
                                onInputsChanges(valueOrEvent.target.value, index, item.label, item.type);
                            } else {
                                onInputsChanges(valueOrEvent, index, item.label, item.type);
                            }
                        }}
                    />
                ))}
            </>
        );
    };

    const Label = (props) => {
        const { PaymentMethodLabel } = props.components;
        return <PaymentMethodLabel text={label} />;
    };

    window.onload = function () {
        const iconElement = document.querySelector(
            '#radio-control-wc-payment-method-options-alg_custom_gateway_1__label'
        );
        if (iconElement && settings.icon) {
            const imgElement = document.createElement('img');
            imgElement.src = settings.icon;
            imgElement.alt = settings.title;
            imgElement.style.width = '24px';
            imgElement.style.height = '24px';

            iconElement.appendChild(imgElement);
        }
    };

    registerPaymentMethod({
        name: `alg_custom_gateway_${i + 1}`,
        gatewayId: `alg_custom_gateway_${i + 1}`,
        label: <Label />,
        content: <Content useState={useState} />,
        edit: <Content useState={useState} />,
        canMakePayment: () => true,
        ariaLabel: label || 'gateway',
        supports: {
            features: settings.supports,
        },
    });
});
wp?.hooks?.addAction('experimental__woocommerce_blocks-checkout-set-active-payment-method', 'extension-fees', function (paymentMethod) {
    var update_cart = extensionCartUpdate({
        namespace: 'extension-fees',
        data: {
            payment_method: paymentMethod.value
        },
    });
});