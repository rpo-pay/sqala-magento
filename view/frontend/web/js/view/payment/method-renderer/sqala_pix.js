define([
    'underscore',
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function (
    _,
    $,
    Component,
    quote,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            active: false,
            template: 'Sqala_Payment/payment/pix',
            pixForm: 'Sqala_Payment/payment/pix-form',
            payerDocumentIdentification: null,
            payerOptionsTypes: [],
            payerDocument: '',
            payerType: 'cpf',
        },

        /**
         * Initializes model instance.
         *
         * @returns {Object}
         */
        initObservable() {
            this._super().observe([
                'payerOptionsTypes',
                'payerDocument',
                'payerType',
                'active'
            ]);
            return this;
        },

        /**
         * Get code
         * @returns {String}
         */
        getCode() {
            return 'sqala_pix';
        },

        getTitle() {
            return 'Pix (Sqala)';
        },

        /**
         * Init component
         */
        initialize() {
            this._super();
            console.log($t('Place Order'));
            const self = this;

            this.active.subscribe((value) => {
                if (value === true) {
                    self.getSelectDocumentTypes();
                }
            });
        },

        async getSelectDocumentTypes() {
            const self = this;

            self.payerOptionsTypes([{id: 'cpf', name: 'CPF'}, {id: 'cnpj', name: 'CNPJ'}]);

            if (quote.billingAddress()) {
                const vatId = quote.billingAddress().vatId;
                if (vatId) {
                    self.payerDocument(vatId);
                }
            }
        },

        getValidationForDocument() {
            const self = this;

            return {
                'required': true,
                'validate-document-identification': '#' + self.getCode() + '_document_identification'
            };
        },

        /**
         * Is Active
         * @returns {Boolean}
         */
        isActive() {
            var active = this.getCode() === this.isChecked();

            this.active(active);
            return active;
        },

        /**
         * Init Form Element
         * @returns {void}
         */
        initFormElement(element) {
            this.formElement = element;
            $(this.formElement).validation();
        },

        /**
         * Before Place Order
         * @returns {void}
         */
        beforePlaceOrder() {
            if (!$(this.formElement).valid()) {
                return;
            }
            this.placeOrder();
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData() {
            let self = this;

            return {
                method: self.getCode(),
                'additional_data': {
                    'payer_document_type': self.payerType(),
                    'payer_document_identification': self.payerDocument(),
                }
            };
        },

        /**
         * Get instruction checkout
         * @returns {string}
         */
        getInstructionCheckout() {
            return window.checkoutConfig.payment[this.getCode()].instruction_checkout;
        },

        /**
         * Adds terms and conditions link to checkout
         * @returns {string}
         */
        getFingerprint() {
            return window.checkoutConfig.payment[this.getCode()].fingerprint;
        },
    });
});
