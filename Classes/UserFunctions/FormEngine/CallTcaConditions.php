<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\UserFunctions\FormEngine;

/**
 * TCA display conditions for the Call archive fields.
 */
class CallTcaConditions
{
    public const CALL_END_TIME_FIELD = 'call_end_time';
    public const ARCHIVE_FLAG = 'archived';
    public const ARCHIVE_DATE = 'archived_at';

    /**
     * True when the call has ended and has not been archived yet — i.e. it may be archived.
     */
    public function notArchivedButPossibleToArchive(array $params, $pObj): bool
    {
        $record = $params['record'] ?? [];

        if (empty($record[self::CALL_END_TIME_FIELD])) {
            return false; // no end date set
        }
        if (!empty($record[self::ARCHIVE_DATE])) {
            return false;
        }

        $callEndDateTime = new \DateTime($record[self::CALL_END_TIME_FIELD]);
        $nowDateTime = new \DateTime();

        return $callEndDateTime < $nowDateTime;
    }

    /**
     * True when archiving has completed (archived_at is set).
     */
    public function archivingIsComplete(array $params, $pObj): bool
    {
        $record = $params['record'] ?? [];

        return !empty($record[self::ARCHIVE_DATE]);
    }
}