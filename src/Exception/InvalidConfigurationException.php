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
namespace MuckiRestic\Exception;

use Exception;

class InvalidConfigurationException extends Exception
{
    public function __construct(
        string $message = "Invalid configuration exception provided",
        int $code = 0,
        ?Exception $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public static function binaryNotFound(string $binaryPath): self
    {
        return new self(sprintf(
            'The restic binary "%s" was not found or is not executable. '
            .'Set an existing path with setBinaryPath(), or install restic so the name resolves through PATH.',
            $binaryPath
        ));
    }

    public static function unreadableVersion(string $binaryPath, string $processOutput): self
    {
        return new self(sprintf(
            'Could not read a restic version from "%s". It ran, so the path is correct, but its output '
            .'does not look like restic. Output was: %s',
            $binaryPath,
            trim($processOutput)
        ));
    }

    public static function nonPositiveTimeout(string $parameter, float $seconds): self
    {
        return new self(sprintf(
            'Parameter %s must be greater than zero, or null for no limit, got %s',
            $parameter,
            var_export($seconds, true)
        ));
    }

    public static function optionLikeValue(string $parameter, string $value): self
    {
        return new self(sprintf(
            'Parameter %s must not start with a dash, got "%s"',
            $parameter,
            $value
        ));
    }
}
