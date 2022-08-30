<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * Test case
 *
 * @author Thorsten Born <thorsten.born@cosmoblonde.de>
 * @author Ingeborg Hess <ingeborg.hess@cosmoblonde.de>
 */
class ApplicantControllerTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Controller\ApplicantController|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder($this->buildAccessibleProxy(\OpenOAP\OpenOap\Controller\ApplicantController::class))
            ->onlyMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function listActionFetchesAllApplicantsFromRepositoryAndAssignsThemToView(): void
    {
        $allApplicants = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $applicantRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\ApplicantRepository::class)
            ->onlyMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $applicantRepository->expects(self::once())->method('findAll')->willReturn($allApplicants);
        $this->subject->_set('applicantRepository', $applicantRepository);

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('applicants', $allApplicants);
        $this->subject->_set('view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenApplicantToView(): void
    {
        $applicant = new \OpenOAP\OpenOap\Domain\Model\Applicant();

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $this->subject->_set('view', $view);
        $view->expects(self::once())->method('assign')->with('applicant', $applicant);

        $this->subject->showAction($applicant);
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenApplicantToApplicantRepository(): void
    {
        $applicant = new \OpenOAP\OpenOap\Domain\Model\Applicant();

        $applicantRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\ApplicantRepository::class)
            ->onlyMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $applicantRepository->expects(self::once())->method('add')->with($applicant);
        $this->subject->_set('applicantRepository', $applicantRepository);

        $this->subject->createAction($applicant);
    }

    /**
     * @test
     */
    public function editActionAssignsTheGivenApplicantToView(): void
    {
        $applicant = new \OpenOAP\OpenOap\Domain\Model\Applicant();

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $this->subject->_set('view', $view);
        $view->expects(self::once())->method('assign')->with('applicant', $applicant);

        $this->subject->editAction($applicant);
    }

    /**
     * @test
     */
    public function updateActionUpdatesTheGivenApplicantInApplicantRepository(): void
    {
        $applicant = new \OpenOAP\OpenOap\Domain\Model\Applicant();

        $applicantRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\ApplicantRepository::class)
            ->onlyMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $applicantRepository->expects(self::once())->method('update')->with($applicant);
        $this->subject->_set('applicantRepository', $applicantRepository);

        $this->subject->updateAction($applicant);
    }

    /**
     * @test
     */
    public function deleteActionRemovesTheGivenApplicantFromApplicantRepository(): void
    {
        $applicant = new \OpenOAP\OpenOap\Domain\Model\Applicant();

        $applicantRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\ApplicantRepository::class)
            ->onlyMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $applicantRepository->expects(self::once())->method('remove')->with($applicant);
        $this->subject->_set('applicantRepository', $applicantRepository);

        $this->subject->deleteAction($applicant);
    }
}
