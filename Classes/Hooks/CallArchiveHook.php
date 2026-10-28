<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Initializes the archive fields when a new Call record is created,
 * so that a freshly created (or copied) call never starts in an archived state.
 */
class CallArchiveHook
{
    protected string $table = 'tx_openoap_domain_model_call';

    public const MODE_UPDATE = 0;
    public const MODE_NEW = 1;
    public const CMD_COPY = 'copy';
    public const STATE_NEW = 'new';

    public function processDatamap_postProcessFieldArray(string $status, string $table, mixed $uid, array &$fields, DataHandler $dataHandler): void
    {
        if ($table !== $this->table or $status !== self::STATE_NEW or ($fields['l10n_parent'] ?? 0) > 0) {
            return;
        }

        $fields['archived'] = 0;
        $fields['archived_at'] = null;
    }
}