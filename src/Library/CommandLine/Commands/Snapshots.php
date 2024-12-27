<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Library\CommandLine\Commands;

use MuckiRestic\Library\CommandLine\CommandLineInterface;
use MuckiRestic\Library\Configuration;

abstract class Snapshots implements CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string
    {
        $format = 'export RESTIC_PASSWORD="%s"'."\n".'%s --repo %s snapshots';
        if($configuration->isJsonOutput()) {
            $format .= ' --json';
        }
        return sprintf(
            $format,
            $configuration->getRepositoryPassword(),
            $configuration->getBinaryPath(),
            $configuration->getRepositoryPath()
        );
    }
}