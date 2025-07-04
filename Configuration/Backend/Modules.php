<?php

/**
 * Definitions for modules provided by EXT:open_oap
 */
return [
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
