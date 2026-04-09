<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Command;

use OpenOAP\OpenOap\Service\BackupService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TYPO3 Console command to create backups
 */
#[AsCommand(
    name: 'open-oap:backup:create',
    description: 'Creates a complete backup of the TYPO3 installation.'
)]
class CreateBackupCommand extends Command
{
    public function __construct(
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
        $this->setDescription('Creates a complete backup of TYPO3')
            ->addArgument(
                'backupId',
                InputArgument::REQUIRED,
                'Unique identifier for the backup'
            )
            ->addArgument(
                'userId',
                InputArgument::REQUIRED,
                'User ID who triggered the backup'
            );
    }

    /**
     * Execute the command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $backupId = $input->getArgument('backupId');
        $userId = (int)$input->getArgument('userId');

        try {
            $output->writeln('<info>Starting backup process...</info>');

            $this->backupService->createBackup($backupId, $userId);

            $output->writeln('<info>Backup completed successfully!</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error creating backup: ' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}
