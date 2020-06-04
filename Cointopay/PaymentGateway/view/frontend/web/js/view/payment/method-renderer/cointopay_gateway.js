/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
		'Magento_Checkout/js/action/redirect-on-success',
    ],
    function (Component, redirectOnSuccessAction, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Cointopay_PaymentGateway/payment/form',
                transactionResult: ''
            },

            initObservable: function () {

                this._super()
                    .observe([
                        'transactionResult'
                    ]);
                return this;
            },

            getCode: function() {
                return 'cointopay_gateway';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_result': this.transactionResult()
                    }
                };
            },

            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.cointopay_gateway.transactionResults, function(value, key) {
                    return {
                        'value': key,
                        'transaction_result': value
                    }
                });
            },
            afterPlaceOrder: function () {
               console.log( redirectOnSuccessAction.redirectUrl );
            }
        });
    }
);