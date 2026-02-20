<?php

use OpenOAP\OpenOap\TypeConverter\UserObjectConverter;

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
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    // RTE Preset
    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['oap_mailtext'] = 'EXT:open_oap/Configuration/TSconfig/RTE/Mailtext.yaml';

    // open_oap_user

    // Language Overrides
    // felogin
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:felogin/Resources/Private/Language/locallang.xlf'][]
        = 'EXT:open_oap/Resources/Private/Language/felogin/locallang.xlf';
    // felogin de
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['de']['EXT:open_oap/Resources/Private/Language/felogin/locallang.xlf'][]
        = 'EXT:open_oap/Resources/Private/Language/felogin/de.locallang.xlf';
    // femanager
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:femanager/Resources/Private/Language/locallang.xlf'][]
        = 'EXT:open_oap/Resources/Private/Language/femanager/locallang.xlf';
    // femanager de
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['de']['EXT:femanager/Resources/Private/Language/locallang.xlf'][]
        = 'EXT:open_oap/Resources/Private/Language/femanager/de.locallang.xlf';

    // XCLASS
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Femanager\Domain\Model\User::class] = [
        'className' => \OpenOAP\OpenOap\Domain\Model\Applicant::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Femanager\Domain\Model\UserGroup::class] = [
        'className' => \OpenOAP\OpenOap\Domain\Model\ApplicantGroup::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Femanager\Controller\NewController::class] = [
        'className' => \OpenOAP\OpenOap\Controller\ApplicantCreateController::class,
    ];

//  changed registration from invitation plugin to "new user" plugin - not needed anymore
//    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Femanager\Controller\InvitationController::class] = [
//        'className' => \OpenOAP\OpenOap\Controller\UserInvitationController::class,
//    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][ \Bithost\Pdfviewhelpers\ViewHelpers\ImageViewHelper::class ] = [
        'className' => \OpenOAP\OpenOap\Xclass\Pdfviewhelpers\ImageViewHelper::class,
    ];

//  changed registration from invitation plugin to "new user" plugin - not needed anymore
//  Add statusAction to nonCacheableAction of femanager invitation plugin
//    if (!in_array('status', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Femanager']['plugins']['Invitation']['controllers'][\In2code\Femanager\Controller\InvitationController::class]['nonCacheableActions'])) {
//        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Femanager']['plugins']['Invitation']['controllers'][\In2code\Femanager\Controller\InvitationController::class]['nonCacheableActions'][] = 'status';
//    }
})();
