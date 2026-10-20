(function (window, OAP) {

    'use strict';

    const document = window.document;
    const labels = OAP.labels || {};
    const formId = 'proposal';

    const selector = {
        toast: 'toast',
        proposalForm: `form[name="${formId}"]`,
    };

    const dataSet = {
        reminderMinutes: 'oapReminderToSave',
    };
    const cssClass = {
        show: 'show'
    };

    // === USE ONLY FOR FORMS WITH NAME="proposal" ===
    const proposalForm = document.querySelector(selector.proposalForm);
    const toast = document.getElementById(selector.toast);

    // Cancel if proposalForm does not exist
    if (!proposalForm || !toast) {
        return;
    }

    // repeated reminder time in minutes
    let reminderMinutes = proposalForm.dataset[dataSet.reminderMinutes];
    let cycle = 1;
    let reminderTime = reminderMinutes * 60 * 1000;
    let reminderTimer;
    let userHasEdited = false;

    // === TOAST FUNCTION ===
    const showToast = function (message, duration = 4000) {

        // Conversion of minutes to seconds
        const timeValue = (reminderMinutes < 1) ?
            Math.round(reminderMinutes * 60) :
            reminderMinutes;
        // Format message and replace %s
        const formattedMessage = message.replace('%s', timeValue * cycle);

        cycle += 1;

        toast.textContent = formattedMessage;
        toast.classList.add(cssClass.show);

        // Avoiding nesting and improving readability
        setTimeout(() => {
            toast.classList.remove(cssClass.show);
        }, duration);
    };


    const startReminderTimer = function () {
        clearTimeout(reminderTimer);

        // Recursive timeout function for recurring reminders
        const reminderFunction = function tick() {
            // Start the timer if a field has been edited
            if (userHasEdited) {
                showToast(labels.JSMSG_REMINDER_TO_SAVE);
                reminderTimer = setTimeout(tick, reminderTime);
            }
        };

        reminderTimer = setTimeout(reminderFunction, reminderTime);
    };

    // === INPUT RECOGNITION ===
    proposalForm.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('input', () => {
            userHasEdited = true;
            startReminderTimer();
        });
    });

}(this, this.OAP || {}));
