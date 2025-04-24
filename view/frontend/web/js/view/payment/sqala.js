define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        var config = window.checkoutConfig.payment,
            methodPix = 'sqala_pix';


        if (methodPix in config) {
            rendererList.push(
                {
                    type: methodPix,
                    component: 'Sqala_Payment/js/view/payment/method-renderer/sqala_pix'
                }
            );
        }

        return Component.extend({});
    }
);
