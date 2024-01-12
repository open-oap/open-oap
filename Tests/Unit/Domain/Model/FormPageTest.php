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
class FormPageTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\FormPage|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\FormPage::class,
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
    public function getTitleReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleForStringSetsTitle(): void
    {
        $this->subject->setTitle('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('title'));
    }

    /**
     * @test
     */
    public function getMenuTitleReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getMenuTitle()
        );
    }

    /**
     * @test
     */
    public function setMenuTitleForStringSetsMenuTitle(): void
    {
        $this->subject->setMenuTitle('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('menuTitle'));
    }

    /**
     * @test
     */
    public function getInternalTitleReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getInternalTitle()
        );
    }

    /**
     * @test
     */
    public function setInternalTitleForStringSetsInternalTitle(): void
    {
        $this->subject->setInternalTitle('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('internalTitle'));
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
    public function getItemGroupsReturnsInitialValueForFormGroup(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getItemGroups()
        );
    }

    /**
     * @test
     */
    public function setItemGroupsForObjectStorageContainingFormGroupSetsItemGroups(): void
    {
        $itemGroup = new \OpenOAP\OpenOap\Domain\Model\FormGroup();
        $objectStorageHoldingExactlyOneItemGroups = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneItemGroups->attach($itemGroup);
        $this->subject->setItemGroups($objectStorageHoldingExactlyOneItemGroups);

        self::assertEquals($objectStorageHoldingExactlyOneItemGroups, $this->subject->_get('itemGroups'));
    }

    /**
     * @test
     */
    public function addItemGroupToObjectStorageHoldingItemGroups(): void
    {
        $itemGroup = new \OpenOAP\OpenOap\Domain\Model\FormGroup();
        $itemGroupsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemGroupsObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($itemGroup));
        $this->subject->_set('itemGroups', $itemGroupsObjectStorageMock);

        $this->subject->addItemGroup($itemGroup);
    }

    /**
     * @test
     */
    public function removeItemGroupFromObjectStorageHoldingItemGroups(): void
    {
        $itemGroup = new \OpenOAP\OpenOap\Domain\Model\FormGroup();
        $itemGroupsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemGroupsObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($itemGroup));
        $this->subject->_set('itemGroups', $itemGroupsObjectStorageMock);

        $this->subject->removeItemGroup($itemGroup);
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
