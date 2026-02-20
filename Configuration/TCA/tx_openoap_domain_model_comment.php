<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_comment',
        'label' => 'text',
        'label_alt' => 'uid,text',
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
        'searchFields' => 'text',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'crdate, text, source, code, state, proposal, item, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
    ],
    'columns' => [
//        'sys_language_uid' => [
//            'exclude' => true,
//            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
//            'config' => [
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
//                'foreign_table' => 'tx_openoap_domain_model_comment',
//                'foreign_table_where' => 'AND {#tx_openoap_domain_model_comment}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_comment}.{#sys_language_uid} IN (-1,0)',
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
        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.creationDate',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'readOnly' => 1,
            ],
        ],
        'cruser_id' => [
            'exclude' => true,
            'label' => 'User ID',
            'config' => [
                'type' => 'number',
                'size' => 4,
                'readOnly' => 1,
            ],
        ],
        'text' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_comment.text',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'source' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_comment.source',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '-- Label --',
                        'value' => 0,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:comment.source_auto',
                        'value' => 1,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:comment.source_edit',
                        'value' => 2,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'code' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_comment.code',
            'config' => [
                'type' => 'number',
                'size' => 4,
                'default' => 0,
            ],
        ],
        'state' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_comment.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:comment.state_new',
                        'value' => 0,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:comment.state_accepted',
                        'value' => 1,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:comment.state_auto_accepted',
                        'value' => 2,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:comment.state_archived',
                        'value' => 9,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'proposal' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_comment.proposal',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_openoap_domain_model_proposal',
                // 'foreign_field' => 'comments',
            ],

        ],
        'answer' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_comment.answer',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_openoap_domain_model_answer',
            ],
        ],
    ],
];
