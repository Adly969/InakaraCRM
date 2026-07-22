<?php

namespace App\Exceptions;

use Exception;

class InvoiceLockedException extends Exception
{
    public function __construct(string $message = 'Invoice outstanding balance is locked for processing by another transaction.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
