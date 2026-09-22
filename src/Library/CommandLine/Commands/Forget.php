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

abstract class Forget implements CommandLineInterface
{
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            'forget',
            '--repo='.$configuration->getRepositoryPath(),
            '--prune',
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }
        if ($configuration->getHostName()) {
            $command[] = '--host='.$configuration->getHostName();
        }
        if ($configuration->getKeepDaily() > 0) {
            $command[] = '--keep-daily='.$configuration->getKeepDaily();
        }
        if ($configuration->getKeepWeekly() > 0) {
            $command[] = '--keep-weekly='.$configuration->getKeepWeekly();
        }
        if ($configuration->getKeepMonthly() > 0) {
            $command[] = '--keep-monthly='.$configuration->getKeepMonthly();
        }
        if ($configuration->getKeepYearly() > 0) {
            $command[] = '--keep-yearly='.$configuration->getKeepYearly();
        }
        if ($configuration->getKeepLast() > 0) {
            $command[] = '--keep-last='.$configuration->getKeepLast();
        }
        if ($configuration->getGroupBy()) {
            $command[] = '--group-by='.$configuration->getGroupBy();
        }
        foreach ($configuration->getTags() as $tag) {
            $command[] = '--tag='.$tag;
        }

        if ($configuration->getSnapshotIds()) {
            $command[] = '--';
            foreach ($configuration->getSnapshotIds() as $snapshotId) {
                $command[] = $snapshotId;
            }
        }

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
