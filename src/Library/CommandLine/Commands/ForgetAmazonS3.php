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

abstract class ForgetAmazonS3 implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        $command = sprintf(
            '%s forget -r %s --prune',
            $configuration->getBinaryPath(),
            $configuration->getAwsS3Endpoint()
        );

        if($configuration->getSnapshotIds()) {

            foreach($configuration->getSnapshotIds() as $snapshotId) {
                $command .= ' '.$snapshotId;
            }
        }

        if($configuration->isJsonOutput()) {
            $command .= ' --json';
        }

        if($configuration->getHostName()) {
            $command .= ' --host '.$configuration->getHostName();
        }

        if($configuration->getKeepDaily() && $configuration->getKeepDaily() > 0) {
            $command .= sprintf(' --keep-daily %u', $configuration->getKeepDaily());
        }

        if($configuration->getKeepWeekly() && $configuration->getKeepWeekly() > 0) {
            $command .= sprintf(' --keep-weekly %u', $configuration->getKeepWeekly());
        }

        if($configuration->getKeepMonthly() && $configuration->getKeepMonthly() > 0) {
            $command .= sprintf(' --keep-monthly %u', $configuration->getKeepMonthly());
        }

        if($configuration->getKeepYearly() && $configuration->getKeepYearly() > 0) {
            $command .= sprintf(' --keep-yearly %u', $configuration->getKeepYearly());
        }

        if($configuration->getKeepLast() && $configuration->getKeepLast() > 0) {
            $command .= sprintf(' --keep-last %u', $configuration->getKeepLast());
        }

        if($configuration->getGroupBy()) {
            $command .= sprintf(' --group-by %s', $configuration->getGroupBy());
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
            'RESTIC_PASSWORD' => $configuration->getRepositoryPassword(),
            'AWS_ACCESS_KEY_ID' => $configuration->getAwsAccessKeyId(),
            'AWS_SECRET_ACCESS_KEY' => $configuration->getAwsSecretAccessKey(),
            'AWS_DEFAULT_REGION' => $configuration->getAwsS3Region()
        ];
    }
}
