<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title,intro_text,teaser_text,emails,fe_user_exceptions',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'call_group, supporter, type, extern_link, title, intro_text, teaser_text, shortcut, emails, call_start_time, call_end_time, hint, proposal_pid, form_pages, items, word_header_logo, word_styles, logo,blocked_languages,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, usergroup, starttime, endtime, --div--;LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.survey_tab,anonym, survey_codes'],
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
                'foreign_table' => 'tx_openoap_domain_model_call',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_call}.{#pid}=###CURRENT_PID### AND {#tx_openoap_domain_model_call}.{#sys_language_uid} IN (-1,0)',
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
                'default' => 1,
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
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'categories' => [
            'config' => [
                'type' => 'category',
            ],
        ],

        'title' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.title',
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
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.intro_text',
            'displayCond' => 'FIELD:type:!=:1',
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
        'teaser_text' => [
            'exclude' => false,
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.teaser_text',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'text',
                'enableRichtext' => false,
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
            ],
        ],
        'shortcut' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.shortcut',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'emails' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.emails',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'call_start_time' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.call_start_time',
            'config' => [
                'type' => 'datetime',
                'dbType' => 'datetime',
                'size' => 12,
                'format' => 'datetime',
                'default' => null,
            ],
        ],
        'call_end_time' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.call_end_time',
            'config' => [
                'type' => 'datetime',
                'dbType' => 'datetime',
                'size' => 12,
                'format' => 'datetime',
                'default' => null,
            ],
        ],
        'fe_user_exceptions' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.fe_user_exceptions',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'proposal_pid' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.proposal_pid',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'select',
                'readOnly' => false,
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \OpenOAP\OpenOap\UserFunctions\FormEngine\DescendantsSelectItemsProcFunc::class . '->getPoolPages',
                'minitems' => 0,
                'maxitems' => 1,
                'size' => 1,
//                'foreign_table' => 'pages',
//                'foreign_table_where' => 'AND {#pages}.pid=###PAGE_TSCONFIG_ID### AND {#pages}.hidden = 0 AND {#pages}.{#sys_language_uid} IN (-1,0)',
//                'type' => 'user',
//                'renderType' => 'specialField'
            ],
        ],
        'form_pages' => [
            'exclude' => false,
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.form_pages',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_formpage',
                'MM' => 'tx_openoap_call_formpage_mm',
//                'foreign_table_where' ist replaced by itemsProcFunc
//                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formpage}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_formpage}.hidden = 0 AND {#tx_openoap_domain_model_formpage}.{#sys_language_uid} IN (-1,0)',
                'itemsProcFunc' => \OpenOAP\OpenOap\UserFunctions\FormEngine\DescendantsSelectItemsProcFunc::class . '->getAllElementsOfFormPages',
                'itemsProcConfig' => [
                    'model' => 'formpage',
                    'pidRoot' => 'pidFormPages'
                ],
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => false
                        ,
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
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.form_items',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_formitem',
                'MM' => 'tx_openoap_call_formitem_mm',
//                'foreign_table_where' ist replaced by itemsProcFunc
//                'foreign_table_where' => 'AND {#tx_openoap_domain_model_formitem}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_formitem}.hidden = 0 AND {#tx_openoap_domain_model_formitem}.{#sys_language_uid} IN (-1,0) AND {#tx_openoap_domain_model_formitem}.{#type} = ###PAGE_TSCONFIG_STR###',
                'itemsProcFunc' => \OpenOAP\OpenOap\UserFunctions\FormEngine\DescendantsSelectItemsProcFunc::class . '->getAllElementsOfFormItems',
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
        'usergroup' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_db.xlf:fe_users.usergroup',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_groups',
                'size' => 6,
                'minitems' => 1,
            ],
        ],
        'word_styles' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.word_styles',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'text',
                'cols' => 50,
                'rows' => 40,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'word_template' => [
            'exclude' => true,
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.word_template',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => ['docx'],
            ],
        ],
        'logo' => [
            'exclude' => true,
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.logo',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => ['gif', 'jpg', 'jpeg', 'png', 'svg', 'pdf'],
            ],
        ],
        'word_header_logo' => [
            'exclude' => true,
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.word_header_logo',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => ['gif', 'jpg', 'jpeg', 'png'],
            ],
        ],
        'blocked_languages' => [
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.blocked_languages',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items' => [
                    [
                        'label' => 'English',
                        'value' => 0,
                    ],
                    [
                        'label' => 'Deutsch',
                        'value' => 1,
                    ],
                ],
                'size' => 3,
                'autoSizeMax' => 10,
                'multiple' => true,
            ],
        ],
        'anonym' => [
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.anonym',
            'description' => 'renderType=checkboxToggle single',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.anonym_item_checked',
                        'labelChecked' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.anonym_item_checked',
                        'labelUnchecked' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.anonym_item_unchecked',
                    ],
                ],
            ],
        ],
        'survey_codes' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.survey_codes',
            'displayCond' => 'FIELD:type:!=:1',
            'config' => [
                'type' => 'text',
                'cols' => 50,
                'rows' => 40,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'call_group' => [
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.call_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '',
                        'value' => 0,
                    ],
                ],
                'foreign_table' => 'tx_openoap_domain_model_callgroup',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_callgroup}.{#sys_language_uid} IN (-1,0)',
            ],
        ],
        'supporter' => [
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.supporter',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '',
                        'value' => 0,
                    ],
                ],
                'foreign_table' => 'tx_openoap_domain_model_supporter',
                'foreign_table_where' => 'AND {#tx_openoap_domain_model_supporter}.{#sys_language_uid} IN (-1,0) ORDER BY uid',
            ],
        ],
        'type' => [
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.type',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'intern',
                        'value' => 0,
                    ],
                    [
                        'label' => 'extern',
                        'value' => 1,
                    ],
                ],
            ],
        ],
        'extern_link' => [
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.extern_link',
            'displayCond' => 'FIELD:type:=:1',
            'config' => [
                'type' => 'input',
                'required' => true,
            ]
        ],
        'hint' => [
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_call.hint',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
            ]
        ],
    ],
];
