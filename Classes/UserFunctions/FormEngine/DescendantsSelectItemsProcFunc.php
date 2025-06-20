<?php
declare(strict_types=1);

namespace OpenOAP\OpenOAP\UserFunctions\FormEngine;

use OpenOAP\OpenOap\Domain\Repository\FormGroupRepository;
use OpenOAP\OpenOap\Domain\Repository\FormItemRepository;
use OpenOAP\OpenOap\Domain\Repository\FormPageRepository;
use OpenOAP\OpenOap\Domain\Repository\GroupTitleRepository;
use OpenOAP\OpenOap\Domain\Repository\ItemOptionRepository;
use OpenOAP\OpenOap\Domain\Repository\ItemValidatorRepository;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * A user function used in select
 */
class DescendantsSelectItemsProcFunc
{
    protected PageRepository $pageRepository;
    protected FormPageRepository $formPageRepository;
    protected FormGroupRepository $formGroupRepository;
    protected GroupTitleRepository $groupTitleRepository;
    protected FormItemRepository $formItemRepository;
    protected ItemOptionRepository $itemOptionRepository;
    protected ItemValidatorRepository $itemValidatorRepository;
    protected int $depth = 10;

    /** @var array<int> $childPids */
    protected array $childPids = [];

    /** @var array<string> $titles */
    protected array $titles = [];

    public function __construct(
        PageRepository          $pageRepository,
        FormPageRepository      $formPageRepository,
        FormGroupRepository     $formGroupRepository,
        FormItemRepository      $formItemRepository,
        ItemOptionRepository    $itemOptionRepository,
        GroupTitleRepository    $groupTitleRepository,
        ItemValidatorRepository $itemValidatorRepository
    )
    {
        $this->pageRepository = $pageRepository;
        $this->formPageRepository = $formPageRepository;
        $this->formGroupRepository = $formGroupRepository;
        $this->formItemRepository = $formItemRepository;
        $this->itemOptionRepository = $itemOptionRepository;
        $this->itemValidatorRepository = $itemValidatorRepository;
        $this->groupTitleRepository = $groupTitleRepository;
    }

    /**
     * Process custom options
     *
     * @return array<string>
     */
    public function render(): array
    {
        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item" style="padding: 5px; background-color: red;">';
        $html[] = '<div class="form-wizards-wrap">';
        $html[] = '<div class="form-wizards-element">';
        $html[] = '<div class="form-control-wrap">';
        $html[] = '<input type="text" value="' . htmlspecialchars('yepp', ENT_QUOTES) . '" ';
        $html[] = '-- will be replaced by the value of the selected item';
        $html[] = ' />';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $resultArray['html'] = implode(LF, $html);
        return $resultArray;
    }

    private function initialize(array $params): void
    {
        $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $typoScript = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT, 'sitepackage'
        );

        $pidKey = $params['config']['itemsProcConfig']['pidRoot'];
        $pidRootsRaw = $typoScript['plugin.']['tx_openoap_dashboard.']['settings.'][$pidKey];

        $pidRoots = GeneralUtility::trimExplode(',', $pidRootsRaw);
        // If there is more than one root ID, we need the titles to distinguish between them
        $rootLevelWithTitle = (count($pidRoots) > 1);

        $childPidsStrings = $this->pageRepository->getPageIdsRecursive($pidRoots, $this->depth);
        $this->childPids = GeneralUtility::trimExplode(',', implode(',', $childPidsStrings));
        $this->titles = $this->collectPageTitle($this->childPids, $pidRoots, $rootLevelWithTitle);

    }

    public function getAllElementsOfFormPages(array &$params): void
    {
        $this->getAllElements(
            $params,
            $this->formPageRepository,
            'getInternalTitle',
            'tcarecords-tx_openoap_domain_model_formpage-default',
            'getTitle'
        );
    }

    public function getAllElementsOfFormGroups(array &$params): void
    {
        $this->getAllElements(
            $params,
            $this->formGroupRepository,
            'getInternalTitle',
            'tcarecords-tx_openoap_domain_model_formgroup-default',
            'getTitle'
        );
    }

    public function getAllElementsOfFormGroupTitles(array &$params): void
    {
        $this->getAllElements(
            $params,
            $this->groupTitleRepository,
            'getInternalTitle',
            'tcarecords-tx_openoap_domain_model_formgroup-default',
            'getTitle'
        );
    }

    public function getAllElementsOfFormItems(array &$params): void
    {
        $this->getAllElements(
            $params,
            $this->formItemRepository,
            'getInternalTitle',
            'tcarecords-tx_openoap_domain_model_formitem-default',
            'getQuestion'
        );
    }

    public function getAllElementsOfItemOptions(array &$params): void
    {
        $this->getAllElements(
            $params,
            $this->itemOptionRepository,
            'getInternalTitle',
            'tcarecords-tx_openoap_domain_model_itemoption-default',
            'getTitle'
        );
    }

    public function getAllElementsOfItemValidators(array &$params): void
    {
        $this->getAllElements(
            $params,
            $this->itemValidatorRepository,
            'getTitle',
            'tcarecords-tx_openoap_domain_model_itemvalidator-default'
        );
    }

    /**
     * @param array $params
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getPoolPages(array &$params): void
    {
        $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $typoScript = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT, 'sitepackage'
        );
        $proposalsPoolRoot = (int)($typoScript['plugin.']['tx_openoap_dashboard.']['settings.']['proposalsPoolId'] ?? 0);

        $table = $params['table'] ?? '';
        // Return early if no table is defined
        if (empty($table)) {
            throw new \UnexpectedValueException('No table is given.', 1381823570);
        }

        // v11-Solution
        // $childPids = $this->queryGeneratorGetTreeList($proposalsPoolRoot, $this->depth); //Will be a string like 1,2,3
        // $childPids = explode(',', $childPids);

        /**@var \TYPO3\CMS\Core\Domain\Repository\PageRepository $pageRepository */
        if (!$this->pageRepository) {
            $this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Domain\Repository\PageRepository::class
            );
        }

        // v12-Solution
        $childPids = $this->pageRepository->getPageIdsRecursive([$proposalsPoolRoot], $this->depth);

        $params['items'] = [];
        $title = [];
        foreach ($childPids as $pageId) {
            if ($pageId == $proposalsPoolRoot) {
                continue;
            }
            $page = $this->pageRepository->getPage($pageId);
            $uid = $page['uid'];
            $pid = $page['pid'];
            $label = $title[$uid] = $page['title'];
            if ($pid !== $proposalsPoolRoot) {
                if (!empty($title[$pid])) {
                    $prefix = trim($title[$pid]) . ' / ';
                } else {
                    $prefix = '';
                }
                $label = $prefix . $label;
                $title[$uid] = $label;
            }
            $params['items'][] = [$label, (integer)$uid];

        }
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
            $titles[$pageId] = '';
            if (in_array($pageId, $pidRoots) and ! $rootLevelWithTitle) {
                continue;
            }
            $page = $this->pageRepository->getPage($pageId);
            $uid = $page['uid'];
            $pid = $page['pid'];
            $label = $titles[$uid] = $page['title'];

            if (!empty($titles[$pid])) {
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
     * Recursively fetch all descendants of a given page
     *
     * @param int $id uid of the page
     * @param int $depth
     * @param int $begin
     * @param string $permClause
     * @return string comma separated list of descendant pages
     *
     * @deprecated This function is no longer required in V12. See also$this->pageRepository->getPageIdsRecursive
     *
     */
    protected function queryGeneratorGetTreeList(int $id, int $depth, int $begin = 0, string $permClause = ''): string
    {
        if ($id < 0) {
            $id = abs($id);
        }
        if ($begin == 0) {
            $theList = (string)$id;
        } else {
            $theList = '';
        }
        if ($id && $depth > 0) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
            $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $queryBuilder->select('uid')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($id, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('sys_language_uid', 0)
                )
                ->orderBy('uid');
            if ($permClause !== '') {
                $queryBuilder->andWhere(QueryHelper::stripLogicalOperatorPrefix($permClause));
            }
            $statement = $queryBuilder->executeQuery();
            while ($row = $statement->fetchAssociative()) {
                if ($begin <= 0) {
                    $theList .= ',' . $row['uid'];
                }
                if ($depth > 1) {
                    $theSubList = $this->queryGeneratorGetTreeList($row['uid'], $depth - 1, $begin - 1, $permClause);
                    if (!empty($theList) && !empty($theSubList) && ($theSubList[0] !== ',')) {
                        $theList .= ',';
                    }
                    $theList .= $theSubList;
                }
            }
        }
        return $theList;
    }

    protected function getAllElements(
        array   &$params,
                $repository,
        string  $titleAttribute,
        string  $tcaRecord,
        ?string $fallbackTitleAttribute = null
    ): void
    {
        $this->initialize($params);
        $params['items'] = [];

        foreach ($this->childPids as $pageId) {
            $elements = $repository->findAllByPid((int)$pageId);

            foreach ($elements as $element) {
                if (isset($params['config']['itemsProcConfig']['type']) and $params['config']['itemsProcConfig']['type'] !== '0') {
                    if (method_exists($element, 'getType') &&
                        $element->getType() !== (int)$params['config']['itemsProcConfig']['type']) {
                        continue;
                    }
                }

                $title = $this->getElementTitle($element, $titleAttribute, $fallbackTitleAttribute);
                $label = $this->createLabel((int)$pageId, $title, $element);

                $params['items'][] = [
                    $label,
                    (int)$element->getUid(),
                    $tcaRecord
                ];
            }
        }
    }

    protected function getElementTitle($element, string $titleAttribute, ?string $fallbackTitleAttribute = null): string
    {
        $title = '';

        if (method_exists($element, $titleAttribute)) {
            $title = $element->$titleAttribute();
        }

        if (empty($title) && $fallbackTitleAttribute !== null && method_exists($element, $fallbackTitleAttribute)) {
            $title = $element->$fallbackTitleAttribute();
        }

        return $title ?: '[Kein Titel]';
    }

    protected function createLabel(int $pageId, string $title, $element): string
    {
        $label = $this->titles[$pageId] !== ''
            ? $this->titles[$pageId] . ' / ' . $title
            : $title;

        return $label . ' [' . (int)$element->getUid() . ']';
    }
}