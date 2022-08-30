<?php

defined('TYPO3') || die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'OpenOap',
        'Dashboard',
        [
            \OpenOAP\OpenOap\Controller\ApplicantController::class => 'dashboard, proposals, mail',
            \OpenOAP\OpenOap\Controller\ProposalController::class => 'download, downloadWord, downloadAttachments, delete',
        ],
        // non-cacheable actions
        [
            \OpenOAP\OpenOap\Controller\ApplicantController::class => 'dashboard, proposals, mail',
            \OpenOAP\OpenOap\Controller\ProposalController::class => 'download, downloadWord, downloadAttachments, delete',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'OpenOap',
        'Proposals',
        [
            \OpenOAP\OpenOap\Controller\ApplicantController::class => 'proposals',
        ],
        // non-cacheable actions
        [
            \OpenOAP\OpenOap\Controller\ApplicantController::class => 'proposals',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'OpenOap',
        'Applicant',
        [
            \OpenOAP\OpenOap\Controller\ApplicantController::class => 'show',
        ],
        // non-cacheable actions
        [
            \OpenOAP\OpenOap\Controller\ApplicantController::class => 'show',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'OpenOap',
        'Applicantform',
        [
            \OpenOAP\OpenOap\Controller\ApplicantController::class => 'edit, extend, update',
        ],
        // non-cacheable actions
        [
            \OpenOAP\OpenOap\Controller\ApplicantController::class => 'edit, extend, update',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'OpenOap',
        'Form',
        [
            \OpenOAP\OpenOap\Controller\ProposalController::class => 'create, edit, update',
        ],
        // non-cacheable actions
        [
            \OpenOAP\OpenOap\Controller\ProposalController::class => 'create, edit, update, delete',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'OpenOap',
        'Notifications',
        [
            \OpenOAP\OpenOap\Controller\ProposalController::class => 'notifications',
        ],
        // non-cacheable actions
        [
            \OpenOAP\OpenOap\Controller\ProposalController::class => 'notifications',
        ]
    );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    dashboard {
                        iconIdentifier = open_oap-plugin-dashboard
                        title = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_dashboard.name
                        description = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_dashboard.description
                        tt_content_defValues {
                            CType = list
                            list_type = openoap_dashboard
                        }
                    }
                    proposals {
                        iconIdentifier = open_oap-plugin-proposals
                        title = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_proposals.name
                        description = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_proposals.description
                        tt_content_defValues {
                            CType = list
                            list_type = openoap_proposals
                        }
                    }
                    applicant {
                        iconIdentifier = open_oap-plugin-applicant
                        title = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_applicant.name
                        description = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_applicant.description
                        tt_content_defValues {
                            CType = list
                            list_type = openoap_applicant
                        }
                    }
                    applicantform {
                        iconIdentifier = open_oap-plugin-applicantform
                        title = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_applicantform.name
                        description = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_applicantform.description
                        tt_content_defValues {
                            CType = list
                            list_type = openoap_applicantform
                        }
                    }
                    form {
                        iconIdentifier = open_oap-plugin-form
                        title = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_form.name
                        description = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_form.description
                        tt_content_defValues {
                            CType = list
                            list_type = openoap_form
                        }
                    }
                    notifications {
                        iconIdentifier = open_oap-plugin-notifications
                        title = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_notifications.name
                        description = LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_notifications.description
                        tt_content_defValues {
                            CType = list
                            list_type = openoap_notifications
                        }
                    }
                }
                show = *
            }
       }'
    );

    // RTE Preset
    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['oap_mailtext'] = 'EXT:open_oap/Configuration/TSconfig/RTE/Mailtext.yaml';
})();
