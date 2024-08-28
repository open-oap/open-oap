<?php

defined('TYPO3') || die();

if (!isset($GLOBALS['TCA']['fe_users']['ctrl']['type'])) {
    // no type field defined, so we define it here. This will only happen the first time the extension is installed!!
    $GLOBALS['TCA']['fe_users']['ctrl']['type'] = 'tx_extbase_type';
    $tempColumnstx_openoap_fe_users = [];
    $tempColumnstx_openoap_fe_users[$GLOBALS['TCA']['fe_users']['ctrl']['type']] = [
        'exclude' => true,
        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap.tx_extbase_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    'label' => '',
                    'value' => '',
                ],
                [
                    'label' => 'Applicant',
                    'value' => 'Tx_OpenOap_Applicant',
                ],
            ],
            'default' => 'Tx_OpenOap_Applicant',
            'size' => 1,
            'maxitems' => 1,
        ],
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumnstx_openoap_fe_users);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    $GLOBALS['TCA']['fe_users']['ctrl']['type'],
    '',
    'after:' . $GLOBALS['TCA']['fe_users']['ctrl']['label']
);

$tmp_open_oap_columns = [

    'tx_openoap_company_email' => [
        'exclude' => true,
        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.company_email',
        'config' => [
            'type' => 'email',
            'size' => 30,
            'eval' => 'nospace',
            'default' => '',
        ],
    ],
    'tx_openoap_preferred_lang' => [
        'exclude' => true,
        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.preferred_lang',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
            'default' => '',
        ],
    ],
    'tx_openoap_privacypolicy' => [
        'exclude' => true,
        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.privacypolicy',
        'config' => [
            'type' => 'check',
            'default' => 0,
        ],
    ],
    'tx_openoap_salutation' => [
        'exclude' => true,
        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.salutation',
        'config' => [
            'type' => 'radio',
            'items' => [
                [
                    'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.salutation.item0',
                    'value' => '0',
                ],
                [
                    'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.salutation.item1',
                    'value' => '1',
                ],
                [
                    'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.salutation.item2',
                    'value' => '2',
                ],
            ],
        ],
    ],
    'tx_openoap_proposals' => [
        'exclude' => true,
        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.proposals',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_openoap_domain_model_proposal',
            'foreign_field' => 'applicant',
            'foreign_table_where' => 'AND {#tx_openoap_domain_model_proposal}.pid=###PAGE_TSCONFIG_ID###',
            'maxitems' => 9999,
            'appearance' => [
                'collapseAll' => 1,
                'levelLinksPosition' => 'top',
                'showSynchronizationLink' => 1,
                'showPossibleLocalizationRecords' => 1,
                'showAllLocalizationLink' => 1,
            ],
        ],
    ],
    'country' => [
        'exclude' => true,
        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant.country',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'foreign_table' => 'tx_openoap_domain_model_itemoption',
            'foreign_table_where' => 'AND {#tx_openoap_domain_model_itemoption}.uid=###PAGE_TSCONFIG_ID###',
            'itemsProcFunc' => OpenOAP\OpenOap\UserFunctions\FormEngine\TypeSelectItemsProcFunc::class . '->countryItemsProcFunc',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tmp_open_oap_columns);

// inherit and extend the show items from the parent class
//if (isset($GLOBALS['TCA']['fe_users']['types']['0']['showitem'])) {
//    $GLOBALS['TCA']['fe_users']['types']['Tx_OpenOap_Applicant']['showitem'] = $GLOBALS['TCA']['fe_users']['types']['0']['showitem'];
//} elseif (is_array($GLOBALS['TCA']['fe_users']['types'])) {
//    // use first entry in types array
//    $fe_users_type_definition = reset($GLOBALS['TCA']['fe_users']['types']);
//    $GLOBALS['TCA']['fe_users']['types']['Tx_OpenOap_Applicant']['showitem'] = $fe_users_type_definition['showitem'];
//} else {
//    $GLOBALS['TCA']['fe_users']['types']['Tx_OpenOap_Applicant']['showitem'] = '';
//}

// add tab for proposal data - removed by commenting out for access rule reasons #TB20221207 - conflict with folder access
//$GLOBALS['TCA']['fe_users']['types']['0']['showitem'] .= ',--div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_applicant,';
//$GLOBALS['TCA']['fe_users']['types']['0']['showitem'] .= 'tx_openoap_salutation, tx_openoap_company_email, tx_openoap_preferred_lang, tx_openoap_privacypolicy, tx_openoap_proposals';

//$GLOBALS['TCA']['fe_users']['columns'][$GLOBALS['TCA']['fe_users']['ctrl']['type']]['config']['items'][] = [
//    'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:fe_users.tx_extbase_type.Tx_OpenOap_Applicant',
//    'Tx_OpenOap_Applicant',
//];

$GLOBALS['TCA']['fe_users']['columns']['gender']['exclude'] = 1;
$GLOBALS['TCA']['fe_users']['columns']['date_of_birth']['exclude'] = 1;
$GLOBALS['TCA']['fe_users']['columns']['email']['config']['eval'] = 'nospace,email';
$GLOBALS['TCA']['fe_users']['columns']['email']['config']['required'] = true;
