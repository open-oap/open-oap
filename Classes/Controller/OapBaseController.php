<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use DOMNode;
use OpenOAP\OpenOap\Domain\Model\Answer;
use OpenOAP\OpenOap\Domain\Model\Applicant;
use OpenOAP\OpenOap\Domain\Model\Call;
use OpenOAP\OpenOap\Domain\Model\Comment;
use OpenOAP\OpenOap\Domain\Model\FormGroup;
use OpenOAP\OpenOap\Domain\Model\FormItem;
use OpenOAP\OpenOap\Domain\Model\FormPage;
use OpenOAP\OpenOap\Domain\Model\GroupTitle;
use OpenOAP\OpenOap\Domain\Model\ItemOption;
use OpenOAP\OpenOap\Domain\Model\ItemValidator;
use OpenOAP\OpenOap\Domain\Model\MetaInformation;
use OpenOAP\OpenOap\Domain\Model\Proposal;
use OpenOAP\OpenOap\Domain\Repository\AnswerRepository;
use OpenOAP\OpenOap\Domain\Repository\ApplicantRepository;
use OpenOAP\OpenOap\Domain\Repository\CallRepository;
use OpenOAP\OpenOap\Domain\Repository\CommentRepository;
use OpenOAP\OpenOap\Domain\Repository\FormItemRepository;
use OpenOAP\OpenOap\Domain\Repository\ItemOptionRepository;
use OpenOAP\OpenOap\Domain\Repository\ProposalRepository;

use OpenOAP\OpenOapUsers\Domain\Model\User;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\DocProtect;
use ReflectionClass;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use ZipArchive;

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
class OapBaseController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected string $messageFile = 'locallang_backend.xlf';

    protected string $locallangFile = 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf';

    protected string $messageSource = '';

    protected const MANDATORY_SIGN = '*';

    protected const PLUGIN_FORM = 'tx_openoap_form';

    protected const MAX_STR_LENGTH_ITEM_ERROR = 40;
    /**
     * @var string
     */
    public static string $defaultUploadFolder = '1:/user_upload/';

    /**
     * Const
     * see TCA: tx_openoap_domain_model_proposal.php
     */
    protected const PROPOSAL_IN_PROGRESS = 1;
    protected const PROPOSAL_SUBMITTED = 2;
//    protected const PROPOSAL_PROCESSING = 3;
    protected const PROPOSAL_RE_OPENED = 4;
    protected const PROPOSAL_ACCEPTED = 5;
    protected const PROPOSAL_DECLINED = 6;

    // Editstate - saved in metaInforamtion of proposal
    protected const META_PROPOSAL_EDITABLE_FIELDS_NO_LIMIT = 0;
    protected const META_PROPOSAL_EDITABLE_FIELDS_LIMIT = 1;

    /**
     * see TCA: tx_openoap_domain_model_formpage.php
     */
    protected const PAGETYPE_DEFAULT = 0;
    protected const PAGETYPE_PREVIEW = 1;

    /**
     * see TCA: tx_openoap_domain_model_formitem.php
     */
    protected const TYPE_STRING = 1;
    protected const TYPE_TEXT = 2;
    protected const TYPE_DATE1 = 3;
    protected const TYPE_DATE2 = 4;
    protected const TYPE_CHECKBOX = 5;
    protected const TYPE_RADIOBUTTON = 6;
    protected const TYPE_SELECT_SINGLE = 7;
    protected const TYPE_SELECT_MULTIPLE = 8;
    protected const TYPE_UPLOAD = 9;

    /**
     * see TCA: tx_openoap_domain_model_comment.php
     */
    protected const COMMENT_STATE_NEW = 0;
    protected const COMMENT_STATE_ACCEPTED = 1;
    protected const COMMENT_STATE_AUTO_ACCEPTED = 2;
    protected const COMMENT_STATE_ARCHIVED = 3;

    /**
     * see TCA: tx_openoap_domain_model_comment.php
     */
    protected const COMMENT_SOURCE_AUTO = 1;
    protected const COMMENT_SOURCE_EDIT = 2;
    protected const COMMENT_SOURCE_EDIT_ANSWER = 3;

    /**
     * see TCA
     */
    protected const OPTION_TYPE_UNDEFINED = 0;
    protected const OPTION_TYPE_SINGLE = 1;
    protected const OPTION_TYPE_MULTIPLE = 2;
    protected const OPTION_TYPE_FUNCTION = 3;

    /**
     * see TCA: - tx_openoap_domain_model_itemvalidator.php
     */
    protected const VALIDATOR_MANDATORY = 1;
    protected const VALIDATOR_INTEGER = 2;
    protected const VALIDATOR_FLOAT = 3;
    protected const VALIDATOR_MAXCHAR = 4;
    protected const VALIDATOR_MINVALUE = 5;
    protected const VALIDATOR_MAXVALUE = 6;
    protected const VALIDATOR_EMAIL = 7;
    protected const VALIDATOR_WEBSITE = 8;
    protected const VALIDATOR_PHONE = 9;
    protected const VALIDATOR_FILE_TYPE = 10;
    protected const VALIDATOR_FILE_SIZE = 11;

    /**
     * EMAIL TEMPLATES
     */
    protected const MAIL_TEMPLATE_PROPOSAL_SUBMIT = 'Email/EmailSubmit';
    protected const MAIL_TEMPLATE_PROPOSAL_REVISION = 'Email/EmailRevision';
    protected const MAIL_TEMPLATE_PROPOSAL_STATUS = 'Email/EmailStatus';

    /**
     * EXPORT TEMPLATES
     */
    protected const EXPORT_TEMPLATE_PATH_PDF = 'EXT:open_oap/Resources/Private/Templates/Pdf/Proposal/Download.html';

    protected const MAX_FOR_LINE_BY_LINE_OUTPUT = 10;

    /**
     * CODES FOR LOGGING
     */
    protected const LOG_PROPOSAL_CREATE = 100;
    protected const LOG_PROPOSAL_CHANGE_STATE = 101;
    protected const LOG_PROPOSAL_SUBMITTED = 102;
    //   protected const LOG_PROPOSAL_PROCESSING = 103;
    protected const LOG_PROPOSAL_RE_OPENED = 104;
    protected const LOG_PROPOSAL_ACCEPTED = 105;
    protected const LOG_PROPOSAL_DECLINED = 106;
    protected const LOG_PROPOSAL_CHANGED_TITLE = 107;
    protected const LOG_PROPOSAL_ANNOTATED = 108;
    protected const LOG_PROPOSAL_DELETE = 109;

    protected const LOG_FORM_CHANGED = 200;
    protected const LOG_FORM_CHANGED_ADDED_ITEM = 201;
    protected const LOG_FORM_CHANGED_REMOVED_ITEM = 202;

    // see \TYPO3\CMS\Core\Messaging\AbstractMessage - just to shorten the vars
    protected const NOTICE = -2;
    protected const INFO = -1;
    protected const OK = 0;
    protected const WARNING = 1;
    protected const ERROR = 2;

    protected const STATE_MIN = 0;
    protected const STATE_MAX = 9;
    protected const STATE_ACCESS_MIN = 2;
    protected const STATE_SKIP = 3;

    protected const UPLOAD_MAX_DEFAULT = 10;
    protected const UPLOAD_MAXSIZE_DEFAULT = 1048576;  // 1MB

    /**
     * identifier for js-text - see open_oap/Resources/Private/Language/locallang.xlf
     */
    protected const XLF_BASE_IDENTIFIER_JSMSG = 'tx_openoap.js_msg.';

    protected const JSMSG_COMMON_MANDATORY = 'common_mandatory';
    protected const JSMSG_ERROR_LABEL = 'error_label';
    protected const JSMSG_ERROR_MESSAGE = 'error_message';
    protected const JSMSG_CHARACTERS_REMAINING = 'characters_remaining';
    protected const JSMSG_MANDATORY = 'mandatory';
    protected const JSMSG_MAX_VALUE = 'max_value';
    protected const JSMSG_MIN_VALUE = 'min_value';
    protected const JSMSG_MIN_SELECTED = 'min_selected';
    protected const JSMSG_MAX_SELECTED = 'max_selected';
    protected const JSMSG_INVALID_EMAIL = 'invalid_email';
    protected const JSMSG_INVALID_WEBADDRESS = 'invalid_webaddress';
    protected const JSMSG_INVALID_PHONE = 'invalid_phone';
    protected const JSMSG_INVALID_FORMAT = 'invalid_format';
    protected const JSMSG_MAX_LEN_EXCEEDED = 'max_len_exceeded';
    protected const JSMSG_NO_INTEGER = 'is_not_an_integer';
    protected const JSMSG_NO_FLOAT = 'is_not_a_float';
    protected const JSMSG_MODAL_CONTENT = 'modal_content';
    protected const JSMSG_MODAL_KEEPEDITING = 'modal_keepediting';
    protected const JSMSG_MODAL_SAVECLOSE = 'modal_saveclose';
    protected const JSMSG_MODAL_SAVE = 'modal_save';
    protected const JSMSG_MODAL_CANCELWARNING = 'modal_warning_hint';
    protected const JSMSG_MODAL_CANCEL = 'modal_cancel';
    protected const JSMSG_MODAL_CONTENT_DELETE = 'modal_content_delete';
    protected const JSMSG_MODAL_DELETE = 'modal_delete';
    protected const JSMSG_MODAL_DELETE_GROUP = 'modal_group_delete';
    protected const JSMSG_UPLOAD_ERROR_TOO_MANY_FILES = 'upload_error_too_many_files';
    protected const JSMSG_UPLOAD_ERROR_FILE_SIZE = 'upload_error_file_size';
    protected const JSMSG_UPLOAD_ERROR_FILE_SIZE_SIMPLE = 'upload_error_file_size_simple';
    protected const JSMSG_UPLOAD_FILE_REMOVED = 'upload_file_removed_not_saved';
    protected const JSMSG_UPLOAD_UPLOADED = 'upload_files_uploaded';
    protected const JSMSG_UPLOAD_MAX_FILES_REACHED = 'upload_max_files_reached';

    protected const FORM_CLASS_BASE_TEXTFIELD = 'form__textfield--';

    protected const XLF_BASE_IDENTIFIER_DEFAULTS = 'tx_openoap.default';
    protected const DEFAULT_TITLE = 'default_title';
    protected const SIGNATURE_DIVIDER = '_';
    protected const KEY_VALUE_DIVIDER = ';';
    protected const DATE_FORMAT_JS = 'dd.mm.yyyy';
    protected const DATE_FORMAT = 'd.m.Y';
    protected const FLOAT_FORMAT_DECIMALS = 2;
    protected const FLOAT_FORMAT_DECIMAL_SEPARATOR = ',';
    protected const FLOAT_FORMAT_THOUSANDS_SEPARATOR = '.';

    protected const XLF_BASE_IDENTIFIER_FLASH = 'tx_openoap.flash.';
    protected const XLF_BASE_IDENTIFIER_LOG = 'tx_openoap_domain_model_proposal.log.';
    protected const FLASH_MSG_SUBMITTED_OKAY = 'proposal_successfully_submitted';
    protected const FLASH_MSG_DELETED_OKAY = 'proposal_successfully_deleted';

    /**
     * @var Site|null
     */
    protected ?Site $site = null;

    /**
     * @var SiteLanguage|null
     */
    protected ?SiteLanguage $language = null;

    /**
     * @var string
     */
    protected string $langCode = '';

    /**
     * @var ApplicantRepository|null
     */
    protected ?ApplicantRepository $applicantRepository = null;

    /**
     * @var CallRepository|null
     */
    protected ?CallRepository $callRepository = null;

    /**
     * @var CommentRepository|null
     */
    protected ?CommentRepository $commentRepository = null;

    /**
     * @var ProposalRepository|null
     */
    protected ?ProposalRepository $proposalRepository = null;

    /**
     * @var AnswerRepository|null
     */
    protected ?AnswerRepository $answerRepository = null;

    /**
     * @var FormItemRepository|null
     */
    protected ?FormItemRepository $formItemRepository = null;

    /**
     * @var FormItemRepository|null
     */
    protected ?ItemOptionRepository $itemOptionRepository = null;

    /**
     * @var PersistenceManager|null
     */
    protected ?PersistenceManager $persistenceManager = null;

    /**
     * @var ModuleTemplateFactory|null
     */
    protected ?ModuleTemplateFactory $moduleTemplateFactory = null;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var array
     */
    protected array $answers = [];

    /**
     * @param ApplicantRepository $applicantRepository
     * @param CallRepository $callRepository
     * @param CommentRepository $commentRepository
     * @param ProposalRepository $proposalRepository
     * @param AnswerRepository $answerRepository
     * @param FormItemRepository $formItemRepository
     * @param ItemOptionRepository $itemOptionRepository
     * @param PersistenceManager $persistenceManager
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function __construct(
        ApplicantRepository $applicantRepository,
        CallRepository $callRepository,
        CommentRepository $commentRepository,
        ProposalRepository $proposalRepository,
        AnswerRepository $answerRepository,
        FormItemRepository $formItemRepository,
        ItemOptionRepository $itemOptionRepository,
        PersistenceManager $persistenceManager
    ) {
        $this->applicantRepository = $applicantRepository;
        $this->callRepository = $callRepository;
        $this->commentRepository = $commentRepository;
        $this->proposalRepository = $proposalRepository;
        $this->answerRepository = $answerRepository;
        $this->formItemRepository = $formItemRepository;
        $this->itemOptionRepository = $itemOptionRepository;
        $this->persistenceManager = $persistenceManager;

        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * Returns the language service
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @param Proposal $proposal
     */
    protected function flattenAnswers(Proposal $proposal): void
    {
        // hashMap for answers
        /** @var Answer $answer */
        foreach ($proposal->getAnswers() as $answer) {
            if (!$answer->getItem()) {
                DebuggerUtility::var_dump($answer, 'this Item isn´t there - but we have an answer for that');
                continue;
            }
            $this->answers[$answer->getItem()->getUid()] = $answer;
        }
    }

    /**
     * @param Proposal $proposal
     */
    protected function flattenItems(Proposal $proposal): void
    {
        /** @var FormPage $page */
        $pageCounter = 1;
        foreach ($proposal->getCall()->getFormPages() as $page) {
            /** @var FormGroup $group */
            foreach ($page->getItemGroups() as $group) {
                /** @var FormItem $item */
                foreach ($group->getItems() as $item) {
                    $this->items[$item->getUid()] = $item;
                    $this->pages[$item->getUid()] = $page;
                    $this->pageNumber[$item->getUid()] = $pageCounter;
                    $this->groups[$item->getUid()] = $group;
                }
            }
            $pageCounter++;
        }
    }

    /**
     * @param Proposal $proposal
     * @return array
     */
    protected function buildItemAnswerMap(Proposal $proposal): array
    {
        // build an mapArray for answers and questions
        $itemAnswerMap = [];
        /**
         * @var int $counter
         * @var Answer $answer
         */
        $answerPointer = 0;
        foreach ($proposal->getAnswers() as $answer) {
            if ($answer->getItem() and $answer->getModel()) {
                $itemAnswerMap[$answer->getModel()->getUid() .
                               '--' .
                               $answer->getElementCounter() .
                               '--' .
                               $answer->getItem()->getUid()] = $answerPointer;
            }
            $answerPointer++;
        }
        return $itemAnswerMap;
    }

    /**
     * Create auto comment on a proposal
     *
     * @param int $logCode
     * @param Proposal|null $proposal
     * @param string $parameter
     * @return Comment $comment
     */
    protected function createLog(int $logCode, Proposal $proposal=null, string $parameter = ''): Comment
    {
        $log = new Comment();
        ObjectAccess::setProperty($log, 'pid', (integer)$this->settings['commentsPoolId']);
        $log->setSource(self::COMMENT_SOURCE_AUTO);
        $log->setState(self::COMMENT_STATE_NEW);
        $log->setCreated(time());
        $log->setCode($logCode);
        if ($proposal != null) {
            $log->setProposal($proposal);
        }
        switch ($logCode) {
            case self::LOG_PROPOSAL_CREATE:
            case self::LOG_PROPOSAL_SUBMITTED:
            case self::LOG_PROPOSAL_DELETE:
                $log->setState(self::COMMENT_STATE_AUTO_ACCEPTED);
                break;
            case self::LOG_PROPOSAL_CHANGE_STATE:
                $log->setText($parameter);
                break;
            case self::LOG_PROPOSAL_ANNOTATED:
            case self::LOG_PROPOSAL_CHANGED_TITLE:
            case self::LOG_FORM_CHANGED:
            case self::LOG_FORM_CHANGED_ADDED_ITEM:
            case self::LOG_FORM_CHANGED_REMOVED_ITEM:
                $log->setState(self::COMMENT_STATE_AUTO_ACCEPTED);
                $log->setText($parameter);
                break;
        }
        return $log;
    }

    /**
     * Parse the mailtext
     *
     * @param Proposal $proposal
     * @param string $mailtext
     * @param array $additionalData
     * @param string|null $langCode
     * @return string Parsed mailtext
     */
    protected function parseMailtext(Proposal $proposal, string $mailtext, array $additionalData=[], $langCode=null): String
    {
        $applicant = $proposal->getApplicant();
        $applicantName = '';
        $applicantTitle = '';
        if ($applicant->getSalutation() > 0) {
            $applicantTitle = LocalizationUtility::translate($this->locallangFile . ':tx_openoap_domain_model_applicant.salutation.item' . $applicant->getSalutation(), 'openOap', [], $langCode);
        }
        $applicantName = $this->getApplicantName($applicant);
        $data = [
            '##APPLICANT-TITLE##' => $applicantTitle,
            '##APPLICANT-NAME##' => $applicantName,
            '##CALL-TITLE##' => $proposal->getCall()->getTitle(),
            '##PROPOSAL-SIGNATURE##' => $this->buildSignature($proposal),
            '##PROPOSAL-TITLE##' => $proposal->getTitle(),
        ];
        if (count($additionalData)>0) {
            foreach ($additionalData as $key => $value) {
                $data[$key] = $value;
            }
        }
        return str_replace(array_keys($data), array_values($data), $mailtext);
    }

    /**
     * Send email
     *
     * @param Proposal $proposal
     * @param TemplatePaths $templatePaths
     * @param string $template
     * @param string $mailtext
     * @param string|null $langCode
     * @param array $additionalData
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    protected function sendEmail(Proposal $proposal, TemplatePaths $templatePaths, string $template, string $mailtext='', $langCode=null, $additionalData=[]): void
    {
        /** @var Applicant $user */
        $user = $proposal->getApplicant();
        $emailTo = $user->getEmail();
        $data = ['proposal' => $proposal, 'signature' => $this->buildSignature($proposal), 'mailtext' => $mailtext, 'siteName' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'], 'languageKey' => $langCode];

        $email = GeneralUtility::makeInstance(FluidEmail::class, $templatePaths);
        $email
                ->to($emailTo)
                ->from(new Address($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'], $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']))
                ->setTemplate($template)
                ->assignMultiple($data)
                ->format('both'); // send HTML and plaintext mail

        if (isset($additionalData['cc'])) {
            $cc = GeneralUtility::trimExplode(',', $additionalData['cc'], true);
            $email->cc(...$cc);
        }
        if (isset($additionalData['replyTo'])) {
            $email->replyTo($additionalData['replyTo']);
        }

        $mailer = GeneralUtility::makeInstance(Mailer::class);
        // @todo: catch if sending failed - add flashMessage
        $mailer->send($email);
    }

    /**
     * Get the class constants
     *
     * @return array The constants
     */
    protected function getConstants(): array
    {
        $reflectionClass = new ReflectionClass($this);

        $constants = [];
        $section = null;
        foreach ($reflectionClass->getConstants() as $key=>$value) {
            if ($section == null || GeneralUtility::trimExplode('_', $key)[0] != $section) {
                $section = GeneralUtility::trimExplode('_', $key)[0];
            }
            $constants[$section][$key] = $value;
        }
        return $constants;
    }

    /**
     * @param string $optionItem
     * @return array|string|string[]
     */
    protected function cleanupOptionItem(string $optionItemStr)
    {
        // initialize Item
        $optionItem = [];
//        $optionItem['key'] = '';
//        $optionItem['label'] = '';

        // cleanup raw string
        $optionItemStr = trim($optionItemStr);
        $optionItemStr = str_replace('"', "'", $optionItemStr);

        $optionParts = GeneralUtility::trimExplode(self::KEY_VALUE_DIVIDER, $optionItemStr, true);

        $optionItem['key'] = $optionParts[0];
        if (count($optionParts) == 1) {
            $optionItem['label'] = $optionParts[0];
        } else {
            $optionItem['label'] = $optionParts[1];
        }
        return $optionItem;
    }

    /**
     * @param FormItem $item
     * @param array $itemsMap
     */
    protected function getOptionsToItemsMap(FormItem $item, array &$itemsMap, $mode = 'add'): void
    {
        $options = [];
        /** @var ItemOption $optionItem */
        foreach ($item->getOptions() as $optionItem) {
            $convertLabel = false;
            if ($item->getType() !== self::TYPE_SELECT_MULTIPLE and $item->getType() !== self::TYPE_SELECT_SINGLE) {
                $convertLabel = true;
            }

            switch ($optionItem->getType()) {
                case self::OPTION_TYPE_SINGLE:
                    $itemOption = $this->cleanupOptionItem($optionItem->getOptions());
                    if ($convertLabel) {
                        $itemOption['label'] = str_replace('|', '<br>', $itemOption['label']);
                    }
                    if ($itemOption['label'] !== '' and $itemOption['value'] !== '') {
                        $options[] = $itemOption;
                    }
                    break;
                case self::OPTION_TYPE_MULTIPLE:
                    $itemOptionsArray = explode("\r\n", $optionItem->getOptions());
                    foreach ($itemOptionsArray as $itemOption) {
                        $itemOption = $this->cleanupOptionItem($itemOption);
                        if ($convertLabel) {
                            $itemOption['label'] = str_replace('|', '<br>', $itemOption['label']);
                        }
                        if ($itemOption['label'] !== '' and $itemOption['value'] !== '') {
                            $options[] = $itemOption;
                        }
                    }
                    break;
                case self::OPTION_TYPE_FUNCTION:
                    break;
            }
        }
        if ($item->isAdditionalValue() and $convertLabel) {
            $options[] = $this->cleanupOptionItem($item->getAdditionalLabel());
        }
        if ($mode == 'add') {
            $itemsMap[$item->getUid()]['options'] = $options;
        } else {
            $itemsMap = $options;
        }
    }

    /**
     * @param string $constClass
     * @return int[]|string[]
     */
    protected function convertConstantsIntoString(string $constClass)
    {
        $constArray = $this->getConstants()[$constClass];
        return array_flip($constArray);
    }

    /**
     * @param FormItem $item
     * @return array
     */
    protected function initializeItemsMap(FormItem $item): array
    {
        $initializedItem = [];
        $initializedItem['MSign'] = '';
        $initializedItem['Mandatory'] = 0;
        $initializedItem['MaxChar'] = '';
        $initializedItem['additionalAttributes'] = [];
        $initializedItem['options'] = [];
        $initializedItem['classes'] = [];
        $initializedItem['classStr'] = '';
        return $initializedItem;
    }

    /**
     * @param FormItem $item
     * @param array $itemsMap
     */
    protected function getValidatorsToItemsMap($item, array &$itemsMap): void
    {
        $itemUid = $item->getUid();
        $validationCodes = [];

        // default validations - based upon item type
        switch ($item->getType()) {
            case self::TYPE_TEXT:
                $itemsMap[$itemUid]['MaxChar'] = $this->settings['defaultMaxCharTextarea'];
                // $validationCodes[] = $this->convertConstantsIntoString('VALIDATOR')[self::VALIDATOR_MAXCHAR];
                $itemsMap[$itemUid]['additionalAttributes']['data-oap-maxlength'] = $this->settings['defaultMaxCharTextarea'];
                break;
            case self::TYPE_STRING:
                $itemsMap[$itemUid]['MaxChar'] = $this->settings['defaultMaxCharTextfield'];
                // $validationCodes[] = $this->convertConstantsIntoString('VALIDATOR')[self::VALIDATOR_MAXCHAR];
                $itemsMap[$itemUid]['additionalAttributes']['data-oap-maxlength'] = $this->settings['defaultMaxCharTextfield'];
                $itemsMap[$itemUid]['type'] = 'text'; // default type for string inputs - will be/can be overwrittten by validators
                if ($item->getUnit()) {
                    $itemsMap[$itemUid]['classes'][] = self::FORM_CLASS_BASE_TEXTFIELD . 'short';
                }
                break;
            case self::TYPE_DATE1:
            case self::TYPE_DATE2:
                $itemsMap[$itemUid]['type'] = 'text';
                $datePickerOptions = [];
                $datePickerOptions['format'] = self::DATE_FORMAT_JS;
                $datePickerOptions['language'] = $this->langCode;
                $datePickerOptions['startView'] = 2; // TODO get from setting
                $datePickerOptions['allowOneSidedRange'] = true; // allowed singe input
                $itemsMap[$itemUid]['classes'][] = self::FORM_CLASS_BASE_TEXTFIELD . 'short';
                break;
            case self::TYPE_SELECT_SINGLE:
                $itemsMap[$itemUid]['additionalAttributes']['data-oap-maxvalue'] = 1;

                // no break
            case self::TYPE_SELECT_MULTIPLE:
                $itemsMap[$itemUid]['type'] = 'select';

                $dualListboxOptions = [];
                $dualListboxOptions['availableTitle'] = '';
                $dualListboxOptions['selectedTitle'] = '';
                $dualListboxOptions['addButtonText'] = '>';
                $dualListboxOptions['removeButtonText'] = '<';
                $dualListboxOptions['addAllButtonText'] = '';
                $dualListboxOptions['removeAllButtonText'] = '';
                $dualListboxOptions['add_button'] = '';

                break;
            case self::TYPE_UPLOAD:
                // initial
                $itemsMap[$itemUid]['MaxValue'] = self::UPLOAD_MAX_DEFAULT;
                $itemsMap[$itemUid]['MaxSize'] = self::UPLOAD_MAXSIZE_DEFAULT;
                $itemsMap[$itemUid]['additionalAttributesFileTypes']['accept'] = '';
                $acceptItems = [];
                break;
        }

        /** @var ItemValidator $validator */
        foreach ($item->getValidators() as $validator) {
            if ($validator->getType()) {
                $validationCodes[] = $this->convertConstantsIntoString('VALIDATOR')[$validator->getType()];
            }
            switch ($validator->getType()) {
                case self::VALIDATOR_MANDATORY:
                    $itemsMap[$itemUid]['MSign'] = self::MANDATORY_SIGN;
                    $itemsMap[$itemUid]['Mandatory'] = 1;
                    break;
                case self::VALIDATOR_MAXCHAR:
                    $itemsMap[$itemUid]['MaxChar'] = $validator->getParam1();
                    $itemsMap[$itemUid]['additionalAttributes']['data-oap-maxlength'] = $validator->getParam1();
                    break;
                case self::VALIDATOR_MINVALUE:
                    $minValue = $validator->getParam1();

                    if ($item->getType() == self::TYPE_DATE1 or $item->getType() == self::TYPE_DATE2) {
                        $minValue = $this->convertIntoDate($minValue);
                        $datePickerOptions['minDate'] = $minValue;
                    }
                    $itemsMap[$itemUid]['MinValue'] = $minValue;
                    $itemsMap[$itemUid]['additionalAttributes']['data-oap-minvalue'] = $minValue;
                    break;
                case self::VALIDATOR_MAXVALUE:
                    $maxValue = $validator->getParam1();

                    if ($item->getType() == self::TYPE_DATE1 or $item->getType() == self::TYPE_DATE2) {
                        $maxValue = $this->convertIntoDate($validator->getParam1());
                        $datePickerOptions['maxDate'] = $maxValue;
                    }
                    $itemsMap[$itemUid]['MaxValue'] = $maxValue;
                    $itemsMap[$itemUid]['additionalAttributes']['data-oap-maxvalue'] = $maxValue;
                    break;
                case self::VALIDATOR_INTEGER:
                case self::VALIDATOR_FLOAT:
                    // do not use type = number, because dots and commas may be used differently than expected.
                    // $itemsMap[$itemUid]['type'] = 'number';
                    $itemsMap[$itemUid]['classes'][] = self::FORM_CLASS_BASE_TEXTFIELD . 'number';
                    break;
                case self::VALIDATOR_EMAIL:
                    $itemsMap[$itemUid]['type'] = 'email';
                    break;
                case self::VALIDATOR_WEBSITE:
                    $itemsMap[$itemUid]['type'] = 'url';
                    break;
                case self::VALIDATOR_PHONE:
                    $itemsMap[$itemUid]['type'] = 'tel';
                    break;
                case self::VALIDATOR_FILE_TYPE:
                    $acceptItems[] = $validator->getParam1();
                    break;
                case self::VALIDATOR_FILE_SIZE:
                    $itemsMap[$itemUid]['MaxSize'] = self::convertStrToBytes($validator->getParam1());
                    break;
            }
        }
        // sum of validations
        $itemsMap[$itemUid]['additionalAttributes']['data-oap-validat'] = implode(',', $validationCodes);
        $type = $this->convertConstantsIntoString('TYPE')[$item->getType()];
        $itemsMap[$itemUid]['additionalAttributes']['data-oap-type'] = $type;

        // date
        if ($item->getType() == self::TYPE_DATE1) {
            $itemsMap[$itemUid]['additionalAttributes']['data-oap-datepicker'] = json_encode($datePickerOptions, JSON_FORCE_OBJECT);
            $itemsMap[$itemUid]['additionalAttributes']['autocomplete'] = 'off';
        }
        if ($item->getType() == self::TYPE_DATE2) {
            $itemsMap[$itemUid]['dateRangeOptions'] = 'data-oap-daterangepicker=\'' . json_encode($datePickerOptions, JSON_FORCE_OBJECT) . '\'';
            $itemsMap[$itemUid]['additionalAttributes']['autocomplete'] = 'off';
        }

        if ($item->getType() == self::TYPE_SELECT_SINGLE) {
            // maxValue for singleSelect = 1
            $itemsMap[$itemUid]['MaxValue'] = 1;
        }

        if ($item->getType() == self::TYPE_SELECT_SINGLE or $item->getType() == self::TYPE_SELECT_MULTIPLE) {
            $itemsMap[$itemUid]['additionalAttributes']['data-oap-duallistbox'] = json_encode($dualListboxOptions, JSON_FORCE_OBJECT);
        }

        if ($item->getType() == self::TYPE_UPLOAD) {
//            $dropzoneOptions = ['autoProcessQueue' => 0, 'uploadMultiple' => 1, 'parallelUploads' => 100, 'maxFiles' => 100, 'url' => '/file/post'];
//            $itemsMap[$itemUid]['additionalAttributes']['data-oap-dropzone'] = json_encode($dropzoneOptions, JSON_FORCE_OBJECT);
            $uploadOptions = ['maxSize' => $itemsMap[$itemUid]['MaxSize'], 'maxFiles' => $itemsMap[$itemUid]['MaxValue']];
            // remove redundant validators and overwrite
            $validationTypes = explode(',', $itemsMap[$itemUid]['additionalAttributes']['data-oap-validat']);
            $validationTypes[] = 'VALIDATOR_FILE';
//            if ($itemsMap[$itemUid]['Mandatory']) {
//                $validationTypes[] = 'VALIDATOR_MANDATORY';
//            }
            $itemsMap[$itemUid]['additionalAttributes']['data-oap-validat'] = implode(',', $validationTypes);
//            $itemsMap[$itemUid]['additionalAttributes']['data-oap-validat'] = explode(;
//            if ($itemsMap[$itemUid]['Mandatory']) {
//                $itemsMap[$itemUid]['additionalAttributes']['data-oap-validat'] .=',VALIDATOR_MANDATORY';
//            }
            $itemsMap[$itemUid]['data-oap-upload'] = json_encode($uploadOptions, JSON_FORCE_OBJECT);
            $itemsMap[$itemUid]['additionalAttributes']['data-oap-uploadValidation'] = '';
            $itemsMap[$itemUid]['additionalAttributes']['accept'] = implode(',', $acceptItems);
        }
    }

    protected function getUploadBaseFolder()
    {
        $uploadFolderId = ($this->settings['uploadFolder'] == '') ? self::$defaultUploadFolder
            : $this->settings['uploadFolder'];
        DebuggerUtility::var_dump($uploadFolderId);
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        DebuggerUtility::var_dump($resourceFactory);
        [$storageId, $storagePath] = explode(':', $uploadFolderId, 2);
        $storage = $resourceFactory->getStorageObject($storageId);
        DebuggerUtility::var_dump($storage);
        DebuggerUtility::var_dump($storagePath);
        $folderNames = GeneralUtility::trimExplode('/', $storagePath, true);

        return $folderNames;
    }
    /**
     * Ensures that upload folder exists, creates it if it does not.
     *
     * @param string $uploadFolderIdentifier
     * @param int $applicantUid
     * @param int $proposalUid
     * @return Folder
     */
    public static function provideUploadFolder(string $uploadFolderIdentifier, int $applicantUid, int $proposalUid): Folder
    {
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

        [$storageId, $storagePath] = explode(':', $uploadFolderIdentifier, 2);
        $storage = $resourceFactory->getStorageObject($storageId);
        $folderNames = GeneralUtility::trimExplode('/', $storagePath, true);
        $folderNames[] = (string)$applicantUid;
        $folderNames[] = (string)$proposalUid;
        $uploadFolder = self::provideTargetFolder($storage->getRootLevelFolder(), implode('/', $folderNames));
        self::provideFolderInitialization($uploadFolder);
        return $uploadFolder;
    }

    /**
     * Ensures that particular target folder exists, creates it if it does not.
     *
     * @param Folder $parentFolder
     * @param string $folderName
     * @return Folder
     */
    public static function provideTargetFolder(Folder $parentFolder, string $folderName): Folder
    {
        return $parentFolder->hasFolder($folderName)
            ? $parentFolder->getSubfolder($folderName)
            : $parentFolder->createFolder($folderName);
    }

    /**
     * Creates empty index.html file to avoid directory indexing,
     * in case it does not exist yet.
     *
     * @param Folder $parentFolder
     */
    public static function provideFolderInitialization(Folder $parentFolder): void
    {
        if (!$parentFolder->hasFile('index.html')) {
            $parentFolder->createFile('index.html');
        }
    }

    /**
     * @param string $str
     * @return int
     */
    public static function convertStrToBytes(string $str): int
    {
        $str = preg_replace(['~ ~', '~,~'], ['', '.'], $str);
        $num = (double)$str;
        if (strtoupper(substr($str, -1)) == 'B') {
            $str = substr($str, 0, -1);
        }
        switch (strtoupper(substr($str, -1))) {
            case 'P':  // very optimistic if it is for an upload
                $num *= 1024;
                // no break
            case 'T':  // very optimistic if it is for an upload
                $num *= 1024;
                // no break
            case 'G':  // very optimistic if it is for an upload
                $num *= 1024;
                // no break
            case 'M':
                $num *= 1024;
                // no break
            case 'K':
                $num *= 1024;
        }

        return (integer)round($num);
    }

    /**
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    public function convertFilesizeToHumanReadableBytes(int $bytes, int $decimals = 2): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $factor = floor(log($bytes, 1024));
        return sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)) . ['B', 'KB', 'MB', 'GB', 'TB', 'PB'][$factor];
    }

    /**
     * @param $date
     * @return false|mixed|string
     */
    protected function convertIntoDate($date)
    {
        switch ($date) {
            case '@today':
                $date = date(self::DATE_FORMAT);
                break;
        }
        return $date;
    }

    /**
     * @param Proposal $proposal
     * @return string
     */
    protected function buildSignature(Proposal $proposal): string
    {
        // default value for signature is 0
        if (!$proposal->getSignature() or !$proposal->getCall()) {
            return '';
        }
        $signature = '';
        // use DIVIDER only, if there is a shortcut
        if (trim($proposal->getCall()->getShortcut()) !== '') {
            $signature .=  trim($proposal->getCall()->getShortcut()) . self::SIGNATURE_DIVIDER;
        }
        $signature .=  sprintf($this->settings['signatureFormat'], (integer)$proposal->getSignature());
        return $signature;
    }

    /**
     * @param string $pdfTemplatePath
     * @param array $arguments
     */
    protected function renderPdfView(string $pdfTemplatePath, array $arguments)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $standaloneView = $objectManager->get(StandaloneView::class);
        $templatePath = GeneralUtility::getFileAbsFileName($pdfTemplatePath);
        $partialRootPath = GeneralUtility::getFileAbsFileName('EXT:open_oap/Resources/Private/Partials');

        $standaloneView->setFormat('html');
        $standaloneView->setTemplatePathAndFilename($templatePath);
        $standaloneView->setPartialRootPaths([$partialRootPath]);
        $standaloneView->assignMultiple($arguments);

        $pdf = $standaloneView->render();
        if ($arguments['destination'] == 'string') {
            return $pdf;
        }
    }

    /**
     * @param Proposal $proposal
     * @return array
     */
    protected function createAnswersMap(Proposal $proposal, int $editStatus = self::META_PROPOSAL_EDITABLE_FIELDS_NO_LIMIT): array
    {
        $itemsMap = [];
        $answersMap = [];
        /** @var Answer $answer */
        foreach ($proposal->getAnswers() as $answer) {
            /** @var FormItem $item */
            $item = $answer->getItem();
            $itemUid = (integer)$item->getUid();
            $answerUid = (integer)$answer->getUid();
            if (!$itemsMap[$itemUid]) {
                $itemsMap[$itemUid] = $this->initializeItemsMap($item);
                $this->getOptionsToItemsMap($item, $itemsMap);
                $this->getValidatorsToItemsMap($item, $itemsMap);
            }
            $answersMap[$answerUid]['answer']['uid'] = $answer->getUid();
            $answersMap[$answerUid]['answer']['value'] = $answer->getValue();
            $answersMap[$answerUid]['answer']['additionalValue'] = $answer->getAdditionalValue();
//            $answersMap[$answerUid]['answer']['formGroupUid'] = $answer->getModel()->getUid();
//            $answersMap[$answerUid]['answer']['counter'] = $answer->getElementCounter();
            $answersMap[$answerUid]['answer']['type'] = $answer->getType();
            $answersMap[$answerUid]['item'] = $itemsMap[$itemUid];
            $answersMap[$answerUid]['disabled'] = false;

            if ($item->getType() == self::TYPE_UPLOAD) {
                /** @var ItemValidator $validator */
                foreach ($item->getValidators() as $validator) {
                    if ($validator->getType() == self::VALIDATOR_MAXVALUE) {
                        $currentN = count(explode(',', $answer->getValue()));
                        if ($currentN >= $validator->getParam1()) {
                            $answersMap[$answerUid]['add_button']['disabled'] = 1;
                        }
                    }
                }
            }

            if ($proposal->getState() !== self::PROPOSAL_IN_PROGRESS and
                $editStatus == self::META_PROPOSAL_EDITABLE_FIELDS_LIMIT) {
                $answersMap[$answerUid]['disabled'] = true;
                $answersMap[$answerUid]['item']['additionalAttributes']['disabled'] = 'disabled';
            }
        }
        return $answersMap;
    }

    /**
     * @param string $generatedFile
     * @param string $targetFile
     */
    protected function mergeTemplateWithWord(string $generatedFile, string $targetFile)
    {
        // open target
        $targetZip = new ZipArchive();
        $targetZip->open($targetFile);
        $targetDocument = $targetZip->getFromName('word/document.xml');
        $targetDom      = new \DOMDocument();
        $targetDom->loadXML($targetDocument);
        $targetXPath = new \DOMXPath($targetDom);
        $targetXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        // open source
        $sourceZip = new ZipArchive();
        $sourceZip->open($generatedFile);
        $sourceDocument = $sourceZip->getFromName('word/document.xml');
        $sourceDom      = new \DOMDocument();
        $sourceDom->loadXML($sourceDocument);
        $sourceXPath = new \DOMXPath($sourceDom);
        $sourceXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        /** @var DOMNode $replacementMarkerNode node containing the replacement marker $CONTENT$ */
        $replacementMarkerNode = $targetXPath->query('//w:p[contains(translate(normalize-space(), " ", ""),"$CONTENT$")]')[0];

        // insert source nodes before the replacement marker
        $sourceNodes = $sourceXPath->query('//w:document/w:body/*[not(self::w:sectPr)]');

        foreach ($sourceNodes as $sourceNode) {
            $imported = $replacementMarkerNode->ownerDocument->importNode($sourceNode, true);
            $inserted = $replacementMarkerNode->parentNode->insertBefore($imported, $replacementMarkerNode);
        }

        // remove $replacementMarkerNode from the target DOM
        $replacementMarkerNode->parentNode->removeChild($replacementMarkerNode);

        // save target
        $targetZip->addFromString('word/document.xml', $targetDom->saveXML());
        $targetZip->close();
    }

    /**
     * @param string $mode
     * @return array
     */
    protected function createStatesArray(string $mode = 'selected'): array
    {
        // create state-Array
        $states = [];
        $min = 1;
        $skip = 0;
        if ($mode == 'selected') {
            $min = self::STATE_ACCESS_MIN;
            $skip = self::STATE_SKIP;
        }
        for ($i = $min; $i <= self::STATE_MAX; $i++) {
            if ($i == $skip) {
                continue;
            }
            $localizedString = LocalizationUtility::translate($this->locallangFile . ':tx_openoap_domain_model_proposal.state.' . (string)$i);
            if ($localizedString) {
                $states[$i] = $localizedString;
            }
        }
        return $states;
    }

    /**
     * @param $proposal
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    protected function createWord($proposal): string
    {
        //        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('word-vorlage-oap.docx');
//
//        $templateProcessor->setValue('date', date("d-m-Y"));
//        $templateProcessor->setValue('call', $proposal->getCall()->getTitle());
//        $templateProcessor->setValue(
//            ['proposalTitle', 'state'],
//            [$proposal->getTitle(), (string) $proposal->getState()]
//        );
//        $templateProcessor->saveAs('MyWordFile.docx');

        // just for tests with word export class

        $phpWord = new PhpWord();

        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        $section = $phpWord->addSection();

//        $header = $section->addHeader();
//        $header->addText('This is my fabulous header!');

//        $footer = $section->addFooter();
//        $footer->addText('Footer text goes here.');

//        $textrun = $section->addTextRun();
//        $textrun->addText('Some text. ');
//        $textrun->addText('And more Text in this Paragraph.');

//        $textrun = $section->addTextRun();
//        $textrun->addText('New Paragraph! ', ['bold' => true]);
//        $textrun->addText('With text...', ['italic' => true]);

//        $rows = 10;
//        $cols = 5;
//        $section->addText('Basic table', ['size' => 16, 'bold' => true]);
//
//        $table = $section->addTable();
//        for ($row = 1; $row <= $rows; $row++) {
//            $table->addRow();
//            for ($cell = 1; $cell <= $cols; $cell++) {
//                $table->addCell(1750)->addText("Row {$row}, Cell {$cell}");
//            }
//        }

        if ('protection' == 'no-protection') {
            $documentProtection = $phpWord->getSettings()->getDocumentProtection();
            $documentProtection->setEditing(DocProtect::FORMS);
        }

        $itemsMap = [];
        // setting format of pseudo form field
        $valueOutputFormat = ['shading' => ['fill' => 'dddddd']];
        $introTextFont = ['bold' => false, 'size' => 11];
        $helpTextFont = ['bold' => false, 'size' => 11, 'italic' => true];
        $pageTitleFontFormat = ['bold' => true, 'size' => 12];
        $pageTitleFontFormat2 = ['bold' => true, 'size' => 11];
        $groupTitleFontFormat = ['bold' => true, 'size' => 12];
        $questionFontFormat = ['bold' => true, 'size' => 11];
        $lineStyle = ['weight' => 1, 'width' => 100, 'height' => 0, 'color' => 333333];

//        $this->addHtmlToSection($section, $proposal->getCall()->getIntroText());

//        $section->addPageBreak();
//        $textRun = $section->addTextRun();
//        $textRun->addText('Form fields can be added in a text run and can be in form of textinput ');
//        $textRun->addFormField('textinput')->setName('MyTextBox');
//        $textRun->addText(', checkbox ');
//        $textRun->addFormField('checkbox')->setDefault(true);
//        $textRun->addText(', or dropdown ');
//        $textRun->addFormField('dropdown')->setEntries(array('Choice 1', 'Choice 2', 'Choice 3'));
//        $textRun->addText('. You have to set document protection to "forms" to enable dropdown.');
//
//        $section->addText('They can also be added as a stand alone paragraph.');
//        $section->addFormField('textinput')->setValue('Your name');

        $this->flattenAnswers($proposal);
        $this->flattenItems($proposal);

        /** @var MetaInformation $metaInfo */
        $metaInfo = new MetaInformation($proposal->getMetaInformation());
        $groupsCounter = $this->groupsCounterMetaInfo($proposal, $metaInfo);
        $answersMap = $this->createAnswersMap($proposal, $metaInfo->getLimitEditableFields());
        $itemAnswerMap = $this->buildItemAnswerMap($proposal);

//        DebuggerUtility::var_dump($proposal);die();
//        DebuggerUtility::var_dump($this->answers);
//        DebuggerUtility::var_dump($answersMap);
//        DebuggerUtility::var_dump($itemAnswerMap);
//        DebuggerUtility::var_dump($itemAnswerMap);
        $answers = [];
        foreach ($proposal->getAnswers() as $answer) {
            $answers[$answer->getModel()->getUid() . '--' . $answer->getElementCounter() . '--' . $answer->getItem()->getUid()] = $answer;
        }

        $comments = [];
        $commentCounter = 0;

//        DebuggerUtility::var_dump($answers);
        //die();
        $pageCounter = 0;
        /** @var FormPage $formPage */
        foreach ($proposal->getCall()->getFormPages() as $formPage) {
            if ($formPage->getType() !== self::PAGETYPE_DEFAULT) {
                continue;
            }
            $pageCounter++;
            if ($pageCounter > 1) {
                $section->addPageBreak();
            }

            $this->addTextToSection($section, $formPage->getTitle(), $pageTitleFontFormat);
            $this->addHtmlToSection($section, $formPage->getIntroText());

            /** @var FormGroup $itemGroup */
            foreach ($formPage->getItemGroups() as $itemGroup) {
                if ($itemGroup->getRepeatableMax() > $itemGroup->getRepeatableMin()) {
                    $label = LocalizationUtility::translate($this->locallangFile . ':tx_openoap.word.group_repeatable');
                    $outputText = sprintf($label, $itemGroup->getRepeatableMax());
                    $this->addHtmlToSection($section, '<span style="font-style:italic">' . $outputText . '</span>');
                }
                for ($i = 0; $i < $groupsCounter[$itemGroup->getUid()]; $i++) {
                    if (count($itemGroup->getGroupTitle())) {
//                        DebuggerUtility::var_dump($itemGroup->getGroupTitle());
                        /** @var GroupTitle $groupTitleObject */
                        $groupTitleObject = $itemGroup->getGroupTitle()[$i];
                        $groupTitle = $groupTitleObject->getTitle();
                    } else {
                        $counterOutput = $groupsCounter[$itemGroup->getUid()] > 1 ? '#' . $i : '';
                        $groupTitle = $itemGroup->getTitle() . ' ' . $counterOutput;
                    }
                    $this->addTextToSection($section, $groupTitle, $groupTitleFontFormat);
                    // todo repeated groups? more then one output of the IntroText?
                    $this->addHtmlToSection($section, $itemGroup->getIntroText());
                    $this->addHtmlToSection($section, '<i>' . $itemGroup->getHelpText() . '</i>');

                    /** @var FormItem $item */
                    foreach ($itemGroup->getItems() as $item) {
                        if (!isset($answers[$itemGroup->getUid() . '--' . $i . '--' . $item->getUid()])) {
                            continue;
                        }

                        /** @var ItemValidator $validator */
                        $mandatorySign = '';
                        $validatorTexts = [];
                        foreach ($item->getValidators() as $validator) {
                            $para1 = $validator->getParam1();
                            $para2 = $validator->getParam2();

                            $validatorRawText = LocalizationUtility::translate($this->locallangFile . ':tx_openoap.word.' . (string)$validator->getType());
                            if ($validator->getType() == self::VALIDATOR_FILE_TYPE) {
                                $para1 = $validator->getParam2();
                                // todo - fallback for validator message on word exports?
                            }
                            $validatorTexts[] = sprintf($validatorRawText, $para1, $para2);
                            if ($validator->getType() == self::VALIDATOR_MANDATORY) {
                                $mandatorySign = self::MANDATORY_SIGN;
                            }
                        }

                        /** @var Answer $answer */
                        $answer = $answers[$itemGroup->getUid() . '--' . $i . '--' . $item->getUid()];

                        $this->addTextToSection($section, $item->getQuestion() . ' ' . $mandatorySign, null, $questionFontFormat);
                        $this->addHtmlToSection($section, $item->getIntroText());
                        $this->addHtmlToSection($section, '<i>' . $item->getHelpText() . '</i>');

                        switch ($item->getType()) {
                            case self::TYPE_STRING:
                                $outputString = $answer->getValue();
                                if ($item->getUnit() !== '') {
                                    $this->addTextToSection($section, 'in ' . $item->getUnit());
                                    if (is_numeric($answer->getValue())) {
                                        $outputString = number_format(
                                            (float)$answer->getValue(),
                                            self::FLOAT_FORMAT_DECIMALS,
                                            self::FLOAT_FORMAT_DECIMAL_SEPARATOR,
                                            self::FLOAT_FORMAT_THOUSANDS_SEPARATOR
                                        );
                                    }
                                }
                                $this->addTextToSection($section, $outputString, null, $valueOutputFormat);

                                break;
                            case self::TYPE_TEXT:
                                // todo Output of the max. number of characters
                                if ($answer->getValue()) {
                                    $this->addTextToSection($section, $answer->getValue(), null, $valueOutputFormat);
                                } else {
                                    // output several empty lines to show that a larger text is expected here.
                                    $this->addTextToSection($section, ' ', null, $valueOutputFormat);
                                    $this->addTextToSection($section, ' ', null, $valueOutputFormat);
                                }
                                // an additional empty line - even if contents have already been entered
                                $this->addTextToSection($section, ' ', null, $valueOutputFormat);

                                break;
                            case self::TYPE_DATE1:
                                $this->addTextToSection($section, $answer->getValue(), null, $valueOutputFormat);
                                break;
                            case self::TYPE_DATE2:
                                $this->addTextToSection(
                                    $section,
                                    LocalizationUtility::translate($this->locallangFile . ':tx_openoap.general.date-from')
                                );
                                $this->addTextToSection($section, $answer->getValue(), null, $valueOutputFormat);
                                $this->addTextToSection(
                                    $section,
                                    LocalizationUtility::translate($this->locallangFile . ':tx_openoap.general.date-to')
                                );
                                $this->addTextToSection($section, $answer->getAdditionalValue(), null, $valueOutputFormat);
                                break;
                            case self::TYPE_SELECT_MULTIPLE:
                            case self::TYPE_CHECKBOX:
                                $this->addTextToSection($section, 'multiple selections allowed');
                                $this->getOptionsToItemsMap($item, $itemsMap);
                                $checksArray = json_decode($answer->getValue(), true);
                                $checks = [];
                                if (is_array($checksArray)) {
                                    foreach ($checksArray as $check) {
                                        $checks[md5($check)] = $check;
                                    }
                                }

                                $selectionInline = '';
                                $inline = $this->decideInline($itemsMap[$item->getUid()]['options']);

                                foreach ($itemsMap[$item->getUid()]['options'] as $option) {
                                    // check if option is set?
                                    if ($checks[md5($option['key'])]) {
                                        $mark = '[X]';
                                    } else {
                                        $mark = '[ ]';
                                    }
                                    if ($inline) {
                                        $selectionInline .= $mark . ' ' . preg_replace('~\|~', ' ', $option['key']) . '  ';
                                    } else {
                                        $this->addTextToSection(
                                            $section,
                                            $mark . ' ' . preg_replace('~\|~', ' ', $option['key']),
                                            null,
                                            $valueOutputFormat
                                        );
                                    }
                                }
                                if ($inline) {
                                    $this->addTextToSection(
                                        $section,
                                        $selectionInline,
                                        null,
                                        $valueOutputFormat
                                    );
                                }
                                if ($item->isAdditionalValue()) {
                                    $this->addTextToSection(
                                        $section,
                                        $answer->getAdditionalValue(),
                                        null,
                                        $valueOutputFormat
                                    );
                                }

                                break;
                            case self::TYPE_SELECT_SINGLE:
                            case self::TYPE_RADIOBUTTON:
                                $this->addTextToSection($section, 'only one selection is allowed');
                                $this->getOptionsToItemsMap($item, $itemsMap);
                                $selectionInline = '';
                                $inline = $this->decideInline($itemsMap[$item->getUid()]['options']);

                                foreach ($itemsMap[$item->getUid()]['options'] as $option) {
                                    // check if option is set?
                                    if ($option['key'] == $answer->getValue()) {
                                        $mark = '(X)';
                                    } else {
                                        $mark = '( )';
                                    }
                                    if ($inline) {
                                        $selectionInline .= $mark . ' ' . preg_replace('~\|~', ' ', $option['key']) . '  ';
                                    } else {
                                        $this->addTextToSection(
                                            $section,
                                            $mark . ' ' . preg_replace('~\|~', ' ', $option['key']),
                                            null,
                                            $valueOutputFormat
                                        );
                                    }
                                }
                                if ($inline) {
                                    $this->addTextToSection(
                                        $section,
                                        $selectionInline,
                                        null,
                                        $valueOutputFormat
                                    );
                                }

                                if ($item->isAdditionalValue()) {
                                    $this->addTextToSection(
                                        $section,
                                        $answer->getAdditionalValue(),
                                        null,
                                        $valueOutputFormat
                                    );
                                }

                                break;
                            case self::TYPE_UPLOAD:
                                $this->addTextToSection(
                                    $section,
                                    'For your own notices - please upload the required documents in the online form.'
                                );
                                $this->addTextToSection($section, ' ', null, $valueOutputFormat);

                                if ($answer->getValue() !== '') {
                                    $files = explode(',', $answer->getValue());
                                    foreach ($files as $fileId) {
                                        /** @var File $file */
                                        $file = $this->resourceFactory->getFileObject($fileId);
                                        $size = $this->convertFilesizeToHumanReadableBytes($file->getProperties()['size'], 0);

                                        $this->addTextToSection($section, ' ' . $file->getName() . ' (' . $size . ')', null, $valueOutputFormat);
                                    }
                                    $this->addTextToSection($section, ' ', null, $valueOutputFormat);
                                } else {
                                    $this->addTextToSection($section, ' ', null, $valueOutputFormat);
                                    $this->addTextToSection($section, ' ', null, $valueOutputFormat);
                                    $this->addTextToSection($section, ' ', null, $valueOutputFormat);
                                }

                                break;
                        }
                        // todo formatting automatic created text/hints
                        $this->addHtmlToSection($section, '<span style="text-align:right;"><i>' . implode('; ', $validatorTexts) . '</i></span>');

                        $commentsOfAnswer = $answer->getComments();
                        if (count($commentsOfAnswer)) {
                            $commentCounter++; // not starting with 0
                            $labelId = 'tx_openoap_notifications.pdf.singular.label';
                            if (count($commentsOfAnswer) > 1) {
                                $labelId = 'tx_openoap_notifications.pdf.plural.label';
                            }
                            $label = LocalizationUtility::translate($this->locallangFile . ':' . $labelId);
                            $commentsFlag = '(' . count($commentsOfAnswer) . ' ' . $label . ') ';
                            $comments[$commentCounter] = [];
                            /** @var Comment $comment */
                            foreach ($commentsOfAnswer as $comment) {
                                $comments[$commentCounter][] = $comment;
                            }

                            $textrun = $section->addTextRun();
                            $textrun->addText($commentsFlag);
                            $textrun->addText((string)$commentCounter, ['superScript' => true]);
                        }

                        $section->addLine($lineStyle);
                    }
                }
            }
        }

        // general comments
        $generalComments = [];
        /** @var Comment $comment */
        foreach ($proposal->getComments() as $comment) {
            if ($comment->getSource() == self::COMMENT_SOURCE_EDIT) {
                $generalComments[] = $comment;
            }
        }

        if (count($comments) or count($generalComments)) {
            // Headline Notifications
            $section->addPageBreak();
            $notificationsLabel = LocalizationUtility::translate($this->locallangFile . ':tx_openoap_notifications.pdf.header');
            $section->addText($notificationsLabel, $pageTitleFontFormat);

            if (count($generalComments)) {
                $notificationsLabel = LocalizationUtility::translate($this->locallangFile . ':tx_openoap_notifications.pdf.generalComments.label');
                $section->addText($notificationsLabel, $pageTitleFontFormat2);

                $this->createListOfComments($generalComments, $section);
            }

            if (count($comments)) {
                $notificationsLabel = LocalizationUtility::translate($this->locallangFile . ':tx_openoap_notifications.pdf.answerComments.label');
                $section->addText($notificationsLabel, $pageTitleFontFormat2);

                foreach ($comments as $commentFlag => $commentsOfAnswer) {
                    $textrun = $section->addTextRun();
                    $textrun->addText($commentFlag, ['superScript' => true], null);
                    $textrun->addText(' ' . $commentsOfAnswer[0]->getAnswer()->getItem()->getQuestion() . ': ');

                    $this->createListOfComments($commentsOfAnswer, $section);
                }
            }
        }
//        DebuggerUtility::var_dump($comments);
//        DebuggerUtility::var_dump($commentCounter);
        //die();
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        $tmpFolder = $this->getTransientFolder();
        $tmpFileName = $this->getWordFileName($proposal->getUid(), 'tmp');
        $tmpFile = $tmpFolder . '/' . $tmpFileName;
        $objWriter->save($tmpFile);
        if (!file_exists($tmpFile)) {
            return '';
        }
        return $tmpFile;
    }

    /**
     * @param int $proposalsId
     * @param string $prefix
     * @return string
     */
    protected function getWordFileName(int $proposalsId, string $prefix = ''): string
    {
        if ($prefix !== '') {
            $prefix .= '-';
        }
        return $prefix . $proposalsId . '-' . date('Ymd-Hi') . '.docx';
    }

    /**
     * @return string
     */
    protected function getTransientFolder(): string
    {
        return Environment::getVarPath() . '/transient';
    }

    /**
     * @param Proposal $proposal
     * @param string $contentWordFileName
     * @param string $templateWordFileName
     */
    protected function mergeWordFiles(Proposal $proposal, string $contentWord, string $templateWord): string
    {
        $this->mergeTemplateWithWord($templateWord, $contentWord, 'MergeResult.docx');

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('MergeResult.docx');
//
//        $templateProcessor->setValue('date', date("d-m-Y"));
        $templateProcessor->setValue('date', LocalizationUtility::translate($this->locallangFile . ':tx_openoap_proposals.exportGenerated.text', 'open_oap', [date('d.m.Y'), date('H:i')]));
        $templateProcessor->setValue('call', $proposal->getCall()->getTitle());
        $templateProcessor->setValue(
            ['proposalTitle', 'state'],
 //           [$proposal->getTitle(), $states[$proposal->getState()]]
            [$proposal->getTitle(), LocalizationUtility::translate($this->locallangFile . ':tx_openoap_proposals.exportStatus.text') . ': ' . $states[$proposal->getState()]]
        );

        $templateProcessor->saveAs('MergeResult.docx');

//        $objReader = \PhpOffice\PhpWord\IOFactory::createReader('Word2007');
//        $phpWordFinal = $objReader->load('MergeResult.docx');
//
//        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWordFinal, 'Word2007');
//        $objWriter->save('MergeResult-1.docx');

        echo 'created word';
        die();
    }

    protected function determineProposalPid(Call $call): int
    {
        return (integer)$this->settings['proposalsPoolId'];

        // we are just using only one proposal folder - no settings from call datasets are used
        // #17904, #17854
        if ($call->getProposalPid()) {
            $proposalPid = $call->getProposalPid();
        } else {
            $proposalPid = $this->settings['proposalsPoolId'];
        }
        return $proposalPid;
    }

    /**
     * Create GroupsCounterArray
     *
     * @param Proposal $proposal
     * @param MetaInformation $metaInfo
     * @return array groupsCounterArray
     */
    protected function groupsCounterMetaInfo(Proposal $proposal, MetaInformation $metaInfo): array
    {
        // create GroupsCounterArray
        $groupsCounter = $metaInfo->getGroupsCounter();
        if (!$groupsCounter) {
            // correction of old proposals with new group data in metainoformation
            // could be changed to check against changed forms
            $groupsCounter = [];
            /** @var FormPage $page */
            foreach ($proposal->getCall()->getFormPages() as $page) {
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
                    }
                }
            }
            $metaInfo->setGroupsCounter($groupsCounter);
        }
        return $groupsCounter;
    }

    /**
     * @param Section $section
     * @param string $htmlText
     */
    protected function addHtmlToSection(Section $section, $htmlText): void
    {
        if (trim($htmlText) == '') {
            return;
        }
        $urlReplacePattern = '~<a *href *= *[\"|\'](.*)[\"|\'].*>(.*)<\/a *>~miU';
        $urlReplaceReplacestring = '$2 ($1)';
        $htmlText = preg_replace($urlReplacePattern, $urlReplaceReplacestring, $htmlText);

        // todo - more common and with real url (not just the id)
        /** @var ServerRequest $serverRequest */
        $serverRequest = $GLOBALS['TYPO3_REQUEST'];
        $site = $serverRequest->getUri()->getScheme() . '://' . $serverRequest->getUri()->getHost();

        $typo3UrlReplacePattern = '~t3:\/\/page\?uid=([0-9]*)~';
        $typo3UrlReplace = $site . '/index.php?id=$1';
        $htmlText = preg_replace($typo3UrlReplacePattern, $typo3UrlReplace, $htmlText);

        $urlReplacePattern = '~>([\r\n\t ]*)<~mU';
        $urlReplaceReplacestring = '><';
        $htmlText = preg_replace($urlReplacePattern, $urlReplaceReplacestring, $htmlText);

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlText, false);
    }

    /**
     * @param Applicant|null $applicant
     * @return string
     */
    protected function getApplicantName(?Applicant $applicant): string
    {
        if ($applicant->getFirstName() != '' || $applicant->getLastName() != '') {
            $applicantName = $applicant->getFirstName() . ' ' . $applicant->getLastName();
        } else {
            $applicantName = $applicant->getUsername();
        }
        return $applicantName;
    }

    /**
     * @param $options
     * @return bool
     */
    private function decideInline($array): bool
    {
        $inline = false;
        if (count($array) > self::MAX_FOR_LINE_BY_LINE_OUTPUT) {
            $inline = true;
        }
        return $inline;
    }

    /**
     * @param string $string
     * @param Section $section
     * @param array $pageTitleFontFormat
     */
    private function addTextToSection(Section $section, $string, array $fStyle = null, array $pStyle = null): void
    {
        $text = htmlspecialchars($string);
        // addText(string $text, mixed $fStyle = null, mixed $pStyle = null)
        $section->addText($text, $fStyle, $pStyle);
    }

    /**
     * @param array $generalComments
     * @param Section $section
     */
    private function createListOfComments(array $generalComments, Section $section): void
    {
        foreach ($generalComments as $comment) {
            $author = $comment->getAuthor();
            if ($author) {
                $authorName = $author->getUserName();
                if ($author->getRealName() !== '') {
                    $authorName = $author->getRealName();
                }
            } else {
                $authorName = '-unknown-';
            }
            $listItemRun = $section->addListItemRun();
            $listItemRun->addText(date('d.m.Y, H:i', $comment->getCreated()) . ', ' . $authorName . '<w:br/>');
            $listItemRun->addText($comment->getText(), ['italic' => true]);
        }
    }

    /**
     * @param $proposal
     * @param ZipArchive|null $zip
     * @param bool $zipFlag
     * @param string $zipFile
     * @param $createFolder
     * @param string $absoluteBasePath
     * @return array|void
     * @throws FileDoesNotExistException
     */
    protected function addAttachmentsToZip($proposal, $zip, bool $zipFlag, string $zipFile, $createFolder, string $absoluteBasePath)
    {
        /** @var ResourceStorage $storage */
        $storage = $this->getStorage();

        foreach ($proposal->getAnswers() as $answer) {
            $value = $answer->getValue();
            if ($value !== '' and $answer->getItem()->getType() == self::TYPE_UPLOAD) {
                $files = explode(',', $value);
                foreach ($files as $fileId) {
                    $file = $this->resourceFactory->getFileObject($fileId);

                    if (!$zipFlag) {
                        $zip = new ZipArchive();
                        if (file_exists($zipFile)) {
                            unlink($zipFile);
                        }
                        $openResult = $zip->open($zipFile, ZipArchive::CREATE);

                        // todo error handling
                        if (!$openResult) {
                            switch ($openResult) {
                                case ZipArchive::ER_EXISTS:
                                case ZipArchive::ER_INCONS:
                                case ZipArchive::ER_INVAL:
                                case ZipArchive::ER_MEMORY:
                                case ZipArchive::ER_NOENT:
                                case ZipArchive::ER_NOZIP:
                                case ZipArchive::ER_OPEN:
                                case ZipArchive::ER_SEEK:
                                    DebuggerUtility::var_dump($openResult, (string)__LINE__);
                                    DebuggerUtility::var_dump($zip, (string)__LINE__);
                                    die();
                            }
                        }
                        $zipFlag = true;
                    }

                    if ($createFolder) {
                        // replace ids in path
                        /** @var User $applicant */
                        $applicant = $proposal->getApplicant();

                        $applicantName = $applicant->getUsername();
                        if ($applicant->getLastname() !== '') {
                            $applicantName = $applicant->getLastName();
                            if ($applicant->getFirstName() !== '') {
                                $applicantName .= '-' . $applicant->getFirstName();
                            }
                        }
                        $userUidFormatted = sprintf('%05d', $applicant->getUid());
                        $userNamePath = $applicantName . '--' . $userUidFormatted;
                        // $signature = $proposal->getSignature();
                        $proposalTitle = $proposal->getTitle();

                        $proposalPathName = '';
                        if ($proposal->getSignature() <> '') {
                            $proposalPathName .= $this->buildSignature($proposal) . '--';
                        }
                        if ($proposalTitle <> '') {
                            $proposalTitle = $storage->sanitizeFileName($proposalTitle);
                        // $proposalTitle = preg_replace(['~ ~','~/~','~\.~','~;~'],['-','-','-','-'],$proposalTitle);
                        } else {
                            $proposalTitle .= $this->getTranslationString(self::XLF_BASE_IDENTIFIER_DEFAULTS . '.' . self::DEFAULT_TITLE);
                        }
                        $proposalUidFormatted = sprintf('%05d', $proposal->getUid());
                        $proposalPathName .=  substr($proposalTitle, 0, 30) . '--' . $proposalUidFormatted;
                        $fileSaveAs = $userNamePath . '/' . $proposalPathName . '/' . basename($file->getIdentifier());
                    } else {
                        $fileSaveAs = basename($file->getIdentifier());
                    }
                    if ($zip) {
                        $zip->addFile($absoluteBasePath . $file->getIdentifier(), $fileSaveAs);
                    }
                }
            }
        }
        return [$zip, $zipFlag];
    }

    /**
     * @return ResourceStorage
     */
    protected function getStorage(): ResourceStorage
    {
        /** @var ResourceStorage $storage */
        $storage = $this->resourceFactory->getStorageObjectFromCombinedIdentifier($this->settings['uploadFolder']);
        return $storage;
    }

    /**
     * @param ResourceStorage $storage
     * @return array
     */
    protected function initializeUploadFolder(ResourceStorage $storage): array
    {
        $configuration = $storage->getConfiguration();
        if (!empty($configuration['pathType']) && $configuration['pathType'] === 'relative') {
            $relativeBasePath = $configuration['basePath'];
            $absoluteBasePath = rtrim(Environment::getPublicPath() . '/' . $relativeBasePath, '/');
        } else {
            $absoluteBasePath = rtrim($configuration['basePath'], '/');
        }
        $uploadFolder = self::provideTargetFolder($storage->getRootLevelFolder(), '_temp_');
        self::provideFolderInitialization($uploadFolder);
        return [$absoluteBasePath, $uploadFolder];
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function getTranslationString($identifier, $parameter = []): string
    {
        $string = $this->getLanguageService()->sL($this->locallangFile . ':' . $identifier);
        if (count($parameter)) {
            return sprintf($string, ...$parameter);
        }
        return $string;
    }

    /**
     * @param string $zipFile
     */
    protected function sendZip(string $zipFile): void
    {
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
        exit;
    }
}
