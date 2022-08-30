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
class CallControllerTest extends UnitTestCase
{
    /**
     * @var \OpenOAP\OpenOap\Controller\CallController|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder($this->buildAccessibleProxy(\OpenOAP\OpenOap\Controller\CallController::class))
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
    public function listActionFetchesAllCallsFromRepositoryAndAssignsThemToView(): void
    {
        $allCalls = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $callRepository = $this->getMockBuilder(\OpenOAP\OpenOap\Domain\Repository\CallRepository::class)
            ->onlyMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $callRepository->expects(self::once())->method('findAll')->willReturn($allCalls);
        $this->subject->_set('callRepository', $callRepository);

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('calls', $allCalls);
        $this->subject->_set('view', $view);

        $this->subject->listAction();
    }
}
