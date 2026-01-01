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

abstract class UnlockAmazonS3 implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        return sprintf(
            'export RESTIC_PASSWORD="%s"'."\n".'export AWS_ACCESS_KEY_ID="%s"'."\n".'export AWS_SECRET_ACCESS_KEY="%s"'."\n".'export AWS_DEFAULT_REGION="%s"'."\n".'export RESTIC_COMPRESSION="auto"'."\n".'%s unlock --repo %s',
            $configuration->getRepositoryPassword(),
            $configuration->getAwsAccessKeyId(),
            $configuration->getAwsSecretAccessKey(),
            $configuration->getAwsS3Region(),
            $configuration->getBinaryPath(),
            $configuration->getAwsS3Endpoint()
        );
    }
}