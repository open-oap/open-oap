<?php

/**
 * AJAX routes for the backend modules of the OpenOAP extension
 */
return [
    // 'openoap_proposal_upload' => [
    //     'path' => '/open-oap/proposal_upload',
    //     'target' => \OpenOAP\OpenOap\Controller\ProposalController::class . '::uploadAction',
    // ],
    'openoap_backup_status_check' => [
        'path' => '/open-oap/backup/status',
        'target' => \OpenOAP\OpenOap\Controller\BackendBackupController::class . '::checkStatusAction',
        'inheritAccessFromModule' => 'system_OpenOapBackendBackup',
    ],
];
