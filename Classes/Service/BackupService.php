<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Service for creating backups
 */
class BackupService
{
    public function __construct(
        private readonly \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool,
    )
    {
    }

    /**
     * Creates a complete backup of the TYPO3 installation
     */
    public function createBackup(string $backupId, int $userId): void
    {
        // Directories to be backed up
        $directories = [
            // Environment::getPublicPath(),
            // Environment::getConfigPath(),
        ];

        // Get all local file storages
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('sys_file_storage');
        $storages = $queryBuilder
            ->select('configuration')
            ->from('sys_file_storage')
            ->where(
                $queryBuilder->expr()->eq('driver', $queryBuilder->createNamedParameter('Local'))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        // Add storage paths to backup directories
        foreach ($storages as $storage) {
            // Parse the FlexForm configuration
            $config = $this->parseFlexFormConfiguration($storage['configuration']);
            if (isset($config['basePath'])) {
                $storagePath = rtrim($config['basePath'], '/');
                // Only add if path exists and is not already included
                if (is_dir($storagePath) && !in_array($storagePath, $directories)) {
                    $directories[] = $storagePath;
                }
            }
        }

        // Create backup directory (in typo3temp/var/backup)
        $backupDir = Environment::getVarPath() . '/backup';
        if (!is_dir($backupDir)) {
            GeneralUtility::mkdir_deep($backupDir);
        }

        // Target archive file
        $archiveFile = $backupDir . '/typo3_' . $backupId . '.zip';
        $tempDir = $backupDir . '/temp_' . $backupId;

        // Status file
        $statusFile = $backupDir . '/status_' . $backupId . '.json';

        $this->updateBackupStatus($statusFile, 0, [
            'id' => $backupId,
            'userId' => $userId,
            'started' => time(),
            'status' => 'running',
            'progress' => 0,
            'archiveFile' => $archiveFile,
        ]);

        $this->logInfo($backupId, 'Backup process started by user ' . $userId);

        try {
            // Create temporary directory for backup files
            if (!is_dir($tempDir)) {
                GeneralUtility::mkdir_deep($tempDir);
            }

            // Update status: 10% - Preparations completed
            $this->updateBackupStatus($statusFile, 10);
            $this->logInfo($backupId, 'Preparations completed, starting database dump');

            // Create database dump
            $databaseDump = $this->createDatabaseDump($tempDir, $backupId);

            // Update status: 40% - Database backup completed
            $this->updateBackupStatus($statusFile, 40);
            $this->logInfo($backupId, 'Database dump completed: ' . basename($databaseDump));

            // Create file backup (ZipArchive)
            $this->logInfo($backupId, 'Starting file backup for ' . count($directories) . ' directories');
            $this->createFileBackup($backupId, $tempDir, $archiveFile, $directories);

            // Update status: 90% - File backup completed
            $this->updateBackupStatus($statusFile, 90);
            $this->logInfo($backupId, 'File backup completed');

            // Cleanup: Delete temporary directory
            GeneralUtility::rmdir($tempDir, true);
            $this->logInfo($backupId, 'Temporary files cleaned up');

            // After successful backup creation:
            $status = json_decode(file_get_contents($statusFile), true);
            $status['status'] = 'completed';
            $status['progress'] = 100;
            $status['finished'] = time();
            $duration = $status['finished'] - $status['started'];

            // Ensure final file size is included
            if (file_exists($archiveFile)) {
                $status['archiveSize'] = filesize($archiveFile);
                $status['archiveSizeFormatted'] = $this->formatFileSize($status['archiveSize']);
                $this->logInfo($backupId, 'Backup completed successfully. Duration: ' . $this->formatDuration($duration) . ', Size: ' . $status['archiveSizeFormatted']);
            } else {
                $this->logInfo($backupId, 'Backup completed but archive file not found');
            }

            // Save updated status to file
            file_put_contents($statusFile, json_encode($status));

            // Remove the scheduler task if it exists
            if (isset($status['taskUid'])) {
                $this->logInfo($backupId, 'Scheduler task reference removed: ' . $status['taskUid']);
            }

            // Send notification
            $this->sendNotificationEmail($userId, $backupId);
            $this->logInfo($backupId, 'Notification email sent to user ' . $userId);
        } catch (\Exception $e) {
            // Failed - Update status
            $status['status'] = 'error';
            $status['error'] = $e->getMessage();
            file_put_contents($statusFile, json_encode($status));

            // Cleanup on error
            if (is_dir($tempDir)) {
                GeneralUtility::rmdir($tempDir, true);
            }

            // Log error
            $errorMessage = 'Backup failed: ' . $e->getMessage();
            $this->logError($errorMessage);
            $this->logInfo($backupId, $errorMessage);

            throw $e;
        }
    }

    /**
     * Updates the backup status
     */
    protected function updateBackupStatus(string $statusFile, int $progress, array $additionalData = []): void
    {
        // Check if file exists
        if (!file_exists($statusFile)) {
            $result = file_put_contents($statusFile, '{}');
            if ($result === false) {
                $this->logError('Failed to create status file: ' . $statusFile);
                return;
            }
        }

        // Read and parse the status file with error handling
        $fileContents = file_get_contents($statusFile);
        if ($fileContents === false) {
            $this->logError('Failed to read status file: ' . $statusFile);
            return;
        }

        $status = json_decode($fileContents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError('Invalid JSON in status file: ' . $statusFile . ' - Error: ' . json_last_error_msg());
            return;
        }

        $status['progress'] = $progress;

        // Add additional data to status
        foreach ($additionalData as $key => $value) {
            $status[$key] = $value;
        }

        // Check if archive file exists and add file size
        if (isset($status['archiveFile']) && file_exists($status['archiveFile'])) {
            $status['archiveSize'] = filesize($status['archiveFile']);
            $status['archiveSizeFormatted'] = $this->formatFileSize($status['archiveSize']);
        }

        // Write status back with error handling
        $result = file_put_contents($statusFile, json_encode($status));
        if ($result === false) {
            $this->logError('Failed to write status file: ' . $statusFile);
        }
    }

    /**
     * Formats file size for display
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Formats duration in seconds to a human readable format
     */
    protected function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        }

        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
            return $minutes . ' minutes, ' . $seconds . ' seconds';
        }

        $hours = floor($seconds / 3600);
        $seconds = $seconds % 3600;
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        return $hours . ' hours, ' . $minutes . ' minutes, ' . $seconds . ' seconds';
    }

    /**
     * Creates a database dump file and saves it in the specified temporary directory.
     *
     * @param string $tempDir The directory where the database dump file will be saved.
     * @param string $backupId The unique identifier for the backup process.
     * @return string The full path to the generated database dump file.
     * @throws \RuntimeException If the database dump fails.
     */
    protected function createDatabaseDump(string $tempDir, string $backupId): string
    {
        $dbDumpFile = $tempDir . '/database_' . $backupId . '.sql';

        // Read database configuration
        $connectionPool = $this->connectionPool;
        $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $connectionParams = $connection->getParams();

        // Get mysqldump binary path from extension configuration
        $extConf = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
            ->get('open_oap');
        $mysqlDumpBinary = !empty($extConf['mysqlDumpBinaryPath']) ?
            $extConf['mysqlDumpBinaryPath'] : 'mysqldump';

        $this->logInfo($backupId, 'Using mysqldump binary: ' . $mysqlDumpBinary);

        // Prepare SQL dump command
        if (Environment::isWindows()) {
            // Windows command
            $command = $mysqlDumpBinary . ' --host=' . escapeshellarg($connectionParams['host'] ?? 'localhost') .
                ' --user=' . escapeshellarg($connectionParams['user'] ?? '') .
                ' --password=' . escapeshellarg($connectionParams['password'] ?? '') .
                ' ' . escapeshellarg($connectionParams['dbname'] ?? '') .
                ' > ' . escapeshellarg($dbDumpFile);
        } else {
            // Unix command
            $command = $mysqlDumpBinary . ' --host=' . escapeshellarg($connectionParams['host'] ?? 'localhost') .
                ' --user=' . escapeshellarg($connectionParams['user'] ?? '') .
                ' --password=' . escapeshellarg($connectionParams['password'] ?? '') .
                ' ' . escapeshellarg($connectionParams['dbname'] ?? '') .
                ' > ' . escapeshellarg($dbDumpFile) . ' 2>&1';
        }

        // Disable time limit for potentially long-running process
        $originalTimeLimit = ini_get('max_execution_time');
        set_time_limit(0);

        // Execute command with timing
        $startTime = microtime(true);

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Restore original time limit
        set_time_limit((int)$originalTimeLimit);

        // Close database connection
        $connection->close();
        $connectionPool->resetConnections();

        if ($returnVar !== 0) {
            $this->logError('Database dump failed with error: ' . implode(PHP_EOL, $output));
            $this->logInfo($backupId, 'Database dump failed after ' . $duration . ' seconds');
            throw new \RuntimeException('Database dump failed with error: ' . implode(PHP_EOL, $output), 5271497568);
        }

        // Log file size and duration
        $dumpSize = filesize($dbDumpFile);
        $this->logInfo($backupId, 'Database dump completed in ' . $duration . ' seconds, size: ' . $this->formatFileSize($dumpSize));

        return $dbDumpFile;
    }

    /**
     * Creates a backup of files
     */
    protected function createFileBackup(string $backupId, string $tempDir, string $archiveFile, array $directories): void
    {
        // Check if ZipArchive is available
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('ZipArchive class is not available. Please install the zip PHP extension.', 9194795999);
        }

        $zip = new \ZipArchive();

        if ($zip->open($archiveFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create zip file: ' . $archiveFile, 5779417386);
        }

        // Get the status file path
        $backupDir = Environment::getVarPath() . '/backup';
        $statusFile = $backupDir . '/status_' . $backupId . '.json';

        // Add database dump
        $databaseDumpFile = $tempDir . '/database_*.sql';
        $databaseDumpFiles = glob($databaseDumpFile);

        foreach ($databaseDumpFiles as $dumpFile) {
            $zip->addFile($dumpFile, 'database/' . basename($dumpFile));
            $this->logInfo($backupId, 'Added database dump ' . basename($dumpFile) . ' to archive');

            // Update status after adding database dump
            $this->updateBackupStatus($statusFile, 45);
        }

        // Count total files to track progress
        $totalFiles = 0;

        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $totalFiles++;
                    }
                }
            }
        }

        $this->logInfo($backupId, 'Found ' . $totalFiles . ' files to backup');

        // Store data for the callback
        $this->backupContext = [
            'backupId' => $backupId,
            'statusFile' => $statusFile,
            'totalFiles' => $totalFiles,
            'archiveFile' => $archiveFile,
            'lastUpdate' => microtime(true),
            'lastLogTime' => microtime(true),
            'lastProgress' => 0,
            'processedFiles' => 0
        ];

        // Register the progress callback
        $zip->registerProgressCallback(0.05, function ($progress) {
            return $this->zipProgressCallback($progress);
        });

        $this->logInfo($backupId, 'Using ZipArchive progress callback');

        // Add directories to the archive
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                $this->logInfo($backupId, 'Processing directory: ' . $directory);
                $this->addDirectoryToZip($zip, $directory, basename($directory));
            } else {
                $this->logInfo($backupId, 'Directory not found: ' . $directory);
            }
        }

        // Close the archive
        $zip->close();
        $this->logInfo($backupId, 'Archive closed. Total files processed: ' . $this->backupContext['processedFiles']);

        // Reset the backup context
        $this->backupContext = null;

        // Final update with the completed archive size
        $this->updateBackupStatus($statusFile, 90, ['fileCount' => $totalFiles]);
    }

    /**
     * Property to store context information for the ZIP progress callback
     *
     * @var array|null
     */
    protected $backupContext = null;

    /**
     * Callback for ZipArchive::registerProgressCallback
     *
     * @param float $progress Progress value between 0 and 1
     * @return bool True to continue, false to abort
     */
    protected function zipProgressCallback(float $progress): bool
    {
        if (!is_array($this->backupContext)) {
            return true; // Continue if no context available
        }

        $now = microtime(true);
        $elapsedSinceLastUpdate = $now - $this->backupContext['lastUpdate'];

        // Only update every 0.5 seconds to avoid too many updates
        if ($elapsedSinceLastUpdate < 0.5 && $progress < 0.99) {
            return true;
        }

        $this->backupContext['lastUpdate'] = $now;

        // Convert progress to percentage (0-100)
        $percentComplete = round($progress * 100);

        // Scale progress from 45% to 90% (database was already at 45%)
        $scaledProgress = 45 + (int)($percentComplete * 0.45);

        // Only update if progress changed
        if ($scaledProgress > $this->backupContext['lastProgress']) {
            $this->backupContext['lastProgress'] = $scaledProgress;

            // Estimate processed files based on progress percentage
            $estimatedProcessedFiles = (int)round($this->backupContext['totalFiles'] * $progress);
            $this->backupContext['processedFiles'] = $estimatedProcessedFiles;

            // Get the current archive size
            $archiveSize = 0;
            $archiveSizeFormatted = '';
            if (file_exists($this->backupContext['archiveFile'])) {
                $archiveSize = filesize($this->backupContext['archiveFile']);
                $archiveSizeFormatted = $this->formatFileSize($archiveSize);
            }

            // Update the status file
            $this->updateBackupStatus($this->backupContext['statusFile'], $scaledProgress, [
                'processedFiles' => $estimatedProcessedFiles,
                'totalFiles' => $this->backupContext['totalFiles'],
                'archiveSize' => $archiveSize,
                'archiveSizeFormatted' => $archiveSizeFormatted
            ]);

            // Log progress every 10% or at least every 30 seconds
            $elapsedSinceLastLog = $now - $this->backupContext['lastLogTime'];
            if ($elapsedSinceLastLog > 30 || ($percentComplete % 10 === 0 && $percentComplete > 0)) {
                $this->backupContext['lastLogTime'] = $now;
                $this->logInfo(
                    $this->backupContext['backupId'],
                    sprintf(
                        'ZIP progress: %d%% complete, estimated files: %d/%d, archive size: %s',
                        $percentComplete,
                        $estimatedProcessedFiles,
                        $this->backupContext['totalFiles'],
                        $archiveSizeFormatted
                    )
                );
            }
        }

        return true; // Continue processing
    }

    /**
     * Adds a directory to the ZIP archive recursively
     */
    protected function addDirectoryToZip(
        \ZipArchive $zip,
        string $directory,
        string $zipPath
    ): void {
        if (!is_dir($directory)) {
            return;
        }

        // Add directory itself
        $zip->addEmptyDir($zipPath);

        // Iterate through files in the directory
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = str_replace($directory, '', $filePath);
            $relativePath = ltrim($relativePath, '/\\');
            $relativePath = $zipPath . '/' . $relativePath;

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } elseif ($file->isFile()) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Logs an error
     */
    public function logError(string $message): void
    {
        $logFile = Environment::getVarPath() . '/log/backup_error.log';
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Logs backup information
     */
    public function logInfo(string $backupId, string $message): void
    {
        $logDir = Environment::getVarPath() . '/log';
        if (!is_dir($logDir)) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($logDir);
        }

        $logFile = $logDir . '/backup_info.log';
        $logMessage = date('Y-m-d H:i:s') . ' - [Backup ' . $backupId . '] ' . $message . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Checks the status of a backup
     */
    public function getBackupStatus(string $backupId): array
    {
        $backupDir = Environment::getVarPath() . '/backup';
        $statusFile = $backupDir . '/status_' . $backupId . '.json';

        if (file_exists($statusFile)) {
            $fileContents = file_get_contents($statusFile);
            if ($fileContents === false) {
                $this->logError('Failed to read status file: ' . $statusFile);
                return [
                    'id' => $backupId,
                    'status' => 'error',
                    'error' => 'Unable to read status file'
                ];
            }

            $status = json_decode($fileContents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError('Invalid JSON in status file: ' . $statusFile . ' - Error: ' . json_last_error_msg());
                return [
                    'id' => $backupId,
                    'status' => 'error',
                    'error' => 'Invalid status file format'
                ];
            }

            return $status;
        }

        return [
            'id' => $backupId,
            'status' => 'not_found',
        ];
    }

    /**
     * Cleans up expired temporary download links
     *
     * @param bool $logOutput Whether to log output (default: false)
     * @return int Number of cleaned-up links
     */
    public function cleanupTemporaryLinks(bool $logOutput = false): int
    {
        $backupDir = Environment::getVarPath() . '/backup';
        $cleanedCount = 0;

        $linkFiles = glob($backupDir . '/links_*.json');

        if (empty($linkFiles)) {
            if ($logOutput) {
                $this->logInfo('cleanup', 'No temporary links found');
            }
            return 0;
        }

        $now = time();

        foreach ($linkFiles as $linksFile) {
            $links = json_decode(file_get_contents($linksFile), true);

            // Skip if links info is invalid
            if (!is_array($links)) {
                continue;
            }

            $backupId = '';
            $expiredLinks = [];
            $validLinks = [];

            // Process each link in the array
            foreach ($links as $linkInfo) {
                // Skip if link info is invalid
                if (!is_array($linkInfo) || !isset($linkInfo['expires']) || !isset($linkInfo['link'])) {
                    continue;
                }

                $backupId = $linkInfo['backupId'] ?? 'unknown';

                // Check if a link has expired
                if ($linkInfo['expires'] < $now || !file_exists($linkInfo['original'])) {
                    // Remove the symlink/copy
                    @unlink($linkInfo['link']);

                    if ($logOutput) {
                        $this->logInfo($backupId, 'Removed expired temporary link: ' . $linkInfo['link']);
                    }

                    $expiredLinks[] = $linkInfo;
                } else {
                    // Keep valid links
                    $validLinks[] = $linkInfo;
                }
            }

            // Update the link file or remove it if all links expired
            if (empty($validLinks)) {
                // All links expired, remove the file
                @unlink($linksFile);

                if ($logOutput) {
                    $this->logInfo($backupId, 'Removed links file as all links expired: ' . $linksFile);
                }
            } else {
                // Some links still valid, update the file
                file_put_contents($linksFile, json_encode($validLinks));

                if ($logOutput && !empty($expiredLinks)) {
                    $this->logInfo($backupId, 'Updated links file after removing ' . count($expiredLinks) . ' expired links');
                }
            }

            $cleanedCount += count($expiredLinks);
        }

        if ($logOutput) {
            $this->logInfo('cleanup', 'Cleaned up ' . $cleanedCount . ' expired temporary links');
        }

        return $cleanedCount;
    }

    /**
     * Sends a notification email to the user
     */
    protected function sendNotificationEmail(int $userId, string $backupId): void
    {
        // Get user email from database
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('be_users');
        $user = $queryBuilder
            ->select('email', 'username')
            ->from('be_users')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($userId,  Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        if ($user && $user['email']) {
            $userEmail = $user['email'];

            // Send email
            $mail = GeneralUtility::makeInstance(MailMessage::class);
            $mail->to($userEmail)
                ->subject(LocalizationUtility::translate('backup.email.subject', 'OpenOap') ?: 'OAP Backup completed')
                ->text(LocalizationUtility::translate('backup.email.body', 'OpenOap') ?:
                    'Your TYPO3 backup has been created and is ready for download. ' .
                    'You can download the backup in the backend module.'
                )
                ->send();
        }
    }

    /**
     * Parses the FlexForm configuration of a storage
     */
    protected function parseFlexFormConfiguration(string $flexFormXml): array
    {
        $config = [];

        // Skip if XML is empty
        if (empty($flexFormXml)) {
            return $config;
        }

        try {
            // Convert FlexForm XML to array
            $flexFormArray = GeneralUtility::xml2array($flexFormXml);

            if (!is_array($flexFormArray) || !isset($flexFormArray['data']['sDEF']['lDEF'])) {
                $this->logError('Failed to parse FlexForm configuration - invalid format');
                return $config;
            }

            // Extract values from FlexForm
            $flexData = $flexFormArray['data']['sDEF']['lDEF'];

            if (isset($flexData['basePath']['vDEF'])) {
                $config['basePath'] = $flexData['basePath']['vDEF'];
            }

            if (isset($flexData['pathType']['vDEF'])) {
                $config['pathType'] = $flexData['pathType']['vDEF'];
            }

            if (isset($flexData['baseUri']['vDEF'])) {
                $config['baseUri'] = $flexData['baseUri']['vDEF'];
            }

            if (isset($flexData['caseSensitive']['vDEF'])) {
                $config['caseSensitive'] = (bool)$flexData['caseSensitive']['vDEF'];
            }

            // Handle case when path type is relative
            if (isset($config['pathType']) && $config['pathType'] === 'relative' && isset($config['basePath'])) {
                $config['basePath'] = Environment::getPublicPath() . '/' . ltrim($config['basePath'], '/');
            }

        } catch (\Exception $e) {
            $this->logError('Error parsing FlexForm: ' . $e->getMessage());
        }

        return $config;
    }
}
