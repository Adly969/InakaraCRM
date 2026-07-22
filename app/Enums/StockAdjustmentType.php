<?php

namespace App\Enums;

enum StockAdjustmentType: string
{
    case Addition = 'addition';
    case Deduction = 'deduction';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Addition => 'Stock Addition (+)',
            self::Deduction => 'Stock Deduction (-)',
        };
    }
}
