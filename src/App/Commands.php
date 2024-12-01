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

class Commands extends Command
{
    public function getVersion(OutputInterface $output): int
    {
        $backupClient = Backup::create();
        $backupClient->setBinaryPath('/var/www/html/bin/restic_0.17.3_linux_386');

        $output->writeln(sprintf('Binary restic version: %s', $backupClient->getResticVersion()));
        return Command::SUCCESS;
    }

    public function createRepository(OutputInterface $output): int
    {
        try {

            $backupClient = Backup::create();
            $backupClient->setBinaryPath('/var/www/html/bin/restic_0.17.3_linux_386');
            $backupClient->setRepositoryPassword('1234');
            $backupClient->setRepositoryPath('./testRep');
            $output->writeln(sprintf('Create repository: %s', $backupClient->createRepository()));

        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function createBackup(OutputInterface $output): int
    {
        try {

            $backupClient = Backup::create();
            $backupClient->setBinaryPath('/var/www/html/bin/restic_0.17.3_linux_386');
            $backupClient->setRepositoryPassword('1234');
            $backupClient->setRepositoryPath('./testRep');
            $backupClient->setBackupPath('/var/www/html/test');
            $output->writeln(sprintf('Create backup: %s', $backupClient->createBackup(true)));

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
            $backupClient->setBinaryPath('/var/www/html/bin/restic_0.17.3_linux_386');
            $backupClient->setRepositoryPassword('1234');
            $backupClient->setRepositoryPath('./testRep');
            $output->writeln(sprintf('Check backup: %s', $backupClient->checkBackup(true)));

        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
