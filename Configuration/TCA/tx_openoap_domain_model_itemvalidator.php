<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_itemvalidator',
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
        ],
        'searchFields' => 'title,param1,param2',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'iconfile' => 'EXT:open_oap/Resources/Public/Icons/oap_model.svg',
        'type' => 'type',
    ],
    'types' => [
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_MANDATORY => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_INTEGER => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_FLOAT => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_MAXCHAR => ['showitem' => 'title, type, param1, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_MINVALUE => ['showitem' => 'title, type, param1, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_MAXVALUE => ['showitem' => 'title, type, param1, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_EMAIL => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_WEBSITE => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_PHONE => ['showitem' => 'title, type, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_FILE_TYPE => ['showitem' => 'title, type, param1, param2, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_FILE_SIZE => ['showitem' => 'title, type, param1, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_GREATERTHAN => ['showitem' => 'title, type, item, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
        \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_LESSTHAN => ['showitem' => 'title, type, item, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, sys_language_uid, l10n_parent, l10n_diffsource, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, '],
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
                        'label' => '',
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
                'eval' => 'trim',
                'default' => '',
                'required' => true,
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
                    [
                        'label' => '',
                        'value' => 0,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_mandatory',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_MANDATORY,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_integer',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_INTEGER,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_float',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_FLOAT,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_maxChar',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_MAXCHAR,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_minValue',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_MINVALUE,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_maxValue',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_MAXVALUE,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_greaterThan',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_GREATERTHAN,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_lessThan',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_LESSTHAN,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_email',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_EMAIL,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_website',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_WEBSITE,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.type_phone',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_PHONE,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.file_type',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_FILE_TYPE,
                    ],
                    [
                        'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_cboap_domain_model_itemvalidator.file_size',
                        'value' => \OpenOAP\OpenOap\Controller\OapBaseController::VALIDATOR_FILE_SIZE,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
                'required' => true,
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
        'item' => [
            'exclude' => false,
            'l10n_display' => 'defaultAsReadonly',
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_itemvalidator.item',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_openoap_domain_model_formitem',
                // 'foreign_table_where' => 'AND {#tx_openoap_domain_model_formitem}.pid=###PAGE_TSCONFIG_ID### AND {#tx_openoap_domain_model_formitem}.hidden = 0 AND {#tx_openoap_domain_model_formitem}.{#sys_language_uid} IN (-1,0)',
                'MM' => 'tx_openoap_itemvalidator_formitem_mm',
                'itemsProcFunc' => \OpenOAP\OpenOap\UserFunctions\FormEngine\DescendantsSelectItemsProcFunc::class .'->getAllElementsOfFormItems',
                'itemsProcConfig' => [
                    'model' => 'formitem',
                    'pidRoot' => 'pidFormItems'
                ],
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
