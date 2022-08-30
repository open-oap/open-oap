<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
//        'languageField' => 'sys_language_uid',
//        'transOrigPointerField' => 'l10n_parent',
//        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title,meta_information',
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'title, signature, state, archived, meta_information, tx_openoap_call, answers, comments, applicant, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
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
//                    ['', 0],
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
                        0 => '',
                        1 => '',
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
                'eval' => 'trim,required',
                'default' => '',
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
        'state' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['-- Label --', 0],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.1', 1],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.2', 2],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.3', 3],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.4', 4],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.5', 5],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_domain_model_proposal.state.6', 6],
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'required',
            ],
        ],
        'archived' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_proposal.archived',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                    ],
                ],
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
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'AND {#fe_users}.pid=###PAGE_TSCONFIG_ID###',
                'default' => 0,
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 1,
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
    ],
];
