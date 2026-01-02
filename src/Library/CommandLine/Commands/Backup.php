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

abstract class Backup implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        $command = sprintf('%s -r %s backup %s',
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
            $command .= ' --host '.$configuration->getHostName();
        }

        if($configuration->getTags()) {

            foreach($configuration->getTags() as $tag) {
                $command .= ' --tag '.$tag;
            }
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
