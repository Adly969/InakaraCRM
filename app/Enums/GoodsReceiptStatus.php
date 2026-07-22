<?php

namespace App\Enums;

enum GoodsReceiptStatus: string
{
    case Draft = 'draft';
    case Received = 'received';
    case Cancelled = 'cancelled';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Received => 'Received (Posted)',
            self::Cancelled => 'Cancelled',
        };
    }
}
