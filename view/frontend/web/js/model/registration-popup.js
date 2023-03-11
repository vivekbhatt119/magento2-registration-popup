/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return {
        modalWindow: null,

        /**
         * Create popUp window for provided element
         *
         * @param {HTMLElement} element
         */
        createPopUp: function (element) {
            var options = {
                'type': 'popup',
                'modalClass': 'popup-registration',
                'focus': '[name=firstname]',
                'responsive': true,
                'innerScroll': true,
                'buttons': []
            };

            this.modalWindow = element;
            $(this.modalWindow).modal(options);
        },

        /** Show registration popup window */
        showModal: function () {
            $(this.modalWindow).modal('openModal').trigger('contentUpdated');
        },

        /** Close registration popup window */
        closeModal: function () {
            $(this.modalWindow).modal('closeModal');
        }
    };
});
