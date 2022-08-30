<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Domain\Model\Answer;
use OpenOAP\OpenOap\Domain\Model\Comment;
use OpenOAP\OpenOap\Domain\Model\FormGroup;
use OpenOAP\OpenOap\Domain\Model\FormItem;
use OpenOAP\OpenOap\Domain\Model\FormPage;
use OpenOAP\OpenOap\Domain\Model\MetaInformation;
use OpenOAP\OpenOap\Domain\Model\Proposal;
use OpenOAP\OpenOap\Domain\Repository\ProposalRepository;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This file is part of the "CB import news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 *           Ingeborg Hess <ingeborg.hess@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * BackendProposalController
 */
class BackendProposalsController extends OapBackendController
{
    /**
     * overview page
     */
    public function showOverviewProposalsAction()
    {
        $this->view->assignMultiple([
            'actionName' => $this->actionMethodName,
        ]);
    }

    /**
     * @throws InvalidQueryException
     */
    public function listProposalsAction(array $filter = [], array $selection = [], int $currentPage = 1): ResponseInterface
    {
        // update state of proposal selection
        if (count($selection) > 0 && is_array($selection['records'])) {
            if ($selection['todo'] == 'submit-export') {
                switch ($selection['export']) {
                    case 'csv':
                        $this->exportCsv($selection['records']);
                        break;
                    case 'downloadDocuments':
                        $this->downloadAttachments($selection['records']);
                        break;
                    case 'pdf':
                        $this->downloadPdfs($selection['records']);
                        break;
                }
            }
            if ($selection['todo'] == 'submit-state' && (int)$selection['state'] > 0) {
                //$this->proposalRepository->updateProposalState($selection['records'], (int)$selection['state']);
                //$updateCount = 0;
                foreach ($selection['records'] as $record) {
                    $proposal = $this->proposalRepository->findByUid((int)$record);
                    if ($proposal != null) {
                        $proposal->setState((int)$selection['state']);
                        $proposal->setEditTstamp(time());

                        if ($selection['state'] == self::PROPOSAL_RE_OPENED) {
                            $proposal = $this->processInnerComments($proposal);
                        } elseif ($selection['state'] == self::PROPOSAL_ACCEPTED || $selection['state'] == self::PROPOSAL_DECLINED) {
                            $proposalsArr[] = $proposal;
                        }

                        $log = $this->createLog((int)('10' . $selection['state']), $proposal);
                        $this->commentRepository->add($log);
                        $this->persistenceManager->persistAll();
                        $proposal->addLog($log);
                        $this->proposalRepository->update($proposal);
                        $this->persistenceManager->persistAll();
                    }
                }
                if (is_array($proposalsArr)) {
                    $this->redirect('customizeStatusMail', 'BackendProposals', null, ['proposals' => $proposalsArr, 'selectedState' => (int)$selection['state']]);
                }
            }
        }
        if ($filter['todo'] == 'clear') {
            $filter = [];
        }
        if (!$this->pageUid) {
            // $this->setMessage('no_page_selected', self::WARNING);
            // use fall-back
            $proposalPid = (integer)$this->settings['settings']['proposalPid'];
        } else {
            $proposalPid = $this->pageUid;
        }

        $callPid = (integer)$this->settings['callPid'];
        $calls = $this->callRepository->findAllByPid($callPid);

        $states = $this->createStatesArray();

        $filterItems = $this->formItemRepository->findByEnabledFilter((int)$this->settings['itemsPid']);
        $filterSelects = [];
        foreach ($filterItems as $filterItem) {
            $filterSelects[$filterItem->getUid()]['item'] = $filterItem;
            foreach ($filterItem->getOptions() as $optionItem) {
                $itemOptionsArray = explode("\r\n", $optionItem->getOptions());
                foreach ($itemOptionsArray as $itemOption) {
                    $itemOption = $this->cleanupOptionItem($itemOption);
                    if ($itemOption !== '') {
                        $filterSelects[$filterItem->getUid()]['options'][$itemOption['key']] = $itemOption['label'];
                    }
                }
            }
        }

        $sorting = [];
        if ($this->request->hasArgument('sortField')) {
            $sortField = htmlspecialchars($this->request->getArgument('sortField'));
            $sortRev = (int)($this->request->getArgument('sortRev'));
            if ($sortRev == 1) {
                $sorting = [$sortField => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING];
            } else {
                $sorting = [$sortField => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING];
            }
        }
        $allItems = $this->proposalRepository->findDemanded($proposalPid, self::STATE_ACCESS_MIN, $filter, $sorting)->toArray();

        $pagination = $this->createPaginator($allItems, $currentPage);

        $this->view->assignMultiple([
            'countOfItems' => count($allItems),
            'filter' => $filter,
            'states' => $states,
            'filterSelects' => $filterSelects,
            'exportFormats' => ['csv' => 'CSV', 'downloadDocuments' => 'Documents', 'pdf' => 'PDF'],
            'stateReopenedValue' => self::PROPOSAL_RE_OPENED,
            'calls' => $calls,
            'actionName' => $this->actionMethodName,
            'paginator' => $pagination['array'],
            'pagination' => $pagination['pagination'],
            'sorted' => ['sortField' => $sortField, 'sortRev' => $sortRev],
        ]);

        return $this->htmlResponse();
    }

    /**
     * @param Proposal $proposal
     * @return ResponseInterface
     */
    public function showProposalAction(Proposal $proposal): ResponseInterface
    {
        $updateProposal = false;

        // Update proposal state
        if ($this->request->hasArgument('selection') && is_array($this->request->getArgument('selection'))) {
            $selection = $this->request->getArgument('selection');
            if ($selection['todo'] == 'submit-state') {
                if ((int)($selection['limitEdit']) == self::META_PROPOSAL_EDITABLE_FIELDS_LIMIT) {
                    $limitEditableFields = self::META_PROPOSAL_EDITABLE_FIELDS_LIMIT;
                } else {
                    $limitEditableFields = self::META_PROPOSAL_EDITABLE_FIELDS_NO_LIMIT;
                }
                $selectedState = (int)($selection['state']);
//                if($selectedState > 0 && $selectedState != $proposal->getState()) {
                if ($selectedState > 0) {
                    $proposal->setState($selectedState);

                    if ($selectedState == self::PROPOSAL_RE_OPENED) {
                        $proposal = $this->processInnerComments($proposal);
                    }

                    $log = $this->createLog((int)('10' . $selectedState), $proposal);
                    $this->commentRepository->add($log);
                    $this->persistenceManager->persistAll();
                    $proposal->addLog($log);
                    $proposal->setEditTstamp(time());
                    $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:message.state_updated'), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                    $this->proposalRepository->update($proposal);

                    if ($selectedState == self::PROPOSAL_ACCEPTED || $selectedState == self::PROPOSAL_DECLINED) {
                        // Send status mail
                        $this->redirect('customizeStatusMail', 'BackendProposals', null, ['proposals' => [$proposal], 'selectedState' => $selectedState]);
                    }
                }
            }
        }
        // Add comment
        if ($this->request->hasArgument('comment') && strlen(trim($this->request->getArgument('comment'))) > 0) {
            $text = htmlspecialchars($this->request->getArgument('comment'));
            if ($this->request->hasArgument('answer')) {
                /** @var Answer $answer */
                $answer = $this->answerRepository->findByUid((int)($this->request->getArgument('answer')));
                if ($answer != null) {
                    $comment = $this->createComment($text, $proposal, $answer);
                    $answer->addComment($comment);
                    $this->answerRepository->update($answer);
                }
            } else {
                $comment = $this->createComment($text, $proposal);
                $proposal->setEditTstamp(time());
            }
            $this->commentRepository->add($comment);
            $this->persistenceManager->persistAll();
            $proposal->addComment($comment);
            $updateProposal = true;
        }

        $filterDemand['source'] = self::COMMENT_SOURCE_EDIT;
        $comments = $this->commentRepository->findDemanded($proposal, $filterDemand);
        $commentNewUri = $this->getBackendNewUri('tx_openoap_domain_model_comment', (int)$this->settings['commentsPoolId']);

        $this->flattenAnswers($proposal);
        $itemAnswerMap = $this->buildItemAnswerMap($proposal);

        /** @var MetaInformation $metaInfo */
        $metaInfo = new MetaInformation($proposal->getMetaInformation());

        // update metaInfo limitEditableFields
        $metaInfoLimitEditableFields = $metaInfo->getlimitEditableFields();
        if (isset($limitEditableFields) && $metaInfoLimitEditableFields != $limitEditableFields) {
            $metaInfo->setLimitEditableFields($limitEditableFields);
            $proposal->setMetaInformation($metaInfo->jsonSerialize());
            $updateProposal = true;
        } else {
            $limitEditableFields = $metaInfoLimitEditableFields;
        }
        // create GroupsCounterArray
        $groupsCounter = $this->groupsCounterMetaInfo($proposal, $metaInfo);
        $updateProposal = true;

        if ($updateProposal) {
            $this->proposalRepository->update($proposal);
        }

        $answersMap = $this->createAnswersMap($proposal);

        $states = $this->createStatesArray();

        // Download PDF
        if ($this->request->hasArgument('download')) {
            $pdfArguments = [
                'destination' => 'download',
                'filepath' => strtr($proposal->getTitle(), ' ', '-') . '--' . date('Ymd-Hi') . '.pdf',
                'generatedDate' => date('d.m.Y'),
                'generatedTime' => date('H:i'),
                'proposal' => $proposal,
                'commentsAtProposal' => $comments,
                'itemTypes' => $this->getConstants()['TYPE'],
                'answers' => $this->answers,
                'answersMap' => $answersMap,
                'itemAnswerMap' => $itemAnswerMap,
                'groupsCounter' => $groupsCounter,
                'settings' => $this->settings,
            ];
            $this->renderPdfView(self::EXPORT_TEMPLATE_PATH_PDF, $pdfArguments);
        }

        $this->view->assignMultiple([
            'comments' => $comments,
            'itemTypes' => $this->getConstants()['TYPE'],
            'proposalStates' => $this->getConstants()['PROPOSAL'],
            'commentNewUri' => $commentNewUri,
            'proposal' => $proposal,
            'states' => $states,
            'answers' => $this->answers,
            'answersMap' => $answersMap,
            'actionName' => $this->actionMethodName,
            'itemAnswerMap' => $itemAnswerMap,
            'groupsCounter' => $groupsCounter,
            'annotationButtonEnabled' => $proposal->getState() != self::PROPOSAL_RE_OPENED,
            'limitEditableFields' => $limitEditableFields,
        ]);

        return $this->htmlResponse();
    }

    /**
     * @param array $proposals
     * @param int $selectedState
     * @param array $selection
     * @return ResponseInterface
     */
    public function customizeStatusMailAction(array $proposals, int $selectedState, array $selection = []): ResponseInterface
    {
        $submitted = $this->request->hasArgument('submit');
        $mailCC = $this->request->hasArgument('cc') ? htmlspecialchars($this->request->getArgument('cc')) : '';
        $mailReplyTo = $this->request->hasArgument('reply') ? htmlspecialchars($this->request->getArgument('reply')) : '';
        $mailTemplatePaths = $this->getMailTemplatePaths();
        $mailTemplate = self::MAIL_TEMPLATE_PROPOSAL_STATUS;
        $additionalMailData = [];
        $proposalList = [];

        foreach ([0, 1] as $langId) {
            $flexformData[$langId]['flexFormString'] = $this->proposalRepository->findMailtextFlexformdata((int)$this->settings['dashboardPageId'], $langId);
            if ($flexformData[$langId]['flexFormString'] != null && $flexformData[$langId]['flexFormString'] != '') {
                $flexformData[$langId]['flexFormArray'] = GeneralUtility::xml2array($flexformData[$langId]['flexFormString']);
            }
        }

        $unparsedDefaultMailtext = $selectedState == self::PROPOSAL_ACCEPTED
            ? $flexformData[0]['flexFormArray']['data']['mail']['lDEF']['settings.eventProposalAcceptedMailtext']['vDEF']
            : $flexformData[0]['flexFormArray']['data']['mail']['lDEF']['settings.eventProposalDeclinedMailtext']['vDEF'];

        foreach ($proposals as $key => $proposalId) {
            $proposal = $this->proposalRepository->findByUid((int)$proposalId);
            $proposalList[$key]['proposal'] = $proposal;
            if ($submitted) {
                $selectedMailAction = $selection[$proposalId];
                $proposalList[$key]['mailaction']['text'] = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:mailing.result.mailAction.status.' . $selectedMailAction);
                if ($selectedMailAction == 0) {
                    continue;
                }
            }

            $proposalLangCode = $this->getProposalFrontendLanguageCode($proposal->getFeLanguageUid());
            $proposalList[$key]['maildata'] = [];
            $proposalList[$key]['maildata']['signature'] = $this->buildSignature($proposal);
            $proposalList[$key]['maildata']['siteName'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
            $proposalList[$key]['maildata']['langCode'] = $proposalLangCode;
            if (is_array($flexformData[$proposal->getFeLanguageUid()]['flexFormArray'])) {
                $mailtext = $selectedState == self::PROPOSAL_ACCEPTED
                    ? $flexformData[$proposal->getFeLanguageUid()]['flexFormArray']['data']['mail']['lDEF']['settings.eventProposalAcceptedMailtext']['vDEF']
                    : $flexformData[$proposal->getFeLanguageUid()]['flexFormArray']['data']['mail']['lDEF']['settings.eventProposalDeclinedMailtext']['vDEF'];
            } else {
                $mailtext = $unparsedDefaultMailtext;
            }
            $proposalList[$key]['maildata']['mailtext'] = $this->parseMailtext($proposal, $mailtext, $additionalMailData, $proposalLangCode);

            if ($selectedMailAction == 1) {
                $additionalData = [];
                if ($this->request->hasArgument('cc') && $this->request->getArgument('cc') != '') {
                    $additionalData['cc'] = htmlspecialchars($this->request->getArgument('cc'));
                }
                if ($this->request->hasArgument('reply') && $this->request->getArgument('reply') != '') {
                    $rto = GeneralUtility::trimExplode(',', htmlspecialchars($this->request->getArgument('reply')), true);
                    $additionalData['replyTo'] = $rto[0];
                }
                $this->sendEmail($proposal, $mailTemplatePaths, $mailTemplate, $proposalList[$key]['maildata']['mailtext'], $proposalLangCode, $additionalData);
            } elseif ($selectedMailAction == 2) {
                $proposalList[$key]['mailaction']['mailto']['href'] = $proposal->getApplicant()->getUsername() . '?cc=';
                if ($this->request->hasArgument('cc')) {
                    $proposalList[$key]['mailaction']['mailto']['href'].= htmlspecialchars($this->request->getArgument('cc'));
                }
            }
        }

        $defaultMailCc = $proposalList[0]['proposal']->getCall()->getEmails();
        $defaultMailCcExploded = GeneralUtility::trimExplode(',', $defaultMailCc, true);
        $proposalList[0]['defaultMailCc'] = $defaultMailCc;
        $proposalList[0]['defaultMailReplyTo'] = is_array($defaultMailCcExploded) && count($defaultMailCcExploded) > 0 ? $defaultMailCcExploded[0] : '';

        $this->view->assignMultiple([
            'proposalStates' => $this->getConstants()['PROPOSAL'],
            'proposals' => $proposals,
            'proposalList' => $proposalList,
            'state' => $selectedState,
            'siteName' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
            'mailtext' => count($proposals) == 1 ? $proposalList[0]['maildata']['mailtext'] : $unparsedDefaultMailtext,
            'submitted' => $submitted,
            'settings' => $this->settings,
        ]);

        return $this->htmlResponse();
    }

    /**
     * Process inner comments on status change to revision
     *
     * @param Proposal $proposal
     * @return Proposal $proposal
     */
    protected function processInnerComments($proposal): Proposal
    {
        $updateCount = 0;
        foreach ($proposal->getComments() as $comment) {
            if ($comment->getSource() == self::COMMENT_SOURCE_EDIT_ANSWER && $comment->getState() == self::COMMENT_STATE_NEW) {
                // state of answer comments will be changed on next submit (after an re-opened draft mode)
                $updateCount++;
            }
        }
        if ($updateCount > 0) {
            $text = (string)$updateCount;
            $log = $this->createLog(self::LOG_PROPOSAL_ANNOTATED, $proposal, $text);
            $this->commentRepository->add($log);
            $this->persistenceManager->persistAll();
            $proposal->addLog($log);
            $this->proposalRepository->update($proposal);
        }

        // Send revision mail
        $flexFormString = $this->proposalRepository->findMailtextFlexformdata((int)$this->settings['dashboardPageId'], $proposal->getFeLanguageUid());
        if ($flexFormString != null && $flexFormString != '') {
            $flexFormArray = GeneralUtility::xml2array($flexFormString);
            $mailtextInRevision = $flexFormArray['data']['mail']['lDEF']['settings.eventProposalInRevisionMailtext']['vDEF'];
            $proposalLangCode = $this->getProposalFrontendLanguageCode($proposal->getFeLanguageUid());

            $commentsNote = '';
            if ($updateCount == 1) {
                $commentsNote = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_openoap_domain_model_proposal.log.' . self::LOG_PROPOSAL_ANNOTATED . '.1', 'openOap', [$updateCount], $proposalLangCode);
            } elseif ($updateCount > 1) {
                $commentsNote = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_openoap_domain_model_proposal.log.' . self::LOG_PROPOSAL_ANNOTATED, 'openOap', [$updateCount], $proposalLangCode);
            }

            $additionalMailData = [
                '##COMMENTS-NOTE##' => $commentsNote,
            ];

            $mailTemplatePaths = $this->getMailTemplatePaths();
            $mailTemplate = self::MAIL_TEMPLATE_PROPOSAL_REVISION;
            $mailText = $this->parseMailtext($proposal, $mailtextInRevision, $additionalMailData, $proposalLangCode);
            $this->sendEmail($proposal, $mailTemplatePaths, $mailTemplate, $mailText, $proposalLangCode);
        }
        return $proposal;
    }

    protected function downloadAttachments($records): void
    {
        /** @var ResourceStorage $storage */
        $storage = $this->getStorage();
        list($absoluteBasePath, $uploadFolder) = $this->initializeUploadFolder($storage);

        // filename and path
        $zipName = 'documents--' . date('Ymd-Hi') . '.zip';
        $zipFile = $absoluteBasePath . $uploadFolder->getIdentifier() . $zipName;
        $zipFlag = false;

        $createFolder = true; // collection of different users - create folder structure

        $proposals = $this->proposalRepository->findByUids($records);
        $zip = null;
        foreach ($proposals as $proposal) {
            list($zip, $zipFlag) = $this->addAttachmentsToZip($proposal, $zip, $zipFlag, $zipFile, $createFolder, $absoluteBasePath);
        }
        if ($zip) {
            $zip->close();
            $this->sendZip($zipFile);
        }
    }

    protected function downloadPdfs($records): void
    {
        $proposals = $this->proposalRepository->findByUids($records);
        $storage = $this->resourceFactory->getStorageObjectFromCombinedIdentifier($this->settings['uploadFolder']);
        $configuration = $storage->getConfiguration();
        if (!empty($configuration['pathType']) && $configuration['pathType'] === 'relative') {
            $relativeBasePath = $configuration['basePath'];
            $absoluteBasePath = rtrim(Environment::getPublicPath() . '/' . $relativeBasePath, '/');
        } else {
            $absoluteBasePath = rtrim($configuration['basePath'], '/');
        }
        $uploadFolder = self::provideTargetFolder($storage->getRootLevelFolder(), '_temp_');
        self::provideFolderInitialization($uploadFolder);
        // filename and path
        $zipName = 'pdf-oap--' . date('Ymd-Hi') . '.zip';
        $zipPath = $absoluteBasePath . $uploadFolder->getIdentifier();
        $zipFile = $zipPath . $zipName;
        $zipFlag = false;

        foreach ($proposals as $proposal) {
            $filterDemand['source'] = self::COMMENT_SOURCE_EDIT;
            $comments = $this->commentRepository->findDemanded($proposal, $filterDemand);
            $answersMap = $this->createAnswersMap($proposal);
            $this->flattenAnswers($proposal);
            $itemAnswerMap = $this->buildItemAnswerMap($proposal);
            /** @var MetaInformation $metaInfo */
            $metaInfo = new MetaInformation($proposal->getMetaInformation());
            // create GroupsCounterArray
            $groupsCounter = $this->groupsCounterMetaInfo($proposal, $metaInfo);
            $pdfArguments = [
                'generatedDate' => date('d.m.Y'),
                'generatedTime' => date('H:i'),
                'proposal' => $proposal,
                'commentsAtProposal' => $comments,
                'itemTypes' => $this->getConstants()['TYPE'],
                'answers' => $this->answers,
                'answersMap' => $answersMap,
                'itemAnswerMap' => $itemAnswerMap,
                'groupsCounter' => $groupsCounter,
                'settings' => $this->settings,
            ];

            $signature = $this->buildSignature($proposal);
            if ($signature !== '') {
                $signature = $signature . '--';
            }
            $fileName = $signature . $proposal->getTitle();
            $fileExt = '.pdf';

            if (count($proposals) == 1) {
                $pdfArguments['destination'] = 'download';
                $pdfArguments['filepath'] = $storage->sanitizeFileName($fileName . '--' . date('Ymd-Hi') . $fileExt);
                $this->renderPdfView(self::EXPORT_TEMPLATE_PATH_PDF, $pdfArguments);
            } else {
                $pdfFile = $zipPath . $storage->sanitizeFileName($fileName . $fileExt);
                $pdfArguments['destination'] = 'string';
                $pdfArguments['filePath'] = $storage->sanitizeFileName($fileName . $fileExt);

                $pdfStr = $this->renderPdfView(self::EXPORT_TEMPLATE_PATH_PDF, $pdfArguments);
                file_put_contents($pdfFile, $pdfStr);

                if (!$zipFlag) {
                    $zip = new \ZipArchive();
                    if (file_exists($zipFile)) {
                        unlink($zipFile);
                    }
                    $openResult = $zip->open($zipFile, \ZipArchive::CREATE);

                    // todo error handling
                    if (!$openResult) {
                        switch ($openResult) {
                            case \ZipArchive::ER_EXISTS:
                            case \ZipArchive::ER_INCONS:
                            case \ZipArchive::ER_INVAL:
                            case \ZipArchive::ER_MEMORY:
                            case \ZipArchive::ER_NOENT:
                            case \ZipArchive::ER_NOZIP:
                            case \ZipArchive::ER_OPEN:
                            case \ZipArchive::ER_SEEK:
                                DebuggerUtility::var_dump($openResult, (string)__LINE__);
                                DebuggerUtility::var_dump($zip, (string)__LINE__);
                                die();
                        }
                    }
                    $zipFlag = true;
                }
                if (file_exists($pdfFile)) {
                    $zip->addFile($pdfFile, $storage->sanitizeFileName($fileName . $fileExt));
                    $pdfArr[] = $pdfFile;
                }
            }
        }
        if ($zip) {
            $zip->close();
            if (headers_sent()) {
                echo 'HTTP header already sent';
                die();
            }
            header('Content-Type: application/zip');
            header('Content-Transfer-Encoding: Binary');
            header('Content-Length: ' . filesize($zipFile));
            header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
            readfile($zipFile);

            unlink($zipFile);

            if (is_array($pdfArr)) {
                foreach ($pdfArr as $pdfFilePath) {
                    unlink($pdfFilePath);
                }
            }
            exit;
        }
    }

    /**
     * @param $records
     * @throws InvalidQueryException
     */
    protected function exportCsv($records): void
    {
        $proposals = $this->proposalRepository->findByUids($records);
        // how could we handle multiple calls in the export funktion
        $pageNo = 1;
        $columnNo = 0;
        $head = [];
        $head[$columnNo++] = 'ID (intern)';
        $head[$columnNo++] = 'Signature (intern)';
        $head[$columnNo++] = 'State (intern)';

        // calculate group repeats
        $groupData = [];
        /** @var Proposal $proposal */
        foreach ($proposals as $proposal) {
            $metaInfo = new MetaInformation($proposal->getMetaInformation());
            $groupCounter = $metaInfo->getGroupsCounter();
            foreach ($groupCounter as $groupUid => $groupItem) {
                if (!$groupData[$groupUid]) {
                    $groupData[$groupUid]['min'] = 1;
                    $groupData[$groupUid]['max'] = $groupItem;
                }
                if ($groupData[$groupUid]['min'] > $groupItem) {
                    $groupData[$groupUid]['min'] = $groupItem;
                }
                if ($groupData[$groupUid]['max'] < $groupItem) {
                    $groupData[$groupUid]['max'] = $groupItem;
                }
            }
        }

        /** @var FormPage $page */
        foreach ($proposals[0]->getCall()->getFormPages() as $page) {
            /** @var FormGroup $group */
            foreach ($page->getItemGroups() as $group) {
                for ($i = $groupData[$group->getUid()]['min'] - 1; $i < $groupData[$group->getUid()]['max']; $i++) {
                    /** @var FormItem $item */
                    foreach ($group->getItems() as $item) {
                        $key = $group->getUid() . '--' . $i . '--' . $item->getUid();
                        $columns[$key] = $columnNo;
                        $head[$columnNo] = $item->getQuestion();
                        if ($groupData[$group->getUid()]['min'] !== $groupData[$group->getUid()]['max']) {
                            $head[$columnNo] .= ' #' . (integer)($i + 1);
                        }
                        $columnNo++;
                        if ($item->isAdditionalValue()) {
                            $head[$columnNo] = 'additionalAnswer';
                            $columnNo++;
                        }
                    }
                }
            }
            $pageNo++;
        }

        // translations of state const for export
        $states = $this->createStatesArray();

        $export = [];
        /** @var Proposal $proposal */
        foreach ($proposals as $proposal) {
            $proposalUid = $proposal->getUid();
            $export[$proposalUid] = [];
            $export[$proposalUid][0] = $proposalUid;
            $export[$proposalUid][1] = $this->buildSignature($proposal);
            $export[$proposalUid][2] = $states[$proposal->getState()];

            /** @var Answer $answer */
            foreach ($proposal->getAnswers() as $answer) {
                $value = $answer->getValue();
                switch ($answer->getItem()->getType()) {
                    case self::TYPE_SELECT_MULTIPLE:
                    case self::TYPE_CHECKBOX:
                        if ($answer->getValue() !== '' and is_array(json_decode($answer->getValue(), true))) {
                            $value = implode(', ', json_decode($answer->getValue(), true));
                        }
                        break;
                    case self::TYPE_UPLOAD:
                        if ($answer->getValue() !== '') {
                            $files = explode(',', $answer->getValue());
                            $values = [];
                            foreach ($files as $file) {
                                $fileObj = $this->resourceFactory->getFileObject($file);
                                $values[] = $fileObj->getName();
                            }
                            $value = implode(', ', $values);
                        }
                }
                $key = $answer->getModel()->getUid() . '--' . $answer->getElementCounter() . '--' . $answer->getItem()->getUid();
                $export[$proposalUid][$columns[$key]] = $value;
                if ($answer->getItem()->isAdditionalValue()) {
                    $export[$proposalUid][$columns[$key] + 1] = $answer->getAdditionalValue();
                }
            }
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=proposal-export--' . date('Ymd-Hi') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, $head);
        foreach ($export as $proposalData) {
            $lineData = [];
            foreach ($head as $columns => $item) {
                if (!$proposalData[$columns]) {
                    $lineData[$columns] = '';
                } else {
                    $lineData[$columns] = $proposalData[$columns];
                }
            }
            fputcsv($output, $lineData);
        }
        die();
    }

    /**
     * Returns the language code of the proposal feLanguageUid
     *
     * @param int $languageId
     * @return string The 2 letter language code
     */
    protected function getProposalFrontendLanguageCode($languageId): String
    {
        $proposalFeLanguage = $GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getLanguageById($languageId);
        return $proposalFeLanguage->getTwoLetterIsoCode();
    }
}
