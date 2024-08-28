<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * Applicant
 */
class Applicant extends \In2code\Femanager\Domain\Model\User
{
    /**
     * @var string
     */
    protected $companyEmail = '';

    /**
     * @var string
     */
    protected $preferredLang = '';

    /**
     * @var bool
     */
    protected $privacypolicy = false;

    /**
     * @var int
     */
    protected $salutation = 0;

    /**
     * Proposals of this applicant
     *
     * @var ObjectStorage<Proposal>
     */
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Cascade(['value' => 'remove'])]
    protected $proposals;

    /**
     * Constructs a new Applicant
     *
     * @param string $username
     * @param string $password
     */
    public function __construct(protected string $username = '', protected string $password = '')
    {
        parent::__construct($username, $password);

        $this->proposals = new ObjectStorage();
    }

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
    public function setCompanyEmail(string $companyEmail): void
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
    public function setPreferredLang(string $preferredLang): void
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
    public function setPrivacypolicy(bool $privacypolicy): void
    {
        $this->privacypolicy = $privacypolicy;
    }

    /**
     * Returns the salutation
     *
     * @return int $salutation
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Sets the salutation
     *
     * @param int $salutation
     */
    public function setSalutation(int $salutation): void
    {
        $this->salutation = $salutation;
    }

    /**
     * Adds a Proposal
     *
     * @param Proposal $proposal
     */
    public function addProposal(Proposal $proposal): void
    {
        $this->proposals->attach($proposal);
    }

    /**
     * Removes a Proposal
     *
     * @param Proposal $proposalToRemove The Proposal to be removed
     */
    public function removeProposal(Proposal $proposalToRemove): void
    {
        $this->proposals->detach($proposalToRemove);
    }

    /**
     * Returns the proposals
     *
     * @return ObjectStorage<Proposal> proposals
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * Sets the proposals
     *
     * @param ObjectStorage<Proposal> $proposals
     */
    public function setProposals(ObjectStorage $proposals): void
    {
        $this->proposals = $proposals;
    }
}
