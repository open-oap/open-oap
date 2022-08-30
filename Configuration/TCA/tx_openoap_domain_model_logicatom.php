<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title,value',
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'title, item, logic, value, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
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
                    ['', 0],
                ],
                'foreign_table' => 'tx_openoap_domain_model_logicatom',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_logicatom}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_logicatom}.{#sys_language_uid} IN (-1,0)',
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
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],

        'title' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
                'default' => '',
            ],
        ],

        'item' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.item',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_formitem',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formitem}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_formitem}.type IN (1,3,4,5,6,7,8)',
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

        'logic' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.logic',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['-- Label --', 0],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.compare.equal', 1],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.compare.greater_equal', 2],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.compare.greater', 3],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.compare.less', 4],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.compare.less_equal', 5],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.compare.unequal', 6],
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => '',
            ],
        ],
        'value' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_logicatom.value',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],

    ],
];
