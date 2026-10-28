<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Task;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Additional scheduler fields for the ArchiveProposalsTask: the start/end hour of
 * the time window in which the task is allowed to run.
 */
class ArchiveProposalsTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    protected const FIELD_START = 'archiveProposalsStartTimeHour';
    protected const FIELD_END = 'archiveProposalsEndTimeHour';
    protected const FIELD_CHUNK = 'archiveProposalsChunk';
    protected const LLL = 'LLL:EXT:open_oap/Resources/Private/Language/locallang.xlf:';

    /**
     * @param ArchiveProposalsTask|AbstractTask|null $task
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule): array
    {
        // Only prefill when the value is not present yet (add form / first edit render).
        // Use isset() rather than empty() so a valid "0" (midnight) is preserved.
        if (!isset($taskInfo[self::FIELD_START]) || $taskInfo[self::FIELD_START] === '') {
            $taskInfo[self::FIELD_START] = $task instanceof ArchiveProposalsTask
                ? $task->startTimeHour
                : ArchiveProposalsTask::DEFAULT_START_TIME_HOUR;
        }
        if (!isset($taskInfo[self::FIELD_END]) || $taskInfo[self::FIELD_END] === '') {
            $taskInfo[self::FIELD_END] = $task instanceof ArchiveProposalsTask
                ? $task->endTimeHour
                : ArchiveProposalsTask::DEFAULT_END_TIME_HOUR;
        }
        if (!isset($taskInfo[self::FIELD_CHUNK]) || $taskInfo[self::FIELD_CHUNK] === '') {
            $taskInfo[self::FIELD_CHUNK] = $task instanceof ArchiveProposalsTask
                ? $task->chunk
                : ArchiveProposalsTask::DEFAULT_CHUNK;
        }

        $additionalFields = [];
        foreach ([self::FIELD_START => 'startTimeHour', self::FIELD_END => 'endTimeHour'] as $fieldId => $labelKey) {
            $value = htmlspecialchars((string)$taskInfo[$fieldId]);
            $additionalFields[$fieldId] = [
                'code' => '<input type="number" min="0" max="24" class="form-control" '
                    . 'name="tx_scheduler[' . $fieldId . ']" id="' . $fieldId . '" value="' . $value . '">',
                'label' => self::LLL . 'tx_openoap.task.archiveProposalsTask.field.' . $labelKey,
                'cshKey' => '',
                'cshLabel' => '',
            ];
        }

        $chunkValue = htmlspecialchars((string)$taskInfo[self::FIELD_CHUNK]);
        $additionalFields[self::FIELD_CHUNK] = [
            'code' => '<input type="number" min="1" step="1" class="form-control" '
                . 'name="tx_scheduler[' . self::FIELD_CHUNK . ']" id="' . self::FIELD_CHUNK . '" value="' . $chunkValue . '">',
            'label' => self::LLL . 'tx_openoap.task.archiveProposalsTask.field.chunk',
            'cshKey' => '',
            'cshLabel' => '',
        ];

        return $additionalFields;
    }

    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule): bool
    {
        $start = (int)($submittedData[self::FIELD_START] ?? ArchiveProposalsTask::DEFAULT_START_TIME_HOUR);
        $end = (int)($submittedData[self::FIELD_END] ?? ArchiveProposalsTask::DEFAULT_END_TIME_HOUR);
        $chunk = (int)($submittedData[self::FIELD_CHUNK] ?? ArchiveProposalsTask::DEFAULT_CHUNK);

        // start > end is a window crossing midnight (e.g. 22 - 2) and therefore allowed;
        // only an equal start and end would describe no window at all.
        if ($start < 0 || $start > 24 || $end < 0 || $end > 24 || $start === $end || $chunk < 1) {
            $this->addMessage(
                $this->getLanguageService()->sL(self::LLL . 'tx_openoap.task.archiveProposalsTask.field.error'),
                ContextualFeedbackSeverity::ERROR
            );
            return false;
        }

        $submittedData[self::FIELD_START] = $start;
        $submittedData[self::FIELD_END] = $end;
        $submittedData[self::FIELD_CHUNK] = $chunk;
        return true;
    }

    /**
     * @param ArchiveProposalsTask|AbstractTask $task
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
    {
        if (!$task instanceof ArchiveProposalsTask) {
            return;
        }
        $task->startTimeHour = (int)$submittedData[self::FIELD_START];
        $task->endTimeHour = (int)$submittedData[self::FIELD_END];
        $task->chunk = (int)$submittedData[self::FIELD_CHUNK];
    }

    protected function getLanguageService(): \TYPO3\CMS\Core\Localization\LanguageService
    {
        return $GLOBALS['LANG'] ?? GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Localization\LanguageServiceFactory::class
        )->createFromUserPreferences($GLOBALS['BE_USER'] ?? null);
    }
}