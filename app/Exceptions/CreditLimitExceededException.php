<?php

namespace App\Exceptions;

use Exception;

class CreditLimitExceededException extends Exception
{
    public function __construct(string $message = "Transaction would exceed the customer's credit limit.", int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
