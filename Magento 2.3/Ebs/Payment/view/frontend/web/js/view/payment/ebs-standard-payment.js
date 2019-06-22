/** Ebs/Payment/view/frontend/web/js/view/payment/secureebsstandard-method.js **/
define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
 
        return Component.extend({
            defaults: {
                template: 'Ebs_Payment/payment/secureebsstandard'
            },
 
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
 
            
        });
    }
);