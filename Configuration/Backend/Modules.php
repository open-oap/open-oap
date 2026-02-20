<?php

/**
 * Definitions for modules provided by EXT:open_oap
 */

$modules = [
    'web_OpenOapBackendForms' => [
        'parent' => 'web',
        'access' => 'user',
        'iconIdentifier' => 'open_oap-plugin-form',
        'path' => '/module/web/OpenOapBackendforms',
        'labels' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_BackendForms.xlf',
        'extensionName' => 'OpenOap',
        'controllerActions' => [
            \OpenOAP\OpenOap\Controller\BackendFormsController::class => [
                'listForms',
                'showOverviewForms',
                'showReleaseNotesForms',
                'previewForm'
            ],
        ],
    ],
    'web_OpenOapBackendProposals' => [
        'parent' => 'web',
        'access' => 'user',
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false,
        'iconIdentifier' => 'open_oap-plugin-proposals',
        'path' => '/module/web/OpenOapBackendproposals',
        'labels' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_BackendProposals.xlf',
        'extensionName' => 'OpenOap',
        'controllerActions' => [
            \OpenOAP\OpenOap\Controller\BackendProposalsController::class => [
                'showOverviewCalls',
                'listProposals',
                'showReleaseNotesProposals',
                'showProposal',
                'customizeStatusMail',
                'uploadAssessmentExcel'
            ],
        ],
    ],
];

// Check if the backup module is enabled in the extension configuration
$backupModuleEnabled = false;
$backupAccess = 'admin';

try {
    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    );
    $extConfig = $extensionConfiguration->get('open_oap');
    $backupModuleEnabled = (bool)($extConfig['enableBackupModule'] ?? false);

    // Get access configuration for the backup module
    $backupAccess = $extConfig['backupAccess'] ?? 'admin';
    // Ensure backupAccess is either 'user' or 'admin'
    if (!in_array($backupAccess, ['user', 'admin'])) {
        $backupAccess = 'admin';
    }
} catch (\Exception $e) {
    // If any error occurs, default to disabled and admin access
    $backupModuleEnabled = false;
    $backupAccess = 'admin';
}

// Only add the backup module if it's enabled in the extension configuration
if ($backupModuleEnabled) {
    $modules['system_OpenOapBackendBackup'] = [
        'parent' => 'system',
        'access' => $backupAccess,
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false,
        'iconIdentifier' => 'open_oap-plugin-backup',
        'path' => '/module/system/OpenOapBackendbackup',
        'labels' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_BackendBackup.xlf',
        'extensionName' => 'OpenOap',
        'controllerActions' => [
            \OpenOAP\OpenOap\Controller\BackendBackupController::class => [
                'index',
                'createBackup',
                'cancelBackup',
                'deleteBackup',
                'checkStatus',
                'download'
            ],
        ],
    ];
}

return $modules;
