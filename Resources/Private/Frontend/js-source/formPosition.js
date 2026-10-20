(function (window) {

    'use strict';

    const document = window.document;
    const formId = 'proposal';

    const selector = {
        proposalForm: `form[name="${formId}"]`,
        groupFieldset: '.form__grouped-fieldset',
        focusable: 'input:not([type="hidden"]):not([disabled]):not([readonly]), textarea:not([disabled]):not([readonly]), select:not([disabled])',
    };

    /*
     * One entry per tab, written before a submit and consumed on the next page load.
     * An entry older than maxAgeMs is ignored: it belongs to a submit that never
     * navigated, because validation blocked it or the user left the page in between.
     */
    const storageKey = 'oapFormPosition';
    const maxAgeMs = 120000;
    // Rounding reserve when comparing document offsets
    const tolerance = 4;
    const groupActions = ['addGroup', 'removeGroup'];

    // === USE ONLY FOR FORMS WITH NAME="proposal" ===
    const proposalForm = document.querySelector(selector.proposalForm);

    // Cancel if proposalForm does not exist
    if (!proposalForm) {
        return;
    }

    const pageField = proposalForm.elements.currentPage;

    /*
     * Identifies the form page a stored position belongs to. The form id carries the
     * proposal uid (proposal-form-<uid>) and currentPage the page inside the form, so a
     * position is never restored for a different proposal or after a page change.
     */
    const getContext = function () {
        return {
            form: proposalForm.id,
            page: pageField ? pageField.value : '',
        };
    };

    const store = function (data) {
        try {
            window.sessionStorage.setItem(storageKey, JSON.stringify(data));
        } catch (e) {
            /*
             * Storage unavailable (private mode, quota, blocked site data).
             * The position is then simply not restored.
             */
        }
    };

    // Reads the entry and removes it right away: a position is good for exactly one load
    const take = function () {
        let raw = null;

        try {
            raw = window.sessionStorage.getItem(storageKey);
            window.sessionStorage.removeItem(storageKey);
        } catch (e) {
            return null;
        }

        if (!raw) {
            return null;
        }

        let data = null;

        try {
            data = JSON.parse(raw);
        } catch (e) {
            return null;
        }

        if (!data || !data.time || (Date.now() - data.time) > maxAgeMs) {
            return null;
        }

        const here = getContext();

        if (data.form !== here.form || data.page !== here.page) {
            return null;
        }

        return data;
    };

    /*
     * A field belongs to the group instance that was just added when it sits at or below
     * the former offset of the add button, which marks the insertion point. Filled fields
     * are skipped so a miss cannot pull the focus away from data the user already entered.
     */
    const isFieldOfAddedGroup = function (field, anchorY) {
        if (field.value || !field.closest(selector.groupFieldset)) {
            return false;
        }

        const rect = field.getBoundingClientRect();

        // Fields that are not rendered (collapsed or hidden branches) have no box
        if (rect.width === 0 && rect.height === 0) {
            return false;
        }

        return (rect.top + window.scrollY) >= (anchorY - tolerance);
    };

    const focusAddedGroup = function (anchorY) {
        const fields = Array.from(proposalForm.querySelectorAll(selector.focusable));
        const target = fields.find(function (field) {
            return isFieldOfAddedGroup(field, anchorY);
        });

        if (target) {
            target.focus({
                preventScroll: true
            });
        }
    };

    const restore = function (data) {
        let userTookOver = false;

        // Real intent to scroll, as opposed to the scroll events our own scrollTo causes
        ['wheel', 'touchmove', 'keydown'].forEach(function (type) {
            window.addEventListener(type, function () {
                userTookOver = true;
            }, {
                once: true,
                passive: true
            });
        });

        window.scrollTo(0, data.y);

        if (data.action === 'addGroup') {
            focusAddedGroup(data.anchorY);
        }

        /*
         * Late layout shifts (web font swap, images) can shorten the document and clamp
         * the scroll position, so apply it once more when everything has settled.
         */
        window.addEventListener('load', function () {
            if (userTookOver || window.scrollY >= (data.y - tolerance)) {
                return;
            }

            window.scrollTo(0, data.y);
        });
    };

    proposalForm.addEventListener('submit', function (e) {
        /*
         * The validator blocks the submit for errors and for the confirm modal. The modal
         * re-clicks the same button afterwards, which lands here again - unprevented.
         */
        if (e.defaultPrevented) {
            return;
        }

        const submitter = e.submitter;
        const here = getContext();
        let action = 'other';
        let anchorY = 0;

        if (submitter) {
            if (groupActions.indexOf(submitter.name) > -1) {
                action = submitter.name;
            }

            anchorY = submitter.getBoundingClientRect().top + window.scrollY;
        }

        store({
            form: here.form,
            page: here.page,
            y: window.scrollY,
            anchorY: anchorY,
            action: action,
            time: Date.now(),
        });
    });

    const position = take();

    if (position) {
        restore(position);
    }

}(this));
