<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Middleware;

use OpenOAP\OpenOap\Service\EditLockService;
use OpenOAP\OpenOap\Service\FrontendAccessControlService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditLockAjax implements MiddlewareInterface
{
    public function __construct(
        private readonly EditLockService $editLockService,
        private readonly FrontendAccessControlService $frontendAccessControlService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $task = $request->getQueryParams()['oap-ajax-task'] ?? null;
        if ($task !== 'editlock-renew' && $task !== 'editlock-release') {
            return $handler->handle($request);
        }

        if ($task === 'editlock-renew') {
            return $this->handleRenew($request);
        }

        return $this->handleRelease($request);
    }

    private function handleRenew(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $proposalUid = (int)($params['proposal'] ?? 0);
        $token = trim((string)($params['editLockToken'] ?? ''));

        $feUserUid = (int)($this->frontendAccessControlService->getFrontendUserId() ?? 0);
        if ($feUserUid === 0 || $proposalUid === 0 || $token === '') {
            return new JsonResponse(['sessionValid' => false, 'lockValid' => false]);
        }

        $sessionStarted = $this->editLockService->getSessionStartedTimestamp($proposalUid, $token);
        if ($sessionStarted === null) {
            return new JsonResponse(['sessionValid' => true, 'lockValid' => false]);
        }

        if (time() - $sessionStarted > EditLockService::HARD_LIMIT_SECONDS) {
            return new JsonResponse(['sessionValid' => false, 'lockValid' => false]);
        }

        $lockValid = $this->editLockService->renew($proposalUid, $token);

        return new JsonResponse(['sessionValid' => true, 'lockValid' => $lockValid]);
    }

    private function handleRelease(ServerRequestInterface $request): ResponseInterface
    {
        $body = $this->parseBody($request);
        $proposalUid = (int)($body['proposal'] ?? 0);
        $token = trim((string)($body['editLockToken'] ?? ''));

        if ($proposalUid > 0 && $token !== '') {
            // Late-beacon protection: If the server has just accessed the lock via renew()/tryAcquire()
            // (typically during the Save→Redirect→editAction cycle), the browser’s pagehide
            // beacon must not delete the newly reacquired entry.
            if (!$this->editLockService->isRecentlyTouched($proposalUid, $token)) {
                $this->editLockService->release($proposalUid, $token);
            }
        }

        return GeneralUtility::makeInstance(Response::class)->withStatus(204);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseBody(ServerRequestInterface $request): array
    {
        $parsed = $request->getParsedBody();
        if (is_array($parsed)) {
            return $parsed;
        }

        $raw = (string)$request->getBody();
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        parse_str($raw, $form);

        return $form;
    }
}
