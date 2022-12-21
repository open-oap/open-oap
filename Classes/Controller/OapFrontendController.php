<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Domain\Model\Comment;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\View\TemplatePaths;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * ProposalController
 */
class OapFrontendController extends OapBaseController
{
    protected string $ext = 'OpenOap';

    /**
     * @var UriBuilder|null
     */
    protected $uriBuilder;

    public function initializeAction()
    {
        parent::initializeAction();
        if (!$this->settings) {
            $this->settings = $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'openOap',
                'dashboard'
            );
        }

        $context = GeneralUtility::makeInstance(Context::class);

        $this->site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        $langId = $context->getPropertyFromAspect('language', 'id');

        $this->language = $this->site->getLanguageById($langId);
        $this->langCode = $this->language->getTwoLetterIsoCode();
    }

    /**
     * Returns the language service
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        if (($GLOBALS['LANG'] ?? null) instanceof LanguageService) {
            return $GLOBALS['LANG'];
        }
        return GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
    }

    /**
     * @param $string
     * @param $postfix
     * @return string
     */
    protected function shortenQuestion($string, $postfix = '&hellip;'): string
    {
        $shortTitle = mb_substr($string, 0, self::MAX_STR_LENGTH_ITEM_ERROR);
        if ($shortTitle !== $string) {
            $shortTitle .= $postfix;
        }
        return $shortTitle;
    }

    protected function createComment(): Comment
    {
        $comment = new Comment();
        ObjectAccess::setProperty($comment, 'pid', $this->settings['settings']['commentsPoolId']);
        $comment->setSource(self::COMMENT_SOURCE_EDIT);
        $comment->setState(self::COMMENT_STATE_NEW);
        return $comment;
    }

    /**
     * @return array
     */
    public function getJsMessages(): array
    {
        $jsMessages = [];
        $jsMsgErrorIndicies = $this->getConstants()['JSMSG'];
        foreach ($jsMsgErrorIndicies as $key => $jsMsgErrorIndex) {
            $jsMessages[$key] = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_JSMSG . $jsMsgErrorIndex);
        }

        // translatable labels for certain frontend elements
        $jsMsgLableIndicies = $this->getConstants()['JSLABEL'];
        foreach ($jsMsgLableIndicies as $key => $jsMsgLabelIndex) {
            $jsMessages[$key] = $this->getTranslationString(self::XLF_BASE_IDENTIFIER_JSLABEL . $jsMsgLabelIndex);
        }

        return $jsMessages;
    }

    /**
     * Returns an instance of TemplatePaths with custom paths added to
     * the paths configured in $GLOBALS['TYPO3_CONF_VARS']['MAIL'].
     *
     * @return TemplatePaths
     */
    protected function getMailTemplatePaths(): TemplatePaths
    {
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $view = $extbaseFrameworkConfiguration['view'];

        $pathArray = array_replace_recursive(
            [
                'layoutRootPaths'   => $GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'],
                'templateRootPaths' => $GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'],
                'partialRootPaths'  => $GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths'],
            ],
            [
                'layoutRootPaths'   => $view['layoutRootPaths'],
                'templateRootPaths' => $view['templateRootPaths'],
                'partialRootPaths'  => $view['partialRootPaths'],
            ]
        );
        return new TemplatePaths($pathArray);
    }
}
