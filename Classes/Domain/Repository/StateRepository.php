<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * The repository for ItemValidators
 */
class StateRepository extends OapAbstractRepository
{
    public const TABLE = 'tx_openoap_domain_model_state';

    public function findAll(): QueryResultInterface|array
    {
        $query = $this->getQueryForBELists('uid');
        $result = $query->execute();

        return $result;
    }

    /**
     * @param int $language
     * @param int $supporter
     *
     * @return array
     * @throws Exception
     */
    public function findStateByLanguage(int $languageUid, int $stateUid): array
    {
        $queryBuilder = $this->getQueryBuilder();

        $queryResult = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($languageUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('l10n_parent', $queryBuilder->createNamedParameter($stateUid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();
        // fetchAssociative() returns false if the state has no translation
        return $queryResult ?: [];
    }

    /**
     * @return QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE);
    }

}
