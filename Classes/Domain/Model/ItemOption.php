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
 * ItemOption
 */
class ItemOption extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * name of set of options
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * options
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $options = '';

    /**
     * optionGroup
     *
     * @var string
     */
    protected $optionGroup = '';

    /**
     * type
     *
     * @var int
     */
    protected $type = 0;

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
     * Returns the options
     *
     * @return string options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options
     *
     * @param string $options
     */
    public function setOptions(string $options)
    {
        $this->options = $options;
    }

    /**
     * Returns the optionGroup
     *
     * @return string $optionGroup
     */
    public function getOptionGroup()
    {
        return $this->optionGroup;
    }

    /**
     * Sets the optionGroup
     *
     * @param string $optionGroup
     */
    public function setOptionGroup(string $optionGroup)
    {
        $this->optionGroup = $optionGroup;
    }

    /**
     * Returns the type
     *
     * @return int $type
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
}
