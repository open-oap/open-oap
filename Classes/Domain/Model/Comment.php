<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 *          Ingeborg Hess <ingeborg.hess@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * Comment
 */
class Comment extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * created
     *
     * @var int
     */
    protected $created;

    /**
     * text
     *
     * @var string
     */
    protected $text = '';

    /**
     * source
     *
     * @var int
     */
    protected $source = 0;

    /**
     * code
     *
     * @var int
     */
    protected $code = 0;

    /**
     * state
     *
     * @var int
     */
    protected $state = 0;

    /**
     * proposal
     *
     * @var \OpenOAP\OpenOap\Domain\Model\Proposal
     */
    protected $proposal;

    /**
     * item
     *
     * @var \OpenOAP\OpenOap\Domain\Model\Answer
     */
    protected $answer;

    /**
     * author
     *
     * @var \TYPO3\CMS\Beuser\Domain\Model\BackendUser
     */
    protected $author;

    /**
     * Returns the text
     *
     * @return string $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the text
     *
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Returns the source
     *
     * @return int source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Sets the source
     *
     * @param int $source
     */
    public function setSource(int $source): void
    {
        $this->source = $source;
    }

    /**
     * Returns the code
     *
     * @return int code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets the code
     *
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
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
     * @param int state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * Returns the created value
     *
     * @return int created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the created value
     *
     * @param int created
     */
    public function setCreated(int $created): void
    {
        $this->created = $created;
    }

    /**
     * Returns the proposal
     *
     * @return \OpenOAP\OpenOap\Domain\Model\Proposal proposal
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * Sets the proposal
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Proposal $proposal
     */
    public function setProposal(\OpenOAP\OpenOap\Domain\Model\Proposal $proposal): void
    {
        $this->proposal = $proposal;
    }

    /**
     * Returns the answer
     *
     * @return \OpenOAP\OpenOap\Domain\Model\Answer answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Sets the answer
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Answer $answer
     */
    public function setAnswer(\OpenOAP\OpenOap\Domain\Model\Answer $answer): void
    {
        $this->answer = $answer;
    }

    /**
     * Returns the author
     *
     * @return \TYPO3\CMS\Beuser\Domain\Model\BackendUser author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the author
     *
     * @param @var \TYPO3\CMS\Beuser\Domain\Model\BackendUser
     */
    public function setAuthor(\TYPO3\CMS\Beuser\Domain\Model\BackendUser $author): void
    {
        $this->author = $author;
    }
}
