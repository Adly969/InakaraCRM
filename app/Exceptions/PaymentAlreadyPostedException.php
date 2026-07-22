<?php

namespace App\Exceptions;

use Exception;

class PaymentAlreadyPostedException extends Exception
{
    public function __construct(string $message = 'Payment has already been posted and cannot be modified.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
