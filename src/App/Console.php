<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2025 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\App;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\String\Exception\ExceptionInterface;
use Symfony\Component\String\Exception\InvalidArgumentException;

use MuckiRestic\Core\Commands as ResticCommands;

#[AsCommand(name: 'muwa:restic:client', description: 'A restic client for backup and restore.')]
class Console extends Commands

{
    protected function configure(): void
    {
        $this->setDefinition(
            new InputDefinition([
                new InputOption(ResticCommands::VERSION->value, null, InputOption::VALUE_NONE, 'Version of restic'),
                new InputOption(ResticCommands::INIT->value, null, InputOption::VALUE_NONE, 'Initialize a new repository'),
                new InputOption(ResticCommands::BACKUP->value, null, InputOption::VALUE_NONE, 'Create Backup into repository'),
                new InputOption(ResticCommands::CHECK->value, null, InputOption::VALUE_NONE, 'Checkup repository'),
                new InputOption(ResticCommands::RESTORE->value, null, InputOption::VALUE_NONE, 'Restore data from repository'),
                new InputOption(ResticCommands::SNAPSHOTS->value, null, InputOption::VALUE_NONE, 'Get list of snapshots from repository'),
                new InputArgument('repositoryPath', InputArgument::REQUIRED, 'Path to the repository'),
                new InputArgument('repositoryPassword', InputArgument::REQUIRED, 'Password for the repository'),
                new InputArgument('backupPath', InputArgument::OPTIONAL, 'Path to the backup directory'),
                new InputOption('remove', '-r', InputOption::VALUE_NONE, 'Flag for to remove a single snapshot by snapshot id'),
                new InputOption('snapshotId', null,InputOption::VALUE_OPTIONAL, 'Snapshot id of a repository item')
            ])
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->selectCommandInput($input, $output);
    }

    protected function selectCommandInput(InputInterface $input, OutputInterface $output): int
    {
        $result = Command::INVALID;
        foreach ($input->getOptions() as $optionKey => $optionValue) {

            if(!$optionValue) {
                continue;
            }
            switch ($optionKey) {

                case ResticCommands::VERSION->value:
                    $result = $this->getVersion($output);
                    break;
                case ResticCommands::INIT->value:
                    $result = $this->createRepository($input, $output);
                    break;
                case ResticCommands::BACKUP->value:
                    $result = $this->createBackup($input, $output);
                    break;
                case ResticCommands::CHECK->value:
                    $result = $this->checkBackup($input, $output);
                    break;
                case ResticCommands::SNAPSHOTS->value:
                    $result = $this->snapshots($input, $output);
                    break;
            }
        }

        return $result;
    }
}
