# Change log

## Version 1.3.1

- Fixing further PHP warnings
- Added single select field
- Maintainable and customizable supporters
- Allow separate email texts per supporter
- Preventing the submission of proposals for expired calls with 3 hours cooling-off period
- Keep error navigation after submit attempt
- Allow bulk decline of proposals
- Significant reduction of PDF export file size
- Allow reset of the revision status
- Consideration of deleted proposals for the signature counter
- Various minor fixes

## Version 1.3

- PHP 8 compatibility
- Added anonymous [surveys](../Configuration/function_survey.md)
- Added configuration for ZIP file format
- Added call groups
- Added dashboard templates
- Fixed error message for dual listbox
- UserTS "oap.access.*" now grants access to nested groups
- Added new validator "greater than" based on another field
- Added new validator "less than" based on another field
- Added new modificator "Grand total" to  calculate the sum of multiple other fields
- Fixed broken felogin password reset
- Use separate logo for Word .docx files

## Version 1.2.2

- add javascript and css files - compiled - for ready-to-use-state (alignment for gitignore for this part)
- removes symlink added by mistake

## Version 1.2.1

- Correction of an outdated, misleading note in the README file.
- check applicant email on send - add log/flashmsg
- set Applicant user email as required
- removed question marks in xlf (de)
- add hidden field for invitation user form - for alignment with fe manager

## Version 1.2

- !important  [TASK] include open_oap_users into open_oap to dissolve the dependence on an own extension
- [BUGFIX] fix missing quote in FormFields partial for Applicant
- [BUGFIX]  access viewhelper - catch missing call times
- style changes - php-cs-fixer
- js: add switch off for cookie edit button
- composer.json requirement changed to php 7.4 (instead of 7.2), newer version of typo3/coding-standards
- add documentation (known issues, add change log)

## Version 1.1.1

- [BUGFIX] create new Answer with additional level of nested groups
- optimize/refactoring BE module for longer proposal lists
- catch failure in case of missing files (but reference in answer)
- access control on dashboard w additional rules, new viewhelper
- correction of namespace (typo) in AjaxUpload
- [BUGFIX] word output: radio button checked?
- no htmlspecialchars function-  to avoid html entities
- refactoring create Word output for selection options
- common function even without backend user (using in FE context)
- Backend output count of proposals in draft state
- remove tab in feUser dialog to avoid access to proposals
- backend validation max chars - cleaned string like FE
- few documentation changes
- few clean ups - remove lines and files

## Version 1.1.0

- first live state
- repeatable form groups
- nested form groups
- more documentation
- built css and javascript (in case there are problems to build by your own)
- add sources of css (sass) and js-sources in Resources/Private/Frontend, including linting
- selection of target folder for saving the proposals (data item in call object)
- phpdoc config file added

## Version 0.1.0

- initial commit of mvp(-) (with a few lacks - see roadmap)
