/* global
    Datepicker, DateRangePicker
*/
(function (window) {

    'use strict';


    const document = window.document;
    const selector = {
        dateFormFields: '[data-oap-datepicker]',
        dateRangeFields: '[data-oap-daterangepicker]',
        datePickerIcon: '.form__icon--datepicker',
    };
    const dataSet = {
        datePicker: 'oapDatepicker',
        dateRangePicker: 'oapDaterangepicker',
    };

    const initialize = function () {
        document.querySelectorAll(selector.dateFormFields).forEach(function (formField) {
            const datePickerOptions = formField.dataset[dataSet.datePicker];
            const jsonOptions = JSON.parse(datePickerOptions);

            let datepicker = new Datepicker(formField, jsonOptions);
            let datepickerIcon = formField.parentElement.querySelector(selector.datePickerIcon);

            if (datepickerIcon) {
                datepickerIcon.addEventListener('click', function () {
                    datepicker.show();
                });
            }
        });

        document.querySelectorAll(selector.dateRangeFields).forEach(function (formField) {
            const datePickerOptions = formField.dataset[dataSet.dateRangePicker];
            const jsonOptions = JSON.parse(datePickerOptions);

            let dateRangePicker = new DateRangePicker(formField, jsonOptions);

            dateRangePicker.datepickers.forEach(function (datepicker) {
                let datepickerIcon = datepicker.element.parentElement.querySelector(selector.datePickerIcon);

                if (datepickerIcon) {
                    datepickerIcon.addEventListener('click', function () {
                        datepicker.show();
                    });
                }
            });

        });

    };

    initialize();

}(this));
