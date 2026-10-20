<?php

namespace OpenOAP\OpenOap\Middleware;

use OpenOAP\OpenOap\Controller\ProposalController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Validation\ResultException;

class AjaxUpload implements MiddlewareInterface
{
    protected const VERSION = '1.0.0';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!isset($request->getQueryParams()['oap-ajax-task'])) {
            return $handler->handle($request);
        }

        $parameter = $request->getQueryParams();
        $result = [];

        switch ($parameter['oap-ajax-task']) {
            case 'info':
                $result = ['status' => 200, 'version' => self::VERSION, 'task' => $parameter['oap-ajax-task']];
                break;

            case 'upload':
                $proposalUid = (int)($parameter['proposal'] ?? 0);
                if ($proposalUid === 0) {
                    return new JsonResponse(['status' => 400, 'error' => 'Missing proposal parameter']);
                }

                // Get applicantUid via DBAL — no Extbase/TypoScript needed
                $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable('tx_openoap_domain_model_proposal');
                $row = $connection->select(
                    ['applicant'],
                    'tx_openoap_domain_model_proposal',
                    ['uid' => $proposalUid]
                )->fetchAssociative();

                if (!$row) {
                    return new JsonResponse(['status' => 404, 'error' => 'Proposal not found']);
                }
                $applicantUid = (int)$row['applicant'];

                // Read uploadFolder from site configuration (available before page-resolver)
                $site = $request->getAttribute('site');
                $siteConfig = $site ? $site->getConfiguration() : [];
                $uploadFolderId = $siteConfig['open_oap']['uploadFolder'] ?? ProposalController::$defaultUploadFolder;
                if ($uploadFolderId === '') {
                    $uploadFolderId = ProposalController::$defaultUploadFolder;
                }

                $uploadFolder = ProposalController::provideUploadFolder($uploadFolderId, $applicantUid, $proposalUid);

                $parsedBody   = $request->getParsedBody() ?? [];
                $allowedTypes = trim((string)($parsedBody['allowedTypes'] ?? ''));
                $maxSizeBytes = (int)($parsedBody['maxSize'] ?? 0);

                $data = ['files' => [], 'ids' => [], 'errors' => []];
                $uploadedFiles = $request->getUploadedFiles()['files'] ?? [];
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                $fileNameValidator = GeneralUtility::makeInstance(FileNameValidator::class);
                foreach ($uploadedFiles as $file) {
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    if (!$fileNameValidator->isValid($file->getClientFilename())) {
                        continue;
                    }
                    if ($allowedTypes !== '' && !$this->isFileAllowed($file, $allowedTypes)) {
                        $data['errors'][] = ['file' => $file->getClientFilename(), 'reason' => 'type'];
                        continue;
                    }
                    if ($maxSizeBytes > 0 && $file->getSize() > $maxSizeBytes) {
                        $data['errors'][] = ['file' => $file->getClientFilename(), 'reason' => 'size'];
                        continue;
                    }
                    try {
                        $uploadedFile = $uploadFolder->addUploadedFile($file, DuplicationBehavior::RENAME);
                    } catch (ResultException $exception) {
                        // Same rejection as in ProposalController::processUploadedFiles(). Without
                        // the guard the exception turns the JSON response into a 500, which the
                        // upload script ignores, so the upload fails without any sign in the
                        // browser. The file is left out of 'files' and listed in 'errors' with the
                        // reason 'rejected', next to the existing 'type' and 'size' reasons.
                        $this->logRejectedUpload($file, $exception);
                        $data['errors'][] = ['file' => $file->getClientFilename(), 'reason' => 'rejected'];
                        continue;
                    }
                    $data['files'][] = [
                        'uid' => $uploadedFile->getUid(),
                        'name' => $uploadedFile->getName(),
                        'url' => $uploadedFile->getPublicUrl(),
                        'size' => $uploadedFile->getSize(),
                    ];
                    $data['ids'][] = $uploadedFile->getUid();
                }

                $result = ['status' => 200, 'version' => self::VERSION, 'task' => $parameter['oap-ajax-task'], 'data' => $data];
                break;
        }

        return new JsonResponse($result);
    }

    protected function isFileAllowed(UploadedFileInterface $file, string $allowedFormats): bool
    {
        $allowed = array_map('trim', explode(',', $allowedFormats));

        $allowedExtensions = [];
        $allowedMimeTypes  = [];

        foreach ($allowed as $entry) {
            if ($entry === '') {
                continue;
            }
            if (str_starts_with($entry, '.')) {
                $allowedExtensions[] = strtolower(ltrim($entry, '.'));
            } else {
                $allowedMimeTypes[] = strtolower($entry);
            }
        }

        $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        if (in_array($ext, $allowedExtensions, true)) {
            return true;
        }

        if (in_array(strtolower($file->getClientMediaType()), $allowedMimeTypes, true)) {
            return true;
        }

        return false;
    }

    /**
     * Write the details of a file rejected by the file abstraction layer to the log.
     *
     * The exception message itself only says "Resource consistency check failed"; the
     * detected mime-type, the file extension and the expected mime-types are carried in
     * the ResultMessage objects and would be lost otherwise.
     */
    protected function logRejectedUpload(UploadedFileInterface $file, ResultException $exception): void
    {
        $details = [];
        foreach ($exception->messages as $message) {
            $details[] = $message->message;
        }

        GeneralUtility::makeInstance(LogManager::class)->getLogger(static::class)->warning(
            'Upload rejected by the file abstraction layer: ' . implode(' ', $details),
            [
                'fileName' => $file->getClientFilename(),
                'clientMimeType' => $file->getClientMediaType(),
                'fileSize' => $file->getSize(),
            ]
        );
    }
}
