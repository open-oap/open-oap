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
 * ItemValidator
 */
class ItemValidator extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * title
     *
     * @var string
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
    protected $title = '';

    /**
     * type
     *
     * @var int
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
    protected $type = 0;

    /**
     * param1
     *
     * @var string
     */
    protected $param1 = '';

    /**
     * param2
     *
     * @var string
     */
    protected $param2 = '';

    /**
     * item
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormItem>
     */
    protected $item = null;

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
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns the param1
     *
     * @return string param1
     */
    public function getParam1()
    {
        return $this->param1;
    }

    /**
     * Sets the param1
     *
     * @param string $param1
     */
    public function setParam1(string $param1): void
    {
        $this->param1 = $param1;
    }

    /**
     * Returns the param2
     *
     * @return string param2
     */
    public function getParam2()
    {
        return $this->param2;
    }

    /**
     * Sets the param2
     *
     * @param string $param2
     */
    public function setParam2(string $param2): void
    {
        $this->param2 = $param2;
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
    public function initializeObject(): void
    {
        $this->item = $this->item ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Adds a FormItem
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormItem $item
     * @return void
     */
    public function addItem(\OpenOAP\OpenOap\Domain\Model\FormItem $item): void
    {
        $this->item->attach($item);
    }

    /**
     * Removes a FormItem
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormItem $itemToRemove The FormItem to be removed
     * @return void
     */
    public function removeItem(\OpenOAP\OpenOap\Domain\Model\FormItem $itemToRemove): void
    {
        $this->item->detach($itemToRemove);
    }

    /**
     * Returns the item
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormItem>
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Sets the item
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormItem> $item
     * @return void
     */
    public function setItem(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $item): void
    {
        $this->item = $item;
    }
}
