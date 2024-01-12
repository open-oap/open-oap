<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;


/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 *          Ingeborg Hess <ingeborg.hess@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * FormModificator
 */
class FormModificator extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * title
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * item
     *
     * @var int
     */
    protected $item = 0;

    /**
     * logic
     *
     * @var int
     */
    protected $logic = 0;

    /**
     * value
     *
     * @var string
     */
    protected $value = '';

    /**
     * items
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormItem>
     */
    protected $items = null;

    /**
     * Returns the title
     *
     * @return string $title
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
     * Returns the item
     *
     * @return int $item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Sets the item
     *
     * @param int $item
     */
    public function setItem(int $item)
    {
        $this->item = $item;
    }

    /**
     * Returns the logic
     *
     * @return int $logic
     */
    public function getLogic()
    {
        return $this->logic;
    }

    /**
     * Sets the logic
     *
     * @param int $logic
     */
    public function setLogic(int $logic)
    {
        $this->logic = $logic;
    }

    /**
     * Returns the value
     *
     * @return string $value
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
     *
     * @return void
     */
    public function initializeObject()
    {
        $this->items = $this->items ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Adds a FormItem
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormItem $item
     * @return void
     */
    public function addItem(\OpenOAP\OpenOap\Domain\Model\FormItem $item)
    {
        $this->items->attach($item);
    }

    /**
     * Removes a FormItem
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormItem $itemToRemove The FormItem to be removed
     * @return void
     */
    public function removeItem(\OpenOAP\OpenOap\Domain\Model\FormItem $itemToRemove)
    {
        $this->items->detach($itemToRemove);
    }

    /**
     * Returns the items
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormItem>
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets the items
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormItem> $items
     * @return void
     */
    public function setItems(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $items)
    {
        $this->items = $items;
    }
}
