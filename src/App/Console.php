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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use MuckiRestic\Library\Configuration;
use MuckiRestic\Library\Backup;
use MuckiRestic\Library\Restore;

class Console extends Command

{
    protected function configure(): void
    {
        $this->setName('muwa:restic:client');
        $this->setDescription('A restic client for backup and restore.');
        $this->setDefinition(
            [
                new InputOption('resticVersion', null, InputOption::VALUE_NONE, 'Version of restic')
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $backupClient = Backup::create();
        $resticVersion = $backupClient->getResticVersion();
        $output->writeln(sprintf('Binary restic version: %s', $resticVersion));
        return Command::SUCCESS;
    }
}
