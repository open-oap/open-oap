(function (window, OAP) {

    'use strict';

    let document = window.document;
    let oapDiv = document.querySelector('[data-oap-asset-path]');

    OAP.assetPath = oapDiv.dataset.oapAssetPath || '';

}(this, this.OAP || {}));
