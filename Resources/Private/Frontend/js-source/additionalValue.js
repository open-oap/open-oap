/* global */
(function (window) {

    'use strict';


    const document = window.document;
    const selector = {
        checkableGroupWithAdditionalValue: '.form__checkable-group--additionalvalue',
        checkableItem: '.form__checkable-label input.form__checkable',
        additionalInput: '.form__textfield',
    };

    const initialize = function () {

        document.querySelectorAll(selector.checkableGroupWithAdditionalValue).forEach(function (formField) {
            // last item checked?
            let additionalInput = formField.querySelector(selector.additionalInput);
            let allItems = formField.querySelectorAll(selector.checkableItem);
            let lastItem = allItems[allItems.length - 1];

            additionalInput.disabled = !lastItem.checked;

            if (lastItem.type === 'radio') {
                allItems.forEach(function (option) {
                    option.addEventListener('change', function () {
                        additionalInput.disabled = !lastItem.checked;
                    });
                });
            }

            if (lastItem.type === 'checkbox') {
                lastItem.addEventListener('change', function () {
                    additionalInput.disabled = !lastItem.checked;
                });
            }
        });
    };

    initialize();

}(this));
