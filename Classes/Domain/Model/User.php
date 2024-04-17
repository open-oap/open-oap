<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Ingeborg Heß <ingeborg.hess@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * User
 */
class User extends \In2code\Femanager\Domain\Model\User
{
    /**
     * companyEmail
     *
     * @var string
     */
    protected $companyEmail = '';

    /**
     * preferredLang
     *
     * @var string
     */
    protected $preferredLang = '';

    /**
     * privacypolicy
     *
     * @var bool
     */
    protected $privacypolicy;

    /**
     * Returns the companyEmail
     *
     * @return string $companyEmail
     */
    public function getCompanyEmail()
    {
        return $this->companyEmail;
    }

    /**
     * Sets the companyEmail
     *
     * @param string $companyEmail
     */
    public function setCompanyEmail(string $companyEmail)
    {
        $this->companyEmail = $companyEmail;
    }

    /**
     * Returns the preferredLang
     *
     * @return string $preferredLang
     */
    public function getPreferredLang()
    {
        return $this->preferredLang;
    }

    /**
     * Sets the preferredLang
     *
     * @param string $preferredLang
     */
    public function setPreferredLang(string $preferredLang)
    {
        $this->preferredLang = $preferredLang;
    }

    /**
     * Returns, whether the user has accepted privacy policy
     *
     * @return bool
     */
    public function isPrivacypolicy(): bool
    {
        return $this->privacypolicy;
    }

    /**
     * Set whether the user has accepted privacy policy
     *
     * @param bool $privacypolicy
     */
    public function setPrivacypolicy(bool $privacypolicy)
    {
        $this->privacypolicy = $privacypolicy;
    }
}
