<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Task;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderReadPermissionsException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Scheduler task to archive proposals and cleanup related data
 */
class ArchiveProposalsTask extends AbstractTask
{
    protected const TABLE_CALLS = 'tx_openoap_domain_model_call';
    protected const TABLE_PROPOSALS = 'tx_openoap_domain_model_proposal';
    protected const TABLE_ANSWERS = 'tx_openoap_domain_model_answer';
    protected const TABLE_COMMENT = 'tx_openoap_domain_model_comment';
    protected const TABLE_SYS_FILE = 'sys_file';
    protected const TABLE_FORMITEMS = 'tx_openoap_domain_model_formitem';

    protected const FIELD_CALL = 'tx_openoap_call';
    protected const FIELD_ARCHIVED = 'archived';
    protected const FIELD_TSTAMP = 'tstamp';
    protected const FIELD_PROPOSAL_STATE = 'state';
    protected const FIELD_ARCHIVED_AT = 'archived_at';
    protected const FIELD_DELETED = 'deleted';
    protected const FIELD_ARCHIVED_STATE = 'archived_state';
    protected const ARCHIVE_FLAG_VALUE = 1;
    // Public: the backend module reads the state to decide what it still offers.
    public const ARCHIVE_STATE_RUNNING = 1;
    public const ARCHIVE_STATE_FINISHED = 2;

    public const DEFAULT_CHUNK = 500;

    /**
     * Max. number of proposals processed per call per run (drafts and submitted
     * are each limited to this). Configurable per task instance in the scheduler
     * backend module. A call stays "in progress" until a run finds nothing left,
     * so large volumes are worked off over multiple runs.
     */
    public int $chunk = self::DEFAULT_CHUNK;

    // Max. number of answer UIDs per soft-delete statement (bounds the IN() list).
    protected const ANSWERS_DELETE_BATCH = 1000;

    // Proposal state constant (mirrors OapBaseController::PROPOSAL_IN_PROGRESS).
    protected const PROPOSAL_IN_PROGRESS = 1;

    // Form item type constant (mirrors OapBaseController::TYPE_UPLOAD).
    protected const TYPE_UPLOAD = 9;

    // Generated Word exports are removed along with the uploads, the generated PDF is kept.
    protected const WORD_EXPORT_EXTENSION = 'docx';
    protected const LOGGING_LEVEL = 10;
    protected const LOGGING_LEVEL_DEBUG = 10;
    protected const LOGGING_LEVEL_INFO = 9;
    protected const LOGGING_LEVEL_WARNING = 5;
    protected const LOGGING_LEVEL_ERROR = 1;

    public const DEFAULT_START_TIME_HOUR = 1;
    public const DEFAULT_END_TIME_HOUR = 4;

    /**
     * Only run the archiving between these hours (0–24). Configurable per task
     * instance in the scheduler backend module (see ArchiveProposalsTaskAdditionalFieldProvider).
     * Set start = 0 and end = 24 to run at any time.
     */
    public int $startTimeHour = self::DEFAULT_START_TIME_HOUR;
    public int $endTimeHour = self::DEFAULT_END_TIME_HOUR;

    /**
     * @throws Exception
     * @throws InsufficientFolderAccessPermissionsException
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function execute(): bool
    {
//        DebuggerUtility::var_dump($this,basename(__FILE__). ' #'. (string) __LINE__ );
//        die();
        if ($this->logger === null) {
            $this->setLogger(
                GeneralUtility::makeInstance(LogManager::class)->getLogger(static::class)
            );
        }

        // Prüfen, ob die aktuelle Zeit innerhalb des erlaubten Zeitfensters liegt
        $currentHour = (int)date('H');

        if (!$this->isWithinRunWindow($currentHour)) {
            if (self::LOGGING_LEVEL >= self::LOGGING_LEVEL_DEBUG) {
                $message = sprintf(
                    '[ArchiveProposalsTask]: !! Archivierung wird nur zwischen %d und %d Uhr durchgeführt. Aktuelle Stunde: %d',
                    $this->startTimeHour,
                    $this->endTimeHour,
                    $currentHour
                );
                $this->addMessage($message, ContextualFeedbackSeverity::INFO);
                $this->logger->info($message);
            }
            return true;
        }

        $callsToArchive = $this->getCallsToArchive();
        if (!$callsToArchive and self::LOGGING_LEVEL >= self::LOGGING_LEVEL_DEBUG) {
            $this->addMessage('No calls to archive found.', ContextualFeedbackSeverity::INFO);
            $this->logger->info('[ArchiveProposalsTask]: No applicant records available for deletion. No updates executed.');
            return true;
        }

        $this->logger->info('[ArchiveProposalsTask] Starte Archivierung. ' . microtime(true));

        $log = [
            'draftAnswers' => 0,
            'draftComments' => 0,
            'draftProposals' => 0,
            'deletedFile' => 0,
            'deletedFolder' => 0,
            'submittedAnswers' => 0,
            'submittedProposals' => 0,
            'removedAnswers' => 0,
            'removedComments' => 0,
        ];

        foreach ($callsToArchive as $call) {
            $this->updateCallSetArchivedInProgress($call['uid']);
            $this->logger->info(sprintf('[ArchiveProposalsTask] Set call %d to "Archiving in progress". ', $call['uid']) . ' ' . microtime(true));

            // --- Handle drafts ---
            $allDrafts = $this->getAllProposals($call['uid'], self::PROPOSAL_IN_PROGRESS);
            $allDraftsAnswers = implode(',', array_column($allDrafts, 'answers'));

            $log['draftAnswers'] += $this->softDeleteAnswers($allDraftsAnswers);
            $log['draftComments'] += $this->softDeleteComments($allDrafts);
            $log['draftProposals'] += $this->softDeleteDraftProposals($allDrafts);

            $fileLog = $this->deleteFiles($allDrafts);
            $log['deletedFile'] += $fileLog['deletedFile'];
            $log['deletedFolder'] += $fileLog['deletedFolder'];

            // --- Handle submitted proposals ---
            $allProposals = $this->getAllProposals($call['uid']);
            $allAnswers = implode(',', array_column($allProposals, 'answers'));

            $log['submittedAnswers'] += $this->softDeleteAnswers($allAnswers);
            $log['submittedProposals'] += $this->setProposalsToArchived($allProposals);

            $fileLog = $this->deleteFiles($allProposals);
            $log['deletedFile'] += $fileLog['deletedFile'];
            $log['deletedFolder'] += $fileLog['deletedFolder'];

            // --- Handle proposals that were deleted before the archiving, e.g. drafts the
            // applicants removed themselves. Their answers, comments and files are still there,
            // and the passes above never see them because of the deleted restriction. ---
            $allRemoved = $this->getRemovedProposals($call['uid']);
            $allRemovedAnswers = implode(',', array_column($allRemoved, 'answers'));

            $log['removedAnswers'] += $this->softDeleteAnswers($allRemovedAnswers);
            $log['removedComments'] += $this->softDeleteComments($allRemoved);

            $fileLog = $this->deleteFiles($allRemoved);
            $log['deletedFile'] += $fileLog['deletedFile'];
            $log['deletedFolder'] += $fileLog['deletedFolder'];
        }

        $log['total'] = array_sum($log);

        if ($log['total'] === 0) {
            $this->setArchiveProcessAsFinished($callsToArchive);
            $logText = 'All calls to archive have been set to archived.';
        } else {
            $logText = 'The following changes have been made: ';
        }

        $logText .= sprintf(
            ' Draft answers: %d Draft comments: %d Draft proposals: %d Deleted files: %d'
            . ' Deleted folders: %d Submitted answers: %d Submitted proposals: %d'
            . ' Answers of removed proposals: %d Comments of removed proposals: %d',
            $log['draftAnswers'],
            $log['draftComments'],
            $log['draftProposals'],
            $log['deletedFile'],
            $log['deletedFolder'],
            $log['submittedAnswers'],
            $log['submittedProposals'],
            $log['removedAnswers'],
            $log['removedComments']
        );

        $this->addMessage($logText, ContextualFeedbackSeverity::INFO);
        $this->logger->info($logText);

        return true;
    }

    /**
     * Add a flash message
     *
     * @param string $message the flash message content
     * @param ContextualFeedbackSeverity $severity the flash message severity
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function addMessage(string $message, ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK): void
    {
        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, '', $severity);
        $service = GeneralUtility::makeInstance(FlashMessageService::class);
        $queue = $service->getMessageQueueByIdentifier();
        $queue->enqueue($flashMessage);
    }

    protected function getQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        if ($table === self::TABLE_CALLS) {
            // Whether a call is visible in the frontend says nothing about whether its data
            // should be archived - a retired call is a candidate rather than an exception.
            $queryBuilder->getRestrictions()
                ->removeByType(StartTimeRestriction::class)
                ->removeByType(EndTimeRestriction::class)
                ->removeByType(HiddenRestriction::class);
        }
        return $queryBuilder;
    }

    /**
     * @throws Exception
     */
    protected function getCallsToArchive(): array
    {
        $qb = $this->getQueryBuilder(self::TABLE_CALLS);
        // One call per run to keep the per-run load bounded. Already-running calls
        // (archived_state = RUNNING) are prioritised so a started call is finished
        // before a newly flagged one is picked up.
        return $qb->select('uid')
            ->from(self::TABLE_CALLS)
            ->where(
                $qb->expr()->eq(self::FIELD_ARCHIVED, $qb->createNamedParameter(self::ARCHIVE_FLAG_VALUE, ParameterType::INTEGER)),
                $qb->expr()->lt(self::FIELD_ARCHIVED_STATE, $qb->createNamedParameter(self::ARCHIVE_STATE_FINISHED, ParameterType::INTEGER))
            )
            ->orderBy(self::FIELD_ARCHIVED_STATE, 'DESC')
            ->addOrderBy('uid', 'ASC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function updateCallSetArchivedInProgress($uid): void
    {
        $qb = $this->getQueryBuilder(self::TABLE_CALLS);
        $qb->update(self::TABLE_CALLS)
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid, \Doctrine\DBAL\ParameterType::INTEGER)))
            ->set(self::FIELD_ARCHIVED, self::ARCHIVE_FLAG_VALUE)
            ->set(self::FIELD_ARCHIVED_STATE, self::ARCHIVE_STATE_RUNNING)
            ->set(self::FIELD_ARCHIVED_AT, date('Y-m-d H:i:s'))
            ->executeStatement();
    }

    /**
     * @throws Exception
     */
    protected function getAllProposals($callUid, int|null $proposalState = null): array
    {
        $qb = $this->getQueryBuilder(self::TABLE_PROPOSALS);

        $constraints = [
            $qb->expr()->eq(self::FIELD_CALL, $qb->createNamedParameter($callUid, \Doctrine\DBAL\ParameterType::INTEGER)),
            $qb->expr()->eq(self::FIELD_ARCHIVED, $qb->createNamedParameter(0, \Doctrine\DBAL\ParameterType::INTEGER)),
        ];

        if ($proposalState !== null) {
            $constraints[] = $qb->expr()->lte(self::FIELD_PROPOSAL_STATE, $qb->createNamedParameter($proposalState, \Doctrine\DBAL\ParameterType::INTEGER));
        }

        return $qb->select('uid', 'applicant', 'answers')
            ->from(self::TABLE_PROPOSALS)
            ->where(...$constraints)
            ->setMaxResults($this->chunk)
            ->executeQuery()
            ->fetchAllAssociativeIndexed();
    }

    protected function softDeleteAnswers(string $answersUids): int
    {
        // $answersUids is a comma-separated list of answer UIDs collected from up to
        // $this->chunk proposals. Split, sanitise and soft-delete in bounded batches
        // (parameterised IN()) to avoid an oversized single statement.
        $uids = array_values(array_unique(array_filter(
            array_map('intval', explode(',', $answersUids)),
            static fn(int $uid): bool => $uid > 0
        )));
        if ($uids === []) {
            return 0;
        }

        $affected = 0;
        foreach (array_chunk($uids, self::ANSWERS_DELETE_BATCH) as $batch) {
            $qb = $this->getQueryBuilder(self::TABLE_ANSWERS);
            $affected += (int)$qb->update(self::TABLE_ANSWERS)
                ->set(self::FIELD_DELETED, 1)
                ->where($qb->expr()->in('uid', $qb->createNamedParameter($batch, ArrayParameterType::INTEGER)))
                ->executeStatement();
        }
        return $affected;
    }

    protected function softDeleteComments(array $proposalsUids): int
    {
        if (!count($proposalsUids)) {
            return 0;
        }
        $qb = $this->getQueryBuilder(self::TABLE_COMMENT);
        return $qb->update(self::TABLE_COMMENT)
            ->set(self::FIELD_DELETED, 1)
            ->where($qb->expr()->in('proposal', array_keys($proposalsUids)))
            ->executeStatement();
    }

    protected function softDeleteDraftProposals(array $allDrafts): int
    {
        if (!count($allDrafts)) {
            return 0;
        }
        $qb = $this->getQueryBuilder(self::TABLE_PROPOSALS);
        return $qb->update(self::TABLE_PROPOSALS)
            ->set(self::FIELD_DELETED, 1)
            ->where($qb->expr()->in('uid', array_keys($allDrafts)))
            ->executeStatement();
    }

    /**
     * @throws Exception
     * @throws InsufficientFolderAccessPermissionsException
     * @throws \TYPO3\CMS\Core\Exception
     * @throws InsufficientFolderReadPermissionsException
     */
    protected function deleteFiles(array $proposals): array
    {
        $log = ['deletedFile' => 0, 'deletedFolder' => 0];

        if (!count($proposals)) {
            return $log;
        }

        // Keep the repository local: the task object is serialized into the database after each run
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $uploadedFileUids = $this->getUploadedFileUids($proposals);

        foreach ($proposals as $proposalUid => $proposal) {
            $folderPattern = sprintf('/%s/%s/', $proposal['applicant'], $proposalUid);

            $qb = $this->getQueryBuilder(self::TABLE_SYS_FILE);
            // Uploads are addressed by UID, the Word exports by their extension. The folder
            // pattern stays as a guard so nothing outside the proposal folder is touched.
            $whatToDelete = [
                $qb->expr()->eq('extension', $qb->createNamedParameter(self::WORD_EXPORT_EXTENSION)),
            ];
            if ($uploadedFileUids !== []) {
                $whatToDelete[] = $qb->expr()->in(
                    'uid',
                    $qb->createNamedParameter($uploadedFileUids, ArrayParameterType::INTEGER)
                );
            }

            $files = $qb->select('identifier', 'storage', 'uid')
                ->from(self::TABLE_SYS_FILE)
                ->where(
                    $qb->expr()->like(
                        'identifier',
                        $qb->createNamedParameter($qb->escapeLikeWildcards($folderPattern) . '%')
                    ),
                    $qb->expr()->or(...$whatToDelete)
                )
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($files as $file) {
                $storage = $storageRepository->getStorageObject($file['storage']);

                try {
                    /** @var File $f */
                    $f = $storage->getFile($file['identifier']);
                    if ($f->exists()) {
                        $f->delete();
                        $log['deletedFile']++;
                    } else {
                        $this->logger->warning(
                            '[ArchiveProposalsTask] File not found: ' . $file['identifier'],
                            []
                        );
                    }
                } catch (FolderDoesNotExistException $e) {
                    $this->logger->error(
                        '[ArchiveProposalsTask] Folder not found: ' . dirname($file['identifier']) . ' / ' . $e->getMessage(),
                        ['exception' => $e]
                    );
                } catch (\Exception $e) {
                    $this->logger->error(
                        '[ArchiveProposalsTask] File handling error: ' . $file['identifier'] . ' / ' . $e->getMessage(),
                        ['exception' => $e]
                    );
                }
            }
        }
        return $log;
    }

    protected function setProposalsToArchived(array $allProposals): int
    {
        if (!count($allProposals)) {
            return 0;
        }
        $qb = $this->getQueryBuilder(self::TABLE_PROPOSALS);
        return $qb->update(self::TABLE_PROPOSALS)
            ->set(self::FIELD_ARCHIVED, 1)
            ->set(self::FIELD_TSTAMP, time())
            ->where($qb->expr()->in('uid', array_keys($allProposals)))
            ->executeStatement();
    }

    /**
     * A window where the start hour is greater than the end hour describes a window
     * crossing midnight, e.g. 22 - 2.
     */
    protected function isWithinRunWindow(int $currentHour): bool
    {
        if ($this->startTimeHour < $this->endTimeHour) {
            return $currentHour >= $this->startTimeHour && $currentHour < $this->endTimeHour;
        }
        return $currentHour >= $this->startTimeHour || $currentHour < $this->endTimeHour;
    }

    /**
     * Proposals that were already deleted before the archiving run, but still have
     * undeleted answers. The regular passes never see them because of the deleted
     * restriction, so their answers, comments and files would stay behind for good.
     *
     * @throws Exception
     */
    protected function getRemovedProposals($callUid): array
    {
        $qb = $this->getQueryBuilder(self::TABLE_PROPOSALS);
        $qb->getRestrictions()->removeAll();

        $hasAnswersLeft = sprintf(
            'EXISTS (SELECT 1 FROM %s answer WHERE answer.%s = %s.uid AND answer.%s = 0)',
            $qb->quoteIdentifier(self::TABLE_ANSWERS),
            $qb->quoteIdentifier('proposal'),
            $qb->quoteIdentifier(self::TABLE_PROPOSALS),
            $qb->quoteIdentifier(self::FIELD_DELETED)
        );

        return $qb->select('uid', 'applicant', 'answers')
            ->from(self::TABLE_PROPOSALS)
            ->where(
                $qb->expr()->eq(self::FIELD_CALL, $qb->createNamedParameter($callUid, ParameterType::INTEGER)),
                $qb->expr()->eq(self::FIELD_DELETED, $qb->createNamedParameter(1, ParameterType::INTEGER)),
                $hasAnswersLeft
            )
            ->setMaxResults($this->chunk)
            ->executeQuery()
            ->fetchAllAssociativeIndexed();
    }

    /**
     * UIDs of the files referenced by the upload answers of the given proposals.
     *
     * @throws Exception
     */
    protected function getUploadedFileUids(array $proposals): array
    {
        $answerUids = [];
        foreach ($proposals as $proposal) {
            foreach (GeneralUtility::intExplode(',', (string)($proposal['answers'] ?? ''), true) as $answerUid) {
                $answerUids[] = $answerUid;
            }
        }
        $answerUids = array_values(array_unique(array_filter($answerUids, static fn (int $uid): bool => $uid > 0)));
        if ($answerUids === []) {
            return [];
        }

        $fileUids = [];
        foreach (array_chunk($answerUids, self::ANSWERS_DELETE_BATCH) as $batch) {
            $qb = $this->getQueryBuilder(self::TABLE_ANSWERS);
            $qb->getRestrictions()->removeAll();
            $values = $qb->select('a.value')
                ->from(self::TABLE_ANSWERS, 'a')
                ->join('a', self::TABLE_FORMITEMS, 'i', $qb->expr()->eq('i.uid', $qb->quoteIdentifier('a.item')))
                ->where(
                    $qb->expr()->in('a.uid', $qb->createNamedParameter($batch, ArrayParameterType::INTEGER)),
                    $qb->expr()->eq('i.type', $qb->createNamedParameter(self::TYPE_UPLOAD, ParameterType::INTEGER))
                )
                ->executeQuery()
                ->fetchFirstColumn();

            foreach ($values as $value) {
                foreach (GeneralUtility::intExplode(',', (string)$value, true) as $fileUid) {
                    $fileUids[] = $fileUid;
                }
            }
        }

        return array_values(array_unique(array_filter($fileUids, static fn (int $uid): bool => $uid > 0)));
    }

    /**
     * Run window and chunk size in the task list of the scheduler module.
     */
    public function getAdditionalInformation(): string
    {
        return sprintf(
            '%02d:00 - %02d:00, %d proposals per run',
            $this->startTimeHour,
            $this->endTimeHour,
            $this->chunk
        );
    }

    protected function setArchiveProcessAsFinished(array $callsToArchive): void
    {
        if (!count($callsToArchive)) {
            return;
        }
        $qb = $this->getQueryBuilder(self::TABLE_CALLS);
        $qb->update(self::TABLE_CALLS)
            ->set(self::FIELD_ARCHIVED_STATE, self::ARCHIVE_STATE_FINISHED)
            ->set(self::FIELD_ARCHIVED_AT, date('Y-m-d H:i:s'))
            ->where($qb->expr()->in('uid', array_column($callsToArchive, 'uid')))
            ->executeStatement();
    }
}
