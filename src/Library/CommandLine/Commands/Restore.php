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

abstract class Restore implements CommandLineInterface
{
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            'restore',
            '--repo='.$configuration->getRepositoryPath(),
            '--target='.$configuration->getRestoreTarget(),
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }

        $command[] = '--';
        $command[] = $configuration->getRestoreItem();

        return $command;
    }

    /**
     * @param Configuration $configuration
     * @return array<string,string>
     */
    public static function getEnvParameters(Configuration $configuration): array
    {
        return [
            'RESTIC_PASSWORD' => $configuration->getRepositoryPassword()
        ];
    }
}
