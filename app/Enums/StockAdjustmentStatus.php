<?php

namespace App\Enums;

enum StockAdjustmentStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approved => 'Approved (Posted)',
            self::Rejected => 'Rejected',
        };
    }
}
