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
 */
class QuestionTypeTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\QuestionType|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\QuestionType::class,
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
    public function getGroupTypeReturnsInitialValueForBool(): void
    {
        self::assertFalse($this->subject->getGroupType());
    }

    /**
     * @test
     */
    public function setGroupTypeForBoolSetsGroupType(): void
    {
        $this->subject->setGroupType(true);

        self::assertTrue($this->subject->_get('groupType'));
    }

    /**
     * @test
     */
    public function getRatingMaxReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getRatingMax()
        );
    }

    /**
     * @test
     */
    public function setRatingMaxForIntSetsRatingMax(): void
    {
        $this->subject->setRatingMax(12);

        self::assertEquals(12, $this->subject->_get('ratingMax'));
    }

    /**
     * @test
     */
    public function getRatingMinReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getRatingMin()
        );
    }

    /**
     * @test
     */
    public function setRatingMinForIntSetsRatingMin(): void
    {
        $this->subject->setRatingMin(12);

        self::assertEquals(12, $this->subject->_get('ratingMin'));
    }

    /**
     * @test
     */
    public function getTypeCodeReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTypeCode()
        );
    }

    /**
     * @test
     */
    public function setTypeCodeForStringSetsTypeCode(): void
    {
        $this->subject->setTypeCode('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('typeCode'));
    }
}
