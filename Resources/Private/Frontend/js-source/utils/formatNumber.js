
(function (window, OAP) {

    'use strict';

    OAP.utils = OAP.utils || {};

    OAP.utils.formatNumber = (function () {
        const document = window.document;
        const selector = {
            proposalForm: 'form.form--proposal',
        };
        const attribute = {
            oapNumberstyle: 'data-oap-numberstyle',
        };
        const numberStyle = {
            eu: 'EU',
            us: 'US'
        };
        const localesMode = {
            eu: 'de-DE',
            us: 'en-US'
        };
        const floatOptionsInteger = {
            maximumFractionDigits: 2,
            minimumFractionDigits: 0
        };
        const floatOptionsDefault = {
            maximumFractionDigits: 2,
            minimumFractionDigits: 2
        };
        const proposalForm = document.querySelector(selector.proposalForm);
        const currentNumberStyle = proposalForm ? proposalForm.getAttribute(attribute.oapNumberstyle) : null;

        // EU is the safe default: only switch to US when explicitly requested.
        const isUs = function () {
            return currentNumberStyle === numberStyle.us;
        };

        const format = function (value) {
            let valueInteger = Math.floor(value);
            let valueDecimal = value - valueInteger;
            let locales = isUs() ? localesMode.us : localesMode.eu;

            if (value && !isNaN(valueInteger)) {
                if (valueDecimal > 0) {
                    // if there are decimals, use up to 2 digits
                    value = new Intl.NumberFormat(locales, floatOptionsDefault).format(value);
                } else {
                    // if there is no decimal, don't use the digits - even no zeros
                    value = new Intl.NumberFormat(locales, floatOptionsInteger).format(value);
                }

            }

            return value;
        };

        const unformat = function (value) {
            if (value === '') {
                return '';
            }

            if (isUs()) {
                // US: comma is the thousands separator, dot the decimal separator
                value = value.toString().replace(/,/g, '');
            } else {
                // EU: dot is the thousands separator, comma the decimal separator
                value = value.toString().replace(/\./g, '').replace(/,/g, '.');
            }

            return parseFloat(value);
        };

        return {
            format: format,
            unformat: unformat,
            isUs: isUs
        };

    }());

}(this, this.OAP || {}));