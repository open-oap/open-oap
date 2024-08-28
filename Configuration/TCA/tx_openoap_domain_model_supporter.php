<?php

use OpenOAP\OpenOap\Utility\LocalizationUtility;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title,menu_title,internal_title,intro_text',
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
    ],
    'types' => [
        '0' => ['showitem' => 'title, event_proposal_submitted_mailtext,event_proposal_in_revision_mailtext,event_proposal_accepted_mailtext,event_proposal_declined_mailtext, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'tx_openoap_domain_model_formpage',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formpage}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_formpage}.{#sys_language_uid} IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'title' => [
            'l10n_display' => 'defaultAsReadonly',
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'event_proposal_submitted_mailtext' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.eventProposalSubmittedMailtext',
            'description' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:flexform.mailtext.editor.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'oap_mailtext',
            ],
        ],
        'event_proposal_in_revision_mailtext' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.eventProposalInRevisionMailtext',
            'description' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:flexform.mailtextRevision.editor.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'oap_mailtext',
            ],
        ],
        'event_proposal_accepted_mailtext' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.eventProposalAcceptedMailtext',
            'description' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:flexform.mailtext.editor.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'oap_mailtext',
            ],
        ],
        'event_proposal_declined_mailtext' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.eventProposalDeclinedMailtext',
            'description' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:flexform.mailtext.editor.description<',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'oap_mailtext',
            ],
        ],
    ],
];
