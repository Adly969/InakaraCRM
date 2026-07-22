<?php

namespace App\Exceptions;

use Exception;

class AllocationExceededException extends Exception
{
    public function __construct(string $message = 'Allocation amount exceeds the available payment or invoice balance.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
