# Constants

# General notes

Constants are basically assigned to the Dashboard plugin and have their default settings in the file open_oap/Configuration/TypoScript/constants.typoscript

in the template input, the values can be adapted to the client or the website.

Especially the page-Ids are managed here.

# Usage

```typo3_typoscript
settings {
    dashboardPageId = 38
    surveyPageId = 106
    formPageId = 55
    proposalsPageId = 58
    notificationsPageId = 61
    masterdataEditPageId = 46
    callPoolId = 24
    proposalPoolId = 28
    itemsPoolId = 21
    proposalsActiveLimit = 5
    proposalsArchivedLimit = 5
    answersPoolId = 29
    commentsPoolId = 53
    countriesItemOptionId = 28
    generalFeGroupsId = 1
    testerFeGroupsId = 2
    metaInfoSeparator = |
    defaultMaxCharTextarea = 2000
    defaultMaxCharTextfield = 200
    defaultSourcesModel = applicant.companyEmail, applicant.firstName, applicant.lastName, applicant.address, applicant.country, applicant.telephone, applicant.fax, applicant.email, applicant.zip, applicant.city, applicant.www, applicant.company, applicant.salutation
    uploadFolder = 2:/
    signatureFormat = %04d
    wordExportTemplateFile = word-vorlage-oap.docx
    surveyOapUser = 6349
    surveyThanksPageId = 113
    surveyAbortPageId = 114
    surveyErrorPageId = 115
    pidFormPages = 23
    pidFormGroups = 22
    pidFormItems = 21
    zipFilePrefix = documents--
    zipFileDateFormat = Ymd-Hi
    zipStructureApplicantFormat = %1$s--%2$05d
    zipStructureProppsalFormat = %1$s--%2$05d
    zipStructureProppsalFormatTitleLength = 30
}
```
