<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\DataHandling\Event\IsTableExcludedFromReferenceIndexEvent;

class PreventReferenceIndex
{
    protected array $excludedTables = [
        'tx_openoap_domain_model_answer',
    ];

    #[AsEventListener(identifier: 'open-oap/prevent-reference-index')]
    public function __invoke(IsTableExcludedFromReferenceIndexEvent $event): void
    {
        if (in_array($event->getTable(), $this->excludedTables, true)) {
            $event->markAsExcluded();
        }
    }
}
