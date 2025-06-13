<?php

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
}
