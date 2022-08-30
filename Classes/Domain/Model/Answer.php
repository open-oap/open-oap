<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * Answer
 */
class Answer extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    // following values not saved in DB
    /**
     * arrayValue/answer
     *
     * @var array
     */
    protected $arrayValue = [];

    // following values are saved in DB
    /**
     * Value/answer
     *
     * @var string
     */
    protected $value = '';

    /**
     * type
     *
     * @var int
     */
    protected $type = 0;

    /**
     * For repeating groups, the counter is saved here
     *
     * @var int
     */
    protected $elementCounter = 0;

    /**
     * If additional answers are allowed, they will be saved here
     *
     * @var string
     */
    protected $additionalValue = '';

    /**
     * The past responses, saved as json.
     *
     * @var string
     */
    protected $pastAnswers = '';

    /**
     * item
     *
     * @var \OpenOAP\OpenOap\Domain\Model\FormItem
     */
    protected $item;

    /**
     * model
     *
     * @var \OpenOAP\OpenOap\Domain\Model\FormGroup
     */
    protected $model;

    /**
     * comments
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\Comment>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $comments;

    /**
     * @param FormItem|null $item
     * @param FormGroup|null $group
     * @param int|null $elementCounter
     * @param int|null $pid
     */
    public function __construct(FormItem $item = null, FormGroup $group = null, int $elementCounter = 0, int $pid = null)
    {
        if ($item) {
            $this->item = $item;
        }
        if ($group) {
            $this->model = $group;
        }
        if ($elementCounter) {
            $this->elementCounter = $elementCounter;
        }
        if ($pid) {
            ObjectAccess::setProperty($this, 'pid', $pid);
        }
        $this->comments = $this->comments ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Returns the value
     *
     * @return string value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value
     *
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * this value isn't saved in DB - just for handling in form
     *
     * @param array $arrayValue
     */
    public function setArrayValue(array $arrayValue)
    {
        $this->arrayValue = $arrayValue;
    }

    /**
     * this value isn't saved in DB - just for handling in form
     *
     * @param array $arrayValue
     * @return array
     */
    public function getArrayValue()
    {
        return $this->arrayValue;
    }

    /**
     * Returns the type
     *
     * @return int type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type
     *
     * @param int $type
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * Returns the elementCounter
     *
     * @return int elementCounter
     */
    public function getElementCounter()
    {
        return $this->elementCounter;
    }

    /**
     * Sets the elementCounter
     *
     * @param int $elementCounter
     */
    public function setElementCounter(int $elementCounter)
    {
        $this->elementCounter = $elementCounter;
    }

    /**
     * Returns the item
     *
     * @return \OpenOAP\OpenOap\Domain\Model\FormItem item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Sets the item
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormItem $item
     */
    public function setItem(\OpenOAP\OpenOap\Domain\Model\FormItem $item)
    {
        $this->item = $item;
    }

    /**
     * Returns the model
     *
     * @return \OpenOAP\OpenOap\Domain\Model\FormGroup model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sets the model
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormGroup $model
     */
    public function setModel(\OpenOAP\OpenOap\Domain\Model\FormGroup $model)
    {
        $this->model = $model;
    }

    /**
     * Returns the additionalValue
     *
     * @return string $additionalValue
     */
    public function getAdditionalValue()
    {
        return $this->additionalValue;
    }

    /**
     * Sets the additionalValue
     *
     * @param string $additionalValue
     */
    public function setAdditionalValue(string $additionalValue)
    {
        $this->additionalValue = $additionalValue;
    }

    /**
     * Returns the pastAnswers
     *
     * @return string $pastAnswers
     */
    public function getPastAnswers()
    {
        return $this->pastAnswers;
    }

    /**
     * Sets the pastAnswers
     *
     * @param string $pastAnswers
     */
    public function setPastAnswers(string $pastAnswers)
    {
        $this->pastAnswers = $pastAnswers;
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
}
