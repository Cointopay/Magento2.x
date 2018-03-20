/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
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
                type: 'cointopay_gateway',
                component: 'Cointopay_PaymentGateway/js/view/payment/method-renderer/cointopay_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
