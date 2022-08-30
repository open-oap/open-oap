<?php

defined('TYPO3') || die();

$pluginSignature = 'open_oap';

/**
 * Register PageTSConfig Files
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
    $pluginSignature,
    'Configuration/TSconfig/oap--developpp.tsconfig',
    'OAP DEVELOPPP Base - Page Configuration'
);

//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
//    $pluginSignature,
//    'Configuration/TSconfig/oap--backup.tsconfig',
//    'OAP BACKUP Base - Page Configuration'
//);
