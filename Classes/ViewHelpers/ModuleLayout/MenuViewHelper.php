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
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\Exception;

/**
 * A ViewHelper for adding a menu to the doc header area
 * of :ref:`<oap:moduleLayout> <typo3-backend-modulelayout>`. It accepts only
 * :ref:`<oap:moduleLayout.menuItem> <typo3-backend-modulelayout-menuitem>` view
 * helpers as children.
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
class MenuViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('identifier', 'string', 'Identifier of the menu', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @throws Exception
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): void
    {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        self::ensureProperNesting($viewHelperVariableContainer);

        /** @var ModuleTemplate $moduleTemplate */
        $moduleTemplate = $viewHelperVariableContainer->get(ModuleLayoutViewHelper::class, ModuleTemplate::class);
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier($arguments['identifier']);

        $viewHelperVariableContainer->add(ModuleLayoutViewHelper::class, Menu::class, $menu);
        $renderChildrenClosure();
        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
        $viewHelperVariableContainer->remove(ModuleLayoutViewHelper::class, Menu::class);
    }

    /**
     * @param ViewHelperVariableContainer $viewHelperVariableContainer
     * @throws Exception
     */
    private static function ensureProperNesting(ViewHelperVariableContainer $viewHelperVariableContainer): void
    {
        if (!$viewHelperVariableContainer->exists(ModuleLayoutViewHelper::class, ModuleTemplate::class)) {
            throw new Exception(sprintf('%s must be nested in <oap:moduleLayout> ViewHelper', self::class), 1531235715);
        }
        if ($viewHelperVariableContainer->exists(ModuleLayoutViewHelper::class, Menu::class)) {
            throw new Exception(sprintf('%s can not be nested in <oap:moduleLayout.menu> ViewHelper', self::class), 1531235777);
        }
    }
}
