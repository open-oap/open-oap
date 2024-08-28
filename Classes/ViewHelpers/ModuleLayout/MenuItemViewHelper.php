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

namespace OpenOAP\OpenOap\ViewHelpers\ModuleLayout;

use OpenOAP\OpenOap\ViewHelpers\ModuleLayoutViewHelper;
use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\Exception;

/**
 * A ViewHelper for adding a menu item to a doc header menu.
 * It must be a child of :ref:`<oap:moduleLayout.menu> <typo3-backend-modulelayout-menu>`.
 *
 * Examples
 * ========
 *
 * Default::
 *
 *    <oap:moduleLayout>
 *        <oap:moduleLayout.menu identifier="MenuIdentifier">
 *            <oap:moduleLayout.menuItem label="Menu item 1" uri="{f:uri.action(action: 'index')}"/>
 *        </oap:moduleLayout.menu>
 *    </oap:moduleLayout>
 */
class MenuItemViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('label', 'string', 'Label of the menu item', true);
        $this->registerArgument('uri', 'string', 'Action uri', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): void
    {
        $request = $renderingContext->getRequest();
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        self::ensureProperNesting($viewHelperVariableContainer);

        /** @var Menu $menu */
        $menu = $viewHelperVariableContainer->get(ModuleLayoutViewHelper::class, Menu::class);
        $menuItem = $menu->makeMenuItem();
        $menuItem->setTitle($arguments['label']);
        $menuItem->setHref($arguments['uri']);
        $menuItem->setActive($request->getAttribute('normalizedParams')->getRequestUri() === $arguments['uri']);
        $menu->addMenuItem($menuItem);
    }

    /**
     * @param ViewHelperVariableContainer $viewHelperVariableContainer
     * @throws Exception
     */
    private static function ensureProperNesting(ViewHelperVariableContainer $viewHelperVariableContainer): void
    {
        if (!$viewHelperVariableContainer->exists(ModuleLayoutViewHelper::class, Menu::class)) {
            throw new Exception(sprintf('%s must be nested in <oap:moduleLayout.menu> ViewHelper', self::class), 1531235592);
        }
    }
}
