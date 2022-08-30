<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use DateTime;
use Exception;
use OpenOAP\OpenOap\Domain\Model\Answer;
use OpenOAP\OpenOap\Domain\Model\Applicant;
use OpenOAP\OpenOap\Domain\Model\Call;
use OpenOAP\OpenOap\Domain\Model\Comment;
use OpenOAP\OpenOap\Domain\Model\FormGroup;
use OpenOAP\OpenOap\Domain\Model\FormItem;
use OpenOAP\OpenOap\Domain\Model\FormPage;
use OpenOAP\OpenOap\Domain\Model\ItemValidator;
use OpenOAP\OpenOap\Domain\Model\MetaInformation;
use OpenOAP\OpenOap\Domain\Model\Proposal;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\TemplateProcessor;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use TYPO3\CMS\Extbase\Validation\Validator\FloatValidator;
use TYPO3\CMS\Extbase\Validation\Validator\IntegerValidator;
use TYPO3\CMS\Extbase\Validation\Validator\UrlValidator;
use TYPO3\CMS\Form\Mvc\Property\Exception\TypeConverterException;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 *          Ingeborg Hess <ingeborg.hess@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * ProposalController
 */
class ProposalController extends OapFrontendController
{
    /**
     * @var array
     */
    protected array $items = [];

    /**
     * @var array
     */
    protected array $pages = [];

    /**
     * @var array
     */
    protected array $pageNumber = [];

    /**
     * @var array
     */
    protected array $groups = [];

    /**
     * @var IntegerValidator|null
     */
    protected ?IntegerValidator $integerValidator = null;

    /**
     * @var FloatValidator|null
     */
    protected ?FloatValidator $floatValidator = null;

    /**
     * @var EmailAddressValidator|null
     */
    protected ?EmailAddressValidator $emailValidator = null;

    /**
     * @var UrlValidator|null
     */
    protected ?UrlValidator $urlValidator = null;

    /**
     * action initialize
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeAction()
    {
        parent::initializeAction();

        // todo check if applicant is logged in

        if ($this->request->hasArgument('proposal') and $this->arguments) {
            $propertyMappingConfiguration = $this->arguments->getArgument('proposal')->getPropertyMappingConfiguration();

            $proposalArguments = $this->request->getArgument('proposal');
            if (isset($proposalArguments['answers'])) {
                // do this only if the Update-Action is called with Answers (not on the first call)
                foreach ($proposalArguments['answers'] as $key => $answer) {
                    if (is_array($answer['arrayValue'])) {
                        $proposalArguments['answers'][$key]['value'] = json_encode($answer['arrayValue'], JSON_FORCE_OBJECT);
                        unset($proposalArguments['answers'][$key]['arrayValue']);
                    } elseif (isset($answer['value'])) {
                        $proposalArguments['answers'][$key]['value'] = trim($answer['value']);
                    }
                }
                $this->request->setArgument('proposal', $proposalArguments);
            }

            $this->integerValidator = new IntegerValidator();
            $this->floatValidator = new FloatValidator();
            $this->emailValidator = new EmailAddressValidator();
            $this->urlValidator = new UrlValidator();
        }
    }

    /**
     * action create
     *
     * @param Applicant $applicant
     * @param Call $call
     */
    public function createAction(Applicant $applicant, Call $call)
    {
        $newProposal = new Proposal();

        // set current user
        $newProposal->setApplicant($applicant);
        $newProposal->setCall($call);
        $proposalPid = $this->determineProposalPid($call);
        ObjectAccess::setProperty($newProposal, 'pid', $proposalPid);
        $newProposal->setState(self::PROPOSAL_IN_PROGRESS);
        $newProposal->setFeLanguageUid($this->language->getLanguageId());
        $newProposal->setTitle($this->getTranslationString(self::XLF_BASE_IDENTIFIER_DEFAULTS . '.' . self::DEFAULT_TITLE));

//        $logItem = $this->createLog(self::LOG_PROPOSAL_CREATE);
//        $newProposal->addComment($logItem);

        $groupsCounter = [];
        /** @var FormPage $page */
        foreach ($call->getFormPages() as $page) {
            /** @var FormGroup $group */
            foreach ($page->getItemGroups() as $group) {
                // the group is optional and no answers are required here - skip
                $groupsCounter[$group->getUid()] = 0;
                if ($group->getRepeatableMin() == 0) {
                    continue;
                }

                // create all groups repeated for requirement - only mininal requirement
                // maximum required will be set later on demand
                for ($groupCounter = 0; $groupCounter < $group->getRepeatableMin(); $groupCounter++) {
                    $groupsCounter[$group->getUid()]++;
                    $this->createAnswersOfGroup($newProposal, $group, $groupCounter);
                }
            }
        }
//        DebuggerUtility::var_dump($newProposal);die();
        $this->persistenceManager->persistAll();

        // save groupsCounter in MetaInformation
        /** @var MetaInformation $metaInfo */
        $metaInfo = new MetaInformation($newProposal->getMetaInformation());
        $metaInfo->setGroupsCounter($groupsCounter);
        $newProposal->setMetaInformation($metaInfo->jsonSerialize());

        //        $newProposal->setPid($this->settings['setting']['proposalPid']);
        $this->proposalRepository->add($newProposal);
        $this->persistenceManager->persistAll();
//        $this->persistenceManager->persistAll();
//        die;

        // set current call - added by parameter
        // catch call object from Repository
        // iterate through the call / pages / groups / items and create answer items
        // set meta information like state, create date and the meta object for the progress
        // persist this object
//        $this->proposalRepository->add($newProposal);
        //        $this->proposalRepository->update($newProposal);

        // Set auto comment with created state on the proposal
        $logItem = $this->createLog(self::LOG_PROPOSAL_CREATE, $newProposal);
        $newProposal->addLog($logItem);
        $newProposal->setEditTstamp(time());
        $this->proposalRepository->update($newProposal);
        $this->persistenceManager->persistAll();

        $uri = $this->uriBuilder
            ->reset()
            ->setTargetPageUid((int)$this->settings['formPageId'])
            ->uriFor('edit', ['proposal' => $newProposal], 'Proposal', $this->ext, 'form');
        $this->redirectToURI($uri, 0, 200);
    }

    /**
     * action edit
     *
     * @param int $currentPage
     * @param \OpenOAP\OpenOap\Domain\Model\Proposal $proposal
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("proposal")
     * @return \Psr\Http\Message\ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws Exception
     */
    public function editAction(\OpenOAP\OpenOap\Domain\Model\Proposal $proposal, int $currentPage = 1): \Psr\Http\Message\ResponseInterface
    {
        // todo Check if proposal in mode to edit!

        $itemsMap = [];
        // build linear items array
        $this->flattenItems($proposal);
        $this->flattenAnswers($proposal);
        $updated = false;

        /** @var FormItem $item */
        foreach ($this->items as $item) {
            $itemUid = (integer)$item->getUid();

            // build validation and options array
            if (!$itemsMap[$item->getUid()]) {
                // initialize item
                $itemsMap[$item->getUid()] = $this->initializeItemsMap($item);

                $this->getOptionsToItemsMap($item, $itemsMap);
                $this->getValidatorsToItemsMap($item, $itemsMap);

                $itemsMap[$item->getUid()]['classStr'] = implode(' ', $itemsMap[$item->getUid()]['classes']);
            }

            // check: is this answer is existing - in case of changing the form after creating this proposal
            /** @var Answer $answer */
            if (!$this->answers[$itemUid]) {
                $answer = new Answer($item, $this->groups[$itemUid], 0, (integer)$this->settings['answersPoolId']);
                $this->answerRepository->add($answer);
                $proposal->addAnswer($answer);
                $log = $this->createLog(self::LOG_FORM_CHANGED_ADDED_ITEM, $proposal, $answer->getItem()->getQuestion());
                $proposal->addLog($log);
                $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_LOG . self::LOG_FORM_CHANGED_ADDED_ITEM) . ' ' . $log->getText();
                $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
                $updated = true;
            }
        }
//        DebuggerUtility::var_dump($proposal->getAnswers(),(string) __LINE__);
        $this->updateAnswersForRemovedItems($proposal);

        $metaInfo = new MetaInformation($proposal->getMetaInformation());
        $groupsCounter = $this->groupsCounterMetaInfo($proposal, $metaInfo);

        $this->proposalRepository->update($proposal);
        if ($updated) {
            $this->persistenceManager->persistAll();
            $this->flattenItems($proposal);
            $this->flattenAnswers($proposal);
        }
        $itemAnswerMap = $this->buildItemAnswerMap($proposal);

        // convert arrays (saved as json) into real array - esp. for checkboxes and multiselect selects
        foreach ($proposal->getAnswers() as $answer) {
            if (is_object(json_decode($answer->getValue()))) {
                $answer->setArrayValue(json_decode($answer->getValue(), true));
            }
        }

        // page control
        $pageControl = [];
        $pageControl['current'] = $currentPage;
        $pageControl['last'] = $metaInfo->getLastPage();
        $pageControl['next'] = ($currentPage < count($proposal->getCall()->getFormPages())) ? $currentPage + 1 : 0;
        $pageControl['previous'] = ($currentPage > 1) ? $currentPage - 1 : 0;

        // todo is this the submit page, we have to check the complete proposal
        /** @var FormPage $page */
        $page = $proposal->getPage($currentPage);
        $validationResults = $this->validateProposal($proposal, $metaInfo, $page->isSubmitPage());

//        $validationResults = new Result();
//        $value = 'keine email';
//        if (!is_string($value) || !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($value)) {
//            /** @var Error $error */
//            $error = new Error($this->getTranslationString('tx_openoap.js_msg.invalid_format',['e-mail']), 1646909921);
//            $validationResults->forProperty('answers.5.value')->addError($error);
//        }
        // DebuggerUtility::var_dump($validationResults,'',20);

        // calculate Comments for menu
        $pageNo = 1;
        /** @var FormPage $page */
        foreach ($proposal->getCall()->getFormPages() as $page) {
            /** @var FormGroup $group */
            foreach ($page->getItemGroups() as $group) {
                /** @var FormItem $item */
                foreach ($group->getItems() as $item) {
                    $itemPageMap[$item->getUid()] = $pageNo;
                }
            }
            $pageNo++;
        }
        $pageMap = [];
//        $answersMap = [];
//        /** @var Answer $answer */
//        foreach ($proposal->getAnswers() as $answer) {
//            // merge itemMap-Data with answers map
//            $answersMap[$answer->getUid()]['item'] = $itemsMap[$answer->getItem()->getUid()];
//            // todo check re-opened setting from clerk to set all items/answers open
//            // see below: depending on comments release of questions
//            $answersMap[$answer->getUid()]['disabled'] = false;
//            if ($proposal->getState() !== self::PROPOSAL_IN_PROGRESS and
//                $metaInfo->getLimitEditableFields() == self::META_PROPOSAL_EDITABLE_FIELDS_LIMIT) {
//                $answersMap[$answer->getUid()]['disabled'] = true;
//                $answersMap[$answer->getUid()]['item']['additionalAttributes']['disabled'] = 'disabled';
//            }
//        }
//        DebuggerUtility::var_dump($answersMap);
        $answersMap = $this->createAnswersMap($proposal, $metaInfo->getLimitEditableFields());
//        DebuggerUtility::var_dump($answersMap_tmp);
//        DebuggerUtility::var_dump(array_diff($answersMap_tmp,$answersMap));
//        die();
        foreach ($proposal->getAnswers() as $answer) {
            /** @var Comment $answerComment */
            foreach ($answer->getComments() as $answerComment) {
                if ($answerComment->getState() == self::COMMENT_STATE_NEW) {
                    // define pages with (active) comments
                    if (!isset($pageMap[$itemPageMap[$answer->getItem()->getUid()]])) {
                        $pageMap[$itemPageMap[$answer->getItem()->getUid()]] = [];
                        $pageMap[$itemPageMap[$answer->getItem()->getUid()]]['notification'] = 0;
                    }
                    $pageMap[$itemPageMap[$answer->getItem()->getUid()]]['notification']++;

                    // set editMode to answer/item
                    $answersMap[$answer->getUid()]['new_comments'] = 1;
//                    $answersMap[$answer->getUid()]['disabled'] = false;
                    unset($answersMap[$answer->getUid()]['item']['additionalAttributes']['disabled']);
                }
            }
        }

        $jsMessages = $this->getJsMessages();

        $this->view->assignMultiple([
            'proposal' => $proposal,
            'editState' => $metaInfo->getLimitEditableFields(),
            'itemTypes' => $this->getConstants()['TYPE'],
            'pageTypes' => $this->getConstants()['PAGETYPE'],
            'proposalStates' => $this->getConstants()['PROPOSAL'],
            'commentStates' => $this->getConstants()['COMMENT'],
            'answersMap' => $answersMap,
            'pageControl' => $pageControl,
            'itemAnswerMap' => $itemAnswerMap,
//            'itemsMap' => $itemsMap,
            'jsMessages' => $jsMessages,
            'validationResults' => $validationResults,
            'pageMap' => $pageMap,
            'groupsCounter' => $groupsCounter,
        ]);
        return $this->htmlResponse();
    }

    /**
     * action update
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Proposal $proposal
     * @throws StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws Exception
     */
    public function updateAction(\OpenOAP\OpenOap\Domain\Model\Proposal $proposal)
    {
        // todo check if proposal editable (= in_progress / re-opened)

        // initial flow - next page is current page
        $submitted = false;
        $close = false;
        $currentPage = 1;
        $nextPage = $currentPage;

        if ($this->request->hasArgument('cancel')) {
            $uri = $this->uriBuilder
                ->reset()
                ->setTargetPageUid((int)$this->settings['dashboardPageId'])
                ->build();
            $this->redirectToURI($uri, 0, 200);
        }

        if ($this->request->hasArgument('close')) {
            $proposal->setEditTstamp(time());
            $close = true;
        }

        //DebuggerUtility::var_dump($this->request->getArguments());die();
        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (integer)$this->request->getArgument('currentPage');
            $nextPage = $currentPage;
        }

        // control flow - next step or previous step
        if ($this->request->hasArgument('gotoPage')) {
            $nextPage = $this->request->getArgument('gotoPage');
            $countOfPages = count($proposal->getCall()->getFormPages());
            if ($nextPage > $countOfPages) {
                $nextPage = $countOfPages;
            }
            if ($nextPage < 1) {
                $nextPage = 1;
            }
        }

        // was this the preview page?
        $previewPage = false;
        if ($this->request->hasArgument('previewPage')) {
            $previewPage = (bool)$this->request->getArgument('previewPage');
        }

        if ($this->request->getOriginalRequest()) {
            $request = $this->request->getOriginalRequest();
        } else {
            $request = $this->request;
        }

        if ($this->request->hasArgument('addGroup')) {
            $this->addGroupToAnswers($proposal);
        }

        if ($this->request->hasArgument('removeGroup')) {
            $this->removeGroupFromAnswers($proposal);
        }

        // handle uploads
        if (count($_FILES)) {
            $this->processUploadedFiles($proposal);
        }

        $this->flattenItems($proposal);
        $this->flattenAnswers($proposal);

        $allPages = [];
        $i = 1;
        foreach ($proposal->getCall()->getFormPages() as $page) {
            $allPages[$i++] = $page;
        }
        $this->processAnswersAdditionalValues();

        $infos = [];
        $info = '';
        $filter = [];
        /** @var FormItem $item */
        foreach ($this->items as $item) {
            // Title of proposal is a "must not be empty" field and to avoid a validation crush we let the value there if the field is empty
//            if ($this->answers[$item->getUid()]->getValue() == '') {
//                continue;
//            }

            if ($item->isEnabledInfo()) {
                // todo: handling of different types, esp. usage of additionalValue and multiple Values
                $infos[$item->getUid()] = $this->answers[$item->getUid()]->getValue();
            }
            if ($item->isEnabledFilter()) {
                if (!$filter[$item->getUid()]) {
                    $filter[$item->getUid()] = [];
                }
                // todo: handling of different types, esp. usage of additionalValue
                $filter[$item->getUid()] = $this->answers[$item->getUid()]->getValue();
            }
            if ($item->isEnabledTitle() and $this->answers[$item->getUid()]->getValue() !== '') {
                if ($proposal->getTitle() !== $this->answers[$item->getUid()]->getValue()) {
                    $logItem = $this->createLog(self::LOG_PROPOSAL_CHANGED_TITLE, $proposal, $this->answers[$item->getUid()]->getValue());
                    $proposal->addLog($logItem);
                }
                $proposal->setTitle($this->answers[$item->getUid()]->getValue());
            }
        }
        // build the info string
        $info = implode(' ' . $this->settings['metaInfoSeparator'] . ' ', $infos);

//        DebuggerUtility::var_dump($info,'info '. __LINE__);
//        DebuggerUtility::var_dump($filter,'filter '. __LINE__);

        $metaInfo = new MetaInformation($proposal->getMetaInformation());
        // DebuggerUtility::var_dump($metaInfo,'metaInfo '. __LINE__);
        $metaInfo->setFilter($filter);
        $metaInfo->setInfo($info);
        $metaInfo->addPage($currentPage, $allPages[$currentPage]);

        // set lastPage only if it is not preview
        if (!$previewPage) {
            $metaInfo->setLastPage($currentPage);
        }
        $proposal->setMetaInformation($metaInfo->jsonSerialize());

        if ($this->request->hasArgument('submit')) {
            $validationResults = $this->validateProposal($proposal, $metaInfo, true);
            if (!$validationResults) {
                $proposal->setState(self::PROPOSAL_SUBMITTED);
                $logItem = $this->createLog(self::LOG_PROPOSAL_SUBMITTED, $proposal);
                $proposal->addLog($logItem);
                $proposal->setEditTstamp(time());

                // todo establish with signals (or hooks) the possibility to extend createSignature with own functions and values
                $proposalPid = $this->determineProposalPid($proposal->getCall());
                $signatureNumber = $this->proposalRepository->getMaxSignature($proposalPid);
                $newSignatureNumber = $signatureNumber + 1;
                $proposal->setSignature($newSignatureNumber);

                // check if there are comments for items - then set read
                /** @var Answer $answer */
                foreach ($proposal->getAnswers() as $answer) {
                    /** @var Comment $comment */
                    foreach ($answer->getComments() as $comment) {
                        if ($comment->getState() == self::COMMENT_STATE_NEW) {
                            $comment->setState(self::COMMENT_STATE_ACCEPTED);
                        }
                        $this->commentRepository->update($comment);
                    }
                }
                $submitted = true;
            }
        }

        $proposal->setFeLanguageUid($this->language->getLanguageId());
        $proposal->setEditTstamp(time());
        $this->proposalRepository->update($proposal);
        $this->persistenceManager->persistAll();
        $this->checkFiles($proposal);

        if ($submitted) {
            $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_FLASH . self::FLASH_MSG_SUBMITTED_OKAY);
            $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
            $this->redirect('mail', 'Applicant', null, ['proposal' => $proposal, 'mailtextSetting' => 'eventProposalSubmittedMailtext', 'mailTemplate' => self::MAIL_TEMPLATE_PROPOSAL_SUBMIT], $this->settings['dashboardPageId']);
        } elseif ($close) {
            $uri = $this->uriBuilder
                ->reset()
                ->setTargetPageUid((int)$this->settings['dashboardPageId'])
                ->build();
        } else {
            $uri = $this->uriBuilder
                ->reset()
                ->setTargetPageUid((int)$this->settings['formPageId'])
                ->uriFor('edit', ['proposal' => $proposal, 'currentPage' => $nextPage], 'Proposal', $this->ext, 'form');
        }
        $this->redirectToURI($uri, 0, 200);
    }

    public function downloadAttachmentsAction(Proposal $proposal)
    {
        /** @var ResourceStorage $storage */
        $storage = $this->getStorage();
        list($absoluteBasePath, $uploadFolder) = $this->initializeUploadFolder($storage);

        // filename and path
        $zipName = 'documents--' . date('Ymd-Hi') . '.zip';
        $zipFile = $absoluteBasePath . $uploadFolder->getIdentifier() . $zipName;
        $zipFlag = false;

        $createFolder = false;
        $zip = null;
        list($zip, $zipFlag) = $this->addAttachmentsToZip($proposal, $zip, $zipFlag, $zipFile, $createFolder, $absoluteBasePath);

        if ($zip) {
            $zip->close();
            // DebuggerUtility::var_dump($zipFile);die();
            $this->sendZip($zipFile);
        }
        // in case there are no Attachments... stop here
        die();
    }

    public function downloadWordAction(Proposal $proposal)
    {
        $contentWordFileName = $this->createWord($proposal);
        if ($contentWordFileName === '') {
            // todo flashmessage - that there was an error and no document exists
            die();
        }

        // get storage for uploadFolder
        /** @var ResourceStorage $storage */
        $storage = $this->getStorage();
        list($absoluteBasePath, $uploadFolder) = $this->initializeUploadFolder($storage);
        $proposalTempFolder = $this->provideTargetFolder($storage->getRootLevelFolder(), '_temp_');

        $file = $storage->getFile($this->settings['wordExportTemplateFile']);

        $targetFileName = $this->getWordFileName($proposal->getUid(), 'merge');
//        $finshedFileName = $this->getWordFileName($proposal->getUid(), 'finished');
        /** @var File $copiedFile */
        $targetFile = $file->copyTo($proposalTempFolder)->rename($targetFileName);

        $targetfileAbsName = $absoluteBasePath . $targetFile->getIdentifier();
//        $finishedFileAbsName = $absoluteBasePath . $proposalTempFolder->getIdentifier() . $finshedFileName;
        $this->mergeTemplateWithWord($contentWordFileName, $targetfileAbsName);

        $templateProcessor = new TemplateProcessor($targetfileAbsName);

        $states = $this->createStatesArray('all');

        $this->wordVarReplacement($proposal, $states[$proposal->getState()], $templateProcessor);

        $templateProcessor->saveAs($targetfileAbsName); // As($mergeFile);

        $filename = $this->getWordFileName($proposal->getUid(), 'proposal');

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        readfile($targetfileAbsName);

        unlink($targetfileAbsName);
        die();
    }

    /**
     * action download
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Proposal $proposal
     */
    public function downloadAction(\OpenOAP\OpenOap\Domain\Model\Proposal $proposal)
    {
        $this->flattenAnswers($proposal);
        $itemAnswerMap = $this->buildItemAnswerMap($proposal);

        /** @var MetaInformation $metaInfo */
        $metaInfo = new MetaInformation($proposal->getMetaInformation());
        $groupsCounter = $this->groupsCounterMetaInfo($proposal, $metaInfo);

        $answersMap = $this->createAnswersMap($proposal, $metaInfo->getLimitEditableFields());
        $commentsAtProposal = $this->commentRepository->findDemanded($proposal, ['source' => self::COMMENT_SOURCE_EDIT]);

        $arguments = [
            'destination' => 'download',
            'filepath' => strtr($proposal->getTitle(), ' ', '-') . '--' . date('Ymd-Hi') . '.pdf',
            'generatedDate' => date('d.m.Y'),
            'generatedTime' => date('H:i'),
            'proposal' => $proposal,
            'commentsAtProposal' => $commentsAtProposal,
            'itemTypes' => $this->getConstants()['TYPE'],
            'answers' => $this->answers,
            'answersMap' => $answersMap,
            'itemAnswerMap' => $itemAnswerMap,
            'groupsCounter' => $groupsCounter,
            'settings' => $this->settings,
        ];

        $this->renderPdfView(self::EXPORT_TEMPLATE_PATH_PDF, $arguments);

        $uri = $this->uriBuilder
            ->reset()
            ->setTargetPageUid((int)$this->settings['dashboardPageId'])
            ->build();
        $this->redirectToURI($uri, 0, 200);
    }

    /**
     * action delete
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Proposal $proposal
     */
    public function deleteAction(\OpenOAP\OpenOap\Domain\Model\Proposal $proposal)
    {
        // One last greeting - log entry
        $log = $this->createLog(self::LOG_PROPOSAL_DELETE, $proposal);
        $proposal->addLog($log);
        $proposal->setEditTstamp(time());
        $this->proposalRepository->update($proposal);
        $this->proposalRepository->remove($proposal);

        $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_FLASH . self::FLASH_MSG_DELETED_OKAY);
        $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

        $uri = $this->uriBuilder
            ->reset()
            ->setTargetPageUid((int)$this->settings['dashboardPageId'])
            ->build();
        $this->redirectToURI($uri, 0, 200);
    }

    /**
     * action notifications
     *
     * @param Proposal|null $proposal
     * @param string $filter
     * @param string $sort
     * @return \Psr\Http\Message\ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("proposal")
     */
    public function notificationsAction(\OpenOAP\OpenOap\Domain\Model\Proposal $proposal=null, String $filter='', String $sort='')
    {
        $filterDemand = [];
        $sortOrder = '';
        if ($filter == 'auto') {
            $filterDemand['source'] = $this->getConstants()['COMMENT']['COMMENT_SOURCE_EDIT'];
        } else {
            $filter='';
        }
        if ($sort == 'asc') {
            $sortOrder = $sort;
        } else {
            $sort = 'desc';
        }
        if ($proposal) {
            // Remove the two types of edited comments from NEW count
            foreach ($proposal->getComments() as $comment) {
                switch ($comment->getSource()) {
                    // Comments IN a proposal are counted as NEW if they own the state COMMENT_STATE_AUTO_ACCEPTED (means the proposal has been re-opened)
                    // These comment types own the state COMMENT_STATE_NEW before the proposal is re-opened only
                    // therefore we look for state COMMENT_STATE_AUTO_ACCEPTED here
                    case self::COMMENT_SOURCE_EDIT_ANSWER:
                        if ($comment->getState() == self::COMMENT_STATE_AUTO_ACCEPTED) {
                            $comment->setState(self::COMMENT_STATE_ACCEPTED);
                            $this->commentRepository->update($comment);
                        }
                        break;

                        // comments AT a proposal are counted as NEW if they own the state COMMENT_STATE_NEW
                        // therefore in this case we look for state COMMENT_STATE_NEW
                    case self::COMMENT_SOURCE_EDIT:
                        if ($comment->getState() == self::COMMENT_STATE_NEW) {
                            $comment->setState(self::COMMENT_STATE_ACCEPTED);
                            $this->commentRepository->update($comment);
                        }
                        break;
                }
            }
            $comments = $this->commentRepository->findDemanded($proposal, $filterDemand, $sortOrder);
        }
        $this->view->assignMultiple([
            'proposal' => $proposal,
            'comments' => $comments,
            'filter' => $filter,
            'sort' => $sort,
            'settings' => $this->settings,
            'states' => $this->getConstants()['PROPOSAL'],
            'logSources' => $this->getConstants()['COMMENT'],
            'proposalAnnotatedCode' => self::LOG_PROPOSAL_ANNOTATED,
        ]);
        return $this->htmlResponse();
    }

    /**
     * @param array $validationResult
     * @param Answer $answer
     * @param string $textIdentifier
     */
    private function setValidationResult(array &$validationResult, Answer $answer, string $textIdentifier, array $parameter = []): void
    {
        $item = $answer->getItem();
        $pageNumber = $this->pageNumber[$item->getUid()];

        $validationOfItem = [];
        $validationOfItem['error'] = true;
        $validationOfItem['id'] = true;
        $validationOfItem['item'] = $item->getUid();
        $validationOfItem['field'] = $this->shortenQuestion($item->getQuestion());
        if (isset($validationResult[$pageNumber][$item->getUid()]['messsages'])) {
            $validationOfItem['messsages'] = $validationResult[$pageNumber][$item->getUid()]['messsages'];
        } else {
            $validationOfItem['messsages'] = [];
        }
        $validationOfItem['messsages'][] = $this->getTranslationString($textIdentifier, $parameter);

        if (!$validationResult['pages'][$pageNumber]) {
            $validationResult['pages'][$pageNumber] = [];
        }
//        if (!$validationResult['answers'][$answer->getUid()]) {
//            $validationResult['answers'][$answer->getUid()] = [];
//        }
        $validationResult['pages'][$pageNumber][] = $answer->getUid();
        $validationResult['answers'][$answer->getUid()] = $validationOfItem;
    }

    /**
     * @param Proposal $proposal
     * @param MetaInformation $metaInformation
     * @param bool $checkAll
     * @return array
     * @throws Exception
     */
    private function validateProposal(Proposal $proposal, MetaInformation $metaInformation, bool $checkAll): array
    {
        // test against validations
        $validationResults = new Result();
        // $error = new Error('Dieses Feld ist ein Pflichtfeld',200);
//        $value = 'keine email';
//        if (!is_string($value) || !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($value)) {
//            $error = new Error($this->getTranslationString(self::XLF_BASE_IDENTIFIER_JSMSG.'invalid_format', ['e-mail']), 1646909921);
//            $validationResults->addError($error);
//        }

        $validationResult = [];
        /** @var Answer $answer */
        foreach ($proposal->getAnswers() as $answer) {
            /** @var FormItem $item */
            $item = $answer->getItem();
            $itemUid = $item->getUid();

            // pages contains all pages previously accessed by the applicant - only these pages should be validated - on standard pages.
            // on preview pages, all pages must be checked (to avoid skipping fields that were not seen before).
            $pages = array_flip($metaInformation->getPages());
            if (!isset($this->pages[$itemUid])) {
                // todo is this behaviour okay for missing pages for answers?
                // there is no page for this answer - so the form has been changed
                continue;
            }
            if (!isset($pages[$this->pages[$itemUid]->getUid()]) and !$checkAll) {
                // $validationResult[$this->pages[$itemUid]->getUid()] = [];
                continue;
            }

            /** @var ItemValidator $validator */
            foreach ($item->getValidators() as $validator) {
                switch ($validator->getType()) {
                    case self::VALIDATOR_MANDATORY:
                        if (trim($answer->getValue()) == '') {
                            $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'mandatory');
                        }
                        break;
                    case self::VALIDATOR_MAXCHAR:
                        if (mb_strlen($answer->getValue()) > (integer)$validator->getParam1()) {
                            $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'max_len_exceeded');
                        }
                        break;
                    case self::VALIDATOR_MINVALUE:
                        if ($answer->getValue() == '') {
                            break;
                        }
                        if ($item->getType() == self::TYPE_DATE1) {
                            $minDate = new DateTime($validator->getParam1());
                            $valueDate = new DateTime($answer->getValue());
                            if ($minDate > $valueDate) {
                                $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'min_value', [$validator->getParam1()]);
                            }
                            break;
                        }
                        if ($item->getType() == self::TYPE_UPLOAD) {
                            $filesArr = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $answer->getValue(), true);
                            if (count($filesArr) < $validator->getParam1()) {
                                $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'min_value', [$validator->getParam1()]);
                            }
                            break;
                        }
                        if ($answer->getValue() < $validator->getParam1()) {
                            $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'min_value', [$validator->getParam1()]);
                        }
                        break;
                    case self::VALIDATOR_MAXVALUE:
                        if ($answer->getValue() == '') {
                            break;
                        }
                        if ($item->getType() == self::TYPE_DATE1) {
                            $date = $this->convertIntoDate($validator->getParam1());

                            $maxDate = new DateTime($date);
                            $valueDate = new DateTime($answer->getValue());
                            if ($valueDate > $maxDate) {
                                $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'max_value', [$validator->getParam1()]);
                            }
                            break;
                        }
                        if ($item->getType() == self::TYPE_UPLOAD) {
                            $filesArr = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $answer->getValue(), true);
                            if (count($filesArr) > $validator->getParam1()) {
                                $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'max_value', [$validator->getParam1()]);
                            }
                            break;
                        }
                        if ($answer->getValue() > $validator->getParam1()) {
                            $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'max_value', [$validator->getParam1()]);
                        }
                        break;
                    case self::VALIDATOR_INTEGER:
                        if ($this->integerValidator->validate($answer->getValue())->hasErrors() and $answer->getValue() !== '') {
                            $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'is_not_an_integer');
                        }
                        break;
                    case self::VALIDATOR_FLOAT:
                        if ($answer->getValue() !== '') {
                            break;
                        }
                        if ($this->floatValidator->validate($answer->getValue())->hasErrors()) {
                            if ($this->integerValidator->validate($answer->getValue())->hasErrors()) {
                                $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'is_not_a_float');
                            }
                        }
                        break;
                    case self::VALIDATOR_EMAIL:
                        if ($this->emailValidator->validate($answer->getValue())->hasErrors() and $answer->getValue() !== '') {
                            $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'invalid_email');
                        }
                        break;
                    case self::VALIDATOR_WEBSITE:
                        if ($this->urlValidator->validate($answer->getValue())->hasErrors() and $answer->getValue() !== '') {
                            $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'invalid_webaddress');
                        }
                        break;
                    case self::VALIDATOR_PHONE:
//                        if (!is_string($answer->getValue())) {
//                            $this->setValidationResult($validationResult, $item, 'tx_openoap.js_msg.invalid_email');
//                        }
                        break;
                }
            }
        }
        return $validationResult;
    }

    /**
     * @param string $answerValue
     * @param int $groupCounter
     * @return string
     */
    protected function getDefaultValueFromForeignObject(string $answerValue, int $groupCounter, Proposal $proposal): string
    {
        if (strpos($answerValue, '@') === 0 and $groupCounter == 0) {
            $answerValueDefault = $answerValue;
            $parameter = substr($answerValue, 1);
            $sources = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
                ',',
                $this->settings['defaultSourcesModel'],
                true
            );
            $sources = array_flip($sources);
            if (isset($sources[$parameter])) {
                $access = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('.', $parameter, true);
                $modelName = $access[0];

                $model = null;
                if ($proposal->_hasProperty($modelName)) {
                    $model = $proposal->_getProperty($modelName);
                }
                if (is_object($model)) {
                    if ($model->_hasProperty($access[1])) {
                        $answerValue = $model->_getProperty($access[1]);
                    }
                }
            }
            if ($answerValueDefault == $answerValue) {
                $answerValue = ''; // clear if no default value determinable
            }
        }
        return (string)$answerValue;
    }

    /**
     * @param FormGroup $group
     * @param int $groupCounter
     * @param Proposal $proposal
     * @throws IllegalObjectTypeException
     */
    protected function createAnswersOfGroup(Proposal $proposal, FormGroup $group, int $groupCounter): void
    {
        /** @var FormItem $item */
        foreach ($group->getItems() as $item) {
            // create new answer object
            $answer = new Answer($item, $group, $groupCounter, (integer)$this->settings['answersPoolId']);
            // set Default  depends on groupCounter (only first group will get the default value
            $answerValue = $this->getDefaultValueFromForeignObject($item->getDefaultValue(), $groupCounter, $proposal);
            $answer->setValue($answerValue);

            $this->answerRepository->add($answer);
            $proposal->addAnswer($answer);
        }
    }

    /**
     * @param Proposal $proposal
     * @throws IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function addGroupToAnswers(Proposal $proposal): void
    {
        $addGroupUid = $this->request->getArgument('addGroup')['__identity'];
        /** @var FormPage $formPage */
        foreach ($proposal->getCall()->getFormPages() as $formPage) {
            /** @var FormGroup $itemGroup */
            foreach ($formPage->getItemGroups() as $itemGroup) {
                if ($itemGroup->getUid() == $addGroupUid) {
                    /** @var MetaInformation $metaInfo */
                    $metaInfo = new MetaInformation($proposal->getMetaInformation());
                    $groupCounter = $metaInfo->getGroupsCounter();
                    // take the number directly (without increment), cause index is starting with 0, but here it is a count
                    $this->createAnswersOfGroup($proposal, $itemGroup, $groupCounter[$itemGroup->getUid()]);
                    $groupCounter[$itemGroup->getUid()]++;
                    $metaInfo->setGroupsCounter($groupCounter);
                    $proposal->setMetaInformation($metaInfo->jsonSerialize());
                }
            }
        }
    }

    /**
     * @param Proposal $proposal
     * @throws IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function removeGroupFromAnswers(Proposal $proposal): void
    {
        $removeParameterJsonStr = $this->request->getArgument('removeGroup');
        $removeParameter = json_decode($removeParameterJsonStr);

        $metaInfo = new MetaInformation($proposal->getMetaInformation());
        $groupCounter = $metaInfo->getGroupsCounter();
        $groupCounter[$removeParameter->itemGroup]--;
        $metaInfo->setGroupsCounter($groupCounter);

        /** @var Answer $answer */
        foreach (clone $proposal->getAnswers() as $answer) {
            if ($answer->getModel()->getUid() == $removeParameter->itemGroup and $answer->getElementCounter() == $removeParameter->elementCounter) {
                $proposal->removeAnswer($answer);
            } elseif ($answer->getModel()->getUid() == $removeParameter->itemGroup and $answer->getElementCounter() > $removeParameter->elementCounter) {
                // correction of item elementCounter // shift all after the deleted answer down/increemnt counter
                $answer->setElementCounter($answer->getElementCounter() - 1);
            }
        }
        $proposal->setMetaInformation($metaInfo->jsonSerialize());
    }

    /**
     * @param Proposal $proposal
     */
    protected function updateAnswersForRemovedItems(Proposal &$proposal): void
    {
        foreach (clone $proposal->getAnswers() as $answer) {
            $itemOfAnswer = $answer->getItem();
            if (!isset($this->items[$itemOfAnswer->getUid()])) {
                // The question has been removed from the form - and therefore the answer must also be removed.
                $proposal->removeAnswer($answer);
                $log = $this->createLog(
                    self::LOG_FORM_CHANGED_REMOVED_ITEM,
                    $proposal,
                    $answer->getItem()->getQuestion()
                );
                $proposal->addLog($log);
                $flashMessage = $this->getTranslationString(
                    self::XLF_BASE_IDENTIFIER_LOG . self::LOG_FORM_CHANGED_REMOVED_ITEM
                ) . ' ' . $log->getText();
                $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
            }
        }
    }

    /**
     * @param Proposal $proposal
     * @throws TypeConverterException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function processUploadedFiles(Proposal $proposal): void
    {
        $this->resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Resource\ResourceFactory::class
        );
        foreach ($_FILES[self::PLUGIN_FORM]['name'] as $fileNameKey => $fileNames) {
            if ($this->request->hasArgument($fileNameKey)) {
                preg_match('~.*\.(.*)\..*~', $fileNameKey, $matches);
                $propertyKey = $matches[1];
                /** @var Answer $uploadAnswer */
                $uploadAnswer = null;
                if ($proposal->getAnswers()->offsetExists($propertyKey)) {
                    $uploadAnswer = $proposal->getAnswers()->offsetGet($propertyKey);
                }
                if (!$uploadAnswer) {
                    // add flashMessage - no match found - this looks like a trial to hack
                    continue;
                }
                if ($uploadAnswer->getItem()->getType() !== self::TYPE_UPLOAD) {
                    // FlashMessage - answer is no upload item
                    continue;
                }

                $uploadFolder = $this->getUploadFolder($proposal);

                $files = $this->request->getArgument($fileNameKey);

                // correction for single upload field... make an array
                if (!isset($files[0])) {
                    $tempFile[0] = $files;
                    $files = $tempFile;
                }

                foreach ($files as $file) {
                    if (!isset($file['error']) || $file['error'] === 0) {
                        if (!GeneralUtility::makeInstance(FileNameValidator::class)->isValid($file['name'])) {
                            throw new TypeConverterException(
                                'Uploading files with PHP file extensions is not allowed!',
                                1471710357
                            );
                        }
                        $uploadedFile = $uploadFolder->addUploadedFile($file, DuplicationBehavior::RENAME);
                        $valueString = $uploadAnswer->getValue();
                        $fileIds = GeneralUtility::trimExplode(',', $valueString, true);
                        $fileIds[] = $uploadedFile->getUid();
                        sort($fileIds);
                        $valueString = implode(',', $fileIds);
                        $uploadAnswer->setValue($valueString);
                    }
                    // todo flashMessage error output
                }
            }
        }
    }

    /**
     * @param Proposal $proposal
     * @return \TYPO3\CMS\Core\Resource\Folder
     */
    protected function getUploadFolder(Proposal $proposal): \TYPO3\CMS\Core\Resource\Folder
    {
        $uploadFolderId = ($this->settings['uploadFolder'] == '') ? self::$defaultUploadFolder
            : $this->settings['uploadFolder'];
        $uploadFolder = $this->provideUploadFolder(
            $uploadFolderId,
            $proposal->getApplicant()->getUid(),
            $proposal->getUid(),
        );
        return $uploadFolder;
    }

    /**
     * @param Proposal $proposal
     */
    protected function checkFiles(Proposal $proposal): void
    {
        // collect all referenced files from proposal
        /** @var Answer $answer */
        $concatFileIds = '';
        foreach ($proposal->getAnswers() as $answer) {
            if ($answer->getItem()->getType() !== self::TYPE_UPLOAD or $answer->getValue() === '') {
                continue;
            }
            $concatFileIds .= ',' . $answer->getValue();
        }
        $fileIds = GeneralUtility::intExplode(',', $concatFileIds, true);
        $fileIds = array_flip($fileIds);
        // catch all Files in the current Folder
        $uploadFolder = $this->getUploadFolder($proposal);
        $savedFiles = $uploadFolder->getFiles();
//        DebuggerUtility::var_dump($savedFiles);die();
        foreach ($savedFiles as $fileName => $savedFile) {
            if (!isset($fileIds[$savedFile->getUid()]) and $fileName !== 'index.html') {
                // remove file, cause the file isn't stored in the proposal
                $savedFile->delete();
            }
        }
    }

    private function processAnswersAdditionalValues(): void
    {
        /** @var Answer $answer */
        foreach ($this->answers as $answer) {
            if ($answer->getItem()->isAdditionalValue()) {
                $item = $answer->getItem();
                if ($answer->getValue() === '') {
                    continue;
                }

                $lastSelectedOption = '';
                $additionalOption = $this->cleanupOptionItem($item->getAdditionalLabel());

                if ($item->getType() == self::TYPE_CHECKBOX) {
                    // multiple - answer is json
                    $valueArray = json_decode($answer->getValue(), true);
                    // last selected option
                    $lastSelectedOption = array_pop($valueArray);
                }

                if ($item->getType() == self::TYPE_RADIOBUTTON) {
                    $lastSelectedOption = $answer->getValue();
                }

                if ($lastSelectedOption !== $additionalOption['key']) {
                    // clean up additional Value
                    $answer->setAdditionalValue('');
                }
            }
        }
    }

    /**
     * @deprecated
     * - just a copy and
     * @param Proposal $proposal
     * @param $itemsMap
     * @param MetaInformation $metaInfo
     * @return array
     */
    protected function createAnswersMap_temp(Proposal $proposal, $itemsMap, MetaInformation $metaInfo): array
    {
        $answersMap = [];
        /** @var Answer $answer */
        foreach ($proposal->getAnswers() as $answer) {
            // merge itemMap-Data with answers map
            $answersMap[$answer->getUid()]['item'] = $itemsMap;
            // todo check re-opened setting from clerk to set all items/answers open
            // see below: depending on comments release of questions
            $answersMap[$answer->getUid()]['disabled'] = false;
            if ($proposal->getState() !== self::PROPOSAL_IN_PROGRESS and
                $metaInfo->getLimitEditableFields() == self::META_PROPOSAL_EDITABLE_FIELDS_LIMIT) {
                $answersMap[$answer->getUid()]['disabled'] = true;
                $answersMap[$answer->getUid()]['item']['additionalAttributes']['disabled'] = 'disabled';
            }
        }
        return $answersMap;
    }

    /**
     * @param Proposal $proposal
     * @param $states
     * @param TemplateProcessor $templateProcessor
     */
    private function wordVarReplacement(Proposal $proposal, $states, TemplateProcessor $templateProcessor): void
    {
        $applicant = $proposal->getApplicant();
        $applicantName = $this->getApplicantName($applicant);
        if (trim($applicantName) !== '') {
            $applicantName .= ' (' . $applicant->getEmail() . ')';
        } else {
            $applicantName = $applicant->getEmail();
        }

        // signature-label
        $proposalId = LocalizationUtility::translate(
            $this->locallangFile . ':tx_openoap_dashboard.proposal.signature.label'
        ) . ': ' . $this->buildSignature($proposal);
        $author = LocalizationUtility::translate($this->locallangFile . ':tx_openoap_proposals.exportAuthor.text') .
                  ': ' .
                  $applicantName;
        $dateLastEdit = LocalizationUtility::translate(
            $this->locallangFile . ':tx_openoap_dashboard.proposal.lastChange.label'
        ) . ': ' . date('d.m.Y', $proposal->getEditTstamp());
        $dateExport = LocalizationUtility::translate(
            $this->locallangFile . ':tx_openoap_proposals.exportGenerated.text',
            'open_oap',
            [date('d.m.Y'), date('H:i')]
        );
        $callState = LocalizationUtility::translate($this->locallangFile . ':tx_openoap_proposals.exportStatus.text') .
                     ': ' .
                     $states;

        // <div><span class="footnote">({commentsAtProposalCount} {f:if(condition:'1 == {commentsAtProposalCount}', then:'{f:translate(key:\'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_notifications.pdf.singular.label\')}', else:'{f:translate(key:\'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:tx_openoap_notifications.pdf.plural.label\')}')}) <sup>{footnoteCounter}</sup></span></div>
        $commonComment = 0;
        /** @var Comment $comment */
        foreach ($proposal->getComments() as $comment) {
            if ($comment->getSource() == self::COMMENT_SOURCE_EDIT) {
                $commonComment++;
            }
        }
        $commentsCount = '';
        if ($commonComment > 0) {
            $commentsCount = LocalizationUtility::translate($this->locallangFile . ':tx_openoap_notifications.pdf.generalComments.label') .
                             ': ' .
                             $commonComment;
        }
//        DebuggerUtility::var_dump($proposal->getComments());
//        DebuggerUtility::var_dump($proposal);
//        die();

        $templateProcessor->setValue(
            ['proposalId', 'author', 'dateExport', 'dateLastEdit', 'callTitle', 'proposalTitle', 'callState', 'commentsCount'],
            [
                $proposalId,
                $author,
                $dateExport,
                $dateLastEdit,
                $proposal->getCall()->getTitle(),
                $proposal->getTitle(),
                $callState,
                $commentsCount,
            ],
        );

        // build intro text (complexElement from HTML)
        // https://github.com/PHPOffice/PHPWord/issues/902#issuecomment-564561115
        $introSection = (new PhpWord())->addSection();
        $this->addHtmlToSection($introSection, $proposal->getCall()->getIntroText());
        $containers = $introSection->getElements();
        // clone the html block in the template
        $templateProcessor->cloneBlock('callIntroBlock', count($containers), true, true);
        // replace the variables with the elements
        for ($i = 0; $i < count($containers); $i++) {
            // be aware of using setComplexBlock
            // and the $i+1 as the cloned elements start with #1
            $templateProcessor->setComplexBlock('callIntro#' . ($i + 1), $containers[$i]);
        }
    }
}
