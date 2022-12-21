/**
 * Function for enabling/disabling the submit button depending on checkboxes (any number - dynamically with specific class).
 */
(function (window) {

    'use strict';

    const document = window.document;
    const selector = {
        submitAccepted: '.submit_accepted',
        submitButton: '#submit-proposal',
    };
    let submittedAcceptedOverAll = false;
    let submittedAcceptingCheckboxes = false;

    const initialize = function () {

        submittedAcceptingCheckboxes = document.querySelectorAll(selector.submitAccepted);

        submittedAcceptingCheckboxes.forEach(function (submitCheckbox) {
            submitCheckbox.addEventListener('change', function () {
                submittedAcceptedOverAll = true;
                submittedAcceptingCheckboxes.forEach(function (submitCheckbox) {
                    submittedAcceptedOverAll = submittedAcceptedOverAll && submitCheckbox.checked;
                });

                document.querySelector(selector.submitButton).disabled = !submittedAcceptedOverAll;
            });
        });

    };

    initialize();

}(this));