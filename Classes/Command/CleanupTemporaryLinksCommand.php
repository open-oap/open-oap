<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Command;

use OpenOAP\OpenOap\Service\BackupService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

/**
 * Symfony Console command to clean up expired temporary download links
 */
#[AsCommand(
    name: 'open-oap:backup:cleanup-links',
    description: 'Removes expired temporary download links for backup files.'
)]
class CleanupTemporaryLinksCommand extends Command
{
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly BackupService $backupService,
    )
    {
        parent::__construct();
    }

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this->setDescription('Removes expired temporary download links for backup files');
    }

    /**
     * Execute the command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('<info>Starting cleanup of expired temporary download links...</info>');

            // Call the cleanup method with logging enabled
            $cleanedCount = $this->backupService->cleanupTemporaryLinks(true);

            // Log the result
            $this->logger->info('Cleaned up ' . $cleanedCount . ' expired temporary download links');
            $output->writeln('<info>Cleaned up ' . $cleanedCount . ' expired temporary download links</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Log any exceptions
            $this->logger->error('Error cleaning up temporary download links: ' . $e->getMessage());
            $output->writeln('<error>Error cleaning up temporary download links: ' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}
