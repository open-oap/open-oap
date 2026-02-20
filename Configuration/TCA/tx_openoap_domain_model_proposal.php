<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => false,
//        'languageField' => 'sys_language_uid',
//        'transOrigPointerField' => 'l10n_parent',
//        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title,meta_information',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
    ],
    'types' => [
        // '1' => ['showitem' => 'title, signature, survey_hash, state, archived, meta_information, tx_openoap_call, answers, comments, applicant, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        '1' => ['showitem' => 'title, signature, survey_hash, state, archived, meta_information, imported_score, imported_action, tx_openoap_call, applicant, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
    ],
    'columns' => [
//        'sys_language_uid' => [
//            'exclude' => true,
//            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
//            'config' => [
//                'default' => -1,
//                'type' => 'language',
//            ],
//        ],
//        'l10n_parent' => [
//            'displayCond' => 'FIELD:sys_language_uid:>:0',
//            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
//            'config' => [
//                'type' => 'select',
//                'renderType' => 'selectSingle',
//                'default' => 0,
//                'items' => [
//                    ['label' => '', 'value' => 0],
//                ],
//                'foreign_table' => 'tx_openoap_domain_model_proposal',
//                'foreign_table_where' => 'AND {#tx_openoap_domain_model_proposal}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_proposal}.{#sys_language_uid} IN (-1,0)',
//            ],
//        ],
//        'l10n_diffsource' => [
//            'config' => [
//                'type' => 'passthrough',
//            ],
//        ],
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
        'edit_tstamp' => [
            'label' => 'edit_tstamp',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'submit_tstamp' => [
            'label' => 'edit_tstamp',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'rejection_tstamp' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'rejection_email' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'fe_language_uid' => [
            'label' => 'fe_language_uid',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'title' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
                'required' => true,
            ],
        ],
        'signature' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.signature',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'survey_hash' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.survey_hash',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'default' => '',
                'readOnly' => true,
            ],
        ],
        'assessment_value' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.assessment_value',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'state' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '-- Label --',
                        'value' => 0,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.1',
                        'value' => 1,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.2',
                        'value' => 2,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.3',
                        'value' => 3,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.4',
                        'value' => 4,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.5',
                        'value' => 5,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.6',
                        'value' => 6,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
                'required' => true,
            ],
        ],
        'archived' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.archived',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'meta_information' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.meta_information',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'tx_openoap_call' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.tx_openoap_call',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_openoap_domain_model_call',
                'foreign_table_where' => '{#tx_openoap_domain_model_call}.{#sys_language_uid} IN (-1,0)',
                'default' => 0,
                'minitems' => 0,
                'maxitems' => 1,
            ],

        ],
        'answers' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.answers',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_answer',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_answer}.pid=###PAGE_TSCONFIG_ID###',
                'default' => 0,
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => false,
                    ],
                    'addRecord' => [
                        'disabled' => true,
                    ],
                    'listModule' => [
                        'disabled' => true,
                    ],
                ],
            ],

        ],
        'comments' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.comments',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_comment',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_comment}.pid=###PAGE_TSCONFIG_ID###',
                'default' => 0,
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => false,
                    ],
                    'addRecord' => [
                        'disabled' => true,
                    ],
                    'listModule' => [
                        'disabled' => true,
                    ],
                ],
            ],

        ],
        'applicant' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.applicant',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'AND {#fe_users}.pid=###PAGE_TSCONFIG_ID###',
                'default' => 0,
                'maxitems' => 1,
                'multiple' => 0,
            ],
        ],
        'reviewer' => [
            'exclude' => true,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.reviewer',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'be_users',
//                'foreign_table_where' => 'AND {#fe_users}.pid=###PAGE_TSCONFIG_ID###',
                'default' => 0,
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 1,
                'multiple' => 0,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => true,
                    ],
                    'addRecord' => [
                        'disabled' => true,
                    ],
                    'listModule' => [
                        'disabled' => true,
                    ],
                ],
            ],

        ],
        'review_time' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.review_time',
            'config' => [
                'dbType' => 'datetime',
                'type' => 'datetime',
                'size' => 12,
                'default' => null,
            ],
        ],

        'assessment_answers' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.assessment_answers',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_answer',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_answer}.pid=###PAGE_TSCONFIG_ID###',
                'default' => 0,
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => false,
                    ],
                    'addRecord' => [
                        'disabled' => true,
                    ],
                    'listModule' => [
                        'disabled' => true,
                    ],
                ],
            ],
        ],
        'imported_score' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.imported_score',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'imported_action' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.imported_action',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
    ],
];
