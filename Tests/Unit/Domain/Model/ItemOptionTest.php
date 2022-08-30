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
class ItemOptionTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\ItemOption|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\ItemOption::class,
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
    public function getOptionGroupReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getOptionGroup()
        );
    }

    /**
     * @test
     */
    public function setOptionGroupForStringSetsOptionGroup(): void
    {
        $this->subject->setOptionGroup('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('optionGroup'));
    }

    /**
     * @test
     */
    public function getOptionsReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getOptions()
        );
    }

    /**
     * @test
     */
    public function setOptionsForStringSetsOptions(): void
    {
        $this->subject->setOptions('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('options'));
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
}
