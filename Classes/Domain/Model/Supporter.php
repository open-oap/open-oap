<?php declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Supporter extends AbstractEntity
{

    /**
     * @var string
     */
    protected string $title = '';

    /***
     * @var string
     */
    protected string $eventProposalSubmittedMailtext = '';

    /***
     * @var string
     */
    protected string $eventProposalInRevisionMailtext = '';

    /***
     * @var string
     */
    protected string $eventProposalAcceptedMailtext = '';

    /***
     * @var string
     */
    protected string $eventProposalDeclinedMailtext = '';

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
    public function getEventProposalSubmittedMailtext(): string
    {
        return $this->eventProposalSubmittedMailtext;
    }

    /**
     * @param string $eventProposalSubmittedMailtext
     */
    public function setEventProposalSubmittedMailtext(string $eventProposalSubmittedMailtext): void
    {
        $this->eventProposalSubmittedMailtext = $eventProposalSubmittedMailtext;
    }

    /**
     * @return string
     */
    public function getEventProposalInRevisionMailtext(): string
    {
        return $this->eventProposalInRevisionMailtext;
    }

    /**
     * @param string $eventProposalInRevisionMailtext
     */
    public function setEventProposalInRevisionMailtext(string $eventProposalInRevisionMailtext): void
    {
        $this->eventProposalInRevisionMailtext = $eventProposalInRevisionMailtext;
    }

    /**
     * @return string
     */
    public function getEventProposalAcceptedMailtext(): string
    {
        return $this->eventProposalAcceptedMailtext;
    }

    /**
     * @param string $eventProposalAcceptedMailtext
     */
    public function setEventProposalAcceptedMailtext(string $eventProposalAcceptedMailtext): void
    {
        $this->eventProposalAcceptedMailtext = $eventProposalAcceptedMailtext;
    }

    /**
     * @return string
     */
    public function getEventProposalDeclinedMailtext(): string
    {
        return $this->eventProposalDeclinedMailtext;
    }

    /**
     * @param string $eventProposalDeclinedMailtext
     */
    public function setEventProposalDeclinedMailtext(string $eventProposalDeclinedMailtext): void
    {
        $this->eventProposalDeclinedMailtext = $eventProposalDeclinedMailtext;
    }
}
