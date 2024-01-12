<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Tests\Unit\Domain\Model;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 *
 * @author Thorsten Born <thorsten.born@cosmoblonde.de>
 * @author Ingeborg Hess <ingeborg.hess@cosmoblonde.de>
 */
class FormItemTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\FormItem|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\FormItem::class,
            ['dummy']
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getQuestionReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getQuestion()
        );
    }

    /**
     * @test
     */
    public function setQuestionForStringSetsQuestion(): void
    {
        $this->subject->setQuestion('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('question'));
    }

    /**
     * @test
     */
    public function getIntroTextReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getIntroText()
        );
    }

    /**
     * @test
     */
    public function setIntroTextForStringSetsIntroText(): void
    {
        $this->subject->setIntroText('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('introText'));
    }

    /**
     * @test
     */
    public function getHelpTextReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getHelpText()
        );
    }

    /**
     * @test
     */
    public function setHelpTextForStringSetsHelpText(): void
    {
        $this->subject->setHelpText('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('helpText'));
    }

    /**
     * @test
     */
    public function getTypeReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getType()
        );
    }

    /**
     * @test
     */
    public function setTypeForIntSetsType(): void
    {
        $this->subject->setType(12);

        self::assertEquals(12, $this->subject->_get('type'));
    }

    /**
     * @test
     */
    public function getEnabledFilterReturnsInitialValueForBool(): void
    {
        self::assertFalse($this->subject->getEnabledFilter());
    }

    /**
     * @test
     */
    public function setEnabledFilterForBoolSetsEnabledFilter(): void
    {
        $this->subject->setEnabledFilter(true);

        self::assertEquals(true, $this->subject->_get('enabledFilter'));
    }

    /**
     * @test
     */
    public function getFilterLabelReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFilterLabel()
        );
    }

    /**
     * @test
     */
    public function setFilterLabelForStringSetsFilterLabel(): void
    {
        $this->subject->setFilterLabel('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('filterLabel'));
    }

    /**
     * @test
     */
    public function getEnabledInfoReturnsInitialValueForBool(): void
    {
        self::assertFalse($this->subject->getEnabledInfo());
    }

    /**
     * @test
     */
    public function setEnabledInfoForBoolSetsEnabledInfo(): void
    {
        $this->subject->setEnabledInfo(true);

        self::assertEquals(true, $this->subject->_get('enabledInfo'));
    }

    /**
     * @test
     */
    public function getEnabledTitleReturnsInitialValueForBool(): void
    {
        self::assertFalse($this->subject->getEnabledTitle());
    }

    /**
     * @test
     */
    public function setEnabledTitleForBoolSetsEnabledTitle(): void
    {
        $this->subject->setEnabledTitle(true);

        self::assertEquals(true, $this->subject->_get('enabledTitle'));
    }

    /**
     * @test
     */
    public function getAdditionalValueReturnsInitialValueForBool(): void
    {
        self::assertFalse($this->subject->getAdditionalValue());
    }

    /**
     * @test
     */
    public function setAdditionalValueForBoolSetsAdditionalValue(): void
    {
        $this->subject->setAdditionalValue(true);

        self::assertEquals(true, $this->subject->_get('additionalValue'));
    }

    /**
     * @test
     */
    public function getDefaultValueReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDefaultValue()
        );
    }

    /**
     * @test
     */
    public function setDefaultValueForStringSetsDefaultValue(): void
    {
        $this->subject->setDefaultValue('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('defaultValue'));
    }

    /**
     * @test
     */
    public function getUnitReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getUnit()
        );
    }

    /**
     * @test
     */
    public function setUnitForStringSetsUnit(): void
    {
        $this->subject->setUnit('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('unit'));
    }

    /**
     * @test
     */
    public function getAdditionalLabelReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAdditionalLabel()
        );
    }

    /**
     * @test
     */
    public function setAdditionalLabelForStringSetsAdditionalLabel(): void
    {
        $this->subject->setAdditionalLabel('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('additionalLabel'));
    }

    /**
     * @test
     */
    public function getOptionsReturnsInitialValueForItemOption(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getOptions()
        );
    }

    /**
     * @test
     */
    public function setOptionsForObjectStorageContainingItemOptionSetsOptions(): void
    {
        $option = new \OpenOAP\OpenOap\Domain\Model\ItemOption();
        $objectStorageHoldingExactlyOneOptions = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneOptions->attach($option);
        $this->subject->setOptions($objectStorageHoldingExactlyOneOptions);

        self::assertEquals($objectStorageHoldingExactlyOneOptions, $this->subject->_get('options'));
    }

    /**
     * @test
     */
    public function addOptionToObjectStorageHoldingOptions(): void
    {
        $option = new \OpenOAP\OpenOap\Domain\Model\ItemOption();
        $optionsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $optionsObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($option));
        $this->subject->_set('options', $optionsObjectStorageMock);

        $this->subject->addOption($option);
    }

    /**
     * @test
     */
    public function removeOptionFromObjectStorageHoldingOptions(): void
    {
        $option = new \OpenOAP\OpenOap\Domain\Model\ItemOption();
        $optionsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $optionsObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($option));
        $this->subject->_set('options', $optionsObjectStorageMock);

        $this->subject->removeOption($option);
    }

    /**
     * @test
     */
    public function getValidatorsReturnsInitialValueForItemValidator(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getValidators()
        );
    }

    /**
     * @test
     */
    public function setValidatorsForObjectStorageContainingItemValidatorSetsValidators(): void
    {
        $validator = new \OpenOAP\OpenOap\Domain\Model\ItemValidator();
        $objectStorageHoldingExactlyOneValidators = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneValidators->attach($validator);
        $this->subject->setValidators($objectStorageHoldingExactlyOneValidators);

        self::assertEquals($objectStorageHoldingExactlyOneValidators, $this->subject->_get('validators'));
    }

    /**
     * @test
     */
    public function addValidatorToObjectStorageHoldingValidators(): void
    {
        $validator = new \OpenOAP\OpenOap\Domain\Model\ItemValidator();
        $validatorsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $validatorsObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($validator));
        $this->subject->_set('validators', $validatorsObjectStorageMock);

        $this->subject->addValidator($validator);
    }

    /**
     * @test
     */
    public function removeValidatorFromObjectStorageHoldingValidators(): void
    {
        $validator = new \OpenOAP\OpenOap\Domain\Model\ItemValidator();
        $validatorsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $validatorsObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($validator));
        $this->subject->_set('validators', $validatorsObjectStorageMock);

        $this->subject->removeValidator($validator);
    }

    /**
     * @test
     */
    public function getModificatorsReturnsInitialValueForFormModificator(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getModificators()
        );
    }

    /**
     * @test
     */
    public function setModificatorsForObjectStorageContainingFormModificatorSetsModificators(): void
    {
        $modificator = new \OpenOAP\OpenOap\Domain\Model\FormModificator();
        $objectStorageHoldingExactlyOneModificators = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneModificators->attach($modificator);
        $this->subject->setModificators($objectStorageHoldingExactlyOneModificators);

        self::assertEquals($objectStorageHoldingExactlyOneModificators, $this->subject->_get('modificators'));
    }

    /**
     * @test
     */
    public function addModificatorToObjectStorageHoldingModificators(): void
    {
        $modificator = new \OpenOAP\OpenOap\Domain\Model\FormModificator();
        $modificatorsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $modificatorsObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($modificator));
        $this->subject->_set('modificators', $modificatorsObjectStorageMock);

        $this->subject->addModificator($modificator);
    }

    /**
     * @test
     */
    public function removeModificatorFromObjectStorageHoldingModificators(): void
    {
        $modificator = new \OpenOAP\OpenOap\Domain\Model\FormModificator();
        $modificatorsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $modificatorsObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($modificator));
        $this->subject->_set('modificators', $modificatorsObjectStorageMock);

        $this->subject->removeModificator($modificator);
    }
}
