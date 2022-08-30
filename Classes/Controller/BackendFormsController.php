<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Domain\Model\Call;
use OpenOAP\OpenOap\Domain\Model\FormPage;

use Psr\Http\Message\ResponseInterface;

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
    public function showOverviewFormsAction()
    {
        $this->view->assignMultiple([
            'actionName' => $this->actionMethodName,
        ]);

        return $this->htmlResponse();
    }

    /**
     * @param Call $call
     */
    public function previewFormAction(Call $call)
    {
        $this->evaluateForm($call);
        /**
         * /typo3/module/web/OpenOapBackend
         * ?id=24
         * &tx_openoap_web_openoapbackend%5Bform%5D=1
         * &tx_openoap_web_openoapbackend%5Baction%5D=previewForm
         * &tx_openoap_web_openoapbackend%5Bcontroller%5D=OapBackend
         */
        $this->view->assignMultiple([
            'evaluationResults' => $this->evalutionResults,
            'actionName' => $this->actionMethodName,
            'call' => $call,
        ]);

        return $this->htmlResponse();
    }

    /**
     * @param array $filter
     * @param int $currentPage
     * @return ResponseInterface
     */
    public function listFormsAction(array $filter = [], int $currentPage = 1): ResponseInterface
    {
        $this->listObjects($this->callRepository, $currentPage);

        $this->view->assignMultiple([
            'actionName' => $this->actionMethodName,
        ]);

        return $this->htmlResponse();
    }

    /**
     * @param $code
     * @param $text
     * @param string $result
     */
    private function setEvaluationItem($code, $text, string $result = '')
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
}
