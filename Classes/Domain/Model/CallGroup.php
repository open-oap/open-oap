<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class CallGroup extends AbstractEntity
{
    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * @var string
     */
    protected string $countryGiz = '';

    /**
     * @var string
     */
    protected string $countryDeg = '';

    /**
     * @var string
     */
    protected string $defaultGiz = '';

    /**
     * @var string
     */
    protected string $defaultDeg = '';

    /**
     * @var string
     */
    protected $blockedLanguages;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCountryGiz(): string
    {
        return $this->countryGiz;
    }

    /**
     * @param string $countryGiz
     */
    public function setCountryGiz(string $countryGiz): void
    {
        $this->countryGiz = $countryGiz;
    }

    /**
     * @return string
     */
    public function getCountryDeg(): string
    {
        return $this->countryDeg;
    }

    /**
     * @param string $countryDeg
     */
    public function setCountryDeg(string $countryDeg): void
    {
        $this->countryDeg = $countryDeg;
    }

    /**
     * @return string
     */
    public function getDefaultGiz(): string
    {
        return $this->defaultGiz;
    }

    /**
     * @param string $defaultGiz
     */
    public function setDefaultGiz(string $defaultGiz): void
    {
        $this->defaultGiz = $defaultGiz;
    }

    /**
     * @return string
     */
    public function getDefaultDeg(): string
    {
        return $this->defaultDeg;
    }

    /**
     * @param string $defaultDeg
     */
    public function setDefaultDeg(string $defaultDeg): void
    {
        $this->defaultDeg = $defaultDeg;
    }

    /**
     * @return string
     */
    public function getBlockedLanguages(): string
    {
        return $this->blockedLanguages;
    }

    /**
     * @param string $blockedLanguages
     */
    public function setBlockedLanguages(string $blockedLanguages): void
    {
        $this->blockedLanguages = $blockedLanguages;
    }
}
