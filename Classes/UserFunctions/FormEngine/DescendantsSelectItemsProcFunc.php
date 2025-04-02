<?php
declare(strict_types = 1);
namespace OpenOAP\OpenOap\UserFunctions\FormEngine;

use OpenOAP\OpenOap\Domain\Repository\FormGroupRepository;
use OpenOAP\OpenOap\Domain\Repository\FormItemRepository;
use OpenOAP\OpenOap\Domain\Repository\FormPageRepository;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * A user function used in select
 */
class DescendantsSelectItemsProcFunc
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var FormPageRepository
     */
    protected $formPageRepository;

    /**
     * @var FormGroupRepository
     */
    protected $formGroupRepository;

    /**
     * @var FormItemRepository
     */
    protected $formItemRepository;

    /**
     * @var int $depth
     */
    protected int $depth = 10;

    /** @var array $childPids */
    protected array $childPids = [];

    /** @var array $titles */
    protected array $titles = [];

    /**
     * @param PageRepository $pageRepository
     */
    public function injectPageRepository(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param FormPageRepository $formPageRepository
     */
    public function injectFormPageRepository(FormPageRepository $formPageRepository)
    {
        $this->formPageRepository = $formPageRepository;
    }

    /**
     * @param FormGroupRepository $formGroupRepository
     */
    public function injectFormGroupRepository(FormGroupRepository $formGroupRepository)
    {
        $this->formGroupRepository = $formGroupRepository;
    }

    /**
     * @param FormItemRepository $formItemRepository
     */
    public function injectFormItemRepository(FormItemRepository $formItemRepository)
    {
        $this->formItemRepository = $formItemRepository;
    }

    /**
     * Process custom options
     *
     * @param array $params
     */
    public function render():array
    {
        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item" style="padding: 5px; background-color: red;">';
//        $html[] = $fieldInformationHtml;
        $html[] =   '<div class="form-wizards-wrap">';
        $html[] =      '<div class="form-wizards-element">';
        $html[] =         '<div class="form-control-wrap">';
        $html[] =            '<input type="text" value="' . htmlspecialchars('yepp', ENT_QUOTES) . '" ';
//        $html[]=               GeneralUtility::implodeAttributes($attributes, true);
        $html[]=               'yepp text';
        $html[]=            ' />';
        $html[] =         '</div>';
        $html[] =      '</div>';
        $html[] =   '</div>';
        $html[] = '</div>';
        $resultArray['html'] = implode(LF, $html);
        return $resultArray;
    }

    private function initialize(array $params): void
    {
        if (!$this->formPageRepository) {
            $this->formPageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FormPageRepository::class);
        }
        if (!$this->formGroupRepository) {
            $this->formGroupRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FormGroupRepository::class);
        }
        if (!$this->formItemRepository) {
            $this->formItemRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FormItemRepository::class);
        }

        $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $typoScript = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,'sitepackage'
        );

        $pidKey = $params['config']['itemsProcConfig']['pidRoot'];
        $pidRoot = (int)$typoScript['plugin.']['tx_openoap_dashboard.']['settings.'][$pidKey];

        $childPidsString = $this->queryGeneratorGetTreeList($pidRoot, $this->depth); //Will be a string like 1,2,3
        $this->childPids = explode(',',$childPidsString );

        $this->titles = $this->collectPageTitle($this->childPids, $pidRoot);

    }
    public function getAllElementsOfFormPages(array &$params): void
    {
        $this->initialize($params);
        $elementRepository = $this->formPageRepository;
        $data['staticTCARecord'] = 'tcarecords-tx_openoap_domain_model_formpage-default';
        $data['firstTitleAttribute'] = 'internalTitle';
        $data['secondTitleAttribute'] = 'title';

        $params = $this->buildParamItems(
            $params,
            $elementRepository,
            $data
        );
    }

    public function getAllElementsOfFormGroups(array &$params): void
    {
        $this->initialize($params);

        $params['items'] = [];
        foreach ($this->childPids as $pageId) {
            $elements = $this->formGroupRepository->findAllByPid((integer) $pageId);
            foreach ($elements as $element) {
                if (isset($params['config']['itemsProcConfig']['type'])) {
                    if ($element->getType() !== (integer) $params['config']['itemsProcConfig']['type']) {
                        continue;
                    }
                }
                // todo no internal title for groups (yet)
                $title = ($element->getInternalTitle()) ? $element->getInternalTitle() : $element->getTitle();
                if ($this->titles[$pageId] !== '') {
                    $label = $this->titles[$pageId] . ' / ' . $title;
                } else {
                    $label = $title;
                }

                $params['items'][] = [$label, (integer) $element->getUid(), 'tcarecords-tx_openoap_domain_model_formgroup-default'];
            }
        }
    }

    public function getAllElementsOfFormItems(array &$params): void
    {
        $this->initialize($params);
        $params['items'] = [];
        foreach ($this->childPids as $pageId) {
            $elements = $this->formItemRepository->findAllByPid((integer) $pageId);

            foreach ($elements as $element) {
                // todo no internal title for groups (yet)
                $title = ($element->getInternalTitle()) ? $element->getInternalTitle() : $element->getQuestion();
                if ($this->titles[$pageId] !== '') {
                    $label = $this->titles[$pageId] . ' / ' . $title;
                } else {
                    $label = $title;
                }

                $params['items'][] = [$label, (integer) $element->getUid(), 'tcarecords-tx_openoap_domain_model_formitem-default'];
            }
        }
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
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,'sitepackage'
        );
        $proposalsPoolRoot = (int)($typoScript['plugin.']['tx_openoap_dashboard.']['settings.']['proposalsPoolId'] ?? 0);

        $table = $params['table'] ?? '';
        // Return early if no table is defined
        if (empty($table)) {
            throw new \UnexpectedValueException('No table is given.', 1381823570);
        }

        // v12-Solution - something like:
        // $this->poolRepository->getPageIdsRecursive();
        // https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97027-ContentObjectRenderer-getTreeList.html

        $childPids = $this->queryGeneratorGetTreeList($proposalsPoolRoot, $this->depth); //Will be a string like 1,2,3
        $childPids = explode(',',$childPids );

        /**@var \TYPO3\CMS\Core\Domain\Repository\PageRepository $pageRepository */
        if (!$this->pageRepository) {
            $this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Domain\Repository\PageRepository::class
            );
        }
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
                $label =  $prefix . $label;
                $title[$uid] = $label;
            }
            $params['items'][] = [$label, (integer) $uid];

        }
    }

    /**
     * @param $childPids
     * @param $pidRoot
     * @return array
     */
    protected function collectPageTitle($childPids, $pidRoot): array
    {
        /**@var \TYPO3\CMS\Core\Domain\Repository\PageRepository $pageRepository */
        if (!$this->pageRepository) {
            $this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Domain\Repository\PageRepository::class
            );
        }

        $titles = [];
        foreach ($childPids as $pageId) {
            $titles[$pageId] = '';
            if ($pageId == $pidRoot) {
                continue;
            }
            $page = $this->pageRepository->getPage($pageId);
            $uid = $page['uid'];
            $pid = $page['pid'];
            $label = $titles[$uid] = $page['title'];

            if ($titles[$pid]) {
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
     * @param array $params
     * @param FormPageRepository|FormItemRepository|FormGroupRepository $elementRepository
     * @param array $data
     * @return array
     */
    // definition for 8.x
    // protected function buildParamItems( array $params, FormPageRepository|FormItemRepository|FormGroupRepository $elementRepository): array {
    protected function buildParamItems( array $params, $elementRepository, $data): array {
        $params['items'] = [];
        foreach ($this->childPids as $pageId) {
            $elements = $elementRepository->findAllByPid((integer)$pageId);

            foreach ($elements as $element) {
                // $title = ($element->getInternalTitle()) ? $element->getInternalTitle() : $element->getTitle();
                $title = ($element->_getProperty($data['firstTitleAttribute'])) ? $element->_getProperty($data['firstTitleAttribute'])
                    : $element->_getProperty($data['secondTitleAttribute']);
                if ($this->titles[$pageId] !== '') {
                    $label = $this->titles[$pageId] . ' / ' . $title;
                } else {
                    $label = $title;
                }

                $params['items'][] = [$label, (integer)$element->getUid(), $data['staticTCARecord']];
            }
        }
        return $params;
    }

    /**
     * Recursively fetch all descendants of a given page
     *
     * @param int $id uid of the page
     * @param int $depth
     * @param int $begin
     * @param string $permClause
     * @return string comma separated list of descendant pages
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
}
