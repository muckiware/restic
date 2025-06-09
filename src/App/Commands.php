<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use MuckiRestic\Library\Configuration;
use MuckiRestic\Library\Backup;
use MuckiRestic\Library\Manage;
use MuckiRestic\Library\Restore;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Test\TestData;
use MuckiRestic\Test\TestHelper;

class Commands extends Command
{
    protected const BACKUP_PATH = './test';
    protected const REPOSITORY_PATH = './testRep';
    protected const REPOSITORY_PASSWORD = '1234';
    public function getVersion(OutputInterface $output): int
    {
        $backupClient = Backup::create();
        $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386');

        $output->writeln(sprintf('Binary restic version: %s', $backupClient->getResticVersion()->getOutput()));
        return Command::SUCCESS;
    }

    public function createRepository(InputInterface $input, OutputInterface $output): int
    {
        try {

            $backupClient = Backup::create();
            $this->prepareExecutionCommand($backupClient, $input);

            $result = $backupClient->createRepository(true)->getOutput();
            if(is_string($result)) {
                $output->writeln(sprintf('Create repository: %s', $result));
            }

        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function createBackup(InputInterface $input, OutputInterface $output): int
    {
        TestHelper::createTextFiles(self::BACKUP_PATH, TestData::BACKUP_TEST_FILES);
        try {

            $backupClient = Backup::create();
            $this->prepareExecutionCommand($backupClient, $input);
            $backupClient->setBackupPath($input->getArgument('backupPath') ?? self::BACKUP_PATH);

            $result = $backupClient->createBackup()->getOutput();
            if(is_string($result)) {
                $output->writeln(sprintf('Create backup: %s', $result));
            }

        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function checkBackup(InputInterface $input, OutputInterface $output): int
    {
        try {

            $backupClient = Backup::create();
            $this->prepareExecutionCommand($backupClient, $input);

            $result = $backupClient->checkBackup()->getOutput();
            if(is_string($result)) {
                $output->writeln(sprintf('Create backup: %s', $result));
            }

        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function checkSnapshots(InputInterface $input, OutputInterface $output): int
    {
        try {

            $manageClient = Manage::create();
            $this->prepareExecutionCommand($manageClient, $input);

            $result = $manageClient->getSnapshots()->getOutput();
            if(is_string($result)) {
                $output->writeln(sprintf('Snapshots: %s', $result));
            }

        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function prepareExecutionCommand(Backup|Manage $backupClient, InputInterface $input): Backup|Manage
    {
        $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386');
        $backupClient->setJsonOutput(false);

        if($input->getArgument('repositoryPath')) {
            $backupClient->setRepositoryPath($input->getArgument('repositoryPath'));
        } else {
            $backupClient->setRepositoryPath(self::REPOSITORY_PATH);
        }

        if($input->getArgument('repositoryPassword')) {
            $backupClient->setRepositoryPassword($input->getArgument('repositoryPassword'));
        } else {
            $backupClient->setRepositoryPassword(self::REPOSITORY_PASSWORD);
        }

        return $backupClient;
    }
}
