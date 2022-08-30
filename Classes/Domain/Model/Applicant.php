<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * Applicant
 */
class Applicant extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
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
    protected $privacypolicy = false;

    /**
     * salutation
     *
     * @var int
     */
    protected $salutation = 0;

    /**
     * Proposals of this applicant
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Proposal>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $proposals;

    /**
     * __construct
     */
    public function __construct()
    {
        // Do not remove the next line: It would break the functionality
        $this->initializeObject();
    }

    /**
     * Initializes all ObjectStorage properties when model is reconstructed from DB (where __construct is not called)
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     */
    public function initializeObject()
    {
        $this->proposals = $this->proposals ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
    public function setSalutation(int $salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * Adds a Proposal
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Proposal $proposal
     */
    public function addProposal(\OpenOAP\OpenOap\Domain\Model\Proposal $proposal)
    {
        $this->proposals->attach($proposal);
    }

    /**
     * Removes a Proposal
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Proposal $proposalToRemove The Proposal to be removed
     */
    public function removeProposal(\OpenOAP\OpenOap\Domain\Model\Proposal $proposalToRemove)
    {
        $this->proposals->detach($proposalToRemove);
    }

    /**
     * Returns the proposals
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Proposal> proposals
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * Sets the proposals
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Proposal> $proposals
     */
    public function setProposals(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $proposals)
    {
        $this->proposals = $proposals;
    }
}
