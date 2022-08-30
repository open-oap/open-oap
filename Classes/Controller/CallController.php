<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

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
 * CallController
 */
class CallController extends OapFrontendController
{
    /**
     * action list
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAction(): \Psr\Http\Message\ResponseInterface
    {
        $calls = $this->callRepository->findAll();
        $this->view->assign('calls', $calls);
        return $this->htmlResponse();
    }
}
