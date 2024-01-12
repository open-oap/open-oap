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
class ProposalControllerTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Controller\ProposalController|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder($this->buildAccessibleProxy(\OpenOAP\OpenOap\Controller\ProposalController::class))
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
    public function listActionFetchesAllProposalsFromRepositoryAndAssignsThemToView(): void
    {
        $allProposals = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $proposalRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\ProposalRepository::class)
            ->onlyMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $proposalRepository->expects(self::once())->method('findAll')->will(self::returnValue($allProposals));
        $this->subject->_set('proposalRepository', $proposalRepository);

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('proposals', $allProposals);
        $this->subject->_set('view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenProposalToView(): void
    {
        $proposal = new \OpenOAP\OpenOap\Domain\Model\Proposal();

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $this->subject->_set('view', $view);
        $view->expects(self::once())->method('assign')->with('proposal', $proposal);

        $this->subject->showAction($proposal);
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenProposalToProposalRepository(): void
    {
        $proposal = new \OpenOAP\OpenOap\Domain\Model\Proposal();

        $proposalRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\ProposalRepository::class)
            ->onlyMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $proposalRepository->expects(self::once())->method('add')->with($proposal);
        $this->subject->_set('proposalRepository', $proposalRepository);

        $this->subject->createAction($proposal);
    }

    /**
     * @test
     */
    public function editActionAssignsTheGivenProposalToView(): void
    {
        $proposal = new \OpenOAP\OpenOap\Domain\Model\Proposal();

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $this->subject->_set('view', $view);
        $view->expects(self::once())->method('assign')->with('proposal', $proposal);

        $this->subject->editAction($proposal);
    }

    /**
     * @test
     */
    public function updateActionUpdatesTheGivenProposalInProposalRepository(): void
    {
        $proposal = new \OpenOAP\OpenOap\Domain\Model\Proposal();

        $proposalRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\ProposalRepository::class)
            ->onlyMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $proposalRepository->expects(self::once())->method('update')->with($proposal);
        $this->subject->_set('proposalRepository', $proposalRepository);

        $this->subject->updateAction($proposal);
    }

    /**
     * @test
     */
    public function deleteActionRemovesTheGivenProposalFromProposalRepository(): void
    {
        $proposal = new \OpenOAP\OpenOap\Domain\Model\Proposal();

        $proposalRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\ProposalRepository::class)
            ->onlyMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $proposalRepository->expects(self::once())->method('remove')->with($proposal);
        $this->subject->_set('proposalRepository', $proposalRepository);

        $this->subject->deleteAction($proposal);
    }
}
