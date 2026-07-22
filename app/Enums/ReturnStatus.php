<?php

namespace App\Enums;

enum ReturnStatus: string
{
    case PENDING_RECEIPT = 'pending_receipt';
    case RECEIVED = 'received';
    case INSPECTED = 'inspected';
    case REJECTED = 'rejected';
    case CREDIT_MEMOED = 'credit_memoed';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING_RECEIPT => 'Pending Receipt',
            self::RECEIVED => 'Received',
            self::INSPECTED => 'Inspected',
            self::REJECTED => 'Rejected',
            self::CREDIT_MEMOED => 'Credit Memoed',
        };
    }
}
