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

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\String\Exception\ExceptionInterface;
use Symfony\Component\String\Exception\InvalidArgumentException;

use MuckiRestic\Library\Configuration;
use MuckiRestic\Library\Backup;
use MuckiRestic\Library\Restore;

#[AsCommand(name: 'muwa:restic:client', description: 'A restic client for backup and restore.')]
class Console extends Commands

{
    protected function configure(): void
    {
        $this->setDefinition(
            new InputDefinition([
                new InputOption('Version', null, InputOption::VALUE_NONE, 'Version of restic'),
                new InputOption('Init', null, InputOption::VALUE_NONE, 'Initialize a new repository'),
                new InputOption('Backup', null, InputOption::VALUE_NONE, 'Create Backup into repository'),
                new InputOption('Check', null, InputOption::VALUE_NONE, 'Checkup repository'),
                new InputOption('Restore', null, InputOption::VALUE_NONE, 'Restore data from repository'),
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

                case 'Version':
                    $result = $this->getVersion($output);
                    break;
                case 'Init':
                    $result = $this->createRepository($output);
                    break;
                case 'Backup':
                    $result = $this->createBackup($output);
                    break;
                default:
                    throw new InvalidArgumentException(sprintf('Invalid option %s', $optionKey));
            }
        }

        return $result;
    }
}
