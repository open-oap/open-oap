<?php

defined('TYPO3') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Dashboard',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_dashboard.name',
    'open_oap-plugin-dashboard',
    'plugins',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_dashboard.description',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Proposals',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_proposals.name',
    'open_oap-plugin-proposals',
    'plugins',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_proposals.description',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Applicant',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_applicant.name',
    'open_oap-plugin-applicant',
    'plugins',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_applicant.description',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Applicantform',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_applicantform.name',
    'open_oap-plugin-applicantform',
    'plugins',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_applicantform.description',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Form',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_form.name',
    'open_oap-plugin-form',
    'plugins',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_form.description',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Notifications',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_notifications.name',
    'open_oap-plugin-notifications',
    'plugins',
    'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_notifications.description',
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'openoap_dashboard', 'after:subheader');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    // Flexform configuration schema file
    'FILE:EXT:open_oap/Configuration/FlexForms/Dashboard.xml',
    'openoap_dashboard'
);
