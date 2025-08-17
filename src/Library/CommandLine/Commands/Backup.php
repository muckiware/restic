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
namespace MuckiRestic\Library\CommandLine\Commands;

use MuckiRestic\Library\CommandLine\CommandLineInterface;
use MuckiRestic\Library\Configuration;

abstract class Backup implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        $command = sprintf('export RESTIC_PASSWORD="%s"'."\n".'%s -r %s backup %s',
            $configuration->getRepositoryPassword(),
            $configuration->getBinaryPath(),
            $configuration->getRepositoryPath(),
            $configuration->getBackupPath()
        );

        if($configuration->isJsonOutput()) {
            $command .= ' --json';
        }
        if($configuration->isCompress()) {
            $command .= ' --compression auto';
        }

        if($configuration->getHostName()) {
            $command .= ' --host %s';
        }

        return $command;
    }
}
