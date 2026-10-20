<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Domain\Model\Call;
use OpenOAP\OpenOap\Domain\Model\FormGroup;
use OpenOAP\OpenOap\Domain\Model\FormItem;
use OpenOAP\OpenOap\Domain\Model\FormPage;
use OpenOAP\OpenOap\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***
 *
 * This file is part of the "CB import news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde
 *
 ***/

/**
 * BackendController
 */
#[AsController]
class BackendFormsController extends OapBackendController
{
    /**
     * @var int $depth
     */
    protected int $depth = 10;

    protected ?PageRepository $pageRepository = null;

    /**
     * @var array
     */
    private $evalutionResults = [];

    /** @var array<string, int> $evaluationCounts */
    private array $evaluationCounts = [];

    /** @var array<int, array<int, array<string, string>>> $itemFindings */
    private array $itemFindings = [];

    /*
        * overview page
        *
        * @return void
        */
    public function showOverviewFormsAction(): ResponseInterface
    {
        $this->moduleTemplate->assignMultiple([
            'actionName' => $this->actionMethodName,
        ]);

        return $this->moduleTemplate->renderResponse('ShowOverviewForms');
    }

    /**
     * The page repository, fetched lazily: some instances inject it into the base controller and
     * others do not, and collectPagesData() asks for it before collectPageTitle() runs.
     *
     * @return PageRepository
     */
    private function getPageRepository(): PageRepository
    {
        if (! $this->pageRepository instanceof PageRepository) {
            $this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                PageRepository::class
            );
        }

        return $this->pageRepository;
    }

    /**
     * Collects the page ids and titles below a starting pid.
     *
     * @param string $settingKey The setting that holds the starting pid, e.g. 'pidFormPages'
     * @param int $recursionDepth How deep to follow the subpages
     * @return array
     */
    protected function collectPagesData(string $settingKey, int $recursionDepth): array
    {
        $startPid = [$this->settings[$settingKey]];
        $pidsStrings = $this->getPageRepository()->getPageIdsRecursive($startPid, $recursionDepth);
        $pidsStringComplete = implode(',', $pidsStrings);
        $childPids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $pidsStringComplete);

        return $this->collectPageTitle($childPids, $startPid, true);
    }

    /**
     * @param Call $call
     */
    public function previewFormAction(Call $call): ResponseInterface
    {
        $this->evaluateForm($call);

        // the folder a record lives in, by pid - the preview names it at the uid of the node, so
        // an editor knows where to look for the record of a page, a group or an item
        $folderTitles = [
            'pages' => $this->collectPagesData('pidFormPages', $this->depth),
            'groups' => $this->collectPagesData('pidFormGroups', $this->depth),
            'items' => $this->collectPagesData('pidFormItems', $this->depth),
        ];

        /**
         * /typo3/module/web/OpenOapBackend
         * ?id=24
         * &tx_openoap_web_openoapbackend%5Bform%5D=1
         * &tx_openoap_web_openoapbackend%5Baction%5D=previewForm
         * &tx_openoap_web_openoapbackend%5Bcontroller%5D=OapBackend
         */
        $this->moduleTemplate->assignMultiple([
            'evaluationResults' => $this->evalutionResults,
            'evaluationCounts' => $this->evaluationCounts,
            'itemFindings' => $this->itemFindings,
            'actionName' => $this->actionMethodName,
            'call' => $call,
            'typeLabels' => $this->collectTypeLabels(),
            'folderTitles' => $folderTitles,
        ]);

        return $this->moduleTemplate->renderResponse('PreviewForm');
    }

    /**
     * @param array $childPids
     * @param array $pidRoots
     * @param bool $rootLevelWithTitle
     * @return array
     */
    protected function collectPageTitle(array $childPids, array $pidRoots, bool $rootLevelWithTitle): array
    {
        $titles = [];
        foreach ($childPids as $pageId) {
            $pageId = (int) $pageId;
            $titles[$pageId] = '';
            if (in_array($pageId, $pidRoots) and ! $rootLevelWithTitle) {
                continue;
            }
            $page = $this->getPageRepository()->getPage($pageId);
            $uid = $page['uid'];
            $pid = $page['pid'];
            $label = $titles[$uid] = $page['title'];

            if (isset($titles[$pid])) {
                $prefix = trim($titles[$pid]) . ' / ';
            } else {
                $prefix = '';
            }
            $label = $prefix . $label;
            $titles[$pageId] = $label;
        }
        return $titles;
    }

    /**
     * @param array $filter
     * @param int $currentPage
     * @return ResponseInterface
     */
    public function listFormsAction(array $filter = [], int $currentPage = 1): ResponseInterface
    {
        $this->listObjects($this->callRepository, $currentPage);

        $this->moduleTemplate->assignMultiple([
            'actionName' => $this->actionMethodName,
        ]);

        return $this->moduleTemplate->renderResponse('ListForms');
    }

    /**
     * @param $code
     * @param $text
     * @param string $result
     */
    private function setEvaluationItem($code, $text, string $result = '', array $refs = []): void
    {
        $this->evalutionResults[] = [
            'code' => $code,
            'text' => $text,
            'result' => $result,
            'refs' => $refs,
        ];
    }

    /**
     * A finding that belongs to one item. The preview repeats it at the item itself, so the
     * editor sees it where it has to be fixed.
     *
     * @param FormItem $item
     * @param string $code
     * @param string $text
     */
    private function addItemFinding(FormItem $item, string $code, string $text): void
    {
        $this->itemFindings[(int)$item->getUid()][] = ['code' => $code, 'text' => $text];
    }

    /**
     * @param Call $call
     */
    private function evaluateForm(Call $call): void
    {
        $warnings = 0;

        // intro/title

        // count pages
        $countPages = count($call->getFormPages());

        $structure = $this->collectFormStructure($call);

        // count groups
        // count items
        $this->evaluationCounts = [
            'pages' => $countPages,
            'groups' => $structure['countGroups'],
            'metaGroups' => $structure['countMetaGroups'],
            'nestedGroups' => $structure['countNestedGroups'],
            'items' => count($structure['items']),
        ];

        // test: overview-and-submit page is there, it's the last page
        $submitPages = [];
        $pageIndex = 0;
        /** @var FormPage $formPage */
        foreach ($call->getFormPages() as $formPage) {
            $pageIndex++; // at the beginning, cause we are starting with 1 and using the number afterwards
            if ($formPage->isSubmitPage()) {
                $submitPages[] = $pageIndex;
            }
        }
        if (count($submitPages) == 0) {
            $this->setEvaluationItem('error', 'the submit page is missing');
        }
        if (count($submitPages) > 1) {
            $this->setEvaluationItem('error', 'there are more than one submit pages');
        }
        if (count($submitPages) > 0 and end($submitPages) <> $pageIndex) {
            $this->setEvaluationItem('error', 'the submitpage is not the last page');
        }
        if (count($submitPages) == 1 and end($submitPages) == $pageIndex) {
            $this->setEvaluationItem('ok', 'one submit page, and it is the last one', 'page ' . $pageIndex);
        }

        // is there an item with the title attribute - and it's only one?
        $titleItems = [];
        foreach ($structure['items'] as $item) {
            if ($item->isEnabledTitle()) {
                $titleItems[] = $item;
            }
        }
        if (count($titleItems) == 0) {
            $this->setEvaluationItem(
                'error',
                'no item carries the title attribute, so no proposal of this call ever gets a title'
            );
        }
        if (count($titleItems) == 1) {
            $this->setEvaluationItem(
                'ok',
                'one item carries the title attribute',
                '',
                $this->buildItemRefs($titleItems)
            );
        }
        if (count($titleItems) > 1) {
            $this->setEvaluationItem(
                'error',
                'more than one item carries the title attribute - the proposal title then follows whichever of them was saved last, and every change is logged as a renaming',
                '',
                $this->buildItemRefs($titleItems)
            );
            foreach ($titleItems as $item) {
                $this->addItemFinding(
                    $item,
                    'error',
                    'This item carries the title attribute, and so do ' . (count($titleItems) - 1)
                    . ' other item(s) of this form. Exactly one is allowed.'
                );
            }
        }

        // witch items have the meta information
        $infoItems = [];
        foreach ($structure['items'] as $item) {
            if ($item->isEnabledInfo()) {
                $infoItems[] = $item;
            }
        }
        $this->setEvaluationItem(
            'log',
            'items with the meta information',
            $infoItems === [] ? '--' : (string)count($infoItems),
            $this->buildItemRefs($infoItems)
        );

        // witch items are set as a filter
        $filterItems = [];
        $filterItemsWithoutOptions = [];
        foreach ($structure['items'] as $item) {
            if (! $item->isEnabledFilter()) {
                continue;
            }
            $filterItems[] = $item;
            if (count($item->getOptions()) == 0) {
                $filterItemsWithoutOptions[] = $item;
                $this->addItemFinding(
                    $item,
                    'warning',
                    'Set as a filter, but no options are attached. The proposals module builds the'
                    . ' filter from the options, so it stays empty.'
                );
            }
        }
        $this->setEvaluationItem(
            'log',
            'items set as a filter',
            $filterItems === [] ? '--' : (string)count($filterItems),
            $this->buildItemRefs($filterItems)
        );
        if ($filterItemsWithoutOptions !== []) {
            $this->setEvaluationItem(
                'warning',
                'items are set as a filter but carry no options - the proposals module builds their filter from the options, so it stays empty',
                '',
                $this->buildItemRefs($filterItemsWithoutOptions)
            );
        }

        // the questions of the call record: they gate the submit and are never stored as answers
        $submitQuestions = [];
        $submitQuestionsWithoutOptions = [];
        $submitQuestionsWithDeadSwitches = [];
        /** @var FormItem $item */
        foreach ($call->getItems() as $item) {
            $submitQuestions[] = $item;
            if (count($item->getOptions()) == 0) {
                $submitQuestionsWithoutOptions[] = $item;
                $this->addItemFinding(
                    $item,
                    'warning',
                    'A question of the submit page without options renders nothing - it does not reach'
                    . ' the applicant and gates no submit.'
                );
            }
            $deadSwitches = $this->collectDeadSwitches($item);
            if ($deadSwitches !== []) {
                $submitQuestionsWithDeadSwitches[] = $item;
                $this->addItemFinding(
                    $item,
                    'warning',
                    'The switch(es) ' . implode(', ', $deadSwitches) . ' have no effect here: they'
                    . ' work on the answer of an item, and a question of the submit page never gets'
                    . ' one. The title of a proposal, the meta information and the filter of the'
                    . ' proposals module are all built from answers.'
                );
            }
        }
        $this->evaluationCounts['submitQuestions'] = count($submitQuestions);
        $this->setEvaluationItem(
            'log',
            'questions of the call record on the submit page',
            $submitQuestions === [] ? '--' : (string)count($submitQuestions),
            $this->buildItemRefs($submitQuestions)
        );
        if ($submitQuestionsWithoutOptions !== []) {
            $this->setEvaluationItem(
                'warning',
                'questions of the submit page carry no options - the frontend builds their checkboxes from the options, so they render nothing',
                '',
                $this->buildItemRefs($submitQuestionsWithoutOptions)
            );
        }
        if ($submitQuestionsWithDeadSwitches !== []) {
            $this->setEvaluationItem(
                'warning',
                'questions of the submit page carry switches that work on answers - title, meta information or filter - and no answer is ever stored for them',
                '',
                $this->buildItemRefs($submitQuestionsWithDeadSwitches)
            );
        }

        $errors = 0;
        foreach ($this->evalutionResults as $evaluationResult) {
            if ($evaluationResult['code'] === 'error') {
                $errors++;
            }
            if ($evaluationResult['code'] === 'warning') {
                $warnings++;
            }
        }
        $this->evaluationCounts['errors'] = $errors;
        $this->evaluationCounts['warnings'] = $warnings;

        $this->sortEvaluationResults();
    }

    /**
     * Errors first, then warnings, then what passed, then the plain log. Within one code the
     * order of the checks is kept.
     */
    private function sortEvaluationResults(): void
    {
        $weight = ['error' => 0, 'warning' => 1, 'ok' => 2, 'log' => 3];
        usort($this->evalutionResults, function (array $a, array $b) use ($weight) {
            return ($weight[$a['code']] ?? 9) <=> ($weight[$b['code']] ?? 9);
        });
    }

    /**
     * Items as references for the protocol: the anchor of the item in the preview and its wording.
     *
     * @param array<int, FormItem> $items
     * @return array<int, array<string, string>>
     */
    private function buildItemRefs(array $items): array
    {
        $refs = [];
        foreach ($items as $item) {
            $refs[] = [
                'anchor' => 'oapfp-item-' . $item->getUid(),
                'label' => $this->describeItem($item),
            ];
        }

        return $refs;
    }

    /**
     * The wording of the select fields, taken from the TCA: type and display type of a group, the
     * type of an item, the type of a validator and the comparison of a logic atom are all stored
     * as integers, and only the TCA knows what they mean.
     *
     * @return array<string, array<string, string>>
     */
    private function collectTypeLabels(): array
    {
        return [
            'groupTypes' => $this->collectTcaItemLabels('tx_openoap_domain_model_formgroup', 'type'),
            'groupDisplayTypes' => $this->collectTcaItemLabels('tx_openoap_domain_model_formgroup', 'display_type'),
            'itemTypes' => $this->collectTcaItemLabels('tx_openoap_domain_model_formitem', 'type'),
            'validatorTypes' => $this->collectTcaItemLabels('tx_openoap_domain_model_itemvalidator', 'type'),
            'logicCompares' => $this->collectTcaItemLabels('tx_openoap_domain_model_logicatom', 'logic'),
            'logicTypes' => $this->collectTcaItemLabels('tx_openoap_domain_model_logicatom', 'type'),
        ];
    }

    /**
     * The items of one TCA select as value => label. A label that is no reference stays as it is,
     * which is what sl() does with it.
     *
     * @param string $table
     * @param string $field
     * @return array<string, string>
     */
    private function collectTcaItemLabels(string $table, string $field): array
    {
        $labels = [];
        foreach ($GLOBALS['TCA'][$table]['columns'][$field]['config']['items'] ?? [] as $selectItem) {
            $labels[(string)$selectItem['value']] = LocalizationUtility::translateBEContext($selectItem['label']);
        }

        return $labels;
    }

    /**
     * Walks the form the way the frontend renders it: a meta group contributes the items of its
     * nested groups, every other group its own items. The own items of a meta group are left out
     * on purpose - the frontend never renders them, and the preview marks them at the group.
     *
     * Items that are used in more than one group are counted once, they share one answer.
     *
     * @param Call $call
     * @return array{countGroups: int, countMetaGroups: int, countNestedGroups: int, items: array<int, FormItem>}
     */
    private function collectFormStructure(Call $call): array
    {
        $countGroups = 0;
        $countMetaGroups = 0;
        $countNestedGroups = 0;
        $items = [];

        /** @var FormPage $formPage */
        foreach ($call->getFormPages() as $formPage) {
            /** @var FormGroup $group */
            foreach ($formPage->getItemGroups() as $group) {
                $countGroups++;
                if ($group->getType() == self::GROUPTYPE_META) {
                    $countMetaGroups++;
                    /** @var FormGroup $nestedGroup */
                    foreach ($group->getItemGroups() as $nestedGroup) {
                        $countNestedGroups++;
                        /** @var FormItem $item */
                        foreach ($nestedGroup->getItems() as $item) {
                            $items[$item->getUid()] = $item;
                        }
                    }
                    continue;
                }
                /** @var FormItem $item */
                foreach ($group->getItems() as $item) {
                    $items[$item->getUid()] = $item;
                }
            }
        }

        return [
            'countGroups' => $countGroups,
            'countMetaGroups' => $countMetaGroups,
            'countNestedGroups' => $countNestedGroups,
            'items' => $items,
        ];
    }

    /**
     * The switches of an item that only work through its answer. On a question of the submit page
     * they are dead: no answer is ever stored for it.
     *
     * @param FormItem $item
     * @return array<int, string>
     */
    private function collectDeadSwitches(FormItem $item): array
    {
        $switches = [];
        if ($item->isEnabledTitle()) {
            $switches[] = 'title';
        }
        if ($item->isEnabledInfo()) {
            $switches[] = 'meta information';
        }
        if ($item->isEnabledFilter()) {
            $switches[] = 'filter';
        }

        return $switches;
    }

    /**
     * One item in the wording of the protocol: the question and the uid behind it.
     *
     * @param FormItem $item
     * @return string
     */
    private function describeItem(FormItem $item): string
    {
        return '"' . $item->getQuestion() . '" (#' . $item->getUid() . ')';
    }

    protected function handleMenu(string $currentAction): void
    {
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('OpenOapBackendForms');

        if ($currentAction === 'previewForm') {
            $menu->addMenuItem(
                $menu->makeMenuItem()
                    ->setTitle(LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:menu.show_call_form'))
                    ->setHref($this->uriBuilder->reset()->uriFor('previewForm', ['call' => $this->request->getArgument('call')]))
                    ->setActive(true)
            );
        }

        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle(LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:menu.list_forms'))
                ->setHref($this->uriBuilder->reset()->uriFor('listForms'))
                ->setActive($currentAction === 'listForms')
        );

        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);

        if ($currentAction === 'previewForm') {
            $callUid = $this->request->getArgument('call');
            $returnUrl = $this->uriBuilder->reset()->uriFor('previewForm', ['call' => $callUid]);
            $deepCopyUrl = (string)$this->backendUriBuilder->buildUriFromRoute('web_OpenOapBackendDeepCopy', [
                'call' => $callUid,
                'returnUrl' => $returnUrl,
            ]);

            $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            $deepCopyButton = $buttonBar->makeLinkButton()
                ->setHref($deepCopyUrl)
                ->setTitle(LocalizationUtility::translate('LLL:EXT:open_oap/Resources/Private/Language/locallang_backend.xlf:menu.deep_copy_call'))
                ->setShowLabelText(true)
                ->setIcon($iconFactory->getIcon('actions-document-duplicates-select', IconSize::SMALL));

            $buttonBar->addButton($deepCopyButton, ButtonBar::BUTTON_POSITION_LEFT);
        }
    }
}
