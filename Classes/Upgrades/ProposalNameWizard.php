<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Upgrades;

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Log\LogManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

#[UpgradeWizard('open-oap_proposalNameWizard')]
final class ProposalNameWizard implements UpgradeWizardInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TABLE_CALLS = 'tx_openoap_domain_model_call';
    protected const DEFAULT_PROPOSAL_NAME = 'proposal';

    public function __construct(private readonly \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool)
    {
        $this->setLogger(
            GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__)
        );
    }

    public function getTitle(): string
    {
        return 'Set default value of proposal name for existing calls';
    }

    public function getDescription(): string
    {
        return 'Set all calls with no proposal name to the default value';
    }

    public function executeUpdate(): bool
    {
        $this->logger->info('Upgrade Wizard started. ' . date('Y-m-d H:i:s'));

        $qb = $this->getQueryBuilder(self::TABLE_CALLS);;
        $qb
            ->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $updatedRows = $qb->update(self::TABLE_CALLS)
            ->where($qb->expr()->eq('proposal_name', $qb->createNamedParameter('')))
            ->set('proposal_name', self::DEFAULT_PROPOSAL_NAME)
            ->executeStatement();

        $this->logger->info('Upgrade Wizard: ' . $updatedRows . ' calls with no proposal name found.');

        return true;
    }


    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws DBALException
     */
    public function updateNecessary(): bool
    {
        // fetch all Calls without a proposal name

        $qb = $this->getQueryBuilder(self::TABLE_CALLS);;
        $qb
            ->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $allCallsWithEmptyProposalName = $qb->select('*')
            ->from(self::TABLE_CALLS)
            ->where($qb->expr()->eq('proposal_name', $qb->createNamedParameter('')))
            ->orderBy('uid', 'ASC')
            ->executeQuery()
            ->rowCount();

        return $allCallsWithEmptyProposalName > 0;
    }

    public function getPrerequisites(): array
    {
        // TODO: Implement getPrerequisites() method.
        return [];
    }

    protected function getQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        return $queryBuilder;
    }
}
