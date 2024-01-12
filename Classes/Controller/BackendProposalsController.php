<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Domain\Model\Answer;
use OpenOAP\OpenOap\Domain\Model\Call;
use OpenOAP\OpenOap\Domain\Model\Comment;
use OpenOAP\OpenOap\Domain\Model\FormGroup;
use OpenOAP\OpenOap\Domain\Model\FormItem;
use OpenOAP\OpenOap\Domain\Model\FormPage;
use OpenOAP\OpenOap\Domain\Model\GroupTitle;
use OpenOAP\OpenOap\Domain\Model\MetaInformation;
use OpenOAP\OpenOap\Domain\Model\Proposal;

use PhpOffice\PhpWord\Exception\Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
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
     * overview page
     */
    public function showOverviewCallsAction(array $filter = [], array $selection = [], int $currentPage = 1)
    {
        $accessPids = [];
        $grantedToAll = false;

        $isAdmin = $GLOBALS['BE_USER']->isAdmin();

        if (!$isAdmin) {
            $userTsConfig = $GLOBALS['BE_USER']->getTSConfig();
            $accessGroups = $userTsConfig['oap.']['access.'] ?? null;
            if ($accessGroups) {
                foreach ($accessGroups as $accessGroup) {
                    $listOfPids = explode(',', $accessGroup);
                    array_push($accessPids, ...$listOfPids);
                }
            }
        } else {
            $grantedToAll = true;
        }
        // get all calls
        $callPid = (int)$this->settings['callPid'];
        $callsQueryResult = $this->callRepository->findAllByPid($callPid);

        $calls = [];
        $counts = [];
        /**@var \TYPO3\CMS\Core\Domain\Repository\PageRepository $pageRepository */
        //        $pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class);

        $counts = [];
        /** @var Call $call */
        foreach ($callsQueryResult as $call) {
            if ($call->getHidden()) {
                continue;
            }

            try {
                $rootlineUids = $grantedToAll
                    ? []
                    : array_column(
                        GeneralUtility::makeInstance(RootlineUtility::class, $call->getProposalPid())->get(),
                        'uid');
            } catch (\Throwable) {
                $rootlineUids = [];
            }

            if ($grantedToAll || array_intersect($rootlineUids, $accessPids)) {
                $countByState = $this->proposalRepository->countByState($call, self::PROPOSAL_SUBMITTED);
                $calls[$call->getUid()] = $call;
                $counts[self::PROPOSAL_SUBMITTED][$call->getUid()] = $countByState;
                $countByState = $this->proposalRepository->countByState($call, self::PROPOSAL_IN_PROGRESS);
                $counts[self::PROPOSAL_IN_PROGRESS][$call->getUid()] = $countByState;
            }
        }
        $paginator = $this->createPaginator($calls, $currentPage);
        $this->view->assignMultiple([
            'paginator' => $paginator,
            'currentPage' => $currentPage,
            'actionName' => $this->actionMethodName,
            'countOfItems' => count($calls),
            'counts' => $counts,
            'states' => $this->getConstants()['PROPOSAL'],
        ]);
    }

    /**
     * @param Call $call
     * @return array
     */
    public function getItemsOfCall(Call $call): array
    {
        $allItems = [];
        /** @var FormPage $formPage */
        foreach ($call->getFormPages() as $formPage) {
            /** @var FormGroup $itemGroup */
            foreach ($formPage->getItemGroups() as $itemGroup) {
                /** @var formItem $item */
                foreach ($itemGroup->getItems() as $item) {
                    $allItems[$item->getUid()] = $item;
                }
            }
        }
        return $allItems;
    }

    /**
     * @throws InvalidQueryException
     */
    public function listProposalsAction(Call $call, array $filter = [], array $selection = [], int $currentPage = 1, int $itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE): ResponseInterface
    {
        // clear function call
        if (($filter['todo'] ?? null) == 'clear') {
            $filter = [];
        }

        if (empty($filter['itemsPerPage'])) {
            $filter['itemsPerPage'] = $itemsPerPage;
        }
        $filter['call'] = $call->getUid();

        // update state of proposal selection
        if (count($selection) > 0 && is_array($selection['records'])) {
            if ($selection['todo'] == 'submit-export') {
                switch ($selection['export']) {
                    case 'csv':
                        $this->exportCsv($selection['records']);
                        break;
                    case 'downloadDocuments':
                        /** @var Folder $folder */
                        $folder = $this->createFolder();
                        $this->createPdfs($selection['records']);
                        $this->createWordFiles($selection['records']);
                        // DebuggerUtility::var_dump($pdfFolder,(string)__LINE__);die();
                        $this->downloadAttachments($selection['records'], $folder);
                        break;
                    case 'pdf':
                        //                        $pdfFolder = $this->createPdfs($selection['records']);
                        $this->downloadPdfs($selection['records']);
                        break;
                }
            }

            if ($selection['todo'] == 'submit-state' && (int)$selection['state'] > 0) {
                foreach ($selection['records'] as $record) {
                    $proposal = $this->proposalRepository->findByUid((int)$record);
                    // to avoid setting a state again check agaist the existing state
                    if ($proposal != null and $proposal->getState() !== (int)$selection['state']) {
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

        $allItems = $this->getItemsOfCall($call);
        $states['filter'] = $this->createStatesArray('selected');
        $states['task'] = $this->createStatesArray('task');

        $filterSelects = [];
        /** @var FormItem $filterItem */
        foreach ($allItems as $filterItem) {
            if ($filterItem->getEnabledFilter()) {
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
        }

        $sorting = [];
        $sortRev = null;
        $sortField = null;

        if ($this->request->hasArgument('sortField')) {
            $sortField = htmlspecialchars($this->request->getArgument('sortField'));
            $sortRev = (int)($this->request->getArgument('sortRev'));
            if ($sortRev == 1) {
                $sorting = ['field' => $sortField, 'direction' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING];
            } else {
                $sorting = ['field' => $sortField, 'direction' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING];
            }
        }
        $allItems = $this->proposalRepository->findDemanded($call->getProposalPid(), self::STATE_ACCESS_MIN_FILTER, $filter, $sorting);
        $pagination = $this->createPaginator($allItems, $currentPage, (int)$filter['itemsPerPage']);

        $this->view->assignMultiple([
            'countOfItems' => count($allItems),
            'filter' => $filter,
            'states' => $states,
            'filterSelects' => $filterSelects,
            'exportFormats' => ['csv' => 'CSV', 'downloadDocuments' => 'Documents', 'pdf' => 'PDF'],
            'stateReopenedValue' => self::PROPOSAL_RE_OPENED,
            'actionName' => $this->actionMethodName,
            'paginator' => $pagination['array'],
            'pagination' => $pagination['pagination'],
            'sorted' => ['sortField' => $sortField, 'sortRev' => $sortRev],
            'call' => $call,
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

        $this->flattenItems($proposal);
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

        $states['task'] = $this->createStatesArray('task');

        $callLogo = $this->getCallLogo($proposal);

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
                'callLogo' => $callLogo,
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
            'call' => $proposal->getCall(),
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
            $selectedMailAction = null;
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
            'call' => $proposal->getCall(),
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

    /**
     * @param $records
     * @param Folder $pdfFolder
     * @throws InvalidQueryException
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    protected function downloadAttachments($records, $pdfFolder): void
    {
        /** @var ResourceStorage $storage */
        $storage = $this->getStorage();
        list($absoluteBasePath, $uploadFolder) = $this->initializeUploadFolder($storage);

        // filename and path
        $datePart = '';
        if ($this->settings['zipFileDateFormat'] !== '') {
            $datePart = date($this->settings['zipFileDateFormat']);
        }
        $zipName = $this->settings['zipFilePrefix'] . $datePart . '.zip';
        $zipFile = $absoluteBasePath . $uploadFolder->getIdentifier() . $zipName;

        $createFolder = true; // collection of different users - create folder structure

        $proposals = $this->proposalRepository->findByUids($records);
        /** @var \ZipArchive $zip */
        $zip = null;
        foreach ($proposals as $proposal) {
            $zip = $this->addAttachmentsToZip($proposal, $zip, $zipFile, $createFolder, $absoluteBasePath, $pdfFolder);
        }
        if ($zip) {
            $zip->close();
            // Simulate recursive deletion of folders and files
            // because some user groups do not have the permission to do so.
            foreach ($pdfFolder->getFiles() as $fileToDelete) {
                $fileToDelete->delete();
            }
            $pdfFolder->delete(false);
            $this->sendZip($zipFile);
            unlink($zipFile);
        }
    }

    /**
     * @return Folder
     */
    protected function createFolder(): Folder
    {
        $storage = $this->resourceFactory->getStorageObjectFromCombinedIdentifier($this->settings['uploadFolder']);
        $tempFolder = time() . '-' . rand(10000, 99999);

        //        DebuggerUtility::var_dump($tempFolder);die();
        $baseUploadFolder = self::provideTargetFolder($storage->getRootLevelFolder(), '_temp_');
        self::provideFolderInitialization($baseUploadFolder);
        $folder = $baseUploadFolder->createFolder($tempFolder);
        self::provideFolderInitialization($folder);
        return $folder;
    }

    /**
     * @param $records
     * @throws InvalidQueryException
     */
    protected function createPdfs($records): void
    {
        $storage = $this->resourceFactory->getStorageObjectFromCombinedIdentifier($this->settings['uploadFolder']);
        //        $absoluteBasePath = $this->getBasePath($storage);

        $proposals = $this->proposalRepository->findByUids($records);

        foreach ($proposals as $proposal) {
            $filterDemand['source'] = self::COMMENT_SOURCE_EDIT;
            $comments = $this->commentRepository->findDemanded($proposal, $filterDemand);
            $answersMap = $this->createAnswersMap($proposal);
            $this->flattenAnswers($proposal);
            $itemAnswerMap = $this->buildItemAnswerMap($proposal);
            /** @var MetaInformation $metaInfo */
            $metaInfo = new MetaInformation($proposal->getMetaInformation());
            $callLogo = $this->getCallLogo($proposal);
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
                'callLogo' => $callLogo,
            ];

            //            $fileName = $proposal->getUid();
            //            $fileExt = '.pdf';

            $pdfFile = $this->createFileName($proposal, '.pdf');
            //            $savePath = $absoluteBasePath . $folder->getIdentifier();
            //            DebuggerUtility::var_dump($pdfFile,(string)__LINE__);
            //            die();
            //            $pdfFile = $savePath . $storage->sanitizeFileName($fileName . $fileExt);
            if (!file_exists($pdfFile)) {
                $pdfArguments['destination'] = 'string';
                $pdfArguments['filePath'] = $pdfFile; // $storage->sanitizeFileName($fileName . $fileExt);

                $pdfStr = $this->renderPdfView(self::EXPORT_TEMPLATE_PATH_PDF, $pdfArguments);
                file_put_contents($pdfFile, $pdfStr);
            }
        }
    }

    /**
     * @param $records
     * @throws InvalidQueryException
     * @throws Exception
     */
    protected function createWordFiles($records): void
    {
        $proposals = $this->proposalRepository->findByUids($records);

        /** @var Proposal $proposal */
        foreach ($proposals as $proposal) {
            // check if file alwarys there

            foreach ([self::WORD_EXPORT_COMPACT, self::WORD_EXPORT_DEFAULT] as $typeOfWord) {
                $wordFileName = $this->createFileName($proposal, '.docx', $typeOfWord);
                if (!file_exists($wordFileName)) {
                    // create new word file
                    $contentWordFileName = $this->createWord($proposal, $wordFileName, $typeOfWord);
                }
            }
        }
    }

    protected function downloadPdfs($records): void
    {
        $storage = $this->resourceFactory->getStorageObjectFromCombinedIdentifier($this->settings['uploadFolder']);
        $absoluteBasePath = $this->getBasePath($storage);

        $proposals = $this->proposalRepository->findByUids($records);

        $uploadFolder = self::provideTargetFolder($storage->getRootLevelFolder(), '_temp_');
        self::provideFolderInitialization($uploadFolder);
        // filename and path
        $zipName = 'pdf-oap--' . date('Ymd-Hi') . '.zip';
        $zipPath = $absoluteBasePath . $uploadFolder->getIdentifier();
        $zipFile = $zipPath . $zipName;
        $zipFlag = false;

        if (count($proposals) == 1) {
            $mode = self::PDF_MODE_DOWNLOAD;
        } else {
            $mode = self::PDF_MODE_FILE;
        }

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
            $callLogo = $this->getCallLogo($proposal);

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
                'callLogo' => $callLogo,
            ];

            $signature = $this->buildSignature($proposal);
            if ($signature !== '') {
                $signature = $signature . '--';
            }
            $fileName = $signature . $proposal->getTitle();
            $fileExt = '.pdf';

            if ($mode == self::PDF_MODE_DOWNLOAD) {
                $pdfArguments['destination'] = 'download';
                $pdfArguments['filepath'] = $storage->sanitizeFileName($fileName . '--' . date('Ymd-Hi') . $fileExt);
                $this->renderPdfView(self::EXPORT_TEMPLATE_PATH_PDF, $pdfArguments);
                // here is the end my friend - After rendering, the pdfviewhelper takes over the transfer to the browser for downloading
            } else {
                $pdfFile = $this->createFileName($proposal, '.pdf');

                if (!file_exists($pdfFile)) {
                    $pdfArguments['destination'] = 'string';
                    $pdfArguments['filePath'] = $storage->sanitizeFileName($pdfFile);

                    $pdfStr = $this->renderPdfView(self::EXPORT_TEMPLATE_PATH_PDF, $pdfArguments);
                    file_put_contents($pdfFile, $pdfStr);
                }
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
                    $zip->addFile($pdfFile, basename($pdfFile));
                    //                    $zip->addFile($pdfFile, $storage->sanitizeFileName($fileName . $fileExt));
                    //                    $pdfArr[] = $pdfFile;
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

            //            if (is_array($pdfArr)) {
            //                foreach ($pdfArr as $pdfFilePath) {
            //                    unlink($pdfFilePath);
            //                }
            //            }
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
        $l0headRow = 0;
        $l1headRow = 1;
        $itemHeadRow = 2;
        $columnNo = 0;

        $head = [];
        $head[$l0headRow] = [];
        $head[$l1headRow] = [];
        $head[$itemHeadRow] = [];

        $head[$itemHeadRow][$columnNo++] = 'ID (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Signature (intern)';
        $head[$itemHeadRow][$columnNo++] = 'State (intern)';

        // calculate group repeats
        $groupsCounter = [];
        /** @var Proposal $proposal */
        foreach ($proposals as $proposal) {
            $metaInfo = new MetaInformation($proposal->getMetaInformation());
            $groupData = $metaInfo->getGroupsCounter();
            $groupsCounter = $metaInfo->countGroups($groupsCounter);
        }

        /** @var FormPage $page */
        foreach ($proposals[0]->getCall()->getFormPages() as $page) {
            /** @var FormGroup $groupL0 */
            foreach ($page->getItemGroups() as $groupL0) {
                // todo build groups row - title of groups L0
                $maxL0 = $groupsCounter[$groupL0->getUid()]['max'];
                for ($indexL0 = 0; $indexL0 < $maxL0; $indexL0++) {
                    $head[$l0headRow][$columnNo] = $this->createGroupTitle($groupL0, $maxL0, $indexL0);

                    // todo build groups row - title of groups L0
                    // $head[$L0headRow][$columnNo] = $group->getTitle()

                    // no item in meta groups
                    if ($groupL0->getType() == self::GROUPTYPE_META) {
                        foreach ($groupL0->getItemGroups() as $groupIndexL1 => $groupL1) {
                            $maxL1 = $groupsCounter[$groupL0->getUid()]['instances'][$indexL0][$groupL1->getUid()]['max'];
                            for ($indexL1 = 0; $indexL1 < $maxL1; $indexL1++) {
                                $head[$l1headRow][$columnNo] = $this->createGroupTitle($groupL1, $maxL1, $indexL1);

                                /** @var FormItem $item */
                                foreach ($groupL1->getItems() as $item) {
                                    $key = $groupL1->getUid() . '--' . $indexL0 . '--' . $indexL1 . '--' . $item->getUid();
                                    $columns[$key] = $columnNo;
                                    $head[$itemHeadRow][$columnNo] = $item->getQuestion();
                                    $columnNo++;
                                    if ($item->isAdditionalValue()) {
                                        $head[$itemHeadRow][$columnNo] = 'additionalAnswer';
                                        $columnNo++;
                                    }
                                    if ($item->getType() == self::TYPE_DATE2) {
                                        $head[$itemHeadRow][$columnNo] = 'Date until';
                                        $columnNo++;
                                    }
                                }
                            }
                        }
                    } else {
                        /** @var FormItem $item */
                        foreach ($groupL0->getItems() as $item) {
                            $key = $groupL0->getUid() . '--' . '0' . '--' . $indexL0 . '--' . $item->getUid();
                            $columns[$key] = $columnNo;
                            $head[$itemHeadRow][$columnNo] = $item->getQuestion();
                            $columnNo++;
                            if ($item->isAdditionalValue()) {
                                $head[$itemHeadRow][$columnNo] = 'additionalAnswer';
                                $columnNo++;
                            }
                            if ($item->getType() == self::TYPE_DATE2) {
                                $head[$itemHeadRow][$columnNo] = 'Date until';
                                $columnNo++;
                            }
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
                if (!$answer->getItem()) {
                    continue;
                }
                switch ($answer->getItem()->getType()) {
                    case self::TYPE_SELECT_SINGLE:
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
                                try {
                                    $fileObj = $this->resourceFactory->getFileObject($file);
                                } catch(\TYPO3\CMS\Core\Resource\Exception $e) {
                                    //                                    echo 'Exception abgefangen: ', $e->getMessage();
                                    //                                    die();
                                    $fileObj = null;
                                }
                                if ($fileObj) {
                                    $values[] = $fileObj->getName();
                                } else {
                                    $values[] = '****missing FILE? ' . $file;
                                }
                            }
                            $value = implode(', ', $values);
                        }
                }
                $key = $answer->getModel()->getUid() . '--' . $answer->getGroupCounter0() . '--' . $answer->getGroupCounter1() . '--' . $answer->getItem()->getUid();
                $export[$proposalUid][$columns[$key]] = $value;
                if ($answer->getItem()->isAdditionalValue()) {
                    $export[$proposalUid][$columns[$key] + 1] = $answer->getAdditionalValue();
                }
                if ($answer->getItem()->getType() == self::TYPE_DATE2) {
                    $export[$proposalUid][$columns[$key] + 1] = $answer->getAdditionalValue();
                }
            }
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=proposal-export--' . date('Ymd-Hi') . '.csv');

        $output = fopen('php://output', 'w');
        foreach ([$l0headRow, $l1headRow] as $headRowForGroup) {
            $lineData = [];
            foreach ($head[$itemHeadRow] as $columns => $item) {
                if (!isset($head[$headRowForGroup][$columns])) {
                    $lineData[$columns] = '';
                } else {
                    $lineData[$columns] = $head[$headRowForGroup][$columns];
                }
            }
            fputcsv($output, $lineData);
        }

        fputcsv($output, $head[$itemHeadRow]);
        foreach ($export as $proposalData) {
            $lineData = [];
            foreach ($head[$itemHeadRow] as $columns => $item) {
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
     * @param FormGroup $group
     * @param int $repeatableMax
     * @param int $index
     * @return string
     */
    public function createGroupTitle(FormGroup $group, int $repeatableMax, int $index): string
    {
        $postfix = '';
        if ($repeatableMax > 1) {
            $countGroupTitles = count($group->getGroupTitle());
            if ($countGroupTitles > 0 and $index <= $countGroupTitles) {
                /** @var GroupTitle $groupTitle */
                $groupTitle = $group->getGroupTitle()[$index];
                $postfix = ' - ' . $groupTitle->getTitle();
            } else {
                $postfix = ' #' . (int)($index + 1);
            }
        }
        return $group->getTitle() . $postfix;
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
