<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formpage',
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
        'searchFields' => 'title,menu_title,internal_title,intro_text',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
        'type' => 'type',
    ],
    'types' => [
        '0' => ['showitem' => 'title, menu_title, internal_title, intro_text, type, item_groups, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        '1' => ['showitem' => 'title, menu_title, internal_title, intro_text, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
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
                        'value' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'categories' => [
            'config'=> [
                'type' => 'category',
            ],
        ],

        'title' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formpage.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
                'required' => true,
            ],
        ],
        'menu_title' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formpage.menu_title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'internal_title' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formpage.internal_title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
                'required' => true,
            ],
        ],
        'intro_text' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formpage.intro_text',
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
        'type' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formpage.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:formpage.type_default',
                        'value' => 0,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_oap.xlf:formpage.type_preview',
                        'value' => 1,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => '',
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
                'itemsProcFunc' => \OpenOAP\OpenOap\UserFunctions\FormEngine\DescendantsSelectItemsProcFunc::class .'->getAllElementsOfFormGroups',
                'MM' => 'tx_openoap_formpage_formgroup_mm',
                'itemsProcConfig' => [
                    'model' => 'formgroup',
                    'pidRoot' => 'pidFormGroups'
                ],
//                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formgroup}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_formgroup}.hidden = 0 AND {#tx_openoap_domain_model_formgroup}.{#sys_language_uid} IN (-1,0)',
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
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_formpage.modificators',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_formmodificator',
                'MM' => 'tx_openoap_formpage_formmodificator_mm',
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
