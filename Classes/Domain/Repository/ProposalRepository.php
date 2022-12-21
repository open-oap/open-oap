<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

use OpenOAP\OpenOap\Domain\Model\Applicant;

use OpenOAP\OpenOap\Domain\Model\Call;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * The repository for Proposals
 */
class ProposalRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * Find Calls/Forms by pid
     *
     * @param int $pid
     */
    public function findAllByPid(int $pid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->setOrderings(['title' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);
        $query->matching($query->equals('pid', $pid));
        $result = $query->execute();
        return $result;
    }

    /**
     * @param int $pid
     * @return int
     */
    public function getMaxSignature(int $pid): int
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->setOrderings(['signature' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);
        $query->matching($query->equals('pid', $pid));
        $query->setLimit(1);
        $result = $query->execute();

        if ($result[0]) {
            return $result[0]->getSignature();
        }
        return 0;
    }

    /**
     * @param Call $call
     * @param int $state
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function countByState(Call $call, int $state): int
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching(
            $query->logicalAnd([
               $query->equals('call', $call),
               $query->greaterThanOrEqual('state', $state),
           ])
        );
        return $query->count();
    }

    /**
     * Find
     *
     * @param array $uids
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByUids(array $uids)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching($query->in('uid', $uids));
        return $query->execute();
    }

    /**
     * @param int $pid
     * @param int $minStatusLevel
     * @param array $filter
     * @param array $sorting
     * @return object[]|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findDemanded(int $pid, int $minStatusLevel, array $filter = [], array $sorting = [])
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(false);
        $query->getQuerySettings()->setRespectStoragePage(true);
        $query->getQuerySettings()->setStoragePageIds([$pid]);

        if (count($sorting) == 0) {
            $sorting = ['edit_tstamp' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING];
        }
        $query->setOrderings($sorting);

        // call
        if ($filter['call'] and $filter['call'] !== '') {
            $constraints[] = $query->equals('call', $filter['call']);
        }
        // state
        if ($filter['state'] and $filter['state'] !== '') {
            $constraints[] = $query->equals('state', $filter['state']);
        } else {
            $constraints[] = $query->greaterThanOrEqual('state', $minStatusLevel);
        }
        // searchword
        if ($filter['searchword'] and $filter['searchword'] !== '') {
            $constraints[] = $query->logicalOr(
                $query->like('title', '%' . $filter['searchword'] . '%'),
                $query->like('signature', '%' . $filter['searchword'] . '%'),
                $query->like('applicant.company', '%' . $filter['searchword'] . '%'),
                $query->like('applicant.username', '%' . $filter['searchword'] . '%')
            );
        }
        $numberOfConstraints = count($constraints);
        if ($numberOfConstraints === 1) {
            $query->matching(reset($constraints));
        } elseif ($numberOfConstraints >= 2) {
            $query->matching($query->logicalAnd(...$constraints));
        }

//               $queryParser = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);
//               DebuggerUtility::var_dump($filter,(string) __LINE__);
//               DebuggerUtility::var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL(),(string) __LINE__);
//               DebuggerUtility::var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters(),(string) __LINE__);
//               DebuggerUtility::var_dump($constraints,(string) __LINE__);

        $result = $query->execute();

//               DebuggerUtility::var_dump($result,(string) __LINE__);

        // dynamic filter
        // in this case we us a regular sql statement
        // based on the previous result uids
        if (is_array($filter['item'])) {
            $join = '';
            $andWhere = '';
            $i = 0;
//            DebuggerUtility::var_dump($filter['item']);
            foreach ($filter['item'] as $itemKey => $item) {
                if ($item != '') {
                    $i++;
                    $join.= ' LEFT JOIN `tx_openoap_domain_model_answer` `tx_openoap_domain_model_answer' . $i . '` ON FIND_IN_SET(`tx_openoap_domain_model_answer' . $i . '`.`uid`,`tx_openoap_domain_model_proposal`.`answers`)';

                    if ($i > 1) {
                        $andWhere.= ' AND ';
                    }
                    $andWhere.= '(`tx_openoap_domain_model_answer' . $i . '`.`item` = ' . (int)$itemKey . ' AND  (`tx_openoap_domain_model_answer' . $i . "`.`value` = '" . htmlspecialchars($item) . "' OR `tx_openoap_domain_model_answer" . $i . "`.`value` LIKE '{%:\"" . htmlspecialchars($item) . "\"%}'))";
                }
            }
            if ($join != '' && count($result) > 0) {
                foreach ($result as $item) {
                    $uids[] = $item->getUid();
                }
                $uidList = implode(',', $uids);
                $stmt = 'SELECT `tx_openoap_domain_model_proposal`.* FROM `tx_openoap_domain_model_proposal` `tx_openoap_domain_model_proposal`';
                $stmt.= $join;
                $stmt.= 'WHERE `tx_openoap_domain_model_proposal`.`uid` IN(' . $uidList . ')';
                $stmt.= ' AND(' . $andWhere . ') ORDER BY FIELD(tx_openoap_domain_model_proposal.uid,' . $uidList . ')';

//               DebuggerUtility::var_dump($stmt, 'statement');

                $query2 = $this->createQuery();
                $result2 = $query2->statement($stmt)->execute();

                //               DebuggerUtility::var_dump($result2);

                return $result2;
            }
        }
        return $result;

        //        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
        //            ->getConnectionForTable('tx_openoap_domain_model_proposal');
        //
        //        // Test-Suche in einem JSON-Objekt in der Spalte "title", das so aussieht
        //        // {"country":"Deutschland"}
        //        $queryBuilder = $connection->createQueryBuilder();
        //        $query = $queryBuilder
        //            ->select('*')
        //            ->from('tx_openoap_domain_model_proposal')
        //            ->where('JSON_CONTAINS(title, \'"Deutschland"\', \'$.country\')');
        //
        //        $rows = $query->execute()->fetchAllAssociative();
        //        DebuggerUtility::var_dump($rows);die();
    }

    /**
     * Find proposals by applicant
     *
     * @param Applicant $applicant
     * @param int $archived
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findProposalsByApplicant(Applicant $applicant, int $archived = 0, int $limit = 0)
    {
        if ($applicant == null) {
            return [];
        }
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(false);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setLanguageOverlayMode(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
//        $query->getQuerySettings()->setStoragePageIds([$pid]);
        //$query->setOrderings(['crdate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);
        $query->setOrderings(['edit_tstamp' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);

        $constraints = [];
        $constraints[] = $query->equals('applicant', $applicant);
        $constraints[] = $query->equals('archived', $archived);
        $query->matching($query->logicalAnd(...$constraints));
        if ($limit > 0) {
            $query->setLimit($limit);
        }
        return $query->execute();
    }

    /**
     * Find proposals by applicant
     *
     * @param Applicant $applicant
     * @param int $archived
     * @return int $count
     */
    public function countProposalsByApplicant(Applicant $applicant, int $archived = 0)
    {
        if ($applicant == null) {
            return 0;
        }
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(false);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setLanguageOverlayMode(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
//        $query->getQuerySettings()->setStoragePageIds([$pid]);
        $constraints = [];
        $constraints[] = $query->equals('applicant', $applicant);
        $constraints[] = $query->equals('archived', $archived);
        $query->matching($query->logicalAnd(...$constraints));
        return $query->count();
    }

    /**
     * Update the state of a proposal
     *
     * @param array $proposalIds
     * @param int $newState
     * @return int $updated number of updates
     */
    public function updateProposalState(array $proposalIds, int $newState)
    {
        if (!is_array($proposalIds) || count($proposalIds) == 0 || $newState == null) {
            return 0;
        }
        $table = 'tx_openoap_domain_model_proposal';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $updateQuery = $queryBuilder
           ->update($table, 't')
           ->where(
               $queryBuilder->expr()->in('t.uid', $queryBuilder->createNamedParameter($proposalIds, Connection::PARAM_INT_ARRAY))
           )
           ->set('t.state', (int)$newState);
//           DebuggerUtility::var_dump($queryBuilder->getParameters(), (string) __LINE__);
//           DebuggerUtility::var_dump($queryBuilder->getSQL(), 'SQL');
        $updated = $updateQuery->execute();
//        DebuggerUtility::var_dump($updated, (string) __LINE__);
    }

    /**
     * @param int $pid
     * @param int $langId
     * @return object[]|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findMailtextFlexformdata(int $pid, int $langId)
    {
        $table = 'tt_content';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $where[] = $queryBuilder->expr()->eq('t.pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT));
        $where[] = $queryBuilder->expr()->eq('t.sys_language_uid', $queryBuilder->createNamedParameter($langId, \PDO::PARAM_INT));
        $where[] = $queryBuilder->expr()->isNotNull('t.pi_flexform');

        $result = $queryBuilder
            ->select('t.pi_flexform')
            ->from($table, 't')
            ->where(...$where)
            ->executeQuery();

        return $result->fetchAssociative()['pi_flexform'];
    }
}
