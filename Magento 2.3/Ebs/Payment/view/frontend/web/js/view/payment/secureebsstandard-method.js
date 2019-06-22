/** Ebs/Payment/view/frontend/web/js/view/payment/secureebsstandard.js **/
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
        rendererList.push(
            {
				type: 'secureebsstandard',
                component: 'Ebs_Payment/js/view/payment/method-renderer/ebs-standard-payment'
				
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);