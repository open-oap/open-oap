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
     * earliest project start
     *
     * @var \DateTime
     */
    protected $projectStartTime;

    /**
     * latest project end
     *
     * @var \DateTime
     */
    protected $projectEndTime;

    /**
     * maximum years of project duration
     *
     * @var int
     */
    protected $projectDurationMax = 0;

    /**
     * minimum years of project duration
     *
     * @var int
     */
    protected $projectDurationMin = 0;

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
     * usergroup
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup>
     */
    protected $usergroup;

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
     * Returns the projectStartTime
     *
     * @return \DateTime projectStartTime
     */
    public function getProjectStartTime()
    {
        return $this->projectStartTime;
    }

    /**
     * Sets the projectStartTime
     *
     * @param \DateTime $projectStartTime
     */
    public function setProjectStartTime(\DateTime $projectStartTime)
    {
        $this->projectStartTime = $projectStartTime;
    }

    /**
     * Returns the projectEndTime
     *
     * @return \DateTime projectEndTime
     */
    public function getProjectEndTime()
    {
        return $this->projectEndTime;
    }

    /**
     * Sets the projectEndTime
     *
     * @param \DateTime $projectEndTime
     */
    public function setProjectEndTime(\DateTime $projectEndTime)
    {
        $this->projectEndTime = $projectEndTime;
    }

    /**
     * Returns the projectDurationMax
     *
     * @return int projectDurationMax
     */
    public function getProjectDurationMax()
    {
        return $this->projectDurationMax;
    }

    /**
     * Sets the projectDurationMax
     *
     * @param int $projectDurationMax
     */
    public function setProjectDurationMax(int $projectDurationMax)
    {
        $this->projectDurationMax = $projectDurationMax;
    }

    /**
     * Returns the projectDurationMin
     *
     * @return int projectDurationMin
     */
    public function getProjectDurationMin()
    {
        return $this->projectDurationMin;
    }

    /**
     * Sets the projectDurationMin
     *
     * @param int $projectDurationMin
     */
    public function setProjectDurationMin(int $projectDurationMin)
    {
        $this->projectDurationMin = $projectDurationMin;
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
}
