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

    public static function optionLikeValue(string $parameter, string $value): self
    {
        return new self(sprintf(
            'Parameter %s must not start with a dash, got "%s"',
            $parameter,
            $value
        ));
    }
}
