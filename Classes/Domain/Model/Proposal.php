<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
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
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
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
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
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
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
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
     * @var
     */
    protected $submitTstamp = 0;

    /**
     * rejectionTstamp
     *
     * @var int
     */
    protected int $rejectionTstamp = 0;

    /**
     * rejectionEmail
     *
     * @var string
     */
    protected string $rejectionEmail = '';

    /**
     * surveyHash
     *
     * @var string
     */
    protected string $surveyHash = '';

    /**
     * answers
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Answer>
     */
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Lazy]
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Cascade(['value' => 'remove'])]
    protected $answers;

    /**
     * comments
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Comment>
     */
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Lazy]
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Cascade(['value' => 'remove'])]
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
    public function initializeObject(): void
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
    public function setTitle(string $title): void
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
    public function setState(int $state): void
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
    public function setFeLanguageUid(int $feLanguageUid): void
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
    public function setCall(\OpenOAP\OpenOap\Domain\Model\Call $call): void
    {
        $this->call = $call;
    }

    /**
     * Adds a Answer
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Answer $answer
     */
    public function addAnswer(\OpenOAP\OpenOap\Domain\Model\Answer $answer): void
    {
        $this->answers->attach($answer);
    }

    /**
     * Removes a Answer
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Answer $answerToRemove The Answer to be removed
     */
    public function removeAnswer(\OpenOAP\OpenOap\Domain\Model\Answer $answerToRemove): void
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
    public function setAnswers(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $answers): void
    {
        $this->answers = $answers;
    }

    /**
     * Adds a Comment
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Comment $comment
     */
    public function addComment(\OpenOAP\OpenOap\Domain\Model\Comment $comment): void
    {
        $this->comments->attach($comment);
    }

    /**
     * Adds a Log
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Comment $comment
     */
    public function addLog(\OpenOAP\OpenOap\Domain\Model\Comment $comment): void
    {
        $this->addComment($comment);
    }

    /**
     * Removes a Comment
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Comment $commentToRemove The Comment to be removed
     */
    public function removeComment(\OpenOAP\OpenOap\Domain\Model\Comment $commentToRemove): void
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
    public function setComments(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $comments): void
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
    public function setApplicant(\OpenOAP\OpenOap\Domain\Model\Applicant $applicant): void
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
    public function setArchived(bool $archived): void
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
    public function setMetaInformation(string $metaInformation): void
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

    /**
     * @return int
     */
    public function getSubmitTstamp(): int
    {
        return $this->submitTstamp;
    }

    /**
     * @param int $submitTstamp
     */
    public function setSubmitTstamp(int $submitTstamp): void
    {
        $this->submitTstamp = $submitTstamp;
    }

    /**
     * Returns the rejectionTstamp
     *
     * @return int
     */
    public function getRejectionTstamp(): int
    {
        return $this->rejectionTstamp;
    }

    /**
     * Sets the rejectionTstamp
     *
     * @param int $rejectionTstamp
     */
    public function setRejectionTstamp(int $rejectionTstamp): void
    {
        $this->rejectionTstamp = $rejectionTstamp;
    }

    /**
     * Returns the rejectionEmail
     *
     * @return string
     */
    public function getRejectionEmail(): string
    {
        return $this->rejectionEmail;
    }

    /**
     * Sets the rejectionEmail
     *
     * @param string $rejectionEmail
     */
    public function setRejectionEmail(string $rejectionEmail): void
    {
        $this->rejectionEmail = $rejectionEmail;
    }

    /**
     * Returns the surveyHash
     *
     * @return string
     */
    public function getSurveyHash(): string
    {
        return $this->surveyHash;
    }

    /**
     * Sets the surveyHash
     *
     * @param string $surveyHash
     */
    public function setSurveyHash(string $surveyHash): void
    {
        $this->surveyHash = $surveyHash;
    }
}
