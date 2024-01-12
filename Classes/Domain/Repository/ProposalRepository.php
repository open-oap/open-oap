<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

use OpenOAP\OpenOap\Domain\Model\Applicant;

use OpenOAP\OpenOap\Domain\Model\Call;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
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
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_openoap_domain_model_proposal');
        $queryBuilder = $connection->createQueryBuilder();
        if (isset($filter['state']) && $filter['state'] !== '') {
            $stateWhere = $queryBuilder->expr()->eq('Proposal.state', $queryBuilder->createNamedParameter($filter['state'], \PDO::PARAM_INT));
        } else {
            $stateWhere = $queryBuilder->expr()->gte('Proposal.state', $queryBuilder->createNamedParameter($minStatusLevel, \PDO::PARAM_INT));
        }
        $query = $queryBuilder
            ->select(
                'Proposal.uid',
                'Proposal.title',
                'Proposal.edit_tstamp AS editTstamp',
                'Proposal.state',
                'Proposal.signature',
                'Proposal.applicant',
                'Proposal.tx_openoap_call AS `call`',
                'Applicant.company AS applicant_company',
                'Applicant.username AS applicant_username',
                'Call.title AS call_title'
            )
            ->from('tx_openoap_domain_model_proposal', 'Proposal')
            ->join(
                'Proposal',
                'fe_users',
                'Applicant',
                $queryBuilder->expr()->eq('Applicant.uid', $queryBuilder->quoteIdentifier('Proposal.applicant'))
            )
            ->join(
                'Proposal',
                'tx_openoap_domain_model_call',
                'Call',
                $queryBuilder->expr()->eq('Call.uid', $queryBuilder->quoteIdentifier('Proposal.tx_openoap_call'))
            )
            ->where(
                $queryBuilder->expr()->eq('Proposal.pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)),
                $stateWhere
            );

        if (!empty($filter['searchword'])) {
            $filter['searchword'] = trim($filter['searchword']);

            $orStatements = $queryBuilder->expr()->or();
            if (strlen($filter['searchword']) > 3) {
                foreach (['Proposal.title', 'Proposal.signature', 'Applicant.company', 'Applicant.username'] as $field) {
                    $orStatements->add(
                        $queryBuilder->expr()->like(
                            $field,
                            $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($filter['searchword']) . '%', \PDO::PARAM_STR)
                        )
                    );
                }
            } else {
                // no like search possible
                foreach (['Proposal.title', 'Proposal.signature', 'Applicant.company', 'Applicant.username'] as $field) {
                    $orStatements->add(
                        $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter($filter['searchword']))
                    );
                }
            }
            $queryBuilder->andWhere($orStatements);
        }

        if (!empty($filter['item'])) {
            $allFilterItemsConcatenated = implode('', $filter['item']);
            if ($allFilterItemsConcatenated !== '') {
                $filterCounter = 0;
                foreach ($filter['item'] as $filterItem => $filterValue) {
                    if ($filterValue !== '') {
                        //                        DebuggerUtility::var_dump($filterValue,__LINE__.' '.$filterItem);
                        $filterCounter++;

                        $orStatements = $queryBuilder->expr()->or();
                        $orStatements->add(
                            $queryBuilder->expr()->andX(
                                $queryBuilder->expr()->eq(
                                    'Answer.value',
                                    $queryBuilder->createNamedParameter($filterValue)
                                ),
                                $queryBuilder->expr()->eq(
                                    'Answer.item',
                                    $queryBuilder->createNamedParameter($filterItem, \PDO::PARAM_INT)
                                )
                            )
                        );
                        $orStatements->add(
                            $queryBuilder->expr()->andX(
                                $queryBuilder->expr()->like(
                                    'Answer.value',
                                    $queryBuilder->createNamedParameter('%\"' . $filterValue . '\"%')
                                ),
                                $queryBuilder->expr()->eq(
                                    'Answer.item',
                                    $queryBuilder->createNamedParameter($filterItem, \PDO::PARAM_INT)
                                )
                            )
                        );
                    }
                }
                $queryBuilder->andWhere($orStatements);

                $queryBuilder
                ->join(
                    'Proposal',
                    'tx_openoap_domain_model_answer',
                    'Answer',
                    $queryBuilder->expr()->inSet('Proposal.answers', 'Answer.uid')
                )
                ->groupBy('Proposal.uid')
                ->addSelectLiteral($queryBuilder->expr()->count('*', 'c'))
                ->add('having', 'c = ' . $filterCounter);
            }
        }

        if (count($sorting) > 0) {
            $query->OrderBy($sorting['field'], $sorting['direction']);
        }

        return $query->executeQuery()->fetchAllAssociative();
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

    public function findContent($search)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setLanguageOverlayMode(false);
        $query->getQuerySettings()->setRespectStoragePage(false);

        //        $query->matching($query->like('title', '%' . $search . '%'));
        $query->matching($query->like('title', '%' . $search . '%'));

        //        $constraints = [];
        //        $constraints[] = $query->like('title', '%' . $search . '%');
        //        $constraints[] = $query->equals('archived', $archived);
        //        $query->matching($query->logicalAnd(...$constraints));
        //        if ($limit > 0) {
        //            $query->setLimit($limit);
        //        }
        //        return $query->execute();
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $queryParser = $objectManager->get(Typo3DbQueryParser::class);
        /** @var QueryBuilder $doctrineQuery */
        $doctrineQuery = $queryParser->convertQueryToDoctrineQueryBuilder($query);
        echo $doctrineQuery->getSQL();
        print_r($doctrineQuery->getParameters());
        DebuggerUtility::var_dump(
            $doctrineQuery->execute()
        );
        DebuggerUtility::var_dump(
            $query->execute()
        );
    }
}
