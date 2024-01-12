<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * Einreichung von Anträgen
 */
class Call extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var int
     */
    protected $hidden = 0;

    /**
     * Name of Call
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * Intro text
     *
     * @var string
     */
    protected $introText = '';

    /**
     * Short text
     *
     * @var string
     */
    protected $teaserText = '';

    /**
     * shortcut for proposal signatur
     *
     * @var string
     */
    protected $shortcut = '';

    /**
     * emails for system mails
     *
     * @var string
     */
    protected $emails = '';

    /**
     * starting of call
     *
     * @var \DateTime
     */
    protected $callStartTime;

    /**
     * ending of call
     *
     * @var \DateTime
     */
    protected $callEndTime;

    /**
     * Exceptions for login (before start or after closing call)
     *
     * @var string
     */
    protected $feUserExceptions = '';

    /**
     * proposalPid
     *
     * @var int
     */
    protected $proposalPid = 0;

    /**
     * formPages
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormPage>
     */
    protected $formPages;

    /**
     * items
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormItem>
     */
    protected $items;

    /**
     * usergroup
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup>
     */
    protected $usergroup;

    /**
     * @var FileReference|null
     */
    protected $wordTemplate;

    /**
     * @var string
     */
    protected string $wordStyles = '';

    /**
     * @var FileReference|null
     */
    protected $wordHeaderLogo;

    /**
     * @var FileReference|null
     */
    protected $logo;

    /**
     * @var string
     */
    protected $blockedLanguages;

    /**
     * @var bool $anonym
     */
    protected $anonym = false;

    /** @var string */
    protected string $surveyCodes = '';

    /**
     * callGroup
     *
     * @var int
     */
    protected int $callGroup = 0;

    /**
     * supporter
     *
     * @var int
     */
    protected $supporter = 0;

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
        $this->formPages = $this->formPages ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->usergroup = $this->usergroup ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Returns the title
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Returns the introText
     *
     * @return string introText
     */
    public function getIntroText()
    {
        return $this->introText;
    }

    /**
     * Sets the introText
     *
     * @param string $introText
     */
    public function setIntroText(string $introText)
    {
        $this->introText = $introText;
    }

    /**
     * Returns the teaserText
     *
     * @return string teaserText
     */
    public function getTeaserText()
    {
        return $this->teaserText;
    }

    /**
     * Sets the teaserText
     *
     * @param string $teaserText
     */
    public function setTeaserText(string $teaserText)
    {
        $this->teaserText = $teaserText;
    }

    /**
     * Returns the emails
     *
     * @return string emails
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Sets the emails
     *
     * @param string $emails
     */
    public function setEmails(string $emails)
    {
        $this->emails = $emails;
    }

    /**
     * Returns the callEndTime
     *
     * @return \DateTime callEndTime
     */
    public function getCallEndTime()
    {
        return $this->callEndTime;
    }

    /**
     * Sets the callEndTime
     *
     * @param \DateTime $callEndTime
     */
    public function setCallEndTime(\DateTime $callEndTime)
    {
        $this->callEndTime = $callEndTime;
    }

    /**
     * Returns the feUserExceptions
     *
     * @return string feUserExceptions
     */
    public function getFeUserExceptions()
    {
        return $this->feUserExceptions;
    }

    /**
     * Sets the feUserExceptions
     *
     * @param string $feUserExceptions
     */
    public function setFeUserExceptions(string $feUserExceptions)
    {
        $this->feUserExceptions = $feUserExceptions;
    }

    /**
     * Adds a FormPage
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormPage $formPage
     */
    public function addFormPage(\OpenOAP\OpenOap\Domain\Model\FormPage $formPage)
    {
        $this->formPages->attach($formPage);
    }

    /**
     * Removes a FormPage
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormPage $formPageToRemove The FormPage to be removed
     */
    public function removeFormPage(\OpenOAP\OpenOap\Domain\Model\FormPage $formPageToRemove)
    {
        $this->formPages->detach($formPageToRemove);
    }

    /**
     * Returns the formPages
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormPage> formPages
     */
    public function getFormPages()
    {
        return $this->formPages;
    }

    /**
     * Sets the formPages
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormPage> $formPages
     */
    public function setFormPages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $formPages)
    {
        $this->formPages = $formPages;
    }

    /**
     * @return int
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param int $hidden
     */
    public function setHidden(int $hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Returns the callStartTime
     *
     * @return \DateTime callStartTime
     */
    public function getCallStartTime()
    {
        return $this->callStartTime;
    }

    /**
     * Sets the callStartTime
     *
     * @param \DateTime $callStartTime
     */
    public function setCallStartTime(\DateTime $callStartTime)
    {
        $this->callStartTime = $callStartTime;
    }

    /**
     * Returns the proposalPid
     *
     * @return int $proposalPid
     */
    public function getProposalPid()
    {
        return $this->proposalPid;
    }

    /**
     * Sets the proposalPid
     *
     * @param int $proposalPid
     */
    public function setProposalPid(int $proposalPid)
    {
        $this->proposalPid = $proposalPid;
    }

    /**
     * Returns the usergroup
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup> usergroup
     */
    public function getUsergroup()
    {
        return $this->usergroup;
    }

    /**
     * Sets the usergroup
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup> $usergroup
     */
    public function setUsergroup(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroup)
    {
        $this->usergroup = $usergroup;
    }

    /**
     * @return string
     */
    public function getShortcut(): string
    {
        return $this->shortcut;
    }

    /**
     * @param string $shortcut
     */
    public function setShortcut(string $shortcut): void
    {
        $this->shortcut = $shortcut;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getItems(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->items;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $items
     */
    public function setItems(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $items): void
    {
        $this->items = $items;
    }

    /**
     * @return FileReference|null
     */
    public function getWordTemplate()
    {
        return $this->wordTemplate;
    }

    /**
     * @param FileReference|null $wordTemplate
     */
    public function setWordTemplate($wordTemplate): void
    {
        $this->wordTemplate = $wordTemplate;
    }

    /**
     * @return FileReference|null
     */
    public function getLogo(): ?FileReference
    {
        return $this->logo;
    }

    /**
     * @return FileReference|null
     */
    public function getWordHeaderLogo(): ?FileReference
    {
        return $this->wordHeaderLogo;
    }

    /**
     * @param FileReference|null $wordHeaderLogo
     */
    public function setWordHeaderLogo(?FileReference $wordHeaderLogo): void
    {
        $this->wordHeaderLogo = $wordHeaderLogo;
    }

    /**
     * @param FileReference|null $logo
     */
    public function setLogo(?FileReference $logo): void
    {
        $this->logo = $logo;
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

    /**
     * @return string
     */
    public function getWordStyles(): string
    {
        return $this->wordStyles;
    }

    /**
     * @param string $wordStyles
     */
    public function setWordStyles(string $wordStyles): void
    {
        $this->wordStyles = $wordStyles;
    }

    public function isAnonym(): bool
    {
        return $this->anonym;
    }

    public function setAnonym(bool $anonym): void
    {
        $this->anonym = $anonym;
    }

    public function getSurveyCodes(): string
    {
        return $this->surveyCodes;
    }

    public function setSurveyCodes(string $surveyCodes): void
    {
        $this->surveyCodes = $surveyCodes;
    }

    /**
     * @return int
     */
    public function getCallGroup(): int
    {
        return $this->callGroup;
    }

    /**
     * @param int $callGroup
     */
    public function setCallGroup(int $callGroup): void
    {
        $this->callGroup = $callGroup;
    }

    /**
     * @return int
     */
    public function getSupporter(): int
    {
        return $this->supporter;
    }

    /**
     * @param int $supporter
     */
    public function setSupporter(int $supporter): void
    {
        $this->supporter = $supporter;
    }
}
