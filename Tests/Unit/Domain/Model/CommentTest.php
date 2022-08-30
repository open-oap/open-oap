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
class CommentTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\Comment|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\Comment::class,
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
    public function getTextReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getText()
        );
    }

    /**
     * @test
     */
    public function setTextForStringSetsText(): void
    {
        $this->subject->setText('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('text'));
    }

    /**
     * @test
     */
    public function getSourceReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getSource()
        );
    }

    /**
     * @test
     */
    public function setSourceForIntSetsSource(): void
    {
        $this->subject->setSource(12);

        self::assertEquals(12, $this->subject->_get('source'));
    }

    /**
     * @test
     */
    public function getCodeReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getCode()
        );
    }

    /**
     * @test
     */
    public function setCodeForIntSetsCode(): void
    {
        $this->subject->setCode(12);

        self::assertEquals(12, $this->subject->_get('code'));
    }

    /**
     * @test
     */
    public function getStateReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getState()
        );
    }

    /**
     * @test
     */
    public function setStateForIntSetsState(): void
    {
        $this->subject->setState(12);

        self::assertEquals(12, $this->subject->_get('state'));
    }
}
