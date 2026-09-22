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
namespace MuckiRestic\Library\CommandLine;

use MuckiRestic\Library\Configuration;

interface CommandLineInterface
{
    /**
     * @param Configuration $configuration
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array;

    /**
     * @param Configuration $configuration
     * @return array<string,string|null>
     */
    public static function getEnvParameters(Configuration $configuration): array;
}
