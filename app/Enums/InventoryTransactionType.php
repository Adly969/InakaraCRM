<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case Receipt = 'receipt';
    case Issue = 'issue';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Receipt => 'Goods Receipt',
            self::Issue => 'Goods Issue',
            self::AdjustmentIn => 'Stock Adjustment (Addition)',
            self::AdjustmentOut => 'Stock Adjustment (Deduction)',
        };
    }
}
