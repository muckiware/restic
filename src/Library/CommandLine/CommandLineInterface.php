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
namespace MuckiRestic\Library\CommandLine;

use MuckiRestic\Library\Configuration;

interface CommandLineInterface
{
    public static function getCommandLine(Configuration $configuration): string;
}
