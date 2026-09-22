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

abstract class SingleForgetAmazonS3 implements CommandLineInterface
{
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        //Does not work with single forget command. The output is just an empty string.
        //if($configuration->isJsonOutput()) {
        //  $command[] = '--json';
        //}

        return [
            $configuration->getBinaryPath(),
            'forget',
            '--repo='.$configuration->getAwsS3Endpoint(),
            '--',
            (string) $configuration->getSnapshotId(),
        ];
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
