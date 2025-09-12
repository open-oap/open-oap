
(function (window, OAP) {

    'use strict';

    OAP.utils = OAP.utils || {};

    OAP.utils.modal = (function () {
        const document = window.document;
        const id = {
            modal: 'oap-modal',
            modalContent: 'oap-modal-content',
            modalSubmit: 'oap-modal-submit',
            modalCancel: 'oap-modal-cancel',
            loader: 'oap-loader'
        };
        const dataSet = {
            warningAccepted: 'warningAccepted'
        };
        let modal,
            modalWrapper,
            modalContent,
            modalSubmit,
            modalCancel,
            loaderWrapper;

        const show = function (content, submit, cancel, form, trigger) {

            if (!modalWrapper || !loaderWrapper) {
                return;
            }

            if (modal) {
                modal.destroy();
            }
            modal = new window.A11yDialog(modalWrapper);

            if (modalContent) {
                modalContent.innerHTML = content;
            }

            if (modalCancel) {
                modalCancel.innerHTML = cancel;
            }

            if (modalSubmit) {
                modalSubmit.innerHTML = submit;
            }

            if (modalSubmit) {
                modalSubmit.addEventListener('click', function () {
                    form.dataset[dataSet.warningAccepted] = 1;
                    modal.hide();

                    // Submit from whichever button was originally clicked
                    trigger.click();
                    loaderWrapper.style.display = 'flex';

                });

                modalSubmit.removeAttribute('disabled');
            }

            modal.show();
        };

        /**
         *  Initialize
         */
        const initialize = function () {
            modalWrapper = document.getElementById(id.modal);
            modalContent = document.getElementById(id.modalContent);
            modalSubmit = document.getElementById(id.modalSubmit);
            modalCancel = document.getElementById(id.modalCancel);

            loaderWrapper = document.getElementById(id.loader);

            if (modalWrapper) {
                // Move to last to avoid z-index issues
                document.body.appendChild(modalWrapper);
            }
            if (loaderWrapper) {
                // Move to last to avoid z-index issues
                document.body.appendChild(loaderWrapper);
            }
        };

        initialize();

        return {
            show: show
        };

    }());

}(this, this.OAP || {}));
