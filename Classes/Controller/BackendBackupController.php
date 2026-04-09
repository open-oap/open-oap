<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Service\BackupService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * BackendBackupController
 */
#[AsController]
class BackendBackupController extends OapBackendController
{
    /**
     * BackupService
     *
     * @var BackupService
     */
    protected $backupService;

    /**
     * Injects the BackupService
     */
    public function injectBackupService(BackupService $backupService): void
    {
        $this->backupService = $backupService;
    }

    /**
     * Displays the main view of the backup module
     */
    public function indexAction(): ResponseInterface
    {
        // Fetch all available backup statuses
        $backupDir = Environment::getVarPath() . '/backup';
        $backupJobs = [];

        if (is_dir($backupDir)) {
            $statusFiles = glob($backupDir . '/status_*.json');

            foreach ($statusFiles as $statusFile) {
                $backupStatus = json_decode(file_get_contents($statusFile), true);

                // Only consider active backups (not downloaded or deleted)
                if (isset($backupStatus['status']) &&
                    !in_array($backupStatus['status'], ['downloaded', 'deleted', 'canceled'])) {
                    $backupJobs[] = $backupStatus;
                }
            }

            // Sort by started timestamp (newest first)
            usort($backupJobs, function($a, $b) {
                return ($b['started'] ?? 0) - ($a['started'] ?? 0);
            });
        }

        $this->moduleTemplate->assign('backupJobs', $backupJobs);

        return $this->moduleTemplate->renderResponse('BackendBackup/Index');
    }

    /**
     * Starts the backup process in the background
     */
    public function createBackupAction(): ResponseInterface
    {
        // Create a unique ID for the backup job
        $backupId = uniqid('');
        $userId = (int)$GLOBALS['BE_USER']->user['uid'];

        // Initialize status file
        $backupDir = Environment::getVarPath() . '/backup';
        if (!is_dir($backupDir)) {
            GeneralUtility::mkdir_deep($backupDir);
        }

        $statusFile = $backupDir . '/status_' . $backupId . '.json';
        $initialStatus = [
            'id' => $backupId,
            'userId' => $userId,
            'started' => time(),
            'status' => 'running',
            'progress' => 0,
            'createdBy' => $GLOBALS['BE_USER']->user['username'],
            'createdById' => $userId
        ];
        file_put_contents($statusFile, json_encode($initialStatus));

        // Schedule the backup task via the TYPO3 Scheduler
        $this->executeBackupCommandAsync($backupId, $userId);

        $this->addFlashMessage(
            LocalizationUtility::translate('backup.started', 'OpenOap') ?: 'The backup process has been started and will run in the background.',
            '',
            ContextualFeedbackSeverity::INFO
        );

        return $this->redirect('index');
    }

    /**
     * Executes the backup command asynchronously in the background
     */
    protected function executeBackupCommandAsync(string $backupId, int $userId): void
    {
        // Create the status file first
        $backupDir = Environment::getVarPath() . '/backup';
        if (!is_dir($backupDir)) {
            GeneralUtility::mkdir_deep($backupDir);
        }

        $statusFile = $backupDir . '/status_' . $backupId . '.json';
        $status = [
            'id' => $backupId,
            'userId' => $userId,
            'status' => 'running',
            'startedAt' => time(),
            'progress' => 0,
            'createdBy' => $GLOBALS['BE_USER']->user['username'],
            'createdById' => $userId
        ];
        file_put_contents($statusFile, json_encode($status));

        // Log the start of the backup process
        $this->backupService->logInfo($backupId, 'Starting backup process for backup ID: ' . $backupId);

        // Get PHP binary path from extension configuration or use default
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extConfig = $extensionConfiguration->get('open_oap');
        $phpPath = !empty($extConfig['phpBinaryPath']) ? $extConfig['phpBinaryPath'] : PHP_BINARY;

        $this->backupService->logInfo($backupId, 'Using PHP binary: ' . $phpPath);

        // Determine the correct path to TYPO3 console script
        $typo3ConsolePath = '';
        $possiblePaths = [
            // Composer installation
            Environment::getProjectPath() . '/vendor/bin/typo3',
            // Legacy installation - typo3cms in document root
            Environment::getPublicPath() . '/../typo3cms',
            // Alternative paths
            Environment::getPublicPath() . '/../typo3',
            Environment::getPublicPath() . '/typo3',
            // Path relative to current script
            dirname(__DIR__, 4) . '/vendor/bin/typo3'
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $typo3ConsolePath = $path;
                $this->backupService->logInfo($backupId, 'Found TYPO3 console at: ' . $path);
                break;
            }
        }

        if (empty($typo3ConsolePath)) {
            $this->backupService->logError('Could not find TYPO3 console script. Backup cannot be started.');
            throw new \RuntimeException('Could not find TYPO3 console script. Please make sure the TYPO3 CLI tool is available.', 9763748469);
        }

        // Different command syntax for Windows vs Unix-like systems
        if (Environment::isWindows()) {
            // Windows version: use WMIC to create a new process that's properly detached
            $commandToRun = sprintf(
                '%s %s open-oap:backup:create %s %s > %s 2>&1',
                escapeshellarg($phpPath),
                escapeshellarg($typo3ConsolePath),
                escapeshellarg($backupId),
                escapeshellarg((string)$userId),
                escapeshellarg($backupDir . '/backup_' . $backupId . '.log')
            );

            // Escape for WMIC
            $commandToRun = str_replace('"', '\\\"', $commandToRun);

            // Windows approach: Use WMIC (more reliable on server systems)
            $command = sprintf(
                'wmic process call create "cmd.exe /c timeout /t 5 /nobreak > nul && %s"',
                $commandToRun
            );
        } else {
            // Unix/Linux version using nohup for proper detachment
            $command = sprintf(
                'nohup sh -c "sleep 5 && %s %s open-oap:backup:create %s %s > %s 2>&1" > /dev/null 2>&1 &',
                escapeshellarg($phpPath),
                escapeshellarg($typo3ConsolePath),
                escapeshellarg($backupId),
                escapeshellarg((string)$userId),
                escapeshellarg($backupDir . '/backup_' . $backupId . '.log')
            );
        }

        // Execute the command asynchronously
        $this->backupService->logInfo($backupId, 'Executing command: ' . $command);
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->backupService->logError('Failed to execute command. Return code: ' . $returnCode);
            throw new \RuntimeException('Failed to execute backup command', 6793886945);
        }
    }

    /**
     * Checks the status of a running backup
     */
    public function checkStatusAction(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
    {
        // Get the backup ID from POST or GET params
        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $backupId = is_array($parsedBody) && isset($parsedBody['backupId']) ? $parsedBody['backupId'] : '';
        } else {
            $queryParams = $request->getQueryParams();
            $backupId = $queryParams['backupId'] ?? '';
        }

        if (empty($backupId)) {
            return new JsonResponse([
                'status' => 'not_running',
                'progress' => 0,
                'error' => 'No backup ID provided'
            ]);
        }

        $status = $this->backupService->getBackupStatus($backupId);
        return new JsonResponse($status);
    }

    /**
     * Downloads a completed backup using symlink method for large files
     */
    public function downloadAction(): ResponseInterface
    {
        $backupId = $this->request->hasArgument('backupId') ?
            $this->request->getArgument('backupId') : '';

        if (empty($backupId)) {
            $this->addFlashMessage(
                LocalizationUtility::translate('backup.download.error', 'OpenOap') ?: 'No backup ID provided.',
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('index');
        }

        $status = $this->backupService->getBackupStatus($backupId);

        if ($status['status'] === 'completed' && isset($status['archiveFile']) && file_exists($status['archiveFile'])) {
            // Create a temporary download link
            $downloadUrl = $this->createTemporaryDownloadLink($backupId, $status['archiveFile']);

            if ($downloadUrl) {
                // Update the status to show that the file has been prepared for download
                $backupDir = Environment::getVarPath() . '/backup';
                $statusFile = $backupDir . '/status_' . $backupId . '.json';

                if (file_exists($statusFile)) {
                    $status = json_decode(file_get_contents($statusFile), true);
                    $status['downloadPrepared'] = time();
                    $status['downloadUrl'] = $downloadUrl;
                    file_put_contents($statusFile, json_encode($status));
                }

                // Redirect to the direct download URL
                return $this->redirectToUri($downloadUrl);
            }
        }

        $this->addFlashMessage(
            LocalizationUtility::translate('backup.download.error', 'OpenOap') ?: 'The backup is not available for download.',
            '',
            ContextualFeedbackSeverity::ERROR
        );

        return $this->redirect('index');
    }

    /**
     * Creates a temporary download link for a backup file
     *
     * @param string $backupId The backup ID
     * @param string $archiveFilePath The path to the archive file
     * @return string|null The download URL or null if creation failed
     */
    protected function createTemporaryDownloadLink(string $backupId, string $archiveFilePath): ?string
    {
        // Create a public directory for temporary downloads if it doesn't exist
        $publicDir = Environment::getPublicPath() . '/typo3temp/assets/downloads/';
        if (!is_dir($publicDir)) {
            GeneralUtility::mkdir_deep($publicDir);
        }

        // Create a unique token for this download
        $uniqueToken = md5($backupId . uniqid('', true));
        $fileName = basename($archiveFilePath, '.zip');
        $linkPath = $publicDir . $fileName . '_' . $uniqueToken . '.zip';
        $linkUrl = '/typo3temp/assets/downloads/' . $fileName . '_' . $uniqueToken . '.zip';

        // Create a symbolic link (or copy file)
        $success = @symlink($archiveFilePath, $linkPath);

        if ($success) {
            $this->backupService->logInfo($backupId, 'Created symlink for download at: ' . $linkPath);
        } else {
            // create a copy instead of a symlink
            $success = copy($archiveFilePath, $linkPath);
            $this->backupService->logInfo($backupId, 'Created file copy for download at: ' . $linkPath);
        }

        if (!$success) {
            $this->backupService->logError('Failed to create temporary download link for ' . $backupId);
            return null;
        }

        // Store information about this temporary link
        $backupDir = Environment::getVarPath() . '/backup';
        $linkInfo = [
            'original' => $archiveFilePath,
            'link' => $linkPath,
            'created' => time(),
            'expires' => time() + 4 * 3600, // 4-hour expiration
            'backupId' => $backupId,
            'token' => $uniqueToken
        ];

        $linksFile = $backupDir . '/links_' . $backupId . '.json';
        $links = [];

        // If the file already exists, load existing links
        if (file_exists($linksFile)) {
            $links = json_decode(file_get_contents($linksFile), true);
            if (!is_array($links)) {
                $links = [];
            }
        }

        // Add the new link to the array
        $links[] = $linkInfo;

        // Save the updated links array
        file_put_contents($linksFile, json_encode($links));

        // Return the download URL
        return $linkUrl;
    }

    /**
     * Cancels a running backup
     */
    public function cancelBackupAction(): ResponseInterface
    {
        $backupId = $this->request->hasArgument('backupId') ?
            $this->request->getArgument('backupId') : '';

        if (empty($backupId)) {
            $this->addFlashMessage(
                LocalizationUtility::translate('backup.cancel.error', 'OpenOap') ?: 'No backup ID provided.',
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('index');
        }

        $status = $this->backupService->getBackupStatus($backupId);

        if ($status['status'] === 'running') {
            // Cancel the backup process
            $this->cancelRunningBackupProcess($backupId);

            // Update status file
            $backupDir = Environment::getVarPath() . '/backup';
            $statusFile = $backupDir . '/status_' . $backupId . '.json';

            if (file_exists($statusFile)) {
                $status = json_decode(file_get_contents($statusFile), true);
                $status['status'] = 'canceled';
                $status['progress'] = 0;
                $status['finished'] = time();
                file_put_contents($statusFile, json_encode($status));
            }

            $this->addFlashMessage(
                LocalizationUtility::translate('backup.canceled', 'OpenOap') ?: 'The backup process has been canceled.',
                '',
                ContextualFeedbackSeverity::INFO
            );
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate('backup.cancel.notrunning', 'OpenOap') ?: 'The backup is not running.',
                '',
                ContextualFeedbackSeverity::WARNING
            );
        }

        return $this->redirect('index');
    }

    /**
     * Deletes a completed backup
     */
    public function deleteBackupAction(): ResponseInterface
    {
        $backupId = $this->request->hasArgument('backupId') ?
            $this->request->getArgument('backupId') : '';

        if (empty($backupId)) {
            $this->addFlashMessage(
                LocalizationUtility::translate('backup.delete.error', 'OpenOap') ?: 'No backup ID provided.',
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('index');
        }

        $status = $this->backupService->getBackupStatus($backupId);

        if ($status['status'] === 'completed') {
            // Delete the archive file if it exists
            if (isset($status['archiveFile']) && file_exists($status['archiveFile'])) {
                @unlink($status['archiveFile']);
            }

            // Also clean up temporary links
            $this->backupService->cleanupTemporaryLinks();

            // Update status file
            $backupDir = Environment::getVarPath() . '/backup';
            $statusFile = $backupDir . '/status_' . $backupId . '.json';

            if (file_exists($statusFile)) {
                $status = json_decode(file_get_contents($statusFile), true);
                $status['status'] = 'deleted';
                $status['deleted'] = time();
                $status['deletedBy'] = $GLOBALS['BE_USER']->user['username'];
                $status['deletedById'] = $GLOBALS['BE_USER']->user['uid'];
                file_put_contents($statusFile, json_encode($status));
            }

            $this->addFlashMessage(
                LocalizationUtility::translate('backup.deleted', 'OpenOap') ?: 'The backup has been deleted.',
                '',
                ContextualFeedbackSeverity::INFO
            );
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate('backup.delete.notcompleted', 'OpenOap') ?: 'Only completed backups can be deleted.',
                '',
                ContextualFeedbackSeverity::WARNING
            );
        }

        return $this->redirect('index');
    }

    /**
     * Cancels a running backup process
     */
    protected function cancelRunningBackupProcess(string $backupId): void
    {
        // Get the backup status file
        $backupDir = Environment::getVarPath() . '/backup';
        $statusFile = $backupDir . '/status_' . $backupId . '.json';

        if (file_exists($statusFile)) {
            $status = json_decode(file_get_contents($statusFile), true);

            // Try to find the process by searching for backup command processes
            $commandPatterns = [
                'open-oap:backup:create ' . preg_quote($backupId)
            ];
            $psCommand = '';

            if (Environment::isWindows()) {
                // Windows command to find and kill the process
                $this->backupService->logInfo($backupId, 'Windows platform detected - attempting to kill process.');
                foreach ($commandPatterns as $pattern) {
                    // Find tasks containing the backup command pattern
                    $findCmd = sprintf(
                        'wmic process where "commandline like \'%%%s%%\'" get processid',
                        $pattern
                    );
                    $this->backupService->logInfo($backupId, 'Finding process with command: ' . $findCmd);
                    exec($findCmd, $pidOutput);

                    // Remove header and empty lines
                    $pids = array_filter(array_map('trim', $pidOutput), function($line) {
                        return !empty($line) && $line !== 'ProcessId';
                    });

                    // Kill each found process
                    foreach ($pids as $pid) {
                        if (is_numeric($pid)) {
                            $killCmd = 'taskkill /F /PID ' . $pid;
                            $this->backupService->logInfo($backupId, 'Killing process with command: ' . $killCmd);
                            exec($killCmd);
                        }
                    }
                }
            } else {
                // Unix command to find and kill the process for each pattern
                foreach ($commandPatterns as $pattern) {
                    $psCommand = sprintf(
                        'ps aux | grep "%s" | grep -v grep | awk \'{print $2}\' | xargs kill -9 2>/dev/null || true',
                        $pattern
                    );
                    $this->backupService->logInfo($backupId, 'Executing command to kill backup process: ' . $psCommand);
                    exec($psCommand);
                }
            }

            // Update status file to indicate cancellation
            $status['status'] = 'canceled';
            $status['cancelledAt'] = time();
            $status['cancelledBy'] = $GLOBALS['BE_USER']->user['uid'] ?? 0;
            $status['cancelledByUsername'] = $GLOBALS['BE_USER']->user['username'] ?? 'unknown';
            file_put_contents($statusFile, json_encode($status));

            $this->backupService->logInfo($backupId, 'Backup process for ID ' . $backupId . ' has been marked as canceled.');

            // Clean up any temporary files
            $tempDir = $backupDir . '/temp_' . $backupId;
            if (is_dir($tempDir)) {
                GeneralUtility::rmdir($tempDir, true);
                $this->backupService->logInfo($backupId, 'Removed temporary directory: ' . $tempDir);
            }
        } else {
            $this->backupService->logError('No status file found for backup ID: ' . $backupId);
        }
    }

    /**
     * Sends an email notification when the backup is completed
     */
    protected function sendNotificationEmail(): void
    {
        $user = $GLOBALS['BE_USER']->user;

        if ($user['email']) {
            $mail = GeneralUtility::makeInstance(MailMessage::class);
            $mail->to($user['email'])
                ->subject(LocalizationUtility::translate('backup.email.subject', 'OpenOap') ?: 'OAP Backup completed')
                ->text(LocalizationUtility::translate('backup.email.body', 'OpenOap') ?: 'Your TYPO3 backup has been created and is ready for download.')
                ->send();
        }
    }
}
