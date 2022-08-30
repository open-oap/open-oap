<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
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
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * type
     *
     * @var int
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
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
    public function setParam1(string $param1)
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
    public function setParam2(string $param2)
    {
        $this->param2 = $param2;
    }
}
