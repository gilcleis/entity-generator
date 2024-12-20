<?php

namespace Gilcleis\Support\Exceptions;

use Exception;

class InvalidJSONFormatException extends Exception
{
    public function __construct(string $response)
    {
        parent::__construct("Response contains invalid JSON. Received response: '{$response}'");
    }
}
