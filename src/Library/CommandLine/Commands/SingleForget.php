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
        $command = 'export RESTIC_PASSWORD="%s"'."\n".'%s forget %s -r %s';

        //Does not work with single forget command. The output is just an empty string.
        //if($configuration->isJsonOutput()) {
        //  $command .= ' --json';
        //}

        return sprintf(
            $command,
            $configuration->getRepositoryPassword(),
            $configuration->getBinaryPath(),
            $configuration->getSnapshotId(),
            $configuration->getRepositoryPath()
        );
    }
}
