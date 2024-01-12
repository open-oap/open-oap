<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class LanguageUtility implements SingletonInterface
{
    /**
     * @return mixed|null
     */
    public static function getLanguageId()
    {
        $context = GeneralUtility::makeInstance(Context::class);
        return $context->getPropertyFromAspect('language', 'id', 0);
    }
}
