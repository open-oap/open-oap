# Constants

# General notes

Constants are basically assigned to the Dashboard plugin and have their default settings in the file open_oap/Configuration/TypoScript/constants.typoscript

in the template input, the values can be adapted to the client or the website.

Especially the page-Ids are managed here. 

# Usage

```typo3_typoscript
settings {
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.dashboard_page_id
    dashboardPageId = 38
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.form_page_id
    formPageId = 55
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.proposals_page_id
    proposalsPageId = 58
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.notifications_page_id
    notificationsPageId = 61
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.masterdata_edit_page_id
    masterdataEditPageId = 46
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.call_pool_id
    callPoolId = 24
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.proposal_pool_id
    proposalPoolId = 28
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.items_pool_id
    itemsPoolId = 21
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.dashboard.proposals_active_limit
    proposalsActiveLimit = 5
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.dashboard.proposals_archived_limit
    proposalsArchivedLimit = 5
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.answers_pool_id
    answersPoolId = 29
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.comments_pool_id
    commentsPoolId = 53
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.masterdata.countries_item_option_id
    countriesItemOptionId = 28
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.tester_fe_groups_id
    testerFeGroupsId = 2
    # cat=plugin.tx_openoap_dashboard/settings; type=string; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.dashboard.meta_info_separator
    metaInfoSeparator = |
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.validation.default_max_char_textarea
    defaultMaxCharTextarea = 2000
    # cat=plugin.tx_openoap_dashboard/settings; type=integer; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.validation.default_max_char_textfield
    defaultMaxCharTextfield = 200
    # cat=plugin.tx_openoap_dashboard/settings; type=string; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.masterdata.default_sources_model
    defaultSourcesModel = applicant.companyEmail, applicant.firstName, applicant.lastName, applicant.address, applicant.country, applicant.telephone, applicant.fax, applicant.email, applicant.zip, applicant.city, applicant.www, applicant.company, applicant.salutation
    # cat=plugin.tx_openoap_dashboard/settings; type=string; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.filestorage.upload_folder
    uploadFolder = 2:/
    # cat=plugin.tx_openoap_dashboard/settings; type=string; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.signature_format
    signatureFormat = %04d
    # cat=plugin.tx_openoap_dashboard/settings; type=string; label=LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:constants.word_export_template_file
    wordExportTemplateFile = word-vorlage-oap.docx
```