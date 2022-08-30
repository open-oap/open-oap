<?php

namespace OpenOAP\OpenOap\Service;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendAccessControlService implements SingletonInterface
{
    /**
     * Frontend user id
     *
     * @return int|null
     */
    public function getFrontendUserId()
    {
        $context = GeneralUtility::makeInstance(Context::class);
        if ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn')) {
            return $context->getPropertyFromAspect('frontend.user', 'id');
        }
        return null;
    }
}
