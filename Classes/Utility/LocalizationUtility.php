<?php declare(strict_types=1);

namespace OpenOAP\OpenOap\Utility;

use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility as Localization;

class LocalizationUtility
{
    /**
     * @param string $key
     * @param string $extensionName
     *
     * @return string|null
     */
    public static function translate(string $key, string $extensionName = 'openOap'): ?string
    {
        return Localization::translate($key, $extensionName);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public static function translateBEContext(string $key)
    {
        $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
        return $languageService->sl($key);
    }
}
