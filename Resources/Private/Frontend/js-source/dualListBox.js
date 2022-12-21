/* global DualListbox */

/*
 * usage of https://github.com/maykinmedia/dual-listbox dual listbox
 * changed javascript file cb_amtrack/open_oap/js-source/lib/cb_dual-listbox.js
 */

(function (window) {

    'use strict';

    const document = window.document;
    const selector = {
        duallistboxFields: '[data-oap-duallistbox]'
    };
    const dataSet = {
        dualListBox: 'oapDuallistbox',
        maxvalue: 'oapMaxvalue',
    };
    const cssClass = {
        removedField: 'dual-listbox__removed',
        disabled: 'js-disabled'
    };

    /**
     * Add dualListboxes
     * @param select
     */
    const addListBox = function (select) {
        const dualListBoxOptions = select.dataset[dataSet.dualListBox];
        const jsonOptions = JSON.parse(dualListBoxOptions);

        let dualbox = new DualListbox(select, jsonOptions);

        dualbox.search_left.setAttribute('type', 'text');
        dualbox.search_right.setAttribute('type', 'text');
        dualbox.search_right.classList.add(cssClass.removedField);

        // Not necessary if showRemoveAllButton=false and showAddAllButton=false set in config
        dualbox.add_all_button.remove();
        dualbox.remove_all_button.remove();

        // if select disabled, deactive controls
        if (select.disabled) {
            dualbox.add_button.disabled = true;
            dualbox.remove_button.disabled = true;
            dualbox.availableList.classList.add(cssClass.disabled);
            dualbox.selectedList.classList.add(cssClass.disabled);
        }


        if (select.dataset[dataSet.maxvalue]) {
            enforceMaxvalue(dualbox);
        }
    };

    /**
     * Add listeners to enforce maxvalue
     * @param dualbox
     */
    const enforceMaxvalue = function (dualbox) {
        dualbox.addEventListener('added', function () {
            checkMaxSelected(dualbox);
        });

        dualbox.addEventListener('removed', function () {
            checkMaxSelected(dualbox);
        });

        // Initial check
        checkMaxSelected(dualbox);
    };

    /**
     * Enable/disable selecting
     * @param dualbox
     */
    const checkMaxSelected = function (dualbox) {
        const maxvalue = dualbox.select.dataset[dataSet.maxvalue];

        if (dualbox.select.querySelectorAll('[selected]').length >= maxvalue) {
            dualbox.add_button.disabled = true;
            dualbox.availableList.classList.add(cssClass.disabled);
        } else {
            dualbox.add_button.disabled = false;
            dualbox.availableList.classList.remove(cssClass.disabled);
        }
    };

    const initialize = function () {
        document.querySelectorAll(selector.duallistboxFields).forEach(function (select) {
            addListBox(select);
        });
    };

    initialize();

}(this));
