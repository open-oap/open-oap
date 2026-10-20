<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Service;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Uid\Uuid;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditLockService
{
    public const TABLE = 'tx_openoap_domain_model_proposallock';
    public const STALE_THRESHOLD_SECONDS = 1800;
    public const HARD_LIMIT_SECONDS = 28800;

    /**
     * Safety check for the beacon release: if the lock record has been accessed on the server side via `renew()` or `tryAcquire()` within the last
     * X seconds, it is highly likely that
     * the save/redirect flow is in progress — we then ignore the AJAX release beacon so that it does not
     * delete the newly restored lock entry.
     */
    public const BEACON_RELEASE_GUARD_SECONDS = 10;

    private readonly ConnectionPool $connectionPool;

    /**
     * ConnectionPool is optional so the service also works when fetched via
     * GeneralUtility::makeInstance() (the controllers use makeInstance; the service
     * is not a public DI service, so its constructor argument is not autowired there).
     */
    public function __construct(?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }

    public function generateToken(): string
    {
        return Uuid::v4()->toRfc4122();
    }

    public function tryAcquire(int $proposalUid, string $token, int $feUserUid): LockResult
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
        $now = time();

        try {
            $connection->insert(self::TABLE, [
                'proposal' => $proposalUid,
                'token' => $token,
                'fe_user' => $feUserUid,
                'last_activity' => $now,
                'session_started' => $now,
            ]);

            return new LockResult(true, $token);
        } catch (UniqueConstraintViolationException) {
            $existing = $this->fetch($proposalUid);
            if ($existing === null) {
                return new LockResult(false, $token);
            }

            // Same token: lock already belongs to us — refresh activity, keep session_started.
            if (hash_equals((string)$existing['token'], $token)) {
                $connection->update(
                    self::TABLE,
                    ['last_activity' => $now],
                    ['proposal' => $proposalUid, 'token' => $token],
                    [Connection::PARAM_INT, Connection::PARAM_INT, Connection::PARAM_STR]
                );

                return new LockResult(true, $token);
            }

            // Foreign token: takeover only if stale. UPDATE with stale-WHERE is atomic.
            $stale = $now - self::STALE_THRESHOLD_SECONDS;
            if ((int)$existing['last_activity'] >= $stale) {
                return new LockResult(false, $token);
            }

            $affected = (int)$connection->executeStatement(
                'UPDATE ' . self::TABLE . '
                 SET token = :token,
                     fe_user = :feUser,
                     last_activity = :now,
                     session_started = :now
                 WHERE proposal = :proposal
                   AND last_activity < :stale',
                [
                    'token' => $token,
                    'feUser' => $feUserUid,
                    'now' => $now,
                    'stale' => $stale,
                    'proposal' => $proposalUid,
                ],
                [
                    'token' => Connection::PARAM_STR,
                    'feUser' => Connection::PARAM_INT,
                    'now' => Connection::PARAM_INT,
                    'stale' => Connection::PARAM_INT,
                    'proposal' => Connection::PARAM_INT,
                ]
            );

            return new LockResult($affected > 0, $token);
        }
    }

    public function isValid(int $proposalUid, string $token): bool
    {
        $row = $this->fetch($proposalUid);
        if ($row === null) {
            return false;
        }
        if (!hash_equals((string)$row['token'], $token)) {
            return false;
        }
        $now = time();
        if ($now - (int)$row['last_activity'] >= self::STALE_THRESHOLD_SECONDS) {
            return false;
        }
        if ($now - (int)$row['session_started'] > self::HARD_LIMIT_SECONDS) {
            return false;
        }

        return true;
    }

    public function renew(int $proposalUid, string $token): bool
    {
        $existing = $this->fetch($proposalUid);
        if ($existing === null || !hash_equals((string)$existing['token'], $token)) {
            return false;
        }

        $this->connectionPool->getConnectionForTable(self::TABLE)->update(
            self::TABLE,
            ['last_activity' => time()],
            ['proposal' => $proposalUid, 'token' => $token],
            [Connection::PARAM_INT, Connection::PARAM_INT, Connection::PARAM_STR]
        );

        return true;
    }

    public function getSessionStartedTimestamp(int $proposalUid, string $token): ?int
    {
        $row = $this->fetch($proposalUid);
        if ($row === null || !hash_equals((string)$row['token'], $token)) {
            return null;
        }

        return (int)$row['session_started'];
    }

    public function release(int $proposalUid, string $token): void
    {
        $this->connectionPool->getConnectionForTable(self::TABLE)->delete(
            self::TABLE,
            ['proposal' => $proposalUid, 'token' => $token],
            [Connection::PARAM_INT, Connection::PARAM_STR]
        );
    }

    /**
     * Returns true if the lock row has been touched on the server within the last
     * BEACON_RELEASE_GUARD_SECONDS (renew/tryAcquire). Guards against late beacons that
     * would otherwise wipe the lock entry just reacquired during the save/redirect flow.
     */
    public function isRecentlyTouched(int $proposalUid, string $token): bool
    {
        $row = $this->fetch($proposalUid);
        if ($row === null || !hash_equals((string)$row['token'], $token)) {
            return false;
        }

        return time() - (int)$row['last_activity'] < self::BEACON_RELEASE_GUARD_SECONDS;
    }

    /**
     * Returns the UIDs of the proposals from $proposalUids that are currently locked
     * (regardless of which FE user locked them). Stale entries are ignored.
     *
     * Note: Locks placed by the current FE user are also returned, as the dashboard
     * does not carry a token — clicking ‘Edit’ would result in a new token conflicting with
     * an active self-imposed lock from another tab.
     *
     * @param int[] $proposalUids
     * @return array<int, true> Map proposalUid => true
     */
    public function findActiveLocks(array $proposalUids): array
    {
        if ($proposalUids === []) {
            return [];
        }

        $now = time();
        $minActivity = $now - self::STALE_THRESHOLD_SECONDS;
        $minStart = $now - self::HARD_LIMIT_SECONDS;

        $qb = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $rows = $qb->select('proposal')
            ->from(self::TABLE)
            ->where(
                $qb->expr()->in('proposal', $qb->createNamedParameter($proposalUids, Connection::PARAM_INT_ARRAY)),
                $qb->expr()->gte('last_activity', $qb->createNamedParameter($minActivity, Connection::PARAM_INT)),
                $qb->expr()->gte('session_started', $qb->createNamedParameter($minStart, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $locked = [];
        foreach ($rows as $row) {
            $locked[(int)$row['proposal']] = true;
        }

        return $locked;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetch(int $proposalUid): ?array
    {
        $qb = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $row = $qb->select('token', 'last_activity', 'session_started')
            ->from(self::TABLE)
            ->where($qb->expr()->eq('proposal', $qb->createNamedParameter($proposalUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return $row === false ? null : $row;
    }
}
