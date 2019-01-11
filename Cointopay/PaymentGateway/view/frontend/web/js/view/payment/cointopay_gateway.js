/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'jquery'
    ],
    function (
        Component,
        rendererList,
        $
    ) {
        'use strict';
        $(function() {
            $(document).on('change', ".cointopay_coin_val", function () {
                var coinVal = $(this).val();
                    $.ajax ({
                        url: '/paymentcointopay/coin/',
                        showLoader: true,
                        data: {coinId:coinVal, type:'security'},
                        type: "POST",
                        success: function(result) {
                            
                        }
                    });
                });
            });
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