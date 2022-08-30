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
class CallTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\Call|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\Call::class,
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
    public function getTeaserTextReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTeaserText()
        );
    }

    /**
     * @test
     */
    public function setTeaserTextForStringSetsTeaserText(): void
    {
        $this->subject->setTeaserText('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('teaserText'));
    }

    /**
     * @test
     */
    public function getEmailsReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getEmails()
        );
    }

    /**
     * @test
     */
    public function setEmailsForStringSetsEmails(): void
    {
        $this->subject->setEmails('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('emails'));
    }

    /**
     * @test
     */
    public function getCallStartTimeReturnsInitialValueForDateTime(): void
    {
        self::assertNull(
            $this->subject->getCallStartTime()
        );
    }

    /**
     * @test
     */
    public function setCallStartTimeForDateTimeSetsCallStartTime(): void
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setCallStartTime($dateTimeFixture);

        self::assertEquals($dateTimeFixture, $this->subject->_get('callStartTime'));
    }

    /**
     * @test
     */
    public function getCallEndTimeReturnsInitialValueForDateTime(): void
    {
        self::assertNull(
            $this->subject->getCallEndTime()
        );
    }

    /**
     * @test
     */
    public function setCallEndTimeForDateTimeSetsCallEndTime(): void
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setCallEndTime($dateTimeFixture);

        self::assertEquals($dateTimeFixture, $this->subject->_get('callEndTime'));
    }

    /**
     * @test
     */
    public function getFeUserExceptionsReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFeUserExceptions()
        );
    }

    /**
     * @test
     */
    public function setFeUserExceptionsForStringSetsFeUserExceptions(): void
    {
        $this->subject->setFeUserExceptions('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('feUserExceptions'));
    }

    /**
     * @test
     */
    public function getProjectStartTimeReturnsInitialValueForDateTime(): void
    {
        self::assertNull(
            $this->subject->getProjectStartTime()
        );
    }

    /**
     * @test
     */
    public function setProjectStartTimeForDateTimeSetsProjectStartTime(): void
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setProjectStartTime($dateTimeFixture);

        self::assertEquals($dateTimeFixture, $this->subject->_get('projectStartTime'));
    }

    /**
     * @test
     */
    public function getProjectEndTimeReturnsInitialValueForDateTime(): void
    {
        self::assertNull(
            $this->subject->getProjectEndTime()
        );
    }

    /**
     * @test
     */
    public function setProjectEndTimeForDateTimeSetsProjectEndTime(): void
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setProjectEndTime($dateTimeFixture);

        self::assertEquals($dateTimeFixture, $this->subject->_get('projectEndTime'));
    }

    /**
     * @test
     */
    public function getProjectDurationMaxReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getProjectDurationMax()
        );
    }

    /**
     * @test
     */
    public function setProjectDurationMaxForIntSetsProjectDurationMax(): void
    {
        $this->subject->setProjectDurationMax(12);

        self::assertEquals(12, $this->subject->_get('projectDurationMax'));
    }

    /**
     * @test
     */
    public function getProjectDurationMinReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getProjectDurationMin()
        );
    }

    /**
     * @test
     */
    public function setProjectDurationMinForIntSetsProjectDurationMin(): void
    {
        $this->subject->setProjectDurationMin(12);

        self::assertEquals(12, $this->subject->_get('projectDurationMin'));
    }

    /**
     * @test
     */
    public function getProposalPidReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getProposalPid()
        );
    }

    /**
     * @test
     */
    public function setProposalPidForIntSetsProposalPid(): void
    {
        $this->subject->setProposalPid(12);

        self::assertEquals(12, $this->subject->_get('proposalPid'));
    }

    /**
     * @test
     */
    public function getFormPagesReturnsInitialValueForFormPage(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getFormPages()
        );
    }

    /**
     * @test
     */
    public function setFormPagesForObjectStorageContainingFormPageSetsFormPages(): void
    {
        $formPage = new \OpenOAP\OpenOap\Domain\Model\FormPage();
        $objectStorageHoldingExactlyOneFormPages = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneFormPages->attach($formPage);
        $this->subject->setFormPages($objectStorageHoldingExactlyOneFormPages);

        self::assertEquals($objectStorageHoldingExactlyOneFormPages, $this->subject->_get('formPages'));
    }

    /**
     * @test
     */
    public function addFormPageToObjectStorageHoldingFormPages(): void
    {
        $formPage = new \OpenOAP\OpenOap\Domain\Model\FormPage();
        $formPagesObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $formPagesObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($formPage));
        $this->subject->_set('formPages', $formPagesObjectStorageMock);

        $this->subject->addFormPage($formPage);
    }

    /**
     * @test
     */
    public function removeFormPageFromObjectStorageHoldingFormPages(): void
    {
        $formPage = new \OpenOAP\OpenOap\Domain\Model\FormPage();
        $formPagesObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $formPagesObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($formPage));
        $this->subject->_set('formPages', $formPagesObjectStorageMock);

        $this->subject->removeFormPage($formPage);
    }
}
