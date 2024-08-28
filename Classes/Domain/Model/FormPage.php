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
 * Seite eines Skizzen-Formulars
 */
class FormPage extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * title
     *
     * @var string
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
    protected $title = '';

    /**
     * Short version of title for menu
     *
     * @var string
     */
    protected $menuTitle = '';

    /**
     * internal title for TYPO3 backend view (for editors)
     *
     * @var string
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
    protected $internalTitle = '';

    /**
     * introText
     *
     * @var string
     */
    protected $introText = '';

    /**
     * type
     *
     * @var int
     */
    protected $type = 0;

    /**
     * itemGroups
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormGroup>
     */
    protected $itemGroups;

    /**
     * modificators
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormModificator>
     */
    protected $modificators = null;

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
        $this->itemGroups = $this->itemGroups ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->modificators = $this->modificators ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
     * Returns the menuTitle
     *
     * @return string menuTitle
     */
    public function getMenuTitle()
    {
        return $this->menuTitle;
    }

    /**
     * Sets the menuTitle
     *
     * @param string $menuTitle
     */
    public function setMenuTitle(string $menuTitle): void
    {
        $this->menuTitle = $menuTitle;
    }

    /**
     * Returns the internalTitle
     *
     * @return string internalTitle
     */
    public function getInternalTitle()
    {
        return $this->internalTitle;
    }

    /**
     * Sets the internalTitle
     *
     * @param string $internalTitle
     */
    public function setInternalTitle(string $internalTitle): void
    {
        $this->internalTitle = $internalTitle;
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
    public function setIntroText(string $introText): void
    {
        $this->introText = $introText;
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
     * @return bool
     * At the moment there are only two page types. ID 1 is the Overview/Submit page.
     * If there are several page types, another way must be chosen here.
     */
    public function isSubmitPage(): bool
    {
        return (bool)$this->type;
    }

    /**
     * Adds a FormGroup
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormGroup $itemGroup
     */
    public function addItemGroup(\OpenOAP\OpenOap\Domain\Model\FormGroup $itemGroup): void
    {
        $this->itemGroups->attach($itemGroup);
    }

    /**
     * Removes a FormGroup
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormGroup $itemGroupToRemove The FormGroup to be removed
     */
    public function removeItemGroup(\OpenOAP\OpenOap\Domain\Model\FormGroup $itemGroupToRemove): void
    {
        $this->itemGroups->detach($itemGroupToRemove);
    }

    /**
     * Returns the itemGroups
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormGroup> itemGroups
     */
    public function getItemGroups()
    {
        return $this->itemGroups;
    }

    /**
     * Sets the itemGroups
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormGroup> $itemGroups
     */
    public function setItemGroups(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $itemGroups): void
    {
        $this->itemGroups = $itemGroups;
    }

    /**
     * Adds a FormModificator
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormModificator $modificator
     * @return void
     */
    public function addModificator(\OpenOAP\OpenOap\Domain\Model\FormModificator $modificator): void
    {
        $this->modificators->attach($modificator);
    }

    /**
     * Removes a FormModificator
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormModificator $modificatorToRemove The FormModificator to be removed
     * @return void
     */
    public function removeModificator(\OpenOAP\OpenOap\Domain\Model\FormModificator $modificatorToRemove): void
    {
        $this->modificators->detach($modificatorToRemove);
    }

    /**
     * Returns the modificators
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormModificator>
     */
    public function getModificators()
    {
        return $this->modificators;
    }

    /**
     * Sets the modificators
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormModificator> $modificators
     * @return void
     */
    public function setModificators(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $modificators): void
    {
        $this->modificators = $modificators;
    }
}
