<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Upgrades;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

#[UpgradeWizard('openOap_formItemFieldnameWizard')]
final class FormItemFieldnameWizard implements UpgradeWizardInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const TABLE_NAME = 'tx_openoap_call_formitem_mm';

    private const DEFAULT_FIELDNAME = 'items';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    )
    {
        $this->setLogger(
            GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__)
        );
    }

    public function getTitle(): string
    {
        return 'Update existing OAP formitem MM entries';
    }

    public function getDescription(): string
    {
        return 'Set default values for fieldname if they are missing';
    }

    public function executeUpdate(): bool
    {
        $this->logger->info('Upgrade Wizard started. ' . date('Y-m-d H:i:s'));

        $qb = $this->getPreparedQueryBuilder();

        $updatedRows = $qb->update(self::TABLE_NAME)
            ->where($qb->expr()->eq('fieldname', $qb->createNamedParameter('')))
            ->set('fieldname', self::DEFAULT_FIELDNAME)
            ->executeStatement();

        $this->logger->info('Upgrade Wizard: Updated ' . $updatedRows . ' entries with empty fieldname.');

        return true;
    }


    /**
     * @throws Exception
     */
    public function updateNecessary(): bool
    {
        $qb = $this->getPreparedQueryBuilder();

        $emptyCount = $qb->select('*')
            ->from(self::TABLE_NAME)
            ->where($qb->expr()->eq('fieldname', $qb->createNamedParameter('')))
            ->executeQuery()
            ->rowCount();

        return $emptyCount > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    private function getPreparedQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder;
    }
}
