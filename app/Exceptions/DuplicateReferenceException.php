<?php

namespace App\Exceptions;

use Exception;

class DuplicateReferenceException extends Exception
{
    public function __construct(string $message = 'Duplicate payment reference number detected.', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
