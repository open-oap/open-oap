<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace OpenOAP\OpenOap\ViewHelpers\ModuleLayout\Button;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\ButtonInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A ViewHelper for adding a link button to the doc header area.
 * It must be a child of :ref:`<oap:moduleLayout> <typo3-backend-modulelayout>`.
 *
 * Examples
 * --------
 *
 * Default::
 *
 *    <oap:moduleLayout>
 *        <oap:moduleLayout.button.linkButton
 *            icon="actions-add"
 *            title="Add record')}"
 *            link="{oap:uri.newRecord(table: 'tx_my_table')}"
 *        />
 *    </oap:moduleLayout>
 */
class LinkButtonViewHelper extends AbstractButtonViewHelper
{
    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('link', 'string', 'Link for the button', true);
    }

    protected static function createButton(ButtonBar $buttonBar, array $arguments, RenderingContextInterface $renderingContext): ButtonInterface
    {
        return $buttonBar->makeLinkButton()->setHref($arguments['link']);
    }
}
