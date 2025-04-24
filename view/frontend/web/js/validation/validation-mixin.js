define(['jquery'], function ($) {
    'use strict';

    return function () {
        function validateCPF (cpf) {
            cpf = cpf.replace(/[^\d]+/g, '');

            if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) {
                return false;
            }

            let sum = 0;
            for (let i = 0; i < 9; i++) {
                sum += parseInt(cpf.charAt(i)) * (10 - i);
            }
            let rev = 11 - (sum % 11);
            if (rev === 10 || rev === 11) {
                rev = 0;
            }
            if (rev !== parseInt(cpf.charAt(9))) {
                return false;
            }

            sum = 0;
            for (let i = 0; i < 10; i++) {
                sum += parseInt(cpf.charAt(i)) * (11 - i);
            }
            rev = 11 - (sum % 11);
            if (rev === 10 || rev === 11) {
                rev = 0;
            }

            return rev === parseInt(cpf.charAt(10));
        }

        function validateCNPJ (cnpj) {
            cnpj = cnpj.replace(/[^\d]+/g, '');

            if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) {
                return false;
            }

            let length = cnpj.length - 2;
            let numbers = cnpj.substring(0, length);
            let digits = cnpj.substring(length);
            let sum = 0;
            let pos = length - 7;

            for (let i = length; i >= 1; i--) {
                sum += numbers.charAt(length - i) * pos--;
                if (pos < 2) pos = 9;
            }

            let result = sum % 11 < 2 ? 0 : 11 - (sum % 11);
            if (result !== parseInt(digits.charAt(0))) {
                return false;
            }

            length = length + 1;
            numbers = cnpj.substring(0, length);
            sum = 0;
            pos = length - 7;

            for (let i = length; i >= 1; i--) {
                sum += numbers.charAt(length - i) * pos--;
                if (pos < 2) pos = 9;
            }

            result = sum % 11 < 2 ? 0 : 11 - (sum % 11);
            return result === parseInt(digits.charAt(1));
        }

        $.validator.addMethod(
            'validate-document-identification',
                function (value) {
                    const type = $('#sqala_pix_payer_document_type').val();
                    const documment = value.replace(/[^\d]+/g, '');

                    if (type === 'cnpj' && documment.length === 14) {
                        return validateCNPJ(documment);
                    } else if (type === 'cpf' && documment.length === 11) {
                        return validateCPF(documment);
                    }

                    return false;
                },
            $.mage.__('Please provide a valid document identification.')
        );
    };
});
