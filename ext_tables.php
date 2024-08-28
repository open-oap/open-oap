<?php

defined('TYPO3') or die();

$GLOBALS['TCA']['tx_openoap_domain_model_supporter']['columns']['event_proposal_submitted_mailtext']['config']['default'] = \OpenOAP\OpenOap\Utility\LocalizationUtility::translateBEContext('LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.eventProposalSubmittedMailtext.default');
$GLOBALS['TCA']['tx_openoap_domain_model_supporter']['columns']['event_proposal_in_revision_mailtext']['config']['default'] = \OpenOAP\OpenOap\Utility\LocalizationUtility::translateBEContext('LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.eventProposalInRevisionMailtext.default');
$GLOBALS['TCA']['tx_openoap_domain_model_supporter']['columns']['event_proposal_accepted_mailtext']['config']['default'] = \OpenOAP\OpenOap\Utility\LocalizationUtility::translateBEContext('LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.eventProposalAcceptedMailtext.default');
$GLOBALS['TCA']['tx_openoap_domain_model_supporter']['columns']['event_proposal_declined_mailtext']['config']['default'] = \OpenOAP\OpenOap\Utility\LocalizationUtility::translateBEContext('LLL:EXT:open_oap/Resources/Private/Language/locallang_db.xlf:tx_openoap_domain_model_supporter.eventProposalDeclinedMailtext.default');
