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
class ApplicantTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Model\Applicant|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \OpenOAP\OpenOap\Domain\Model\Applicant::class,
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
    public function getCompanyEmailReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getCompanyEmail()
        );
    }

    /**
     * @test
     */
    public function setCompanyEmailForStringSetsCompanyEmail(): void
    {
        $this->subject->setCompanyEmail('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('companyEmail'));
    }

    /**
     * @test
     */
    public function getPreferredLangReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getPreferredLang()
        );
    }

    /**
     * @test
     */
    public function setPreferredLangForStringSetsPreferredLang(): void
    {
        $this->subject->setPreferredLang('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('preferredLang'));
    }

    /**
     * @test
     */
    public function getPrivacypolicyReturnsInitialValueForBool(): void
    {
        self::assertFalse($this->subject->getPrivacypolicy());
    }

    /**
     * @test
     */
    public function setPrivacypolicyForBoolSetsPrivacypolicy(): void
    {
        $this->subject->setPrivacypolicy(true);

        self::assertEquals(true, $this->subject->_get('privacypolicy'));
    }

    /**
     * @test
     */
    public function getSalutationReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getSalutation()
        );
    }

    /**
     * @test
     */
    public function setSalutationForIntSetsSalutation(): void
    {
        $this->subject->setSalutation(12);

        self::assertEquals(12, $this->subject->_get('salutation'));
    }

    /**
     * @test
     */
    public function getProposalsReturnsInitialValueForProposal(): void
    {
        $newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        self::assertEquals(
            $newObjectStorage,
            $this->subject->getProposals()
        );
    }

    /**
     * @test
     */
    public function setProposalsForObjectStorageContainingProposalSetsProposals(): void
    {
        $proposal = new \OpenOAP\OpenOap\Domain\Model\Proposal();
        $objectStorageHoldingExactlyOneProposals = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $objectStorageHoldingExactlyOneProposals->attach($proposal);
        $this->subject->setProposals($objectStorageHoldingExactlyOneProposals);

        self::assertEquals($objectStorageHoldingExactlyOneProposals, $this->subject->_get('proposals'));
    }

    /**
     * @test
     */
    public function addProposalToObjectStorageHoldingProposals(): void
    {
        $proposal = new \OpenOAP\OpenOap\Domain\Model\Proposal();
        $proposalsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['attach'])
            ->disableOriginalConstructor()
            ->getMock();

        $proposalsObjectStorageMock->expects(self::once())->method('attach')->with(self::equalTo($proposal));
        $this->subject->_set('proposals', $proposalsObjectStorageMock);

        $this->subject->addProposal($proposal);
    }

    /**
     * @test
     */
    public function removeProposalFromObjectStorageHoldingProposals(): void
    {
        $proposal = new \OpenOAP\OpenOap\Domain\Model\Proposal();
        $proposalsObjectStorageMock = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->onlyMethods(['detach'])
            ->disableOriginalConstructor()
            ->getMock();

        $proposalsObjectStorageMock->expects(self::once())->method('detach')->with(self::equalTo($proposal));
        $this->subject->_set('proposals', $proposalsObjectStorageMock);

        $this->subject->removeProposal($proposal);
    }
}
