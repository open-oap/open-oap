<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Domain\Model\Call;
use OpenOAP\OpenOap\Domain\Model\FormPage;
use OpenOAP\OpenOap\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;

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
     * @var array
     */
    private $evalutionResults = [];

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
     * @param Call $call
     */
    public function previewFormAction(Call $call): ResponseInterface
    {
        $this->evaluateForm($call);

        /**
         * /typo3/module/web/OpenOapBackend
         * ?id=24
         * &tx_openoap_web_openoapbackend%5Bform%5D=1
         * &tx_openoap_web_openoapbackend%5Baction%5D=previewForm
         * &tx_openoap_web_openoapbackend%5Bcontroller%5D=OapBackend
         */
        $this->moduleTemplate->assignMultiple([
            'evaluationResults' => $this->evalutionResults,
            'actionName' => $this->actionMethodName,
            'call' => $call,
        ]);

        return $this->moduleTemplate->renderResponse('PreviewForm');
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
    private function setEvaluationItem($code, $text, string $result = ''): void
    {
        $this->evalutionResults[] = ['code' => $code, 'text' => $text, 'result' => $result];
    }

    /**
     * @param Call $call
     */
    private function evaluateForm(Call $call): void
    {
        $warnings = 0;

        // intro/title

        $this->setEvaluationItem('log', 'test of call/form' . $call->getTitle());

        // count pages
        $countPages = count($call->getFormPages());
        $this->setEvaluationItem('log', 'Counts of pages', (string)$countPages);

        // count groups
        // count items

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
        if (end($submitPages) <> $pageIndex) {
            $this->setEvaluationItem('error', 'the submitpage is not the last page');
        }

        // is there an item with the title attribute - and it's only one?

        // witch items have the meta information

        // witch items are set as a filter
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
    }
}
