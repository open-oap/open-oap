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
 * LogicAtom
 */
class LogicAtom extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
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
}
