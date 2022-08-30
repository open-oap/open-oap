<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_answer',
        'label' => 'value',
        'label_alt' => 'uid, value',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
//        'languageField' => 'sys_language_uid',
//        'transOrigPointerField' => 'l10n_parent',
//        'transOrigDiffSourceField' => 'l10n_diffsource',
        'enablecolumns' => [
        ],
        'searchFields' => 'value,additional_value,past_answers',
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'value, type, element_counter, additional_value, past_answers, item, model, comments, '],
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
//                'foreign_table' => 'tx_openoap_domain_model_answer',
//                'foreign_table_where' => 'AND {#tx_openoap_domain_model_answer}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_answer}.{#sys_language_uid} IN (-1,0)',
//            ],
//        ],
//        'l10n_diffsource' => [
//            'config' => [
//                'type' => 'passthrough',
//            ],
//        ],

        'value' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_answer.value',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'type' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_answer.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['-- Label --', 0],
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => '',
            ],
        ],
        'element_counter' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_answer.element_counter',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int',
                'default' => 0,
            ],
        ],
        'additional_value' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_answer.additional_value',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'past_answers' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_answer.past_answers',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'item' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_answer.item',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_openoap_domain_model_formitem',
                'default' => 0,
                'minitems' => 0,
                'maxitems' => 1,
            ],

        ],
        'model' => [
            'exclude' => true,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_answer.model',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_openoap_domain_model_formgroup',
                'default' => 0,
                'minitems' => 0,
                'maxitems' => 1,
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
    ],
];
