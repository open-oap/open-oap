<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Upgrades;

use Doctrine\DBAL\ParameterType;
use OpenOAP\OpenOap\Controller\OapBaseController;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates the proposal upload folders from the flat scheme
 *   <base>/<applicantUid>/<proposalUid>/
 * to the sharded scheme
 *   <base>/<applicantUid ÷ bucketSize>/<applicantUid>/<proposalUid>/
 *
 * The upload base folder is resolved per site from the site configuration key
 * `open_oap.uploadFolder` (the same source the AjaxUpload middleware uses), with
 * a fallback to OapBaseController::$defaultUploadFolder.
 *
 * Candidates are the union of the applicant uids from the proposals table and the
 * numeric folders that physically exist under the base (so orphaned upload folders
 * without a proposal record are migrated too). They are processed in ascending uid
 * order, which guarantees that a small applicant uid N (colliding namewise with the
 * bucket "N" used by uids N*1000..N*1000+999) is moved away before that bucket is
 * created.
 *
 * Per applicant: if the target does not exist yet, the whole legacy folder is moved
 * (fast path); if it already exists (interrupted/partial run, or a pre-existing
 * bucket contents), the legacy proposal subfolders are merged into it and the then
 * empty legacy folder is removed. Uses the FAL API (moveFolder), so sys_file
 * identifiers/hashes and file references (which point to sys_file UIDs) stay
 * consistent. Idempotent and repeatable.
 */
#[UpgradeWizard('openOapProposalFolderSharding')]
final class ProposalFolderShardingWizard implements UpgradeWizardInterface, ChattyInterface, RepeatableInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TABLE_PROPOSALS = 'tx_openoap_domain_model_proposal';

    protected OutputInterface $output;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly ResourceFactory $resourceFactory,
        private readonly SiteFinder $siteFinder,
    ) {
        $this->setLogger(GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__));
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return 'OAP: shard proposal upload folders by applicant uid';
    }

    public function getDescription(): string
    {
        return 'Moves proposal upload folders from <base>/<applicantUid>/ to '
            . '<base>/<applicantUid ÷ ' . OapBaseController::APPLICANT_FOLDER_BUCKET_SIZE . '>/<applicantUid>/ '
            . 'to keep the number of entries per directory manageable for large user counts. '
            . 'Uses the FAL API, so file references stay intact.';
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    public function updateNecessary(): bool
    {
        foreach ($this->getUploadFolderIdentifiers() as $identifier) {
            $base = $this->resolveBase($identifier);
            if ($base === null) {
                continue;
            }
            [$storage, $basePath] = $base;
            if (!$storage->hasFolder($basePath)) {
                continue;
            }
            $candidates = $this->getApplicantCandidates($storage, $basePath);
            $maxBucket = $candidates === [] ? 0 : intdiv(max($candidates), OapBaseController::APPLICANT_FOLDER_BUCKET_SIZE);
            foreach ($candidates as $applicantUid) {
                if ($this->isLegacyApplicantFolder($storage, $basePath, $applicantUid, $maxBucket)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function executeUpdate(): bool
    {
        $bucketSize = OapBaseController::APPLICANT_FOLDER_BUCKET_SIZE;

        foreach ($this->getUploadFolderIdentifiers() as $identifier) {
            $base = $this->resolveBase($identifier);
            if ($base === null) {
                $this->write(sprintf('Skipping upload folder "%s" (storage not available).', $identifier));
                continue;
            }
            [$storage, $basePath] = $base;
            if (!$storage->hasFolder($basePath)) {
                $this->write(sprintf('Upload folder "%s" has no base folder — nothing to migrate.', $identifier));
                continue;
            }
            $baseFolder = $storage->getFolder($basePath);
            $candidates = $this->getApplicantCandidates($storage, $basePath);
            $maxBucket = $candidates === [] ? 0 : intdiv(max($candidates), $bucketSize);
            $this->write(sprintf('Migrating upload folder "%s" (%d candidate applicants) …', $identifier, count($candidates)));

            $moved = 0;
            $merged = 0;
            $skipped = 0;
            foreach ($candidates as $applicantUid) {
                if (!$this->isLegacyApplicantFolder($storage, $basePath, $applicantUid, $maxBucket)) {
                    $skipped++;
                    continue;
                }
                try {
                    if ($this->migrateApplicant($storage, $baseFolder, $basePath, $applicantUid, $bucketSize)) {
                        $moved++;
                    } else {
                        $merged++;
                    }
                    if (($moved + $merged) % 100 === 0) {
                        $this->write(sprintf('… processed %d applicant folders', $moved + $merged));
                    }
                } catch (\Throwable $e) {
                    $this->logger->error(
                        sprintf('[ProposalFolderShardingWizard] Failed to migrate applicant %d in "%s": %s', $applicantUid, $identifier, $e->getMessage()),
                        ['exception' => $e]
                    );
                    $this->write(sprintf('ERROR migrating applicant %d: %s', $applicantUid, $e->getMessage()));
                }
            }
            $this->write(sprintf('  → "%s": moved %d, merged %d, skipped %d.', $identifier, $moved, $merged, $skipped));
        }

        return true;
    }

    /**
     * Migrates a single applicant folder. Returns true for a whole-folder move
     * (fast path), false for a merge into an existing target.
     */
    protected function migrateApplicant(ResourceStorage $storage, Folder $baseFolder, string $basePath, int $applicantUid, int $bucketSize): bool
    {
        $bucket = (string)intdiv($applicantUid, $bucketSize);
        $legacyPath = $basePath . $applicantUid . '/';
        $bucketPath = $basePath . $bucket . '/';
        $targetPath = $bucketPath . $applicantUid . '/';

        $bucketFolder = $storage->hasFolder($bucketPath)
            ? $storage->getFolder($bucketPath)
            : $storage->createFolder($bucket, $baseFolder);

        // Fast path: target does not exist yet → move the whole legacy folder.
        if (!$storage->hasFolder($targetPath)) {
            $storage->moveFolder($storage->getFolder($legacyPath), $bucketFolder);
            return true;
        }

        // Merge path: move each proposal subfolder into the existing target.
        $targetFolder = $storage->getFolder($targetPath);
        foreach ($storage->getFolder($legacyPath)->getSubfolders() as $subfolder) {
            if (!$targetFolder->hasFolder($subfolder->getName())) {
                $storage->moveFolder($subfolder, $targetFolder);
            }
        }
        // Remove the now (proposal-)empty legacy folder (only auto-generated stubs
        // like index.html may remain — no proposal subfolders left).
        $legacyFolder = $storage->getFolder($legacyPath);
        if ($legacyFolder->getSubfolders() === []) {
            $storage->deleteFolder($legacyFolder, true);
        } else {
            $this->write(sprintf('  ! applicant %d: legacy folder not empty after merge, left in place.', $applicantUid));
        }
        return false;
    }

    /**
     * A numeric folder directly under base is a legacy applicant folder when it
     * exists and is not a bucket. A bucket is recognised structurally: it contains
     * a child folder that itself has subfolders (bucket → applicant → proposal),
     * whereas a legacy applicant folder's children (proposals) only contain files.
     * The (cheap) bucket check is only needed for uids small enough to be a bucket.
     */
    protected function isLegacyApplicantFolder(ResourceStorage $storage, string $basePath, int $applicantUid, int $maxBucket): bool
    {
        $legacyPath = $basePath . $applicantUid . '/';
        if (!$storage->hasFolder($legacyPath)) {
            return false;
        }
        if ($applicantUid <= $maxBucket && $this->isBucketFolder($storage->getFolder($legacyPath), $applicantUid)) {
            return false;
        }
        return true;
    }

    /**
     * Whether the folder is used as a shard bucket for $bucketNumber, i.e. every
     * child folder is an applicant folder that maps back to this bucket
     * (intdiv(childUid, bucketSize) === bucketNumber). A legacy applicant folder,
     * in contrast, contains proposal folders whose uids generally do not map back
     * to the (small) applicant uid — so a single deviating child marks it as legacy.
     * An empty folder is treated as a bucket (nothing to migrate).
     */
    protected function isBucketFolder(Folder $folder, int $bucketNumber): bool
    {
        $children = $folder->getSubfolders();
        if ($children === []) {
            return true;
        }
        $bucketSize = OapBaseController::APPLICANT_FOLDER_BUCKET_SIZE;
        foreach ($children as $child) {
            $name = $child->getName();
            if (!ctype_digit($name) || intdiv((int)$name, $bucketSize) !== $bucketNumber) {
                return false;
            }
        }
        return true;
    }

    /**
     * Union of applicant uids from proposals and numeric folders physically present
     * under base, ascending and distinct.
     *
     * @return int[]
     */
    protected function getApplicantCandidates(ResourceStorage $storage, string $basePath): array
    {
        $uids = [];
        foreach ($this->getApplicantUids() as $uid) {
            $uids[$uid] = $uid;
        }
        if ($storage->hasFolder($basePath)) {
            foreach ($storage->getFolder($basePath)->getSubfolders() as $folder) {
                $name = $folder->getName();
                if (ctype_digit($name)) {
                    $uid = (int)$name;
                    if ($uid > 0) {
                        $uids[$uid] = $uid;
                    }
                }
            }
        }
        ksort($uids);
        return array_values($uids);
    }

    /**
     * Distinct upload folder identifiers (e.g. "2:/") configured per site via
     * `open_oap.uploadFolder`, falling back to the extension default.
     *
     * @return string[]
     */
    protected function getUploadFolderIdentifiers(): array
    {
        $identifiers = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            $configured = $site->getConfiguration()['open_oap']['uploadFolder'] ?? '';
            if (is_string($configured) && trim($configured) !== '') {
                $identifiers[$configured] = $configured;
            }
        }
        if ($identifiers === []) {
            $identifiers[OapBaseController::$defaultUploadFolder] = OapBaseController::$defaultUploadFolder;
        }
        return array_values($identifiers);
    }

    /**
     * @return array{0: ResourceStorage, 1: string}|null storage + base folder path (e.g. "/")
     */
    protected function resolveBase(string $uploadFolderIdentifier): ?array
    {
        [$storageUid, $path] = array_pad(explode(':', $uploadFolderIdentifier, 2), 2, '');
        try {
            $storage = $this->resourceFactory->getStorageObject((int)$storageUid);
        } catch (\Throwable) {
            return null;
        }
        $trimmed = trim($path, '/');
        $basePath = $trimmed === '' ? '/' : '/' . $trimmed . '/';
        return [$storage, $basePath];
    }

    /**
     * Distinct applicant uids (ascending) that have proposals — including deleted
     * ones, so their orphaned upload folders are migrated too.
     *
     * @return int[]
     */
    protected function getApplicantUids(): array
    {
        $qb = $this->connectionPool->getQueryBuilderForTable(self::TABLE_PROPOSALS);
        $qb->getRestrictions()->removeByType(DeletedRestriction::class);
        $rows = $qb->selectLiteral('DISTINCT applicant')
            ->from(self::TABLE_PROPOSALS)
            ->where($qb->expr()->gt('applicant', $qb->createNamedParameter(0, ParameterType::INTEGER)))
            ->orderBy('applicant', 'ASC')
            ->executeQuery()
            ->fetchFirstColumn();

        return array_map('intval', $rows);
    }

    protected function write(string $message): void
    {
        $this->logger->info('[ProposalFolderShardingWizard] ' . $message);
        if (isset($this->output)) {
            $this->output->writeln($message);
        }
    }
}