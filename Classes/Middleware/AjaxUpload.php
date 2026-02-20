<?php

namespace OpenOAP\OpenOap\Middleware;

use OpenOAP\OpenOap\Controller\ProposalController;
use OpenOAP\OpenOap\Domain\Repository\ProposalRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class AjaxUpload implements MiddlewareInterface
{
    protected const VERSION = '1.0.0';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        //        DebuggerUtility::var_dump($response);
        //        DebuggerUtility::var_dump($request);
        if (!isset($request->getQueryParams()['oap-ajax-task'])) {
            return $response;
        }

        $parameter = $request->getQueryParams();
        $result = [];
        switch ($parameter['oap-ajax-task']) {
            case 'info':
                $result = ['status' => 200, 'version' => self::VERSION, 'task' => $parameter['oap-ajax-task']];
                break;
            case 'upload':
                $proposalUid = $parameter['proposal'];

                /** @var ConfigurationManager $configurationManager */
                $configurationManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConfigurationManager::class);
                $settings = $configurationManager->getConfiguration(
                    \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    'openOap',
                    'dashboard'
                );

                /** @var ProposalRepository $proposalRepository */
                $proposalRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ProposalRepository::class);

                $proposal = $proposalRepository->findByUid($proposalUid);
                $applicantUid = $proposal->getApplicant()->getUid();

                $uploadFolderId = ($settings['uploadFolder'] == '') ? ProposalController::$defaultUploadFolder : $settings['uploadFolder'];

                $uploadFolder = ProposalController::provideUploadFolder($uploadFolderId, $applicantUid, $proposalUid);
                $data = [];
                $data['files'] = [];
                $data['ids'] = [];
                for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    $file = [
                        'name' => $_FILES['files']['name'][$i],
                        'type' => $_FILES['files']['type'][$i],
                        'tmp_name' => $_FILES['files']['tmp_name'][$i],
                        'error' => $_FILES['files']['error'][$i],
                        'size' => $_FILES['files']['size'][$i],
                        ];
                    $uploadedFile = $uploadFolder->addUploadedFile($file, DuplicationBehavior::RENAME);
                    $data['files'][] = ['uid' => $uploadedFile->getUid(), 'name' => $uploadedFile->getName(), 'url' => $uploadedFile->getPublicUrl(), 'size' => $uploadedFile->getSize()];
                    $data['ids'][] = $uploadedFile->getUid();
                }

                $result = ['status' => 200, 'version' => self::VERSION, 'task' => $parameter['oap-ajax-task'], 'data' => $data];
                break;
        }

        return new JsonResponse($result);
    }
}
