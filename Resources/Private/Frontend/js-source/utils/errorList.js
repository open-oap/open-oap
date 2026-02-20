
(function (window, OAP) {

    'use strict';

    OAP.utils = OAP.utils || {};

    OAP.utils.errorList = (function () {
        const document = window.document;
        const cssClass = {
            error: 'form__error',
            errorListBox: 'form__error-list-box',
            errorList: 'form__error-list',
            errorListItem: 'form__error-list-item',
            errorListLink: 'form__error-list-link',
            errorListMessage: 'form__error-list-text',
            alertMessage: 'form__alert',
            formGroup: 'form__group',
            formGroupError: 'form__group--error',
            formTableCell: 'form__table-cell',
            formCheckableLabel: 'form__checkable-label',
        };
        const selector = {
            form: '.form--proposal'
        };
        const labels = OAP.labels || {};

        /**
         * Draw attention to form errors
         * @param form
         * @param clear
         * @returns {*}
         */
        const highlightErrors = function (form, clear) {
            const errorInputs = form.querySelectorAll('[aria-invalid="true"]');
            const alertBoxCompleteData = 'completeMessage';
            let alertBox = form.querySelector('[role="alert"]');
            let completeDataAlert = alertBox ? alertBox.dataset[alertBoxCompleteData] : false;
            let errorList = '';
            let alertHtml = '';

            if (errorInputs.length < 1 && !completeDataAlert) {
                return;
            }

            if (!alertBox) {
                alertBox = document.createElement('div');
                alertBox.setAttribute('role', 'alert');
                form.insertBefore(alertBox, form.firstChild);
            }

            if (clear || alertBox.innerHTML === '') {
                errorInputs.forEach(function (input) {
                    if (input.id && input.id !== '') {
                        let label = form.querySelector('label[for=' + input.id + ']');
                        let anchor = input.id + '--item';
                        let errorOutput = '';

                        if (label) {
                            if (label.classList.contains(cssClass.formCheckableLabel)) {
                                anchor = label.dataset.legend;
                                label = form.querySelector('legend#' + label.dataset.legend);
                            }
                        }
                        if (!label) {
                            let inputFormGroup = input.closest('.' + cssClass.formGroup);

                            if (inputFormGroup) {
                                label = inputFormGroup.querySelector('label');
                                anchor = inputFormGroup.id;
                            }
                        }
                        if (!label) {
                            // Especially for tables that do not have their own label, the label text is stored directly in the cell and queried here.
                            let inputTableCell = input.closest('.' + cssClass.formTableCell);

                            if (inputTableCell) {
                                errorOutput = inputTableCell.dataset.errorlabel;
                                anchor = input.id;
                            }
                        }
                        if (label && errorOutput === '') {
                            let fullText = label.textContent.split('*');

                            errorOutput = fullText[0];
                        }
                        if (errorOutput) {
                            errorList += '<li class="' + cssClass.errorListItem + '"><a href="#' + anchor + '" class="' + cssClass.errorListLink + '">' + errorOutput + '</a></li>';
                        }

                    }

                });

                alertHtml = '<ul class="' + cssClass.errorList + '">' + errorList + '</ul>';
            } else {
                alertHtml = alertBox.innerHTML;

                highlightErrorsFields(alertBox);
            }

            window.setTimeout(function () {
                if (completeDataAlert) {
                    // Wrap it to make it different (in case SRs are being picky), but nothing more
                    alertBox.innerHTML = '<div>' + alertHtml + '</div>';
                } else {
                    alertBox.innerHTML = '<div class="' + cssClass.errorListBox + '"><p class="' + cssClass.errorListMessage + '"><strong>' + labels.JSMSG_ERROR_LABEL + ':</strong> ' + labels.JSMSG_ERROR_MESSAGE + '</p>' + alertHtml + '</div>';
                }

                if (alertBox.id) {
                    // Wanted? Back-button issues?
                    if (window.location.hash === '') {
                        window.location.hash = '#' + alertBox.id;
                    }
                }

            }, 150);

        };

        /**
         * For forms that do not mark their own inputs as having errors - work backwards from the main error message, mark the inputs as invalid
         * Currently only relevant for fe_login
         * @param finisherAlertBox
         */
        const highlightErrorsFields = function (finisherAlertBox) {
            const errorLinks = finisherAlertBox.querySelectorAll('.' + cssClass.errorListLink);

            errorLinks.forEach(function (link) {
                let input = document.querySelector(link.hash);
                let inputWrapper;

                if (!input) {
                    return;
                }

                input.classList.add(cssClass.error);
                input.setAttribute('aria-invalid', 'true');

                inputWrapper = input.closest('.' + cssClass.formGroup);

                if (inputWrapper) {
                    inputWrapper.classList.add(cssClass.formGroupError);
                }
            });
        };

        /**
         * Dynamic update of error list for specific form
         * @param form
         */
        const update = function (form) {
            highlightErrors(form, true);
        };

        /**
         *  Initialize
         */
        const initialize = function () {
            document.querySelectorAll(selector.form).forEach(function (form) {
                highlightErrors(form);
            });
        };

        return {
            show: initialize,
            update: update
        };

    }());

}(this, this.OAP || {}));
