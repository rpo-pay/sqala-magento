var config = {
    map: {
        '*': {
            QRCode: 'Sqala_Payment/js/qrcode.min'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Sqala_Payment/js/validation/validation-mixin': true
            }
        }
    }
};
