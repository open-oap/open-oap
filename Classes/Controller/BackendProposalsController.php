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

use OpenOAP\OpenOap\Service\ExcelExportService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use OpenOAP\OpenOap\Domain\Repository\SupporterRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\Exception\Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
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
    const SESSION_TAG_OAP_PROPOSAL_BACKEND = 'oapProposalBackend';


    /**
     * overview page
     */
    public function showOverviewProposalsAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'actionName' => $this->actionMethodName,
        ]);

        return $this->htmlResponse();
    }

    /**
     * overview page
     */
    public function showOverviewCallsAction(array $filter = [], array $selection = [], int $currentPage = 1): ResponseInterface
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

        return $this->htmlResponse();
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
    public function listProposalsAction(?Call $call = null, array $filter = [], array $selection = [], int $currentPage = 1, int $itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE): ResponseInterface
    {
        $userSession = $GLOBALS['BE_USER']->getSession();
        $sessionStoredData = $userSession->get(self::SESSION_TAG_OAP_PROPOSAL_BACKEND);

        if(is_null($call)) {
            return $this->redirect('showOverviewCalls' , 'BackendProposals');
        }

        // clear function call
        if (($filter['todo'] ?? null) == 'clear') {
            $filter = [];
        }

        if (empty($filter['itemsPerPage'])) {
            $filter['itemsPerPage'] = $itemsPerPage;
        }
        $filter['call'] = $call->getUid();

        $allThresholds = [];
        if ($call->getAssessmentThreshold() > 0) {
            // build filter even for AssessmentThreshold
            try {
                $allThresholds = $this->proposalRepository->fetchAssessmentThresholdsByCall($call);
            } catch (DBALException $e) {
            } catch (\Doctrine\DBAL\Driver\Exception $e) {
            }
        }

        // update state of proposal selection
        if (count($selection) > 0 && is_array($selection['records'])) {
            if ($selection['todo'] == 'submit-assessment') {
                // forwarding to the assessment detail page with the list of ids from selection
                if ($selection['records'][0]) {
                    $proposal = $this->proposalRepository->findByUid($selection['records'][0]);
                    return (new ForwardResponse('showProposal'))->withArguments(['proposal' => $proposal, 'selection' => $selection]);
                }
            }
            if ($selection['todo'] == 'submit-export') {
                switch ($selection['export']) {
                    case 'csv':
                        $this->exportCsv($selection['records']);
                        break;
                    case 'excel':
                        $this->exportExcel($selection['records']);
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
                $updateCount = 0;

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
                        $updateCount++;
                    }
                }

                if ($updateCount) {
                    $this->addFlashMessage($updateCount . 'x ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:message.state_updated'), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK);
                }

                if (isset($proposalsArr) && is_array($proposalsArr)) {
                    if (count($proposalsArr) > 1) {
                        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:message.multiple_selected'), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING);
                    }

                    return $this->redirect('customizeStatusMail', 'BackendProposals', null, ['proposals' => $proposalsArr, 'selectedState' => (int)$selection['state']]);
                }
            }
        }

        $allItems = $this->getItemsOfCall($call);
        $states['filter'] = $this->createStatesArray('selected');
        $states['task'] = $this->createStatesArray('task');
        $ratings['filter'] = [];

        $importedAction['filter'] = $this->proposalRepository->fetchImportedActionsByCall($call);
        $importedFilterShow = false;
        if (count($importedAction['filter']) > 0) {
            $importedFilterShow = true;
        }

        $assessmentFilterShow = false;
        foreach ($allThresholds as $thresholdItem) {
            $assessmentFilterShow = true;
            $thresholdItemLabel = $thresholdItem;
            if ($thresholdItem == '') {
                $thresholdItemLabel = 'not rated yet**';
            }
            $ratings['filter'][] = ['key' => $thresholdItem,'label' => $thresholdItemLabel];
        }
        $ratings['filter'][] = ['key' => "threshold",'label' => 'threshold ** ('.$call->getAssessmentThreshold().')'];
        $filterSelects = $this->applyFilter($allItems);
        $sorting = $this->buildSorting();
        $allItems = $this->proposalRepository->findDemanded($call->getProposalPid(), self::STATE_ACCESS_MIN_FILTER, $call->getAssessmentThreshold(), $filter, $sorting);
        $pagination = $this->createPaginator($allItems, $currentPage, (int) $filter['itemsPerPage']);

        $this->view->assignMultiple([
            'proposalStates' => $this->getConstants()['PROPOSAL'],
            'countOfItems' => count($allItems),
            'filter' => $filter,
            'states' => $states,
            'ratings' => $ratings,
            'importedAction' => $importedAction,
            'filterSelects' => $filterSelects,
            'exportFormats' => ['csv' => 'CSV', 'excel' => 'Excel', 'downloadDocuments' => 'Documents', 'pdf' => 'PDF'],
            'stateReopenedValue' => self::PROPOSAL_RE_OPENED,
            'actionName' => $this->actionMethodName,
            'paginator' => $pagination['array'],
            'pagination' => $pagination['pagination'],
            'sorted' => ['sortField' => $sorting['field'], 'sortRev' => $sorting['revert']],
            'call' => $call,
            'paginatorPageDirectLink' => true,
            'assessmentFilterShow' => $assessmentFilterShow,
            'importedFilterShow' => $importedFilterShow,
        ]);

        $dataCollection = ['filter' => $filter, 'sorting' => $sorting, 'selection' => $selection, 'currentPage' => $currentPage, 'itemsPerPage' => $itemsPerPage];
        $userSession->set(self::SESSION_TAG_OAP_PROPOSAL_BACKEND,$dataCollection);
        return $this->htmlResponse();
    }

    /**
     * @param Call $call
     * @param array $upload
     * @return ResponseInterface
     */
    public function uploadAssessmentExcelAction(Call $call, array $upload = []): ResponseInterface
    {
        $call = $this->importXlsxData($call, $upload);
        return (new ForwardResponse('listProposals'))->withArguments(['call' => $call]);
    }

    /**
     * @param Proposal $proposal
     * @return ResponseInterface
     */
    public function showProposalAction(Proposal $proposal, array $filter = [], array $selection = [], int $currentPage = 0): ResponseInterface
    {
        $userSession = $GLOBALS['BE_USER']->getSession();
        $sessionStoredData = $userSession->get(self::SESSION_TAG_OAP_PROPOSAL_BACKEND);

        $updateProposal = false;
        $call = $proposal->getCall();
        $filter = !empty($sessionStoredData['filter']) ? $sessionStoredData['filter'] : $filter;

        $states['filter'] = $this->createStatesArray('selected', $call);
        $states['task'] = $this->createStatesArray('task', $call);

        $sorting = !empty($sessionStoredData['sorting']) ? $sessionStoredData['sorting'] : ['direction' => '', 'field' => '', 'revert' => false];;

        $allItems = $this->proposalRepository->findDemanded($call->getProposalPid(), self::STATE_ACCESS_MIN_FILTER, $call->getAssessmentThreshold(), $filter, $sorting);
        // change the proposal to the one from the paginator
        // $currentPage
        if ($this->request->hasArgument('download')) {
            // in case of download call... use the given proposal
            $proposalId = $proposal->getUid();
        } else {
            if ($currentPage == 0) {
                // change current page to the selected one
                foreach ($allItems as $pageCounter => $proposalItem) {
                    if ($proposalItem['uid'] == $proposal->getUid()) {
                        $currentPage = $pageCounter + 1;
                    }
                }
            } else {
                $proposalId = $allItems[$currentPage - 1]['uid'];
                $proposal = $this->proposalRepository->findByUid((integer) $proposalId);
            }

            $pagination = $this->createPaginator($allItems, $currentPage, 1,self::PAGINATOR_ITEMS);
        }


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
                    $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:message.state_updated'), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK);
                    $this->proposalRepository->update($proposal);

                    if ($selectedState == self::PROPOSAL_ACCEPTED || $selectedState == self::PROPOSAL_DECLINED) {
                        // Send status mail
                        return $this->redirect('customizeStatusMail', 'BackendProposals', null, ['proposals' => [$proposal], 'selectedState' => $selectedState]);
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

        // create assessment data
        $assessmentItems = $proposal->getCall()->getAssessmentItems();

        $assessmentMap = [];
        $assessmentValues = [];
        /** @var Answer $assessmentAnswer */

        if ($this->request->hasArgument('task')) {
            if ($this->request->getArgument('task') == 'assessment') {
                // catch assessment data
                $allAssessmentAnswers = $proposal->getAssessmentAnswers();
                // DebuggerUtility::var_dump($allAssessmentAnswers,(string)__LINE__);
                $proposal->setAssessmentValue('0');

                $beUserUid = $GLOBALS['BE_USER']->user['uid'];
                /** @var \TYPO3\CMS\Beuser\Domain\Model\BackendUser $beUser */
                $beUser = GeneralUtility::makeInstance(BackendUserRepository::class)->findByUid($beUserUid);

                $proposal->setReviewer($beUser);
                $now = new \DateTime();
                $proposal->setReviewTime($now);
                foreach ($allAssessmentAnswers as $assessmentAnswer) {
                    if (in_array($assessmentAnswer->getItem()->getType(),[self::TYPE_CHECKBOX, self::TYPE_RADIOBUTTON, self::TYPE_SELECT_SINGLE, self::TYPE_SELECT_MULTIPLE])) {
                        if (count($assessmentAnswer->getArrayValue())) {
                            $assessmentAnswer->setValue(json_encode($assessmentAnswer->getArrayValue(), JSON_FORCE_OBJECT));
                            foreach ($assessmentAnswer->getArrayValue() as $value) {
                                $newValueInt = (integer) $proposal->getAssessmentValue() + (integer) $value;
                                $proposal->setAssessmentValue((string) $newValueInt);
                            }
                        } else {
                            $newValueInt = (integer) $proposal->getAssessmentValue() + (integer) $assessmentAnswer->getValue();
                            $proposal->setAssessmentValue((string) $newValueInt);
                        }
                    }
                    // DebuggerUtility::var_dump($assessmentAnswer->getValue(),(string)__LINE__);
                    $this->answerRepository->update($assessmentAnswer);
                }
                // update proposal assessment data
            }
            $this->proposalRepository->update($proposal);
            $this->persistenceManager->persistAll();
        }
        if ($proposal->getAssessmentAnswers()) {

            foreach ($proposal->getAssessmentAnswers() as $assessmentAnswer) {
                if ($assessmentAnswer->getItem()) {
                    $assessmentValues[$assessmentAnswer->getItem()->getUid()] = $assessmentAnswer;
                    if (is_object(json_decode($assessmentAnswer->getValue()))) {
                        $assessmentAnswer->setArrayValue(json_decode($assessmentAnswer->getValue(), true));
                    }
                }
            }
        }

        $newAnswers = false;
        /** @var FormItem $assessmentItem */
        foreach ($assessmentItems as $assessmentItem) {
            $this->getOptionsToItemsMap($assessmentItem,$assessmentMap);

            if (!isset($assessmentValues[$assessmentItem->getUid()])) {
                $newAnswers = true;
                $newAssessmentAnswer = new Answer();
                $newAssessmentAnswer->setItem($assessmentItem);
                $newAssessmentAnswer->setPid((int)$this->settings['answersPoolId']);
                $newAssessmentAnswer->setValue('');
                $newAssessmentAnswer->setArrayValue([]);
                $this->answerRepository->add($newAssessmentAnswer);
                $proposal->addAssessmentAnswer($newAssessmentAnswer);
                $assessmentValues[$assessmentItem->getUid()] = $newAssessmentAnswer;
            }
        }
        if ($newAnswers) {
            $this->proposalRepository->update($proposal);
            $this->persistenceManager->persistAll();
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
            'paginator' => $pagination['array'],
            'paginationButtons' => $pagination['buttons'],
            'allItems' => $allItems,
            'pagination' => $pagination['pagination'],
            'paginationShow' => $pagination['show'],
            'filter' => $filter,
            'sorted' => ['sortField' => $sorting['field'], 'sortRev' => $sorting['revert']],
            'paginatorPageDirectLink' => false,
            'assessmentsMap' => $assessmentMap,
            'assessmentValues' => $assessmentValues,
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

        foreach ($proposals as $key => $proposalId) {
            /** @var Proposal $proposal */
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

            $proposalLangCode = $this->getProposalFrontendLanguageCode($proposal);
            $proposalList[$key]['maildata'] = [];
            $proposalList[$key]['maildata']['signature'] = $this->buildSignature($proposal);
            $proposalList[$key]['maildata']['siteName'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
            $proposalList[$key]['maildata']['langCode'] = $proposalLangCode;
            if ($supporter = $proposal->getCall()->getSupporter()) {
                if ($proposal->getFeLanguageUid() > 0)
                {
                    $translatedSupporter = $this->getTranslatedSupporter($proposal->getFeLanguageUid(), $supporter->getUid());
                    $unparsedDefaultMailtext = $selectedState == self::PROPOSAL_ACCEPTED
                        ? $translatedSupporter['event_proposal_accepted_mailtext'] ?? ''
                        : $translatedSupporter['event_proposal_declined_mailtext'] ?? '';
                }
                else {
                    $unparsedDefaultMailtext = $selectedState == self::PROPOSAL_ACCEPTED
                        ? $supporter->getEventProposalAcceptedMailtext()
                        : $supporter->getEventProposalDeclinedMailtext();
                }

            } else {
                $unparsedDefaultMailtext = 'Missing Email Text';
            }
            $proposalList[$key]['maildata']['mailtext'] = $this->parseMailtext($proposal, $unparsedDefaultMailtext, $additionalMailData, $proposalLangCode);

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

                if ($selectedState === self::PROPOSAL_DECLINED) {
                    $proposal->setRejectionTstamp(time());
                    $proposal->setRejectionEmail($proposal->getApplicant()?->getEmail());

                    $this->proposalRepository->update($proposal);
                }
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
     * Upload xlsx file and import external data to the proposals of the given call
     *
     * @param Call $call
     * @param array $upload
     * @return Call $call
     */
    public function importXlsxData(Call $call, array $upload): Call
    {
        if (array_key_exists('name', $upload) == false) {
            $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:message.file_upload_failed'), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            return $call;
        }

        if ($upload['error'] !== 0) {
            $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:message.file_upload_error'), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            return $call;
        }

        $extensionConfiguration = $this->extensionConfiguration->get('open_oap');
        $proposalAssessmentImportColumnNames = $extensionConfiguration['proposalAssessmentImportColumnNames'] ?? '';
        $proposalAssessmentImportColumnNames = GeneralUtility::trimExplode(',', $proposalAssessmentImportColumnNames, true);
        $idNameKey = array_search(self::PROPOSAL_ASSESSMENT_COLUMN_ID, $proposalAssessmentImportColumnNames);
        $scoreNameKey = array_search(self::PROPOSAL_ASSESSMENT_COLUMN_SCORE, $proposalAssessmentImportColumnNames);
        $actionNameKey = array_search(self::PROPOSAL_ASSESSMENT_COLUMN_ACTION, $proposalAssessmentImportColumnNames);

        if ($idNameKey === false || $scoreNameKey === false || $actionNameKey === false) {
            $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:message.data_import_failed'), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            return $call;
        }

        $tempFilePath = GeneralUtility::upload_to_tempfile($upload['tmp_name']);

        $spreadsheet = IOFactory::load($tempFilePath);

        // Data are assumed in the first sheet
        $sheet = $spreadsheet->getSheet(0);

        // First row and last column identifier
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Array for collecting data we want to import
        $importData = [];

        $signatures = [];
        $signatureNotFound = [];

        $callShortcut = $call->getShortcut();
        for ($row = 1; $row <= $highestRow; $row++) {
            // In the first row we are looking for the columns we need by header name
            if ($row === 1) {
                $headerRowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
                foreach ($headerRowData[0] as $key => $value) {
                    if ($value == $proposalAssessmentImportColumnNames[$idNameKey]) {
                        $idColumn = $key;
                    } elseif ($value == $proposalAssessmentImportColumnNames[$scoreNameKey]) {
                        $scoreColumn = $key;
                    } elseif ($value == $proposalAssessmentImportColumnNames[$actionNameKey]) {
                        $actionColumn = $key;
                    }
                }
                continue;
            }
            // Loop through the rows and columns collecting the selected data we need
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if (!$rowData[0][$idColumn]) {
                continue;
            }
            $importSignature = $rowData[0][$idColumn];
            $proposalSignatureIdentifier = $this->rebuildProposalSignatureFromSignature($importSignature, $callShortcut);
            if ($proposalSignatureIdentifier > 0) {
                $score = $rowData[0][$scoreColumn];
                $action = $rowData[0][$actionColumn];
                $importData[$proposalSignatureIdentifier] = [
                    'signature' => $importSignature,
                    'import_score' => $score,
                    'import_action' => $action
                ];
                $signatures[] = $proposalSignatureIdentifier;
            } else {
                $signatureNotFound[] = $rowData[0][$idColumn];
            }
        }

        $scoreImportNew = 0;
        $scoreImportNoChanges = 0;
        $scoreImportUpdated = [];
        $actionImportNew = 0;
        $actionImportNoChanges = 0;
        $actionImportUpdated = [];
        foreach ($importData as $signatureKey => $import) {
            $doUpdate = false;

            $proposalResult = $this->proposalRepository->findBySignatures([$signatureKey], $call->getProposalPid());

            $proposal = $proposalResult->getFirst();
            $proposalSignature = $proposal->getSignature();

            $signatureOfImport = $importData[$proposalSignature]['signature'];
            // the score value to import
            $scoreToImport = (string)$importData[$proposalSignature]['import_score'];
            // the action value to import
            $actionToImport = $importData[$proposalSignature]['import_action'];
            // the imported_score value of the proposal
            $proposalScore = $proposal->getImportedScore();
            // the imported_action value of the proposal
            $proposalAction = $proposal->getImportedAction();

            // CASE 1: the score value is initially imported to the proposal
            if ($proposalScore == '' && $scoreToImport != '') {
                $proposal->setImportedScore($scoreToImport);
                $doUpdate = true;
                $scoreImportNew++;

                // CASE 2: an already existing score value of the proposal is updated by the import value
            } elseif ($proposalScore != '' && $scoreToImport != '' && $proposalScore != $scoreToImport) {
                $proposal->setImportedScore($scoreToImport);
                $doUpdate = true;
                $scoreImportUpdated[] = $signatureOfImport;

                // CASE 3: the score value of the proposal and the import value are the same, no import is done
            } elseif ($proposalScore == $scoreToImport) {
                $scoreImportNoChanges++;
            }

            // CASE 1: the action value is initially imported to the proposal
            if ($proposalAction == '' && $actionToImport != '') {
                $proposal->setImportedAction($actionToImport);
                $doUpdate = true;
                $actionImportNew++;

                // CASE 2: an already existing import_action value of the proposal is updated by the import
            } elseif ($proposalAction != '' && $actionToImport != '' && $proposalAction != $actionToImport) {
                $proposal->setImportedAction($actionToImport);
                $doUpdate = true;
                $actionImportUpdated[] = $signatureOfImport;

                // CASE 3: the import_action value of the proposal and the action value of the import are the same, no import is done
            } elseif ($proposalAction == $actionToImport) {
                $actionImportNoChanges++;
            }

            if ($doUpdate) {
                $this->proposalRepository->update($proposal);
            }

        }
        $this->persistenceManager->persistAll();

        $LINEBREAK = "\r\n";
        $successMessage = "The import results:" . $LINEBREAK;
        $successMessage .= "Values:" . $LINEBREAK;
        $successMessage .= '- new score values inserted: ' . $scoreImportNew . $LINEBREAK;
        $successMessage .= '- score values without change: ' . $scoreImportNoChanges . $LINEBREAK;
        $successMessage .= '- score values overwritten: ' . count($scoreImportUpdated);
        if (count($scoreImportUpdated) > 0) {
            $successMessage .= ' (' . implode(', ', $scoreImportUpdated) . ')';
        }
        $successMessage .= $LINEBREAK;
        $successMessage .= 'Actions: ' . $LINEBREAK;
        $successMessage .= '- new action values entered: ' . $actionImportNew . $LINEBREAK;
        $successMessage .= '- action values without change: ' . $actionImportNoChanges . $LINEBREAK;
        $successMessage .= '- action values overwritten : ' . count($actionImportUpdated);
        if (count($actionImportUpdated) > 0) {
            $successMessage .= ' (' . implode(', ', $actionImportUpdated) . ')' . $LINEBREAK;
        }
        $this->addFlashMessage($successMessage, '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK);

        if (count($signatureNotFound) > 0) {
            $warningMessage = count($signatureNotFound) . ' proposal(s) were not found (' . implode(', ', $signatureNotFound) . '). ';
            $this->addFlashMessage($warningMessage, '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING);
        }

        $lastMessage = ' To update the results below after an import, please click the search button.';
        $this->addFlashMessage($lastMessage, '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);

        // The file is no longer needed
        unlink($tempFilePath);

        return $call;
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
        if ($supporter = $proposal->getCall()->getSupporter()) {
            if ($proposal->getFeLanguageUid() > 0) {
                $translatedSupporter = $this->getTranslatedSupporter($proposal->getFeLanguageUid(), $supporter->getUid());
                $mailtextInRevision = $translatedSupporter['event_proposal_in_revision_mailtext'] ?? '';
            } else {
                $mailtextInRevision = $supporter->getEventProposalInRevisionMailtext();
            }

            $proposalLangCode = $this->getProposalFrontendLanguageCode($proposal);

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
        $proposals = $this->proposalRepository->findByUids($records, ['signature' => 'ASC']);
        // how could we handle multiple calls in the export funktion
        $pageNo = 1;
        $l0headRow = 0;
        $l1headRow = 1;
        $itemHeadRow = 2;
        $columnNo = 0;
        $countryCounter = 1;

        $head = [];
        $head[$l0headRow] = [];
        $head[$l1headRow] = [];
        $head[$itemHeadRow] = [];

        $head[$itemHeadRow][$columnNo++] = 'ID (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Applicant-E-Mail (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Applicant-ID (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Signature (intern)';
        $head[$itemHeadRow][$columnNo++] = 'State (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Submitted';
        $head[$itemHeadRow][$columnNo++] = 'Last Changed';
        $head[$itemHeadRow][$columnNo++] = 'Survey (Hash)';
        $head[$itemHeadRow][$columnNo++] = 'Assessment (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Reviewer (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Reviewed on (intern)';

        /** @var FormItem $assessmentItem */
        foreach ($proposals[0]->getCall()->getAssessmentItems() as $assessmentItem) {
            $head[$itemHeadRow][$columnNo++] = $assessmentItem->getQuestion();
        }

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

                            // wished dirty code increment country count for item with the question = Project country
                            if ($item->getQuestion() === 'Project country') {
                                $head[$itemHeadRow][$columnNo - 1] .= " #$countryCounter";
                                $countryCounter++;
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
            $colI = 0;
            $export[$proposalUid][$colI++] = $proposalUid;
            $export[$proposalUid][$colI++] = $proposal->getApplicant()->getEmail();
            $export[$proposalUid][$colI++] = $proposal->getApplicant()->getUid();
            $export[$proposalUid][$colI++] = $this->buildSignature($proposal);
            $export[$proposalUid][$colI++] = $states[$proposal->getState()];
            $export[$proposalUid][$colI++] = date("d.m.Y", $proposal->getSubmitTstamp());
            $export[$proposalUid][$colI++] = date("d.m.Y", $proposal->getEditTstamp());
            $export[$proposalUid][$colI++] = $proposal->getSurveyHash();

            $value = (string) $proposal->getAssessmentValue();
            if ($value == '') {
                $value = '--';
            }
            if ((string) $value == '0' OR (integer) $value == 0) {
                $value = '0 ';
            }
            $export[$proposalUid][$colI++] = $value;
            $reviewerName = '';
            if ($proposal->getReviewer()) {
                $reviewerName = $proposal->getReviewer()->getRealName();
                if ($reviewerName == '') {
                    $reviewerName = $proposal->getReviewer()->getUserName();
                }
            }
            $export[$proposalUid][$colI++] = $reviewerName;
            $reviewTime = '';
            if ($proposal->getReviewTime()) {
                $reviewTime = $proposal->getReviewTime()->format('Y-m-d H:i');
            }
            $export[$proposalUid][$colI++] = $reviewTime;

            /** @var Answer $assessmentAnswer */
            foreach ($proposal->getAssessmentAnswers() as $assessmentAnswer) {
                $valueArray = [];
                if (is_array(json_decode($assessmentAnswer->getValue(), true))) {
                    $valueArray = json_decode($assessmentAnswer->getValue(), true);
                } else {
                    $valueArray = [$assessmentAnswer->getValue()];
                }
                foreach ($valueArray as $i => $valueItem) {
                    if (is_integer((integer) $valueItem) AND $valueItem === '0') {
                            $valueArray[$i] = $valueItem.' ';
                    }
                }
                $export[$proposalUid][$colI++] = join(',',$valueArray);
            }
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
                if (!isset($columns[$key])) {
                    // TODO: investigate
                    continue;
                }
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
                if (empty($proposalData[$columns])) {
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
     * Excel export function with phpspreadsheet based on csv export function
     *
     * @param $records
     * @throws InvalidQueryException
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    protected function exportExcel($records): void
    {
        /** @var ExcelExportService $excelExportService */
        $excelExportService = GeneralUtility::makeInstance(\OpenOAP\OpenOap\Service\ExcelExportService::class);

        $proposals = $this->proposalRepository->findByUids($records);

        $excelExportService->setProposals($proposals);

        // translations of state const for export
        $states = $this->createStatesArray();
        $excelExportService->setStates($states);

        $excelExportService->setResourceFactory($this->resourceFactory);
        $excelExportService->setSettings($this->settings);

        $spreadsheet = $excelExportService->createSpreadsheet();
        // Write to file
        $writer = new Xlsx($spreadsheet);
        $filename = 'proposal-export--' . date('Ymd-Hi');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0, must-revalidate');
        $writer->save('php://output');
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
     * @param Proposal $proposal
     * @return string The 2 letter language code
     */
    protected function getProposalFrontendLanguageCode(Proposal $proposal): string
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        try {
            $site = $siteFinder->getSiteByPageId($proposal->getPid());
            $siteLanguage = $site->getLanguageById($proposal->getFeLanguageUid());

            return $siteLanguage->getLocale()->getLanguageCode();
        } catch (\Throwable) {
            return 'en';
        }
    }

    /**
     * @param array $allItems
     * @return array
     */
    private function applyFilter(array $allItems): array
    {
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
        return $filterSelects;
    }

    /**
     * @return array
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    private function buildSorting(): array
    {
        $sorting = [];
        $sortField = 'signature';
        $sortRevert = 0;

        // two cases of transmitted values, one nested, one directly
        if ($this->request->hasArgument('sorted')) {
            $sortField = htmlspecialchars($this->request->getArgument('sorted')['sortField']);
            $sortRevert = (int)($this->request->getArgument('sorted')['sortRev']);
        }
        if ($this->request->hasArgument('sortField')) {
            $sortField = htmlspecialchars($this->request->getArgument('sortField'));
            $sortRevert = (int)($this->request->getArgument('sortRev'));
        }
        if ($sortField !== '') {
            if ($sortRevert == 1) {
                $sorting = [
                    'field' => $sortField,
                    'direction' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                ];
            } else {
                $sorting = [
                    'field' => $sortField,
                    'direction' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                ];
            }
            $sorting['revert'] = $sortRevert;
        }
        return $sorting;
    }

    /**
     * @param array $filter
     * @param int $itemsPerPage
     * @param Call $call
     * @return array
     */
    private function getFilter(array $filter, int $itemsPerPage, Call $call): array
    {
        if (isset($filter['todo']) and $filter['todo'] == 'clear') {
            $filter = [];
        }

        if (!isset($filter['itemsPerPage'])) {
            $filter['itemsPerPage'] = $itemsPerPage;
        }
        $filter['call'] = $call->getUid();
        return $filter;
    }

    /**
     * @param int $languageUid
     * @param int $supporter
     * @return array
     */
    protected function getTranslatedSupporter(int $languageUid, int $supporter): array
    {
        $supporterRepository = GeneralUtility::makeInstance(SupporterRepository::class);

        return $supporterRepository->findSupporterByLanguage($languageUid, $supporter);
    }
}
