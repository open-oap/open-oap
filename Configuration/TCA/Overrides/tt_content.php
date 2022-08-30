<?php

defined('TYPO3') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Dashboard',
    'oap dashboard'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Proposals',
    'oap proposals'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Applicant',
    'oap applicant'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Applicantform',
    'oap applicantform'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Form',
    'oap form'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'OpenOap',
    'Notifications',
    'oap notifications'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['openoap_dashboard'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'openoap_dashboard',
    // Flexform configuration schema file
    'FILE:EXT:open_oap/Configuration/FlexForms/Dashboard.xml'
);
