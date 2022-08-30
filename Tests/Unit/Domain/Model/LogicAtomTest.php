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
class LogicAtomTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\LogicAtom|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\LogicAtom::class,
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
    public function getItemReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getItem()
        );
    }

    /**
     * @test
     */
    public function setItemForIntSetsItem(): void
    {
        $this->subject->setItem(12);

        self::assertEquals(12, $this->subject->_get('item'));
    }

    /**
     * @test
     */
    public function getLogicReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getLogic()
        );
    }

    /**
     * @test
     */
    public function setLogicForIntSetsLogic(): void
    {
        $this->subject->setLogic(12);

        self::assertEquals(12, $this->subject->_get('logic'));
    }

    /**
     * @test
     */
    public function getValueReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getValue()
        );
    }

    /**
     * @test
     */
    public function setValueForStringSetsValue(): void
    {
        $this->subject->setValue('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('value'));
    }
}
