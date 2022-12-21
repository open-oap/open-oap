# Parts

## Frontend for applicants

For the "technical terms" see also the [glossary](../glossar.md).

The frontend is formed by the following plugins:
(The corresponding pages with the respective localisations and static texts must be built by editors with the necessary rights. See here also [Backend-Site-Pages/SysFolder](../Backend/backend-site-management.md))
- User registration (foreign extension)
- User login (foreign extension)
- Dashboard
- List of active applications
- List of archived applications
- Comments/notes display

## Backend for editors
- Creation of forms (calls, pages, groups, items, options). These are data sets that are stored separately in SysFolders. Restrictions are implemented in the TCA of the respective data sets through settings in TSConfig.
- Preview of the form in the backend module "OAP forms

## Backend for clerks
- The submitted applications/proposals are managed in the backend module "OAP Proposals". Here there is the possibility to access the data if the user has submitted the application (no backend access for draft documents).
- Functions in the backend module:
    - Export PDF, Word, uploaded documents in zip archive
    - Export as csv
    - Commenting on applications (overall) and on individual fields/questions
    - Status change (e.g. rejected or for post-processing so that the fields with comments can be edited again.

