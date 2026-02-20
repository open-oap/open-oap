<?php declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SupporterRepository
{
    public const TABLE = 'tx_openoap_domain_model_supporter';

    public function __construct(private readonly \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool)
    {
    }

    /**
     * @param int $language
     * @param int $supporter
     *
     * @return array
     */
    public function findSupporterByLanguage(int $language, int $supporter): array
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($language, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('l10n_parent', $queryBuilder->createNamedParameter($supporter, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();
    }

    /**
     * @return QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable(self::TABLE);
    }
}
