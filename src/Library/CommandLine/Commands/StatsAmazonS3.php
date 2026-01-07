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

abstract class StatsAmazonS3 implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        $command = sprintf(
            '%s -r %s stats',
            $configuration->getBinaryPath(),
            $configuration->getRepositoryPath(),
        );

        if($configuration->isJsonOutput()) {
            $command .= ' --json';
        }

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