<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Service;

final readonly class LockResult
{
    public function __construct(
        public bool $acquired,
        public string $token,
    ) {
    }
}