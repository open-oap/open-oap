/**
 * Function for adding modal dialog events to links
 */
(function (window, OAP) {

    'use strict';

    const document = window.document;
    const labels = OAP.labels || {};
    const selector = {
        modalLinks: '.modal__link',
    };
    const dataSet = {
        modalText: 'oapModaltext',
        modalSubmit: 'oapModalsubmit',
        modalCancel: 'oapModalcancel',
        warningAccepted: 'warningAccepted',
    };

    const initialize = function () {

        document.querySelectorAll(selector.modalLinks).forEach(function (link) {

            link.addEventListener('click', function (e) {
                if (!link.dataset[dataSet.warningAccepted]) {
                    e.preventDefault();

                    if (OAP.utils && OAP.utils.modal) {
                        let modalContent = labels[link.dataset[dataSet.modalText]];
                        let modalSubmit = labels[link.dataset[dataSet.modalSubmit]];
                        let modalCancel = labels[link.dataset[dataSet.modalCancel]];

                        OAP.utils.modal.show(modalContent, modalSubmit, modalCancel, link, link);
                    }
                }
            });
        });

    };

    initialize();

}(this, this.OAP || {}));
