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
    public static function getCommandLine(Configuration $configuration): string
    {
        $command = '%s restore %s -r %s  --target %s';
        if($configuration->isJsonOutput()) {
            $command .= ' --json';
        }

        return sprintf(
            $command,
            $configuration->getBinaryPath(),
            $configuration->getRestoreItem(),
            $configuration->getRepositoryPath(),
            $configuration->getRestoreTarget()
        );
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
