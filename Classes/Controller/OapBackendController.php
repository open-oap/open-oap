<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Domain\Model\Answer;
use OpenOAP\OpenOap\Domain\Model\Comment;
use OpenOAP\OpenOap\Domain\Model\Proposal;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\View\TemplatePaths;

/***
 *
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
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
class OapBackendController extends OapBaseController
{
    protected string $ext = 'open_oap';

    /**
     * @var UriBuilder|null
     */
    protected $backendUriBuilder;

    /**
     * @var int
     */
    protected int $pageUid = 0;

    /**
     * @var string
     */
    protected string $siteIdentifier = '';

    public function initializeAction(): void
    {
        parent::initializeAction();
        // set messageSource
        $this->messageSource = 'LLL:EXT:' . $this->ext . '/Resources/Private/Language/' . $this->messageFile . ':message.';

        $this->pageUid = (int)($GLOBALS['_GET']['id'] ?? 0);
        $this->siteIdentifier = $GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getIdentifier();

        $this->backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
    }

    /**
     * @param array $allItems
     * @param int $currentPage
     * @param int $itemsPerPage
     * @return array
     */
    protected function createPaginator(array $allItems, int $currentPage, int $itemsPerPage = 50, int $type = self::PAGINATOR_PAGES): array
    {
        $pagination['array'] = [];
        $pagination['pagination'] = null;

        if (!count($allItems)) {
            $this->setMessage('no_calls_found', self::WARNING);
        } else {
            // todo use constant instead of magic number - even here
            $pagination['array'] = new ArrayPaginator($allItems, $currentPage, $itemsPerPage);
            $pagination['pagination'] = new SimplePagination($pagination['array']);
            $this->view->assign('pages', range(1, $pagination['pagination']->getLastPageNumber()));
            $minPage = 1;
            $maxPage = count($allItems);
            $pagesBefore = self::PAGINATOR_SHOW_BEFORE;
            $pagesAfter = self::PAGINATOR_SHOW_AFTER;

            if ($maxPage > $pagesBefore + $pagesAfter) {
                $minPage = max($minPage, $currentPage - $pagesBefore);
                if ($minPage === 1) {
                    $pagesAfter += $pagesBefore - ($currentPage - $minPage);
                }
                $maxPage = min($maxPage, $currentPage + $pagesAfter);
                if (($maxPage - $currentPage) < $pagesAfter) {
                    $remainingPages = $pagesAfter - ($maxPage - $currentPage);
                    $minPage = max(1, $minPage - $remainingPages);
                }
            }
            $pagination['show'] = [
                'minPage' => $minPage,
                'maxPage' => $maxPage,
            ];
            if ($type == self::PAGINATOR_ITEMS) {
                $pagination['buttons']['first'] = 0;
                $pagination['buttons']['previous'] = 0;
                $pagination['buttons']['next'] = 0;
                $pagination['buttons']['last'] = 0;
                if ($currentPage > 1) {
                    $pagination['buttons']['first'] = $allItems[0]['uid'];
                    $pagination['buttons']['previous'] = $allItems[$currentPage - 2]['uid'];
                }
                $pagination['buttons']['current'] = $allItems[$currentPage - 1];
                if ($currentPage < count($allItems)) {
                    $pagination['buttons']['next'] = $allItems[$currentPage]['uid'];
                    $pagination['buttons']['last'] = $allItems[count($allItems) - 1]['uid'];
                }
            }
        }
        return $pagination;
    }

    /*
     * overview page
     *
     * @return void
     */
    public function showOverviewAction(): void
    {
        echo __FUNCTION__;
        die();
    }

    /*
     * release notes
     *
     * @return void
     */
    public function showReleaseNotesAction(): \Psr\Http\Message\ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * @param string $messageId
     * @param int $messageType
     */
    protected function setMessage(string $messageId, int $messageType): void
    {
        $messageText = $this->getLanguageService()->sL($this->messageSource . $messageId);

        $this->addFlashMessage(
            $messageText,
            '',
            $messageType,
            false
        );
    }

    /**
     * @param int $currentPage
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function listObjects($repository, int $currentPage): void
    {
        $moduleUri = $this->backendUriBuilder->buildUriFromRoutePath('/module/web/OpenOapBackendforms');
        $allItems = [];
        $arrayPaginator = null;
        $pagination = null;
        if (!$this->pageUid) {
            $this->setMessage('no_page_selected', self::WARNING);
            // use fall-back
            $callPid = (int)$this->settings['callPid'] ?? 0;
        } else {
            $callPid = $this->pageUid;
        }
        $allItems = $repository->findAllByPid($callPid)->toArray();
        if (!count($allItems)) {
            $this->setMessage('no_calls_found', self::WARNING);
        } else {
            $arrayPaginator = new ArrayPaginator($allItems, $currentPage, 100);
            $pagination = new SimplePagination($arrayPaginator);
            $this->view->assign('pages', range(1, $pagination->getLastPageNumber()));
        }

        $this->view->assignMultiple(
            [
                'moduleUri' => $moduleUri,
                'paginator' => $arrayPaginator,
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * Get URI to create a new record in backend
     *
     * @param string $tableName
     * @param int $poolId pool identifier to store the new record in
     * @return string
     */
    protected function getBackendNewUri(string $tableName, int $poolId): string
    {
        $uriParameters = [
            'edit' => [
                $tableName => [
                    $poolId => 'new',
                ],
            ],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];

        return (string)$this->backendUriBuilder->buildUriFromRoute('record_edit', $uriParameters);
    }

    /**
     * Create comment for a proposal
     *
     * @param string $text
     * @param Proposal $proposal
     * @param Answer|null $answer
     * @return Comment $comment
     */
    protected function createComment(string $text, Proposal $proposal, Answer $answer=null): Comment
    {
        $beUser = GeneralUtility::makeInstance(BackendUserRepository::class)->findByUid($GLOBALS['BE_USER']->user['uid']);
        $comment = new Comment();
        $comment->setCreated(time());
        ObjectAccess::setProperty($comment, 'pid', $this->settings['commentsPoolId']);
        if ($answer != null) {
            $comment->setAnswer($answer);
            $comment->setSource(self::COMMENT_SOURCE_EDIT_ANSWER);
        } else {
            $comment->setSource(self::COMMENT_SOURCE_EDIT);
        }
        $comment->setState(self::COMMENT_STATE_NEW);
        $comment->setProposal($proposal);
        $comment->setText($text);
        $comment->setAuthor($beUser);
        return $comment;
    }

    /**
     * Returns an instance of TemplatePaths with custom paths added to
     * the paths configured in $GLOBALS['TYPO3_CONF_VARS']['MAIL'].
     *
     * @return TemplatePaths
     */
    protected function getMailTemplatePaths(): TemplatePaths
    {
        $backendConfigurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager::class);
        $typoscriptSetup = $backendConfigurationManager->getTypoScriptSetup();
        $view = $typoscriptSetup['module.']['tx_openoap_web_openoapbackendproposals.']['view.'];
        $pathArray = array_replace_recursive(
            [
                'layoutRootPaths'   => $GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'],
                'templateRootPaths' => $GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'],
                'partialRootPaths'  => $GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths'],
            ],
            [
                'layoutRootPaths'   => $view['layoutRootPaths.'],
                'templateRootPaths' => $view['templateRootPaths.'],
                'partialRootPaths'  => $view['partialRootPaths.'],
            ]
        );
        return new TemplatePaths($pathArray);
    }
}
