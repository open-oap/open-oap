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
class ItemValidatorTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\ItemValidator|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\ItemValidator::class,
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
    public function getParam1ReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getParam1()
        );
    }

    /**
     * @test
     */
    public function setParam1ForStringSetsParam1(): void
    {
        $this->subject->setParam1('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('param1'));
    }

    /**
     * @test
     */
    public function getParam2ReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getParam2()
        );
    }

    /**
     * @test
     */
    public function setParam2ForStringSetsParam2(): void
    {
        $this->subject->setParam2('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('param2'));
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
}
