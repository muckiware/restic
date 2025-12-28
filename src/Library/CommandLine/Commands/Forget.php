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

abstract class Forget implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        $command = sprintf(
            'export RESTIC_PASSWORD="%s"'."\n".'%s forget -r %s --prune',
            $configuration->getRepositoryPassword(),
            $configuration->getBinaryPath(),
            $configuration->getRepositoryPath()
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
}
