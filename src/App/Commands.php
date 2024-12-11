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

    public function createRepository(OutputInterface $output): int
    {
        try {

            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386');
            $backupClient->setRepositoryPassword(self::REPOSITORY_PASSWORD);
            $backupClient->setRepositoryPath(self::REPOSITORY_PATH);

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

    public function createBackup(OutputInterface $output): int
    {
        TestHelper::createTextFiles(self::BACKUP_PATH, TestData::BACKUP_TEST_FILES);
        try {

            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386');
            $backupClient->setRepositoryPassword(self::REPOSITORY_PASSWORD);
            $backupClient->setRepositoryPath(self::REPOSITORY_PATH);
            $backupClient->setBackupPath(self::BACKUP_PATH);
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

    public function checkBackup(OutputInterface $output): int
    {
        try {

            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386');
            $backupClient->setRepositoryPassword(self::REPOSITORY_PASSWORD);
            $backupClient->setRepositoryPath(self::REPOSITORY_PATH);
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
}
