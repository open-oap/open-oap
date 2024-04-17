<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
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
     * @var int
     */
    protected $groupCounter0 = 0;

    /**
     * @var int
     */
    protected $groupCounter1 = 0;

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
     * @param int $groupCounter0
     * @param int $groupCounter1
     * @param int|null $pid
     */
    public function __construct(FormItem $item = null, FormGroup $group = null, int $groupCounter0 = 0, int $groupCounter1 = 0, int $pid = null)
    {
        if ($item) {
            $this->item = $item;
        }
        if ($group) {
            $this->model = $group;
        }
        if ($groupCounter0) {
            $this->groupCounter0 = $groupCounter0;
        }
        if ($groupCounter1) {
            $this->groupCounter1 = $groupCounter1;
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
     * @return array elementCounter
     */
    public function getElementCounter()
    {
        return json_decode((string)$this->elementCounter, true);
    }

    /**
     * Sets the elementCounter
     *
     * @param array $elementCounter
     */
    public function setElementCounter(array $elementCounter)
    {
        $this->elementCounter = json_encode($elementCounter);
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

    /**
     * @return int
     */
    public function getGroupCounter0(): int
    {
        return $this->groupCounter0;
    }

    /**
     * @param int $groupCounter0
     */
    public function setGroupCounter0(int $groupCounter0): void
    {
        $this->groupCounter0 = $groupCounter0;
    }

    /**
     * @return int
     */
    public function getGroupCounter1(): int
    {
        return $this->groupCounter1;
    }

    /**
     * @param int $groupCounter1
     */
    public function setGroupCounter1(int $groupCounter1): void
    {
        $this->groupCounter1 = $groupCounter1;
    }
}
