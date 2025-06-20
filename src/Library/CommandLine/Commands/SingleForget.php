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

abstract class SingleForget implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        $command = 'export RESTIC_PASSWORD="%s"'."\n".'%s -r %s forget %s --prune';
        if($configuration->isJsonOutput()) {
            $command .= ' --json';
        }

        return sprintf(
            $command,
            $configuration->getRepositoryPassword(),
            $configuration->getBinaryPath(),
            $configuration->getRepositoryPath(),
            $configuration->getSnapshotId()
        );
    }
}
