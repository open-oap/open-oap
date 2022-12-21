<?php

defined('TYPO3') || die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'OpenOap',
        'web',
        'BackendForms',
        '',
        [
            \OpenOAP\OpenOap\Controller\BackendFormsController::class => 'listForms, showOverviewForms, showReleaseNotesForms, previewForm',
        ],
        [
            'access' => 'user,group',
            'icon'   => 'EXT:open_oap/Resources/Public/Icons/oap_module_common.svg',
            'labels' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_BackendForms.xlf',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'OpenOap',
        'web',
        'BackendProposals',
        '',
        [
            \OpenOAP\OpenOap\Controller\BackendProposalsController::class => 'showOverviewCalls, listProposals, showReleaseNotesProposals, showProposal, customizeStatusMail',
        ],
        [
            'access' => 'user,group',
            'icon'   => 'EXT:open_oap/Resources/Public/Icons/oap_module_common.svg',
            'labels' => 'LLL:EXT:open_oap/Resources/Private/Language/locallang_BackendProposals.xlf',
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_call', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_call.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_call');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_formpage', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_formpage.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_formpage');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_formgroup', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_formgroup.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_formgroup');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_formitem', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_formitem.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_formitem');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_itemoption', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_itemoption.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_itemoption');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_itemvalidator', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_itemvalidator.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_itemvalidator');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_proposal', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_proposal.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_proposal');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_answer', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_answer.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_answer');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_grouptitle', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_grouptitle.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_grouptitle');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_comment', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_comment.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_comment');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_openoap_domain_model_logicatom', 'EXT:open_oap/Resources/Private/Language/locallang_csh_tx_openoap_domain_model_logicatom.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_openoap_domain_model_logicatom');
})();
