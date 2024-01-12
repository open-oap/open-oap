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
class ProposalTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\Proposal|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\Proposal::class,
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
    public function getSignatureReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getSignature()
        );
    }

    /**
     * @test
     */
    public function setSignatureForStringSetsSignature(): void
    {
        $this->subject->setSignature('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('signature'));
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

    /**
     * @test
     */
    public function getArchivedReturnsInitialValueForBool(): void
    {
        self::assertFalse($this->subject->getArchived());
    }

    /**
     * @test
     */
    public function setArchivedForBoolSetsArchived(): void
    {
        $this->subject->setArchived(true);

        self::assertEquals(true, $this->subject->_get('archived'));
    }

    /**
     * @test
     */
    public function getMetaInformationReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getMetaInformation()
        );
    }

    /**
     * @test
     */
    public function setMetaInformationForStringSetsMetaInformation(): void
    {
        $this->subject->setMetaInformation('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('metaInformation'));
    }

    /**
     * @test
     */
    public function getCallReturnsInitialValueForCall(): void
    {
        self::assertEquals(
            null,
            $this->subject->getCall()
        );
    }

    /**
     * @test
     */
    public function setCallForCallSetsCall(): void
    {
        $callFixture = new \OpenOAP\OpenOap\Domain\Model\Call();
        $this->subject->setCall($callFixture);

        self::assertEquals($callFixture, $this->subject->_get('call'));
    }

    /**
     * @test
     */
    public function getAnswersReturnsInitialValueForAnswer(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getAnswers()
        );
    }

    /**
     * @test
     */
    public function setAnswersForObjectStorageContainingAnswerSetsAnswers(): void
    {
        $answer = new \OpenOAP\OpenOap\Domain\Model\Answer();
        $objectStorageHoldingExactlyOneAnswers = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneAnswers->attach($answer);
        $this->subject->setAnswers($objectStorageHoldingExactlyOneAnswers);

        self::assertEquals($objectStorageHoldingExactlyOneAnswers, $this->subject->_get('answers'));
    }

    /**
     * @test
     */
    public function addAnswerToObjectStorageHoldingAnswers(): void
    {
        $answer = new \OpenOAP\OpenOap\Domain\Model\Answer();
        $answersObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $answersObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($answer));
        $this->subject->_set('answers', $answersObjectStorageMock);

        $this->subject->addAnswer($answer);
    }

    /**
     * @test
     */
    public function removeAnswerFromObjectStorageHoldingAnswers(): void
    {
        $answer = new \OpenOAP\OpenOap\Domain\Model\Answer();
        $answersObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $answersObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($answer));
        $this->subject->_set('answers', $answersObjectStorageMock);

        $this->subject->removeAnswer($answer);
    }

    /**
     * @test
     */
    public function getCommentsReturnsInitialValueForComment(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getComments()
        );
    }

    /**
     * @test
     */
    public function setCommentsForObjectStorageContainingCommentSetsComments(): void
    {
        $comment = new \OpenOAP\OpenOap\Domain\Model\Comment();
        $objectStorageHoldingExactlyOneComments = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneComments->attach($comment);
        $this->subject->setComments($objectStorageHoldingExactlyOneComments);

        self::assertEquals($objectStorageHoldingExactlyOneComments, $this->subject->_get('comments'));
    }

    /**
     * @test
     */
    public function addCommentToObjectStorageHoldingComments(): void
    {
        $comment = new \OpenOAP\OpenOap\Domain\Model\Comment();
        $commentsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $commentsObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($comment));
        $this->subject->_set('comments', $commentsObjectStorageMock);

        $this->subject->addComment($comment);
    }

    /**
     * @test
     */
    public function removeCommentFromObjectStorageHoldingComments(): void
    {
        $comment = new \OpenOAP\OpenOap\Domain\Model\Comment();
        $commentsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $commentsObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($comment));
        $this->subject->_set('comments', $commentsObjectStorageMock);

        $this->subject->removeComment($comment);
    }

    /**
     * @test
     */
    public function getApplicantReturnsInitialValueForApplicant(): void
    {
        self::assertEquals(
            null,
            $this->subject->getApplicant()
        );
    }

    /**
     * @test
     */
    public function setApplicantForApplicantSetsApplicant(): void
    {
        $applicantFixture = new \OpenOAP\OpenOap\Domain\Model\Applicant();
        $this->subject->setApplicant($applicantFixture);

        self::assertEquals($applicantFixture, $this->subject->_get('applicant'));
    }
}
