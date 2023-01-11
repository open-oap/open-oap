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
use PhpOffice\PhpWord\TemplateProcessor;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
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

        $this->frontendAccessControlService = GeneralUtility::makeInstance(\OpenOAP\OpenOap\Service\FrontendAccessControlService::class);
        $frontendUserId = $this->frontendAccessControlService->getFrontendUserId();
        if ($frontendUserId == null) {
            // todo resend to login
        }
        $this->applicant = $this->applicantRepository->findByUid($frontendUserId);

        $action = $this->request->getArgument('action');
        // todo check notification action cause there is proposal null possible - why?
        $actionsWithProposal = ['delete', 'download', 'downloadAttachments', 'downloadWord', 'edit', 'update'];

        if (isset($actionsWithProposal[$action]) and !$this->request->hasArgument('proposal')) {
            $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_FLASH . self::FLASH_MSG_PROPOSAL_NOT_FOUND);
            $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

            $this->redirectToDashboard();
        }

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
        $groupsMap = [];
        /** @var FormPage $page */
        foreach ($call->getFormPages() as $page) {
            $groupsMap[$page->getUid()] = [];
            /** @var FormGroup $group */
            foreach ($page->getItemGroups() as $groupL0) {
                if (!isset($groupsCounter[$groupL0->getUid()])) {
                    $groupsCounter[$groupL0->getUid()] = [];
                }
                $groupsCounter[$groupL0->getUid()]['current'] = $groupL0->getRepeatableMin();
                $groupsCounter[$groupL0->getUid()]['min'] = $groupL0->getRepeatableMin();
                $groupsCounter[$groupL0->getUid()]['max'] = $groupL0->getRepeatableMax();
                $groupsCounter[$groupL0->getUid()]['instances'] = [];
                $groupsCounter[$groupL0->getUid()]['meta'] = false;

                if ($groupL0->getType() == self::GROUPTYPE_META) {
                    $groupsCounter[$groupL0->getUid()]['meta'] = true;
                    for ($groupCounterL0 = 0; $groupCounterL0 < $groupL0->getRepeatableMin(); $groupCounterL0++) {
                        $groupsCounter[$groupL0->getUid()]['instances'][$groupCounterL0] = [];
                        /** @var FormGroup $groupL1 */
                        foreach ($groupL0->getItemGroups() as $groupL1) {
                            if (!isset(
                                $groupsCounter[$groupL0->getUid()]['instances'][$groupCounterL0][$groupL1->getUid()]
                            )) {
                                $groupsCounter[$groupL0->getUid()]['instances'][$groupCounterL0][$groupL1->getUid(
                                )] = [];
                            }
                            $groupsCounter[$groupL0->getUid()]['instances'][$groupCounterL0][$groupL1->getUid()]['current'] = $groupL1->getRepeatableMin();

                            // create all items for the instances
                            for ($groupCounterL1 = 0; $groupCounterL1 < $groupL1->getRepeatableMin(); $groupCounterL1++) {
                                $this->createAnswersOfGroup($newProposal, $groupL1, $groupCounterL0, $groupCounterL1);
                            }
                        }
                    }
                } else {
                    for ($groupCounterL0 = 0; $groupCounterL0 < $groupL0->getRepeatableMin(); $groupCounterL0++) {
                        $this->createAnswersOfGroup($newProposal, $groupL0, 0, $groupCounterL0);
                    }
                }
            }
            $this->persistenceManager->persistAll();
        }

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
        // current user is not able to edit the proposal cause not the right owner:
        if ($proposal->getApplicant() !== $this->applicant) {
            $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_FLASH . self::FLASH_MSG_PROPOSAL_ACCESS_DENIED);
            $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

            $this->redirectToDashboard();
        }

        if (!$proposal->getCall()) {
            $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_FLASH . self::FLASH_MSG_CALL_MISSING);
            $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

            $this->redirectToDashboard();
        }

        // todo Check if proposal in mode to edit!
        if ($proposal->getState() !== self::PROPOSAL_IN_PROGRESS and $proposal->getState() !== self::PROPOSAL_RE_OPENED) {
            $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_FLASH . self::FLASH_MSG_PROPOSAL_NOT_EDITABLE);
            $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

            $this->redirectToDashboard();
        }

        $itemsMap = [];
        // build linear items array
        $this->flattenItems($proposal);
        $this->flattenAnswers($proposal);
        $updated = false;

        /** @var FormItem $item */
        foreach ($this->items as $item) {
            $itemUid = (int)$item->getUid();

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
                $answer = new Answer($item, $this->groups[$itemUid], 0, (int)$this->settings['answersPoolId']);
                $this->answerRepository->add($answer);
                $proposal->addAnswer($answer);
                $log = $this->createLog(self::LOG_FORM_CHANGED_ADDED_ITEM, $proposal, $answer->getItem()->getQuestion());
                $proposal->addLog($log);
                $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_LOG . self::LOG_FORM_CHANGED_ADDED_ITEM) . ' ' . $log->getText();
                $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
                $updated = true;
            }
        }
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
        $answersMap = $this->createAnswersMap($proposal, $metaInfo->getLimitEditableFields());
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

        $submitItemOptions = [];
        foreach ($proposal->getCall()->getItems() as $item) {
            $this->getOptionsToItemsMap($item, $submitItemOptions);
        }

        $jsMessages = $this->getJsMessages();
        $this->view->assignMultiple([
            'proposal' => $proposal,
            'editState' => $metaInfo->getLimitEditableFields(),
            'itemTypes' => $this->getConstants()['TYPE'],
            'pageTypes' => $this->getConstants()['PAGETYPE'],
            'proposalStates' => $this->getConstants()['PROPOSAL'],
            'commentStates' => $this->getConstants()['COMMENT'],
            'groupDisplayTypes' => $this->getConstants()['GROUPDISPLAY'],
            'answersMap' => $answersMap,
            'pageControl' => $pageControl,
            'itemAnswerMap' => $itemAnswerMap,
            'itemsMap' => $itemsMap,
            'jsMessages' => $jsMessages,
            'validationResults' => $validationResults,
            'pageMap' => $pageMap,
            'groupsCounter' => $groupsCounter,
            'submitItemOptions' => $submitItemOptions,
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

        $flashMessageSaved = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_FLASH . self::FLASH_MSG_SAVED);

        if ($this->request->hasArgument('cancel')) {
            $flashMessage = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_FLASH . self::FLASH_MSG_CANCELLED);
            $this->addFlashMessage($flashMessage, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

            $this->redirectToDashboard();
        }

        if ($this->request->hasArgument('close')) {
            $proposal->setEditTstamp(time());
            $close = true;
        }

        //DebuggerUtility::var_dump($this->request->getArguments());die();
        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (int)$this->request->getArgument('currentPage');
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
//                $itemsMap = [];
//                if ($item->getOptions() !== '') {
//                    $this->getOptionsToItemsMap($item, $itemsMap);
//                }
//                DebuggerUtility::var_dump($itemsMap);
//                die();
                $value = $this->answers[$item->getUid()]->getValue();
                $data = json_decode($value, true);
                if ($data) {
                    $value = implode(',', $data);
                }
                if ($value !== '') {
                    $infos[$item->getUid()] = $value;
                }
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
            // @todo: check submit_accepted
            // how to check, if values of options are not pre-set?
            // editorial guideline: use only yes?
            // how do you can save without check the items?

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
            $this->addFlashMessage($flashMessageSaved, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

            $uri = $this->uriBuilder
                ->reset()
                ->setTargetPageUid((int)$this->settings['dashboardPageId'])
                ->build();
        } else {
            $this->addFlashMessage($flashMessageSaved, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

            $uri = $this->uriBuilder
                ->reset()
                ->setTargetPageUid((int)$this->settings['formPageId'])
                ->uriFor('edit', ['proposal' => $proposal, 'currentPage' => $nextPage], 'Proposal', $this->ext, 'form');
        }
        $this->redirectToURI($uri, 0, 200);
    }

    /**
     * @throws FileDoesNotExistException
     */
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
        $zip = $this->addAttachmentsToZip($proposal, $zip, $zipFile, $createFolder, $absoluteBasePath);

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

        if ($proposal->getCall()->getWordTemplate()) {
            $file = $proposal->getCall()->getWordTemplate()->getOriginalResource()->getOriginalFile();
        } else {
            $file = $storage->getFile($this->settings['wordExportTemplateFile']);
        }

        $targetFileName = $this->getWordFileName($proposal->getUid(), 'merge');
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
        $commentsAtItemsCounter = 0;
        foreach ($answersMap as $answer) {
            $commentsAtItemsCounter += $answer['commentsCounter'];
        }
        $callLogo = $this->getCallLogo($proposal);

        $arguments = [
            'destination' => 'download',
            'filepath' => strtr($proposal->getTitle(), ' ', '-') . '--' . date('Ymd-Hi') . '.pdf',
            'generatedDate' => date('d.m.Y'),
            'generatedTime' => date('H:i'),
            'proposal' => $proposal,
            'commentsAtProposal' => $commentsAtProposal,
            'answers' => $this->answers,
            'answersMap' => $answersMap,
            'itemAnswerMap' => $itemAnswerMap,
            'groupsCounter' => $groupsCounter,
            'settings' => $this->settings,
            'commentsAtItemsCounter' => $commentsAtItemsCounter,
            'callLogo' => $callLogo,

            'itemTypes' => $this->getConstants()['TYPE'],
            'pageTypes' => $this->getConstants()['PAGETYPE'],
            'proposalStates' => $this->getConstants()['PROPOSAL'],
            'commentStates' => $this->getConstants()['COMMENT'],
            'groupDisplayTypes' => $this->getConstants()['GROUPDISPLAY'],
        ];

        $this->renderPdfView(self::EXPORT_TEMPLATE_PATH_PDF, $arguments);

        $this->redirectToDashboard();
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

        $this->redirectToDashboard();
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
                        $cleanedString = preg_replace('~[\n\r\t]~', '', $answer->getValue());
                        if (mb_strlen($cleanedString) > (int)$validator->getParam1()) {
                            $this->setValidationResult(
                                $validationResult,
                                $answer,
                                self::XLF_BASE_IDENTIFIER_JSMSG . 'max_len_exceeded'
                            );
                        }
                        break;
                    case self::VALIDATOR_MINVALUE:
                        if ($answer->getValue() == '') {
                            break;
                        }
                        if ($item->getType() == self::TYPE_DATE1 or $item->getType() == self::TYPE_DATE2) {
                            $minDate = new DateTime($validator->getParam1());
                            $valueDate = new DateTime($answer->getValue());
//                            die();
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
                        if ($item->getType() == self::TYPE_DATE1 or $item->getType() == self::TYPE_DATE2) {
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
                        if ($answer->getValue() !== '') {
                            // values are formatted with dots - remove for validation
                            $cleanupValue = preg_replace('~\.~', '', $answer->getValue());
                            if ($this->integerValidator->validate($cleanupValue)->hasErrors()) {
                                $this->setValidationResult($validationResult, $answer, self::XLF_BASE_IDENTIFIER_JSMSG . 'is_not_an_integer');
                            }
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
     * @param Proposal $proposal
     * @param FormGroup $group
     * @param int $groupCounterL0
     * @param int $groupCounterL1
     * @throws IllegalObjectTypeException
     */
    protected function createAnswersOfGroup(Proposal $proposal, FormGroup $group, int $groupCounterL0, int $groupCounterL1): void
    {
        /** @var FormItem $item */
        foreach ($group->getItems() as $item) {
            // create new answer object
            $answer = new Answer($item, $group, $groupCounterL0, $groupCounterL1, (int)$this->settings['answersPoolId']);
            // set Default  depends on groupCounter (only first group will get the default value
            // only in level 1 are items
            if ($groupCounterL0 == 0 and $groupCounterL1 == 0) {
                $answerValueDefault = $this->getDefaultValueFromForeignObject($item->getDefaultValue(), $groupCounterL1, $proposal);
                // convert value to array - if item is multiple
                if ($answerValueDefault !== '' and (in_array($item->getType(), [self::TYPE_CHECKBOX, self::TYPE_SELECT_SINGLE, self::TYPE_SELECT_MULTIPLE]))) {
                    $answerValue = [];
                    $answerValue[0] = $answerValueDefault;
                    $answerValueDefault = json_encode($answerValue, JSON_FORCE_OBJECT);
                }
                $answer->setValue($answerValueDefault);
            }
            // todo just for testing
//             $answer->setValue($group->getUid().'--'.$groupCounterL0.'--'.$groupCounterL1.' '.$item->getUid());

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
//        DebuggerUtility::var_dump($proposal->getAnswers(),(string)__LINE__);
        $addGroupValue = $this->request->getArgument('addGroup');
        $addGroupData = json_decode($addGroupValue, true);
        /** @var MetaInformation $metaInfo */
        $metaInfo = new MetaInformation($proposal->getMetaInformation());
        $groupsCounter = $metaInfo->getGroupsCounter();
        /** @var FormPage $formPage */
        foreach ($proposal->getCall()->getFormPages() as $formPage) {
            /** @var FormGroup $itemGroup */
            foreach ($formPage->getItemGroups() as $itemGroup) {
                if ($itemGroup->getUid() == $addGroupData['L0GroupUid']) {
                    if ($addGroupData['L0GroupUid'] == $addGroupData['L1GroupUid']) {
                        // no meta group!
                        // take the number directly (without increment), cause index is starting with 0, but here it is a count
                        $this->createAnswersOfGroup(
                            $proposal,
                            $itemGroup,
                            0,
                            $groupsCounter[$itemGroup->getUid()]['current']
                        );
                        $groupsCounter[$itemGroup->getUid()]['current']++;
                    } elseif ($addGroupData['L1GroupUid'] > 0) {
                        // inside a meta group
                        foreach ($itemGroup->getItemGroups() as $itemGroupL1) {
                            if ($itemGroupL1->getUid() == (int)$addGroupData['L1GroupUid']) {
                                $this->createAnswersOfGroup(
                                    $proposal,
                                    $itemGroupL1,
                                    (int)$addGroupData['L0GroupIndex'],
                                    $groupsCounter[$itemGroup->getUid()]['instances'][$addGroupData['L0GroupIndex']][$itemGroupL1->getUid()]['current']
                                );
                                $groupsCounter[$itemGroup->getUid()]['instances'][$addGroupData['L0GroupIndex']][$itemGroupL1->getUid()]['current']++;
                            }
                        }
                    } else {
                        // just to fit existing function for later refactoring
                        $groupCounterL0 = $groupsCounter[$itemGroup->getUid()]['current'];
                        $groupL0 = $itemGroup;
                        $newProposal = $proposal;

                        // here starts the code from createAction...
                        foreach ($itemGroup->getItemGroups() as $groupL1) {
                            if (!isset(
                                $groupsCounter[$groupL0->getUid()]['instances'][$groupCounterL0][$groupL1->getUid()]
                            )) {
                                $groupsCounter[$groupL0->getUid()]['instances'][$groupCounterL0][$groupL1->getUid(
                                )] = [];
                            }
                            $groupsCounter[$groupL0->getUid()]['instances'][$groupCounterL0][$groupL1->getUid()]['current'] = $groupL1->getRepeatableMin();

                            // create all items for the instances
                            for ($groupCounterL1 = 0; $groupCounterL1 < $groupL1->getRepeatableMin(); $groupCounterL1++) {
                                $this->createAnswersOfGroup($newProposal, $groupL1, $groupCounterL0, $groupCounterL1);
                            }
                        }
                        $groupsCounter[$itemGroup->getUid()]['current']++;
                    }

                    $metaInfo->setGroupsCounter($groupsCounter);
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
        $groupsCounter = $metaInfo->getGroupsCounter();

        // unested group upper level
        if ($removeParameter->L1GroupUid == ''
            and (
                count($groupsCounter[$removeParameter->L0GroupUid]['instances']) == 0
                or
                !isset(
                    $groupsCounter[$removeParameter->L0GroupUid]['instances']
                )
            )
        ) {
            // remove none nested group from uppper level
            $groupsCounter[$removeParameter->L0GroupUid]['current']--;
            $removeType = 'unnested_group_level_0';
            foreach (clone $proposal->getAnswers() as $answer) {
                if ($answer->getModel()->getUid() == $removeParameter->L0GroupUid and
                    $answer->getGroupCounter1() == $removeParameter->L0GroupIndex) {
                    $proposal->removeAnswer($answer);
                }
                if ($answer->getModel()->getUid() == $removeParameter->L0GroupUid and
                    $answer->getGroupCounter1() > $removeParameter->L0GroupIndex) {
                    $newGroupIndex = $answer->getGroupCounter1() - 1;
                    $answer->setGroupCounter1($newGroupIndex);
                }
            }
        }

        if ($removeParameter->L1GroupUid > 0 and $removeParameter->L0GroupUid > 0) {
            $groupsCounter[$removeParameter->L0GroupUid]['instances'][$removeParameter->L0GroupIndex][$removeParameter->L1GroupUid]['current']--;
            foreach (clone $proposal->getAnswers() as $answer) {
                if ($answer->getModel()->getUid() == $removeParameter->L1GroupUid
                    and $answer->getGroupCounter1() == $removeParameter->L1GroupIndex
                        and $answer->getGroupCounter0() == $removeParameter->L0GroupIndex
                ) {
                    $proposal->removeAnswer($answer);
                }
                if ($answer->getModel()->getUid() == $removeParameter->L1GroupUid
                    and $answer->getGroupCounter1() > $removeParameter->L1GroupIndex
                        and $answer->getGroupCounter0() == $removeParameter->L0GroupIndex
                ) {
                    $newGroupIndex = $answer->getGroupCounter1() - 1;
                    $answer->setGroupCounter1($newGroupIndex);
                }
            }
        }

        // remove nested groups upper level
        if ($removeParameter->L1GroupUid == ''
            and
            count($groupsCounter[$removeParameter->L0GroupUid]['instances']) > 0
        ) {
            // remove nested group from upper level
            $groupsCounter[$removeParameter->L0GroupUid]['current']--;

            $newInstances = [];
            foreach ($groupsCounter[$removeParameter->L0GroupUid]['instances'] as $nestedIndex => $nestedGroups) {
                if ($nestedIndex < $removeParameter->L0GroupIndex) {
                    $newInstances[] = $nestedGroups;
                }
                if ($nestedIndex == $removeParameter->L0GroupIndex) {
                    foreach ($nestedGroups as $nestedGroupUid => $nestedData) {
                        foreach (clone $proposal->getAnswers() as $answer) {
                            if ($answer->getModel()->getUid() == $nestedGroupUid and
                                $answer->getGroupCounter1() >= $removeParameter->L1GroupIndex and
                                $answer->getGroupCounter0() == $nestedIndex) {
                                $proposal->removeAnswer($answer);
                            }
                        }
                    }
                }
                if ($nestedIndex > $removeParameter->L0GroupIndex) {
                    foreach ($nestedGroups as $nestedGroupUid => $nestedData) {
                        foreach (clone $proposal->getAnswers() as $answer) {
                            if ($answer->getModel()->getUid() == $nestedGroupUid and
                                $answer->getGroupCounter1() >= $removeParameter->L1GroupIndex and
                                $answer->getGroupCounter0() == $nestedIndex) {
                                $newGroupIndex = $answer->getGroupCounter0() - 1;
                                $answer->setGroupCounter0($newGroupIndex);
                            }
                        }
                    }

                    $newInstances[] = $nestedGroups;
                }
            }
            $groupsCounter[$removeParameter->L0GroupUid]['instances'] = $newInstances;
        }

        $metaInfo->setGroupsCounter($groupsCounter);
        $proposal->setMetaInformation($metaInfo->jsonSerialize());
    }

    /**
     * @param Proposal $proposal
     */
    protected function updateAnswersForRemovedItems(Proposal &$proposal): void
    {
        foreach (clone $proposal->getAnswers() as $answer) {
            if (!$answer->getItem()) {
                continue;
            }
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
}
