# Function Survey

## Concept

The aim of this addition is the possibility of anonymous surveys. These can be conducted, for example, after an application process.
In order to guarantee anonymity (and to make it visible to the outside), no login should be required for the survey.
The basic concept of oap so far has been that there is basically one user (= applicant) and each form is started from the personal dashboard.
This also meant that everything was done after a login.

The new function makes it possible to call up a form with the survey call id as a parameter:

```
https://oap.xxxx.de/SurveyForm?survey=78
```
- "SurveyForm" is the name/alias of the page.
- The parameter survey passes the ID of the call (here 78)

After the call, a new proposal is assigned to the anonymous user and the form is started.
The form is processed according to the normal rules, with the following exceptions:
1. there is no close function (this button is hidden for anonymous surveys)
2. cancel leads to termination and no data is transferred (in principle, the data remains in the system as a draft)
3. no e-mail is sent after the submit (at present). This can be realised more easily after the e-mail handling has been modified.
4. After the submit, the user is forwarded to a thank you page. 5.
5. in case of a close ("Cancel" button), the user is redirected to the cancel page.

## Model and System Changes
Here are just the most important...

### Database
It is necessary to perform a DB-compare (should it not be done automatically in the case of an automatic deployment).
The call record receives the new field (toggle field) "anonymous".

### Texts
n addition to the labels for the call attribute "anonymous", a default title is defined for the anonymous surveys.<br>
tx_openoap.default.default_title_survey

## Requirements
### A aeneral anonymous user
This user is created in advance, if possible in a separate pool folder (e.g. FE-User-Survey_anonymous), so that this user is not accidentally deleted or deactivated.

The Id of this user is communicated via the constants:

surveyOapUser: "Id of oap survey user (anonym)"

### A form page
This page must be outside the login-protected page tree.
The form plugin "oap form [openoap_form]" is required in the page.

The page is entered as a survey form page with a setting in the constants:

Page with proposal form (plugin) for survey<br>
plugin.tx_openoap_dashboard.settings.surveyPageId

### A thank you page
The thank you page is called after the submit (instead of the normal redirect to the dashboard)<br>.
The page is set with a constant:

Survey page for saying Thank you after submitting survey<br>
plugin.tx_openoap_dashboard.settings.surveyThanksPageId

### A cancel page
This is called after the Cancel (instead of the normal redirect).<br>
The page is set with a constant:

Survey page reaction of aborting survey<br>
plugin.tx_openoap_dashboard.settings.surveyAbortPageId
