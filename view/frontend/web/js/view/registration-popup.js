/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Vivek_RegistrationPopup/js/action/signup',
    'Magento_Customer/js/customer-data',
    'Vivek_RegistrationPopup/js/model/registration-popup',
    'mage/translate',
    'mage/url',
    'Magento_Ui/js/modal/alert',
    'mage/validation'
], function ($, ko, Component, signupAction, customerData, registrationPopup, $t, url, alert) {
    'use strict';

    return Component.extend({
        formUrl: window.registrationPopup.formUrl,
        modalWindow: null,
        isLoading: ko.observable(false),

        /**
         * Init
         */
        initialize: function () {
            var self = this;

            this._super();
            let url = this.formUrl;
            $.get(url, function (res) {
                $(res).appendTo('#registrationPopup').applyBindings();
            });
            signupAction.registerLoginCallback(function () {
                self.isLoading(false);
            });
        },

        /** Init popup registration window */
        setModalElement: function (element) {
            if (registrationPopup.modalWindow == null) {
                registrationPopup.createPopUp(element);
            }
        },

        /** Is registration form enabled for current customer */
        isActive: function () {
            var customer = customerData.get('customer');

            return customer() == false; //eslint-disable-line eqeqeq
        },

        /** Show registration popup window */
        showModal: function () {
            if (this.modalWindow) {
                $(this.modalWindow).modal('openModal');
            } else {
                alert({
                    content: $t('Guest checkout is disabled.')
                });
            }
        },

        /** Close registration popup window */
        closeModal: function () {
            registrationPopup.closeModal();
        },

        /**
         * Provide registration action
         *
         * @return {Boolean}
         */
        signup: function (formUiElement, event) {
            var signupData = {},
                formElement = $(event.currentTarget),
                formDataArray = formElement.serializeArray();

            event.stopPropagation();
            formDataArray.forEach(function (entry) {
                signupData[entry.name] = entry.value;
            });

            if (formElement.validation() &&
                formElement.validation('isValid')
            ) {
                this.isLoading(true);
                signupAction(signupData);
            }

            return false;
        }
    });
});
