<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_itemvalidator',
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
        'searchFields' => 'title,param1,param2',
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
        'type' => 'type',
    ],
    'types' => [
        // type_mandatory
        '1' => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_integer
        '2' => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_float
        '3' => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_maxChar
        '4' => ['showitem' => 'title, type, param1, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_minValue
        '5' => ['showitem' => 'title, type, param1, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_maxValue
        '6' => ['showitem' => 'title, type, param1, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_email
        '7' => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_website
        '8' => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_phone
        '9' => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // file_type
        '10' => ['showitem' => 'title, type, param1, param2, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // file_size
        '11' => ['showitem' => 'title, type, param1, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
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
                'foreign_table' => 'tx_openoap_domain_model_itemvalidator',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_itemvalidator}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_itemvalidator}.{#sys_language_uid} IN (-1,0)',
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
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_itemvalidator.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
                'default' => '',
            ],
        ],
        'type' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_itemvalidator.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['-- Label --', 0],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_mandatory', 1],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_integer', 2],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_float', 3],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_maxChar', 4],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_minValue', 5],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_maxValue', 6],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_email', 7],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_website', 8],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_phone', 9],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.file_type', 10],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.file_size', 11],
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'required',
            ],
        ],
        'param1' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_itemvalidator.param1',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'param2' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_itemvalidator.param2',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],

    ],
];
