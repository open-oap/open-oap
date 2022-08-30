<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_comment',
        'label' => 'text',
        'label_alt' => 'uid,text',
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
        'searchFields' => 'text',
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
//                    ['', 0],
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
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.creationDate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'readOnly' => 1,
            ],
        ],
        'cruser_id' => [
            'exclude' => true,
            'label' => 'User ID',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int',
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
                    ['-- Label --', 0],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:comment.source_auto', 1],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:comment.source_edit', 2],
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
                'type' => 'input',
                'size' => 4,
                'eval' => 'int',
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
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:comment.state_new', 0],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:comment.state_accepted', 1],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:comment.state_auto_accepted', 2],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:comment.state_archived', 9],
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
                'foreign_field' => 'comments',
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
