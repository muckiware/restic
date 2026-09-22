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

abstract class BackupAmazonS3 implements CommandLineInterface
{
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            '--repo='.$configuration->getAwsS3Endpoint(),
            'backup',
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }
        if ($configuration->isCompress()) {
            $command[] = '--compression=auto';
        }
        if ($configuration->getHostName()) {
            $command[] = '--host='.$configuration->getHostName();
        }
        foreach ($configuration->getTags() as $tag) {
            $command[] = '--tag='.$tag;
        }

        $command[] = '--';
        $command[] = (string) $configuration->getBackupPath();

        return $command;
    }

    /**
     * @param Configuration $configuration
     * @return array<string,string|null>
     */
    public static function getEnvParameters(Configuration $configuration): array
    {
        return [
            'RESTIC_PASSWORD' => $configuration->getRepositoryPassword(),
            'AWS_ACCESS_KEY_ID' => $configuration->getAwsAccessKeyId(),
            'AWS_SECRET_ACCESS_KEY' => $configuration->getAwsSecretAccessKey(),
            'AWS_DEFAULT_REGION' => $configuration->getAwsS3Region()
        ];
    }
}
