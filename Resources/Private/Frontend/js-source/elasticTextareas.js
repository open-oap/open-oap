(function (window) {

    'use strict';

    const document = window.document;
    const selector = {
        textarea: 'textarea',
    };
    const eventType = {
        input: 'input',
    };

    document.querySelectorAll(selector.textarea).forEach(function (textarea) {
        textarea.style.height = textarea.scrollHeight + 'px';
        textarea.style.overflowY = 'hidden';

        textarea.addEventListener(eventType.input, function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });

}(this));
