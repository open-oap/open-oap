
(function (window, OAP) {

    'use strict';

    const document = window.document;
    const selector = {
        countableAreas: 'textarea[data-oap-maxlength]'
    };
    const cssClass = {
        counterBox: 'counter',
        countCurrent: 'counter-current',
        maxedOut: 'js-counter-max'
    };
    const dataSet = {
        maxlength: 'oapMaxlength'
    };
    const labels = OAP.labels || {};
    const counterLabel = labels.JSMSG_CHARACTERS_REMAINING;
    const placeholderLabel = '%s';

    const addCounter = function (textarea) {
        const countBox = getCountBox(textarea);

        textarea.addEventListener('keyup', function () {
            updateCounter(textarea, countBox);
        });

        // Set initial counter
        updateCounter(textarea, countBox);
    };

    const getCountBox = function (textarea) {
        let countBoxWrapper = document.createElement('div');

        if (textarea.id && textarea.id !== '') {
            let countBoxId = textarea.id + '-counter';

            countBoxWrapper.id = countBoxId;
            textarea.setAttribute('aria-describedby', countBoxId);
        }

        countBoxWrapper.innerHTML = counterLabel.replace(placeholderLabel, '<span class="' + cssClass.countCurrent + '"></span>');
        countBoxWrapper.classList.add(cssClass.counterBox);

        textarea.parentNode.appendChild(countBoxWrapper);

        return {
            wrapper: countBoxWrapper,
            countCurrent: countBoxWrapper.querySelector('.' + cssClass.countCurrent)
        };
    };

    const updateCounter = function (textarea, countBox) {
        const utf8SafeLength = [...textarea.value.replace(/[\n\r\t]/g, '')].length;

        countBox.countCurrent.innerText = textarea.dataset[dataSet.maxlength] - utf8SafeLength;

        if (utf8SafeLength >= textarea.dataset[dataSet.maxlength]) {
            countBox.wrapper.classList.add(cssClass.maxedOut);
        } else {
            countBox.wrapper.classList.remove(cssClass.maxedOut);
        }
    };

    const initialize = function () {
        document.querySelectorAll(selector.countableAreas).forEach(function (textarea) {
            addCounter(textarea);
        });
    };

    initialize();

}(this, this.OAP || {}));