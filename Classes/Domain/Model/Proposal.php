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
 * Proposal
 */
class Proposal extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * title
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected string $title = '';

    /**
     * signature
     *
     * @var int
     */
    protected $signature = 0;

    /**
     * state
     *
     * @var int
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected int $state = 0;

    /**
     * feLanguageUid
     *
     * @var int
     */
    protected $feLanguageUid;

    /**
     * archived
     *
     * @var bool
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $archived = false;

    /**
     * metaInformation
     *
     * @var string
     */
    protected $metaInformation = '';

    /**
     * call
     *
     * @var \OpenOAP\OpenOap\Domain\Model\Call
     */
    protected $call;

    /**
     * @var int
     */
    protected $editTstamp;

    /**
     * answers
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Answer>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $answers;

    /**
     * comments
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Comment>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $comments;

    /**
     * applicant
     *
     * @var \OpenOAP\OpenOap\Domain\Model\Applicant
     */
    protected $applicant;

    /**
     * @param int $pageNumber
     * @return FormPage
     */
    public function getPage(int $pageNumber): FormPage
    {
        $page = null;
        if ($pageNumber > 0 and $pageNumber <= count($this->getCall()->getFormPages())) {
            $page = $this->getCall()->getFormPages()->offsetGet($pageNumber - 1);
        }
        return $page;
    }

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
        $this->answers = $this->answers ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->comments = $this->comments ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
     * Returns the state
     *
     * @return int state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the state
     *
     * @param int $state
     */
    public function setState(int $state)
    {
        $this->state = $state;
    }

    /**
     * Returns the feLanguageUid
     *
     * @return int feLanguageUid
     */
    public function getFeLanguageUid()
    {
        return $this->feLanguageUid;
    }

    /**
     * Sets the feLanguageUid
     *
     * @param int $feLanguageUid
     */
    public function setFeLanguageUid(int $feLanguageUid)
    {
        $this->feLanguageUid = $feLanguageUid;
    }

    /**
     * Returns the call
     *
     * @return \OpenOAP\OpenOap\Domain\Model\Call call
     */
    public function getCall()
    {
        return $this->call;
    }

    /**
     * Sets the call
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Call $call
     */
    public function setCall(\OpenOAP\OpenOap\Domain\Model\Call $call)
    {
        $this->call = $call;
    }

    /**
     * Adds a Answer
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Answer $answer
     */
    public function addAnswer(\OpenOAP\OpenOap\Domain\Model\Answer $answer)
    {
        $this->answers->attach($answer);
    }

    /**
     * Removes a Answer
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Answer $answerToRemove The Answer to be removed
     */
    public function removeAnswer(\OpenOAP\OpenOap\Domain\Model\Answer $answerToRemove)
    {
        $this->answers->detach($answerToRemove);
    }

    /**
     * Returns the answers
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Answer> answers
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Sets the answers
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Answer> $answers
     */
    public function setAnswers(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $answers)
    {
        $this->answers = $answers;
    }

    /**
     * Adds a Comment
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Comment $comment
     */
    public function addComment(\OpenOAP\OpenOap\Domain\Model\Comment $comment)
    {
        $this->comments->attach($comment);
    }

    /**
     * Adds a Log
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Comment $comment
     */
    public function addLog(\OpenOAP\OpenOap\Domain\Model\Comment $comment)
    {
        $this->addComment($comment);
    }

    /**
     * Removes a Comment
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Comment $commentToRemove The Comment to be removed
     */
    public function removeComment(\OpenOAP\OpenOap\Domain\Model\Comment $commentToRemove)
    {
        $this->comments->detach($commentToRemove);
    }

    /**
     * Returns the comments
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Comment> comments
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Sets the comments
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Comment> $comments
     */
    public function setComments(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $comments)
    {
        $this->comments = $comments;
    }

    /**
     * Returns the applicant
     *
     * @return \OpenOAP\OpenOap\Domain\Model\Applicant $applicant
     */
    public function getApplicant()
    {
        return $this->applicant;
    }

    /**
     * Sets the applicant
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Applicant $applicant
     */
    public function setApplicant(\OpenOAP\OpenOap\Domain\Model\Applicant $applicant)
    {
        $this->applicant = $applicant;
    }

    /**
     * Returns the archived
     *
     * @return bool $archived
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Sets the archived
     *
     * @param bool $archived
     */
    public function setArchived(bool $archived)
    {
        $this->archived = $archived;
    }

    /**
     * Returns the boolean state of archived
     *
     * @return bool
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * Returns the metaInformation
     *
     * @return string $metaInformation
     */
    public function getMetaInformation()
    {
        return $this->metaInformation;
    }

    /**
     * Sets the metaInformation
     *
     * @param string $metaInformation
     */
    public function setMetaInformation(string $metaInformation)
    {
        $this->metaInformation = $metaInformation;
    }

    /**
     * @return int
     */
    public function getSignature(): int
    {
        return $this->signature;
    }

    /**
     * @param int $signature
     */
    public function setSignature(int $signature): void
    {
        $this->signature = $signature;
    }

    /**
     * @return int
     */
    public function getEditTstamp(): int
    {
        return $this->editTstamp;
    }

    /**
     * @param int $editTstamp
     */
    public function setEditTstamp(int $editTstamp): void
    {
        $this->editTstamp = $editTstamp;
    }
}
