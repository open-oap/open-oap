
(function (window, OAP) {

    'use strict';

    OAP.utils = OAP.utils || {};

    OAP.utils.formatNumber = (function () {
        const locales = 'de-DE';
        const floatOptionsInteger = {
            maximumFractionDigits: 2,
            minimumFractionDigits: 0
        };
        const floatOptionsDefault = {
            maximumFractionDigits: 2,
            minimumFractionDigits: 2
        };

        const format = function (value) {
            let valueInteger = Math.floor(value);
            let valueDecimal = value - valueInteger;

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

        return {
            format: format
        };

    }());

}(this, this.OAP || {}));
