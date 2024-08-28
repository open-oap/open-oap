<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup',
        'label' => 'internal_title',
        'label_alt' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title,internal_title,intro_text,help_text,model_name',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
        'type' => 'type',
    ],
    'types' => [
        '0' => ['showitem' => 'title, internal_title, type, intro_text, help_text, display_type, repeatable_min, repeatable_max, items, group_title, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        '1' => ['showitem' => 'title, internal_title, type, intro_text, help_text, repeatable_min, repeatable_max, item_groups, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
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
                    [
                        'label' => '',
                        'value' => 0,
                    ],
                ],
                'foreign_table' => 'tx_openoap_domain_model_formgroup',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formgroup}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_formgroup}.{#sys_language_uid} IN (-1,0)',
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
                        'value' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],

        'title' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
                'required' => true,
            ],
        ],
        'internal_title' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.internal_title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'type' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.type_default',
                        'value' => 0,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.type_meta',
                        'value' => 1,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => '',
            ],
        ],
        'intro_text' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.intro_text',
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
                'rows' => 15,
                'eval' => 'trim',
            ],

        ],
        'help_text' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.help_text',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'model_name' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.model_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'display_type' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.display_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.display_type_default',
                        'value' => 0,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.display_type_table',
                        'value' => 1,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'repeatable_min' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.repeatable_min',
            'config' => [
                'type' => 'number',
                'size' => 4,
                'default' => 1,
            ],
        ],
        'repeatable_max' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.repeatable_max',
            'config' => [
                'type' => 'number',
                'size' => 4,
                'default' => 1,
            ],
        ],
        'item_groups' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formpage.item_groups',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_formgroup',
                'MM' => 'tx_openoap_formgroup_formgroup_mm',
                'itemsProcFunc' => \OpenOAP\OpenOap\UserFunctions\FormEngine\DescendantsSelectItemsProcFunc::class .'->getAllElementsOfFormGroups',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formgroup}.{#type} IN (0)',
//                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formgroup}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_formgroup}.hidden = 0 AND {#tx_openoap_domain_model_formgroup}.{#sys_language_uid} IN (-1,0) AND {#tx_openoap_domain_model_formgroup}.{#type} IN (0)',
                'itemsProcConfig' => [
                    'model' => 'formgroup',
                    'pidRoot' => 'pidFormGroups',
                    'type' => '0'
                ],
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

        'items' => [
            'exclude' => false,
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.items',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_formitem',
                // 'foreign_table_where' => 'AND {#tx_openoap_domain_model_formitem}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_formitem}.hidden = 0 AND {#tx_openoap_domain_model_formitem}.{#sys_language_uid} IN (-1,0)',
                'MM' => 'tx_openoap_formgroup_formitem_mm',
                'itemsProcFunc' => \OpenOAP\OpenOap\UserFunctions\FormEngine\DescendantsSelectItemsProcFunc::class .'->getAllElementsOfFormItems',
                'itemsProcConfig' => [
                    'model' => 'formitem',
                    'pidRoot' => 'pidFormItems'
                ],
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
        'group_title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.group_title',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_grouptitle',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_grouptitle}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_grouptitle}.hidden = 0 AND {#tx_openoap_domain_model_grouptitle}.{#sys_language_uid} IN (-1,0)',
                'MM' => 'tx_openoap_formgroup_grouptitle_mm',
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
        'modificators' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formgroup.modificators',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_formmodificator',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formmodificator}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_formmodificator}.hidden = 0 AND {#tx_openoap_domain_model_formmodificator}.{#sys_language_uid} IN (-1,0)',
                'MM' => 'tx_openoap_formgroup_formmodificator_mm',
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

    ],
];
