<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem',
        'label' => 'question',
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
        'searchFields' => 'question,intro_text,help_text,filter_label,default_value,unit,additional_label',
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
        'type' => 'type',
    ],
    'types' => [
        // type_string
        '1' => ['showitem' => 'question, intro_text, help_text, type, unit, default_value, validators, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.tab.meta, enabled_info, enabled_title, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_text 2
        '2' => ['showitem' => 'question, intro_text, help_text, type, default_value, validators, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_date1 3
        '3' => ['showitem' => 'question, intro_text, help_text, type, validators, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.tab.meta,enabled_filter, filter_label, enabled_info, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_date2 4
        '4' => ['showitem' => 'question, intro_text, help_text, type, validators, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.tab.meta,enabled_filter, filter_label, enabled_info, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_checkbox 5
        '5' => ['showitem' => 'question, intro_text, help_text, type, options, additional_value, additional_label, validators, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.tab.meta, enabled_filter, filter_label, enabled_info, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_radiobutton 6
        '6' => ['showitem' => 'question, intro_text, help_text, type, options, additional_value, additional_label, validators, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.tab.meta,enabled_filter, filter_label, enabled_info, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_select 7
        '7' => ['showitem' => 'question, intro_text, help_text, type, options, validators, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.tab.meta, enabled_filter, filter_label, enabled_info, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_select multiple 8
        '8' => ['showitem' => 'question, intro_text, help_text, type, options, validators, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.tab.meta, enabled_filter, filter_label, enabled_info, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        // type_upload 8
        '9' => ['showitem' => 'question, intro_text, help_text, type, validators, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.tab.meta, enabled_filter, filter_label, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
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
                'foreign_table' => 'tx_openoap_domain_model_formitem',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formitem}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_formitem}.{#sys_language_uid} IN (-1,0)',
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

        'question' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.question',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
                'default' => '',
            ],
        ],
        'intro_text' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.intro_text',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => false,
                    ],
                ],
                'cols' => 40,
                'rows' => 10,
                'eval' => 'trim',
            ],

        ],
        'help_text' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.help_text',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 7,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'type' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['-- Label --', 0],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_string', 1],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_text', 2],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_date1', 3],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_date2', 4],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_checkbox', 5],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_radiobutton', 6],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_select_single', 7],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_select_multiple', 8],
                    ['LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:tx_openoap_domain_model_formitem.type_upload', 9],
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'required',
            ],
        ],
        'enabled_filter' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.enabled_filter',
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
        'filter_label' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.filter_label',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'enabled_info' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.enabled_info',
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
        'enabled_title' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.enabled_title',
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
        'additional_value' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.additional_value',
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
        'default_value' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.default_value',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'unit' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.unit',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'additional_label' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.additional_label',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'options' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.options',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_itemoption',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_itemoption}.pid IN ( ###PAGE_TSCONFIG_IDLIST###) AND {#tx_openoap_domain_model_itemoption}.hidden = 0 AND {#tx_openoap_domain_model_itemoption}.{#sys_language_uid} IN (-1,0)',
                'MM' => 'tx_openoap_formitem_itemoption_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => false,
                    ],
                    'addRecord' => [
                        'disabled' => false,
                        'options' => [
                            'pid' => '###PAGE_TSCONFIG_ID###',
                        ],
                    ],
                    'listModule' => [
                        'disabled' => false,
                    ],
                ],
            ],

        ],
        'validators' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.validators',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_itemvalidator',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_itemvalidator}.pid IN (###PAGE_TSCONFIG_IDLIST###) AND {#tx_openoap_domain_model_itemvalidator}.hidden = 0 AND {#tx_openoap_domain_model_itemvalidator}.{#sys_language_uid} IN (-1,0)',
                'MM' => 'tx_openoap_formitem_itemvalidator_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => false,
                    ],
                    'addRecord' => [
                        'disabled' => false,
                        'options' => [
                            'pid' => '###PAGE_TSCONFIG_ID###',
                        ],
                    ],
                    'listModule' => [
                        'disabled' => true,
                    ],
                ],
            ],

        ],
        'dependent_on' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formitem.dependent_on',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_logicatom',
                'MM' => 'tx_openoap_formitem_logicatom_mm',
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
