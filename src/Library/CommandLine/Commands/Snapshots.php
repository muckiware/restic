<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2026 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Library\CommandLine\Commands;

use MuckiRestic\Library\CommandLine\CommandLineInterface;
use MuckiRestic\Library\Configuration;

abstract class Snapshots implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        $command = sprintf('%s --repo %s snapshots',
            $configuration->getBinaryPath(),
            $configuration->getRepositoryPath()
        );

        if($configuration->isJsonOutput()) {
            $command .= ' --json';
        }

        if($configuration->getHostName()) {
            $command .= ' --host '.$configuration->getHostName();
        }

        return $command;
    }

    public static function getEnvParameters(Configuration $configuration): array
    {
        return [
            'RESTIC_PASSWORD' => $configuration->getRepositoryPassword()
        ];
    }
}