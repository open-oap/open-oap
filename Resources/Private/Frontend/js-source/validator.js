
(function (window, OAP) {

    'use strict';


    const document = window.document;
    const selector = {
        validatableFormFields: '[data-oap-validat]',
        // serverErrorList: '.form__error-list-box',
        serverErrorList: '[data-oap-servererror]',
        enableDisabledElements: '[type=checkbox][disabled], [type=radiobutton][disabled], select[disabled]',
    };
    const cssClass = {
        pseudoFileInput: 'form__upload-value',
        errorMessage: 'error-message',
        errorMessageLabel: 'form__label-question--error',
        errorMessageItem: 'error-message__item'
    };
    const dataSet = {
        validate: 'oapValidat',
        type: 'oapType',
        maxlength: 'oapMaxlength',
        minvalue: 'oapMinvalue',
        maxvalue: 'oapMaxvalue',
        warningAccepted: 'warningAccepted',
        showAlwaysModal: 'oapShowmodal',
        modalText: 'oapModaltext',
        modalSubmit: 'oapModalsubmit',
        modalCancel: 'oapModalcancel',
        serverError: 'oapServererror',
        uploadresult: 'oapUploadvalidation',
    };
    const labels = OAP.labels || {};
    const idRoot = 'oap-proposal-';
    const blockSubmit = true;
    const validatorSeparator = ',';
    const errorIcon = '<svg class="form__icon form__icon--error" width="20" height="22" focusable="false" aria-hidden="true"> <use xlink:href="/typo3conf/ext/open_oap/Resources/Public/Icons/sprite.svg#icon-error" x="0" y="0"/> </svg>';
    let validationOnStart = false;
    let countId = 0;
    let validatableFormFields = [];
    let activeFormIds = [];
    let selectValues = {};

    const addValidation = function (formField) {
        const type = formField.dataset[dataSet.type];
        const rawValidators = formField.dataset[dataSet.validate].split(validatorSeparator);
        // Clear out duplicates:
        const validators = [...new Set(rawValidators)];
        let insertionParent = formField.closest('label') || formField.parentNode;

        if (type === 'TYPE_CHECKBOX' || type === 'TYPE_RADIOBUTTON') {
            insertionParent = formField.closest('.form__checkable-group');
        }

        const messageBox = addMessageBox(insertionParent, formField);
        const form = formField.closest('form');

        if (form) {
            activeFormIds.push(getId(form));
        }

        formField.labelElement = document.querySelector('[for="' + formField.id + '"]');
        if (type === 'TYPE_CHECKBOX' || type === 'TYPE_RADIOBUTTON') {
            formField.labelElement = document.querySelector('#' + formField.labelElement.dataset.legend);
        }

        formField.addEventListener('blur', function () {
            selectValues = {};

            validateFormField(formField, type, validators, messageBox);
        });

        // BE displaying main error list, so validate immediately
        if (validationOnStart !== '0') {
            validateFormField(formField, type, validators, messageBox);
        }

        /**
         *  Validate initial value - unclear if needed
         * validateFormField(formField, type, validators, messageBox);
         */

        validatableFormFields.push({
            formField: formField,
            validators: validators,
            messageBox: messageBox,
            formId: form.id,
            type: type,
        });
    };

    const convertStringToDate = function (value) {
        if (!value) {
            return false;
        }

        value = value.toString().replace(/^(.{2})\.(.{2})\.(.{4})$/, '$3-$2-$1');

        let valueParsed = Date.parse(value);

        return valueParsed;
    };

    const validateFormField = function (formField, type, validators, messageBox) {
        let passedValidation = {
            critical: true,
            relaxed: true
        };

        // Clear old messages before proceeding
        formField.removeAttribute('aria-invalid');

        if (formField.labelElement) {
            formField.labelElement.classList.remove(cssClass.errorMessageLabel);
        }

        messageBox.innerHTML = '';

        validators.forEach(function (validator) {
            // remove leading and trailing spaces

            if (formField.type !== 'file' && formField.type !== 'select-multiple') {
                // there is an unknown problem with file names?
                formField.value = formField.value.toString().trim();
            }

            const fieldHasValue = formField.value && formField.value !== '';
            // Field is a number, and contains no non-number characters (allows dots, but not commas)
            const fieldValueIsNumber = !isNaN(Number(formField.value));
            /*
             * selectable validations: - definition see cb_packages/open_oap/Classes/Controller/OapAbstractController.php
             *   VALIDATOR_MANDATORY
             *   VALIDATOR_INTEGER - done
             *   VALIDATOR_MAXCHAR - done
             *   VALIDATOR_MINVALUE - done
             *   VALIDATOR_MAXVALUE - done
             *   VALIDATOR_EMAIL - done
             *   VALIDATOR_WEBSITE - NOT USED
             *   VALIDATOR_PHONE - done
             */

            switch (validator) {
            // No default
            case 'VALIDATOR_MANDATORY': {
                let passed = true;

                switch (formField.type) {
                case 'checkbox':
                case 'radio': {
                    const parentId = document.querySelector('#' + formField.id).closest('.form__group').querySelector(':scope legend').id;

                    if (typeof selectValues[parentId] === 'undefined') {
                        // .form__checkable is necessary for checkbox, because TYPO3 creates hidden input fields there.
                        const selectItems = document.querySelector('#' + formField.id).closest('.form__checkable-group').querySelectorAll('input.form__checkable');

                        passed = false;
                        selectItems.forEach(function (selectItem) {
                            if (selectItem.checked) {
                                passed = true;
                            }
                        });
                        selectValues[parentId] = passed;
                    }
                    break;
                }
                case 'hidden': {
                    if (formField.classList.contains(cssClass.pseudoFileInput)) {
                        if (!formField.value || formField.value === '') {
                            passed = false;
                        }
                    }
                    break;
                }
                case 'file': {
                    passed = true;
                    break;
                }
                default: {
                    if (!formField.value || formField.value === '') {
                        passed = false;
                    }
                }
                }

                if (!passed) {
                    showErrorMessage(messageBox, labels.JSMSG_MANDATORY, formField);
                    passedValidation.relaxed = false;
                }
                break;
            }
            case 'VALIDATOR_MAXCHAR': {
                if ((formField.dataset[dataSet.maxlength] - [...formField.value.replace(/[\n\r\t]/g, '')].length) < 0) {
                    showErrorMessage(messageBox, labels.JSMSG_MAX_LEN_EXCEEDED, formField);
                    passedValidation.critical = false;
                }
                break;
            }
            case 'VALIDATOR_INTEGER': {
                /**
                 * Only run if field has a value
                 * Value could be a string - need to convert with Number(), then check if it's an integer.
                 * NOTE: '1.000', '123.00' etc would match as integer
                 */
                if (fieldHasValue && !Number.isInteger(Number(formField.value.toString().replace(/\./g, '').replace(/,/g, '.')))) {
                    showErrorMessage(messageBox, labels.JSMSG_NO_INTEGER, formField);
                    passedValidation.critical = false;
                }
                break;
            }
            case 'VALIDATOR_FLOAT': {
                /**
                 * Only run if field has a value
                 * Value could be a string - need to convert with Number(), then check if it's an float/finite.
                 */
                if (fieldHasValue && !Number.isFinite(Number(formField.value.toString().replace(/\./g, '').replace(/,/g, '.')))) {
                    showErrorMessage(messageBox, labels.JSMSG_NO_FLOAT, formField);
                    passedValidation.critical = false;
                }
                break;
            }
            case 'VALIDATOR_MINVALUE': {
                /**
                 * Only run if field has a value
                 * Value could be a string - need to convert with Number()
                 * Fails validation if not a number, or if less than minvalue
                 */
                if (!fieldHasValue) {
                    break;
                }
                switch (type) {
                case 'TYPE_DATE1':
                case 'TYPE_DATE2': {
                    let currentValueDate = convertStringToDate(formField.value);
                    let minValueDate = convertStringToDate(formField.dataset[dataSet.minvalue]);

                    if (currentValueDate < minValueDate) {
                        passedValidation.critical = false;
                    }
                    break;
                }

                case 'TYPE_UPLOAD': {
                    // files are saved in a hidden field. To detext this for files we are using a certain class
                    if (formField.classList.contains(cssClass.pseudoFileInput) && formField.value !== '') {
                        let fileRefs = formField.value.split(',');

                        if (fileRefs.length < formField.dataset[dataSet.minvalue]) {
                            /*
                             * Too few files is no reason not to be able to save.
                             * The final validation will then only take place on the closing page.
                             * But the hint can already be given out here.
                             */
                            passedValidation.critical = true;
                            showErrorMessage(messageBox, labels.JSMSG_MIN_VALUE, formField, {
                                '%d': formField.dataset[dataSet.minvalue]
                            });
                        }
                    }
                    break;
                }

                default: {
                    let unformattedValue = formField.value.toString().replace(/\./g, '').replace(/,/g, '.');

                    if (!fieldValueIsNumber || Number(unformattedValue) < formField.dataset[dataSet.minvalue]) {
                        passedValidation.critical = false;
                    }
                }
                }

                if (!passedValidation.critical) {
                    let minValueFormatted = formField.dataset[dataSet.minvalue];

                    if (OAP.utils && OAP.utils.formatNumber) {
                        minValueFormatted = OAP.utils.formatNumber.format(formField.dataset[dataSet.minvalue]);
                    }
                    showErrorMessage(messageBox, labels.JSMSG_MIN_VALUE, formField, {
                        '%d': minValueFormatted
                    });
                }

                break;
            }
            case 'VALIDATOR_MAXVALUE': {
                /**
                 * Only run if field has a value
                 * Value could be a string - need to convert with Number()
                 * Fails validation if not a number, or if greater than maxvalue
                 */
                switch (type) {
                case 'TYPE_DATE1':
                case 'TYPE_DATE2': {
                    let currentValueDate = convertStringToDate(formField.value);
                    let maxValueDate = convertStringToDate(formField.dataset[dataSet.maxvalue]);

                    if (currentValueDate > maxValueDate) {
                        passedValidation.critical = false;
                    }
                    break;
                }
                case 'TYPE_UPLOAD': {
                    // files are saved in a hidden field. To detext this for files we are using a certain class
                    if (formField.classList.contains(cssClass.pseudoFileInput) && formField.value !== '') {
                        let fileRefs = formField.value.split(',');

                        if (fileRefs.length > formField.dataset[dataSet.maxvalue]) {
                            // Too many files is a reason for not being able to save.
                            passedValidation.critical = false;
                        }
                    }
                    break;
                }

                default: {
                    let unformattedValue = formField.value.toString().replace(/\./g, '').replace(/,/g, '.');

                    if (!fieldValueIsNumber || Number(unformattedValue) > formField.dataset[dataSet.maxvalue]) {
                        passedValidation.critical = false;
                    }
                }
                }

                if (!passedValidation.critical) {
                    let maxValueFormatted = formField.dataset[dataSet.maxvalue];

                    if (OAP.utils && OAP.utils.formatNumber) {
                        maxValueFormatted = OAP.utils.formatNumber.format(maxValueFormatted);
                    }
                    showErrorMessage(messageBox, labels.JSMSG_MAX_VALUE, formField, {
                        '%d': maxValueFormatted
                    });
                }

                break;
            }
            case 'VALIDATOR_EMAIL': {
                /**
                 * Only run if field has a value
                 * Basic email regex only, does *not* guarantee a valid email address
                 */
                if (fieldHasValue && !/^\S+@\S+\.\S+$/.test(formField.value)) {
                    showErrorMessage(messageBox, labels.JSMSG_INVALID_EMAIL, formField);
                    passedValidation.critical = false;
                }
                break;
            }
            case 'VALIDATOR_PHONE': {
                /**
                 * Only run if field has a value
                 * Basic regex only
                 */
                if (fieldHasValue && !/^[0-9\s()+-]*$/.test(formField.value)) {
                    showErrorMessage(messageBox, labels.JSMSG_INVALID_PHONE, formField);
                    passedValidation.critical = false;
                }
                break;
            }
            case 'VALIDATOR_FILE': {
                // validate file upload - decentralised - Check takes place in upload.js
                if (formField.type !== 'file') {
                    let uploadValidation = formField.dataset[dataSet.uploadresult];

                    if (uploadValidation.includes('size')) {
                        showErrorMessage(messageBox, labels.JSMSG_UPLOAD_ERROR_FILE_SIZE_SIMPLE, formField);
                        passedValidation.critical = false;
                    }
                    if (uploadValidation.includes('max')) {
                        showErrorMessage(messageBox, labels.JSMSG_UPLOAD_MAX_FILES_REACHED, formField);
                        passedValidation.critical = false;
                    }
                    if (uploadValidation.includes('min')) {
                        showErrorMessage(messageBox, labels.JSMSG_UPLOAD_MIN_FILES_REACHED, formField);
                        passedValidation.critical = false;
                    }
                }
            }
            }
        });

        return passedValidation;

    };

    const addMessageBox = function (insertionParent, formField) {
        let messageBox = document.createElement('div');

        messageBox.classList.add(cssClass.errorMessage);

        if (formField.id && formField.id !== '') {
            const currentAriaDescribedBy = formField.getAttribute('aria-describedby');

            let messageBoxId = formField.id + '-error';

            messageBox.id = messageBoxId;

            // Preserve and extend any semantic relationships
            formField.setAttribute('aria-describedby', (currentAriaDescribedBy ? currentAriaDescribedBy + ' ' : '') + messageBoxId);
        }

        insertionParent.appendChild(messageBox);

        return messageBox;
    };

    const showErrorMessage = function (messageBox, message, formField, replacements) {
        if (replacements) {
            Object.entries(replacements).forEach(function (replacement) {
                message = message.replace(replacement[0], replacement[1]);
            });
        }

        messageBox.innerHTML += '<span class="' + cssClass.errorMessageItem + '">' + errorIcon + message + '</span>';

        formField.setAttribute('aria-invalid', 'true');
        if (formField.labelElement) {
            formField.labelElement.classList.add(cssClass.errorMessageLabel);
        }
    };

    const validateOnSubmit = function (activeForm) {
        activeForm.addEventListener('submit', function (e) {
            let passedValidation = {
                critical: true,
                relaxed: true
            };

            validatableFormFields.forEach(function (field) {
                // Only validate if field in current form
                if (field.formId) {
                    let fieldPassedValidation = validateFormField(field.formField, field.type, field.validators, field.messageBox);

                    if (!fieldPassedValidation.critical) {
                        // Only override if failed
                        passedValidation.critical = false;
                    }

                    if (!fieldPassedValidation.relaxed) {
                        // Only override if failed
                        passedValidation.relaxed = false;
                    }
                }
            });

            if (!passedValidation.critical) {
                e.preventDefault();

                if (OAP.utils && OAP.utils.errorList) {
                    OAP.utils.errorList.update(activeForm);
                }
            } else if ((!passedValidation.relaxed && !activeForm.dataset[dataSet.warningAccepted]) || activeForm.dataset[dataSet.showAlwaysModal]) {
                // Non-critical validation errors - show a warning, then allow submit
                e.preventDefault();

                if (OAP.utils && OAP.utils.modal) {
                    let modalContent = labels[e.submitter.dataset[dataSet.modalText]];
                    let modalSubmit = labels[e.submitter.dataset[dataSet.modalSubmit]];
                    let modalCancel = labels[e.submitter.dataset[dataSet.modalCancel]];

                    OAP.utils.modal.show(modalContent, modalSubmit, modalCancel, activeForm);
                }
            }
            document.querySelectorAll(selector.enableDisabledElements).forEach(function (element) {
                element.disabled = 0;
            });
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
        const serverErrorList = document.querySelector(selector.serverErrorList);

        // is this the form page?
        if (!serverErrorList) {
            return;
        }

        validationOnStart = serverErrorList.dataset[dataSet.serverError];

        document.querySelectorAll(selector.validatableFormFields).forEach(function (formField) {
            addValidation(formField);
        });

        if (OAP.utils && OAP.utils.errorList) {
            OAP.utils.errorList.show();
        }

        if (blockSubmit) {
            document.querySelectorAll('form').forEach(function (activeForm) {
                if (activeFormIds.includes(activeForm.id)) {
                    validateOnSubmit(activeForm);
                }
            });
        }

    };

    initialize();

}(this, this.OAP || {}));
