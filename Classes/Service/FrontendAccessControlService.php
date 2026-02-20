<?php

namespace OpenOAP\OpenOap\Service;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendAccessControlService implements SingletonInterface
{
    public function __construct(private readonly \TYPO3\CMS\Core\Context\Context $context)
    {
    }

    /**
     * Frontend user id
     *
     * @return int|null
     */
    public function getFrontendUserId()
    {
        if ($this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn')) {
            return $this->context->getPropertyFromAspect('frontend.user', 'id');
        }

        return null;
    }
}
