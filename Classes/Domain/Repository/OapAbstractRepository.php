<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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
* Place for common functions
*/
class OapAbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @param QueryInterface $query
     */
    protected function sqlDebug(QueryInterface $query): void
    {
        $queryParser = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);
        DebuggerUtility::var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL());
        DebuggerUtility::var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters());
    }

    /**
     * Find formPage by pid
     *
     * @param int $pid
     */
    public function findAllByPid(int $pid, string $orderByColumn = 'uid'): QueryResultInterface|array
    {
        $query = $this->getQueryForBELists($orderByColumn);
        $query->matching($query->equals('pid', $pid));
        $result = $query->execute();

        return $result;
    }

    /**
     * @return QueryInterface
     */
    protected function getQueryForBELists(string $orderBy, string $orderDir = 'ASC'): QueryInterface
    {
        $orderDirection = $orderDir == 'ASC' ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING;

        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->setOrderings([$orderBy => $orderDirection]);

        return $query;
    }

}
