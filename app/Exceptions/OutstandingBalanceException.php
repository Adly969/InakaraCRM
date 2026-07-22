<?php

namespace App\Exceptions;

use Exception;

class OutstandingBalanceException extends Exception
{
    public function __construct(string $message = 'Outstanding balance check failed.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
