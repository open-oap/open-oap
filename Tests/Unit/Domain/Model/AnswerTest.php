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
class AnswerTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\Answer|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\Answer::class,
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
    public function getElementCounterReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getElementCounter()
        );
    }

    /**
     * @test
     */
    public function setElementCounterForIntSetsElementCounter(): void
    {
        $this->subject->setElementCounter(12);

        self::assertEquals(12, $this->subject->_get('elementCounter'));
    }

    /**
     * @test
     */
    public function getAdditionalValueReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAdditionalValue()
        );
    }

    /**
     * @test
     */
    public function setAdditionalValueForStringSetsAdditionalValue(): void
    {
        $this->subject->setAdditionalValue('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('additionalValue'));
    }

    /**
     * @test
     */
    public function getPastAnswersReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getPastAnswers()
        );
    }

    /**
     * @test
     */
    public function setPastAnswersForStringSetsPastAnswers(): void
    {
        $this->subject->setPastAnswers('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('pastAnswers'));
    }

    /**
     * @test
     */
    public function getItemReturnsInitialValueForFormItem(): void
    {
        self::assertNull(
            $this->subject->getItem()
        );
    }

    /**
     * @test
     */
    public function setItemForFormItemSetsItem(): void
    {
        $itemFixture = new \OpenOAP\OpenOap\Domain\Model\FormItem();
        $this->subject->setItem($itemFixture);

        self::assertEquals($itemFixture, $this->subject->_get('item'));
    }

    /**
     * @test
     */
    public function getModelReturnsInitialValueForFormGroup(): void
    {
        self::assertNull(
            $this->subject->getModel()
        );
    }

    /**
     * @test
     */
    public function setModelForFormGroupSetsModel(): void
    {
        $modelFixture = new \OpenOAP\OpenOap\Domain\Model\FormGroup();
        $this->subject->setModel($modelFixture);

        self::assertEquals($modelFixture, $this->subject->_get('model'));
    }

    /**
     * @test
     */
    public function getCommentsReturnsInitialValueForComment(): void
    {
        self::assertNull(
            $this->subject->getComments()
        );
    }

    /**
     * @test
     */
    public function setCommentsForCommentSetsComments(): void
    {
        $commentsFixture = new \OpenOAP\OpenOap\Domain\Model\Comment();
        $this->subject->setComments($commentsFixture);

        self::assertEquals($commentsFixture, $this->subject->_get('comments'));
    }
}
