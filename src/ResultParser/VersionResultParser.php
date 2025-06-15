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
namespace MuckiRestic\ResultParser;

use MuckiRestic\Core\Defaults;

class VersionResultParser
{
    /**
     * @param string $input
     * @return string|null
     */
    public static function getVersionResultFromTextOutput(string $input): ?string
    {
        if (preg_match(Defaults::RESTIC_VERSION_TEXT_PATTERN, $input, $matches)) {
            return $matches[0];
        }

        return null;
    }

    public static function getGoVersionResultFromTextOutput(string $input): ?string
    {
        if (preg_match(Defaults::GO_VERSION_TEXT_PATTERN, $input, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
