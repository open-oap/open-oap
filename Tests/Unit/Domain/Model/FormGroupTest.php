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
class FormGroupTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\FormGroup|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\FormGroup::class,
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
    public function getModelNameReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getModelName()
        );
    }

    /**
     * @test
     */
    public function setModelNameForStringSetsModelName(): void
    {
        $this->subject->setModelName('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('modelName'));
    }

    /**
     * @test
     */
    public function getRepeatableMaxReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getRepeatableMax()
        );
    }

    /**
     * @test
     */
    public function setRepeatableMaxForIntSetsRepeatableMax(): void
    {
        $this->subject->setRepeatableMax(12);

        self::assertEquals(12, $this->subject->_get('repeatableMax'));
    }

    /**
     * @test
     */
    public function getItemsReturnsInitialValueForFormItem(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getItems()
        );
    }

    /**
     * @test
     */
    public function setItemsForObjectStorageContainingFormItemSetsItems(): void
    {
        $item = new \OpenOAP\OpenOap\Domain\Model\FormItem();
        $objectStorageHoldingExactlyOneItems = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneItems->attach($item);
        $this->subject->setItems($objectStorageHoldingExactlyOneItems);

        self::assertEquals($objectStorageHoldingExactlyOneItems, $this->subject->_get('items'));
    }

    /**
     * @test
     */
    public function addItemToObjectStorageHoldingItems(): void
    {
        $item = new \OpenOAP\OpenOap\Domain\Model\FormItem();
        $itemsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemsObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($item));
        $this->subject->_set('items', $itemsObjectStorageMock);

        $this->subject->addItem($item);
    }

    /**
     * @test
     */
    public function removeItemFromObjectStorageHoldingItems(): void
    {
        $item = new \OpenOAP\OpenOap\Domain\Model\FormItem();
        $itemsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemsObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($item));
        $this->subject->_set('items', $itemsObjectStorageMock);

        $this->subject->removeItem($item);
    }

    /**
     * @test
     */
    public function getGroupTitleReturnsInitialValueForGroupTitle(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getGroupTitle()
        );
    }

    /**
     * @test
     */
    public function setGroupTitleForObjectStorageContainingGroupTitleSetsGroupTitle(): void
    {
        $groupTitle = new \OpenOAP\OpenOap\Domain\Model\GroupTitle();
        $objectStorageHoldingExactlyOneGroupTitle = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneGroupTitle->attach($groupTitle);
        $this->subject->setGroupTitle($objectStorageHoldingExactlyOneGroupTitle);

        self::assertEquals($objectStorageHoldingExactlyOneGroupTitle, $this->subject->_get('groupTitle'));
    }

    /**
     * @test
     */
    public function addGroupTitleToObjectStorageHoldingGroupTitle(): void
    {
        $groupTitle = new \OpenOAP\OpenOap\Domain\Model\GroupTitle();
        $groupTitleObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $groupTitleObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($groupTitle));
        $this->subject->_set('groupTitle', $groupTitleObjectStorageMock);

        $this->subject->addGroupTitle($groupTitle);
    }

    /**
     * @test
     */
    public function removeGroupTitleFromObjectStorageHoldingGroupTitle(): void
    {
        $groupTitle = new \OpenOAP\OpenOap\Domain\Model\GroupTitle();
        $groupTitleObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $groupTitleObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($groupTitle));
        $this->subject->_set('groupTitle', $groupTitleObjectStorageMock);

        $this->subject->removeGroupTitle($groupTitle);
    }

    /**
     * @test
     */
    public function getDependentOnReturnsInitialValueForLogicAtom(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getDependentOn()
        );
    }

    /**
     * @test
     */
    public function setDependentOnForObjectStorageContainingLogicAtomSetsDependentOn(): void
    {
        $dependentOn = new \OpenOAP\OpenOap\Domain\Model\LogicAtom();
        $objectStorageHoldingExactlyOneDependentOn = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneDependentOn->attach($dependentOn);
        $this->subject->setDependentOn($objectStorageHoldingExactlyOneDependentOn);

        self::assertEquals($objectStorageHoldingExactlyOneDependentOn, $this->subject->_get('dependentOn'));
    }

    /**
     * @test
     */
    public function addDependentOnToObjectStorageHoldingDependentOn(): void
    {
        $dependentOn = new \OpenOAP\OpenOap\Domain\Model\LogicAtom();
        $dependentOnObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $dependentOnObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($dependentOn));
        $this->subject->_set('dependentOn', $dependentOnObjectStorageMock);

        $this->subject->addDependentOn($dependentOn);
    }

    /**
     * @test
     */
    public function removeDependentOnFromObjectStorageHoldingDependentOn(): void
    {
        $dependentOn = new \OpenOAP\OpenOap\Domain\Model\LogicAtom();
        $dependentOnObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $dependentOnObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($dependentOn));
        $this->subject->_set('dependentOn', $dependentOnObjectStorageMock);

        $this->subject->removeDependentOn($dependentOn);
    }
}
