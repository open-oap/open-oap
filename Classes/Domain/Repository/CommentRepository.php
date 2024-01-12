<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

use OpenOAP\OpenOap\Domain\Model\Proposal;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 *          Ingeborg Hess <ingeborg.hess@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * The repository for Comments
 */
class CommentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'crdate' => QueryInterface::ORDER_DESCENDING,
    ];
    /**
     * Find demanded comments
     *
     * @param Proposal $proposal
     * @param array $filter
     * @param string $sortOrder
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findDemanded(Proposal $proposal, array $filter=[], String $sortOrder='')
    {
        if ($proposal == null) {
            return [];
        }
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(false);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setLanguageOverlayMode(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
        if ($sortOrder == 'asc') {
            $query->setOrderings([
                'crdate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
            ]);
        }

        $constraints = [];
        $constraints[] = $query->equals('proposal', $proposal);
        if (!empty($filter['source'])) {
            $constraints[] = $query->equals('source', (int)($filter['source']));
        }

        $numberOfConstraints = count($constraints);
        if ($numberOfConstraints === 1) {
            $query->matching(reset($constraints));
        } elseif ($numberOfConstraints >= 2) {
            $query->matching($query->logicalAnd(...$constraints));
        }

        return $query->execute();
    }

    /**
     * Count comments by proposal and source
     *
     * @param Proposal $proposal
     * @param int $source
     * @return int count
     */
    public function countCommentsByProposalAndSource(Proposal $proposal, int $source = 0)
    {
        if ($proposal == null) {
            return 0;
        }
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(false);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setLanguageOverlayMode(false);
        $query->getQuerySettings()->setRespectStoragePage(false);

        $constraints = [];
        $constraints[] = $query->equals('proposal', $proposal);
        $constraints[] = $query->equals('source', $source);

        $query->matching($query->logicalAnd(...$constraints));
        return $query->count();
    }

    /**
     * Count comments by proposal and state
     *
     * @param Proposal $proposal
     * @param int $state
     * @return int count
     */
    public function countCommentsByProposalAndState(Proposal $proposal, int $state = 0)
    {
        if ($proposal == null) {
            return 0;
        }
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(false);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setLanguageOverlayMode(false);
        $query->getQuerySettings()->setRespectStoragePage(false);

        $constraints = [];
        $constraints[] = $query->equals('proposal', $proposal);
        $constraints[] = $query->equals('state', $state);

        $query->matching($query->logicalAnd(...$constraints));
        return $query->count();
    }

    /**
     * Update the comments state of a proposal
     *
     * @param array $proposalIds
     * @param int $oldState
     * @param int $newState
     * @param int|null $source
     * @return int $updated number of updates
     */
    public function updateCommentsState(array $proposalIds, int $oldState, int $newState, int $source=null)
    {
        //DebuggerUtility::var_dump(\OpenOAP\OpenOap\Controller\OapBaseController::COMMENT_SOURCE_EDIT_ANSWER, 'Konstante Edit_Answer');exit;
        $table = 'tx_openoap_domain_model_comment';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $where[] = $queryBuilder->expr()->in('t.proposal', $queryBuilder->createNamedParameter($proposalIds, Connection::PARAM_INT_ARRAY));
        $where[] = $queryBuilder->expr()->eq('t.state', $queryBuilder->createNamedParameter($oldState, \PDO::PARAM_INT));
        if ($source != null) {
            $where[] = $queryBuilder->expr()->eq('t.source', $queryBuilder->createNamedParameter($source, \PDO::PARAM_INT));
        }

        $updateQuery = $queryBuilder
           ->update($table, 't')
           ->where(...$where)
           ->set('t.state', (int)$newState);
        //          DebuggerUtility::var_dump($queryBuilder->getParameters(), (string) __LINE__);
        //          DebuggerUtility::var_dump($queryBuilder->getSQL(), 'SQL');
        return $updateQuery->execute();
    }
}
