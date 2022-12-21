/* global */
(function (window, OAP) {

    'use strict';


    const document = window.document;
    const selector = {
        formattingField: '.form__textfield--number',
    };

    const initialize = function () {

        document.querySelectorAll(selector.formattingField).forEach(function (formField) {

            formField.addEventListener('blur', function () {
                if (this.value.toString().trim() === '') {
                    return;
                }

                if (OAP.utils && OAP.utils.formatNumber) {
                    let fieldValue = this.value.toString().replace(/\./g, '').replace(/,/g, '.');

                    this.value = OAP.utils.formatNumber.format(fieldValue);
                }
            });
        });
    };

    initialize();

}(this, this.OAP || {}));
