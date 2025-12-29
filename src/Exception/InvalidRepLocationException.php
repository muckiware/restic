<?php

namespace MuckiRestic\Exception;

use Exception;

class InvalidRepLocationException extends Exception
{
    public function __construct(
        string $message = "Invalid repository location exception",
        int $code = 0,
        ?Exception $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
