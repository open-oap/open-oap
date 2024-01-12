(function (window) {

    'use strict';


    const document = window.document;
    const selector = {
        modifiableFormFields: '[data-oap-modificators]',
    };
    const dataSet = {
        modificators: 'oapModificators',
        type: 'oapType',
        totalItems: 'oapTotalItems',
        totalTriggers: 'oapTotalTriggers',
        totalCarryover: 'oapTotalCarryover',
    };
    const idRoot = 'oap-proposal-';
    const modificatorSeparator = ',';
    let modifyOnStart = false;
    let countId = 0;
    let modifiableFormFields = [];
    let activeFormIds = [];

    const addModificator = function (formField) {
        const type = formField.dataset[dataSet.type];
        const rawModificators = formField.dataset[dataSet.modificators].split(modificatorSeparator);
        // Clear out duplicates:
        const modificators = [...new Set(rawModificators)];

        const form = formField.closest('form');

        if (form) {
            activeFormIds.push(getId(form));
        }

        formField.addEventListener('blur', function () {
            modifyFormField(formField, type, modificators);
        });

        // BE displaying main error list, so validate immediately
        if (modifyOnStart !== '0') {
            modifyFormField(formField, type, modificators);
        }

        /**
         *  Validate initial value - unclear if needed
         * validateFormField(formField, type, validators);
         */

        modifiableFormFields.push({
            formField: formField,
            modificators: modificators,
            formId: form.id,
            type: type,
        });
    };

    const convertStringToNumber = function (value) {
        if (!value) {
            return 0;
        }

        return Number(value.toString().replace(/\./g, '').replace(/,/g, '.'));
    };

    const modifyFormField = function (formField, type, modificators) {
        modificators.forEach(function (modificator) {
            // remove leading and trailing spaces
            if (formField.type !== 'file' && formField.type !== 'select-multiple') {
                // there is an unknown problem with file names?
                formField.value = formField.value.toString().trim();
            }

            /*
             * selectable modificators: - definition see cb_packages/open_oap/Classes/Controller/OapAbstractController.php
             *   MODIFICATOR_TOTAL
             */

            switch (modificator) {
            case 'MODIFICATOR_TOTAL': {
                // no logic required for the grand total field
                if (typeof formField.dataset[dataSet.totalItems] !== 'undefined') {
                    return;
                }

                if (typeof formField.dataset[dataSet.totalTriggers] === 'undefined') {
                    return;
                }

                const fieldTriggers = document.querySelectorAll(formField.dataset[dataSet.totalTriggers]);

                for (const fieldTrigger of fieldTriggers) {
                    if (typeof fieldTrigger.dataset[dataSet.totalItems] === 'undefined') {
                        return;
                    }

                    const fieldCarryover = Number(fieldTrigger.dataset[dataSet.totalCarryover]);
                    const fieldItems = fieldTrigger.dataset[dataSet.totalItems].split(',');

                    let value = fieldCarryover;

                    for (let fieldItemId of fieldItems) {
                        const fieldItem = document.querySelector(fieldItemId);

                        value += convertStringToNumber(fieldItem.value);
                    }

                    fieldTrigger.value = value;

                    // trigger validators/formatNumber
                    fieldTrigger.dispatchEvent(new Event('blur'));
                }
                break;
            }
            }
        });
    };

    const getId = function (element) {
        if (element.id) {
            return element.id;
        }

        countId += 1;
        element.id = idRoot + countId;

        return idRoot + countId;
    };

    const initialize = function () {
        document.querySelectorAll(selector.modifiableFormFields).forEach(function (formField) {
            addModificator(formField);
        });
    };

    initialize();

}(this));
