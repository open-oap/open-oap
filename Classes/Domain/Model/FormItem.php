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
 * Question for Form
 */
class FormItem extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * question
     *
     * @var string
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
    protected string $question = '';

    /**
     * internal title for TYPO3 backend view (for editors)
     *
     * @var string
     */
    protected $internalTitle = '';

    /**
     * introText
     *
     * @var string
     */
    protected string $introText = '';

    /**
     * helpText
     *
     * @var string
     */
    protected string $helpText = '';

    /**
     * type
     *
     * @var int
     */
    #[\TYPO3\CMS\Extbase\Annotation\Validate(['validator' => 'NotEmpty'])]
    protected $type = 0;

    /**
     * enabledFilter
     *
     * @var bool
     */
    protected $enabledFilter = false;

    /**
     * enabledInfo
     *
     * @var bool
     */
    protected $enabledInfo = false;

    /**
     * enabledTitle
     *
     * @var bool
     */
    protected $enabledTitle = false;

    /**
     * additionalValue
     *
     * @var bool
     */
    protected $additionalValue = false;

    /**
     * additionalType
     *
     * @var integer
     */
    protected $additionalType = 1;

    /**
     * defaultValue
     *
     * @var string
     */
    protected $defaultValue = '';

    /**
     * unit
     *
     * @var string
     */
    protected $unit = '';

    /**
     * additionalLabel
     *
     * @var string
     */
    protected $additionalLabel = '';

    /**
     * options
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\ItemOption>
     */
    protected $options;

    /**
     * validators
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\ItemValidator>
     */
    protected $validators;

    /**
     * filterLabel
     *
     * @var string
     */
    protected $filterLabel = '';

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
        $this->options = $this->options ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->validators = $this->validators ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->modificators = $this->modificators ?: new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Returns the question
     *
     * @return string question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Sets the question
     *
     * @param string $question
     */
    public function setQuestion(string $question): void
    {
        $this->question = $question;
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
     * Returns the helpText
     *
     * @return string helpText
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * Sets the helpText
     *
     * @param string $helpText
     */
    public function setHelpText(string $helpText): void
    {
        $this->helpText = $helpText;
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
     * Adds a ItemOption
     *
     * @param \OpenOAP\OpenOap\Domain\Model\ItemOption $option
     */
    public function addOption(\OpenOAP\OpenOap\Domain\Model\ItemOption $option): void
    {
        $this->options->attach($option);
    }

    /**
     * Removes a ItemOption
     *
     * @param \OpenOAP\OpenOap\Domain\Model\ItemOption $optionToRemove The ItemOption to be removed
     */
    public function removeOption(\OpenOAP\OpenOap\Domain\Model\ItemOption $optionToRemove): void
    {
        $this->options->detach($optionToRemove);
    }

    /**
     * Returns the options
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\ItemOption> options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\ItemOption> $options
     */
    public function setOptions(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $options): void
    {
        $this->options = $options;
    }

    /**
     * Adds a ItemValidator
     *
     * @param \OpenOAP\OpenOap\Domain\Model\ItemValidator $validator
     */
    public function addValidator(\OpenOAP\OpenOap\Domain\Model\ItemValidator $validator): void
    {
        $this->validators->attach($validator);
    }

    /**
     * Removes a ItemValidator
     *
     * @param \OpenOAP\OpenOap\Domain\Model\ItemValidator $validatorToRemove The ItemValidator to be removed
     */
    public function removeValidator(\OpenOAP\OpenOap\Domain\Model\ItemValidator $validatorToRemove): void
    {
        $this->validators->detach($validatorToRemove);
    }

    /**
     * Returns the validators
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\ItemValidator> validators
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Sets the validators
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\ItemValidator> $validators
     */
    public function setValidators(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $validators): void
    {
        $this->validators = $validators;
    }

    /**
     * Returns the enabledFilter
     *
     * @return bool $enabledFilter
     */
    public function getEnabledFilter()
    {
        return $this->enabledFilter;
    }

    /**
     * Sets the enabledFilter
     *
     * @param bool $enabledFilter
     */
    public function setEnabledFilter(bool $enabledFilter): void
    {
        $this->enabledFilter = $enabledFilter;
    }

    /**
     * Returns the boolean state of enabledFilter
     *
     * @return bool
     */
    public function isEnabledFilter()
    {
        return $this->enabledFilter;
    }

    /**
     * Returns the enabledInfo
     *
     * @return bool $enabledInfo
     */
    public function getEnabledInfo()
    {
        return $this->enabledInfo;
    }

    /**
     * Sets the enabledInfo
     *
     * @param bool $enabledInfo
     */
    public function setEnabledInfo(bool $enabledInfo): void
    {
        $this->enabledInfo = $enabledInfo;
    }

    /**
     * Returns the boolean state of enabledInfo
     *
     * @return bool
     */
    public function isEnabledInfo()
    {
        return $this->enabledInfo;
    }

    /**
     * Returns the additionalValue
     *
     * @return bool $additionalValue
     */
    public function getAdditionalValue()
    {
        return $this->additionalValue;
    }

    /**
     * @return integer
     */
    public function getAdditionalType(): int
    {
        return $this->additionalType;
    }

    /**
     * @param integer $additionalType
     */
    public function setAdditionalType(int $additionalType): void
    {
        $this->additionalType = $additionalType;
    }

    /**
     * Sets the additionalValue
     *
     * @param bool $additionalValue
     */
    public function setAdditionalValue(bool $additionalValue): void
    {
        $this->additionalValue = $additionalValue;
    }

    /**
     * Returns the boolean state of additionalValue
     *
     * @return bool
     */
    public function isAdditionalValue()
    {
        return $this->additionalValue;
    }

    /**
     * Returns the defaultValue
     *
     * @return string $defaultValue
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Sets the defaultValue
     *
     * @param string $defaultValue
     */
    public function setDefaultValue(string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the unit
     *
     * @return string $unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Sets the unit
     *
     * @param string $unit
     */
    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    /**
     * Returns the additionalLabel
     *
     * @return string $additionalLabel
     */
    public function getAdditionalLabel()
    {
        return $this->additionalLabel;
    }

    /**
     * Sets the additionalLabel
     *
     * @param string $additionalLabel
     */
    public function setAdditionalLabel(string $additionalLabel): void
    {
        $this->additionalLabel = $additionalLabel;
    }

    /**
     * Returns the filterLabel
     *
     * @return string $filterLabel
     */
    public function getFilterLabel()
    {
        return $this->filterLabel;
    }

    /**
     * Sets the filterLabel
     *
     * @param string $filterLabel
     */
    public function setFilterLabel(string $filterLabel): void
    {
        $this->filterLabel = $filterLabel;
    }

    /**
     * Returns the enabledTitle
     *
     * @return bool $enabledTitle
     */
    public function getEnabledTitle()
    {
        return $this->enabledTitle;
    }

    /**
     * Sets the enabledTitle
     *
     * @param bool $enabledTitle
     */
    public function setEnabledTitle(bool $enabledTitle): void
    {
        $this->enabledTitle = $enabledTitle;
    }

    /**
     * Returns the boolean state of enabledTitle
     *
     * @return bool
     */
    public function isEnabledTitle()
    {
        return $this->enabledTitle;
    }

    public function getInternalTitle(): string
    {
        return $this->internalTitle;
    }

    public function setInternalTitle(string $internalTitle): void
    {
        $this->internalTitle = $internalTitle;
    }

    /**
     * Adds a FormModificator
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormModificator $modificator
     */
    public function addModificator(\OpenOAP\OpenOap\Domain\Model\FormModificator $modificator): void
    {
        $this->modificators->attach($modificator);
    }

    /**
     * Removes a FormModificator
     *
     * @param \OpenOAP\OpenOap\Domain\Model\FormModificator $modificatorToRemove The FormModificator to be removed
     */
    public function removeModificator(\OpenOAP\OpenOap\Domain\Model\FormModificator $modificatorToRemove): void
    {
        $this->modificators->detach($modificatorToRemove);
    }

    /**
     * Returns the modificators
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormModificator> modificators
     */
    public function getModificators()
    {
        return $this->modificators;
    }

    /**
     * Sets the modificators
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OpenOAP\OpenOap\Domain\Model\FormModificator> $modificators
     */
    public function setModificators(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $modificators): void
    {
        $this->modificators = $modificators;
    }
}
