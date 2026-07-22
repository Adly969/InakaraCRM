<?php

namespace App\Exceptions;

use Exception;

class InvalidPaymentTransitionException extends Exception
{
    public function __construct(string $message = 'Invalid payment status transition requested.', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
