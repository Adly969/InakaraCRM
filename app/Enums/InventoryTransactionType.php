<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case GoodsReceipt = 'goods_receipt';
    case GoodsIssue = 'goods_issue';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';
    case Opname = 'opname';

    public function label(): string
    {
        return match ($this) {
            self::GoodsReceipt => 'Goods Receipt (GRN)',
            self::GoodsIssue => 'Goods Issue (GIN)',
            self::Transfer => 'Stock Transfer (TRF)',
            self::Adjustment => 'Stock Adjustment (ADJ)',
            self::Opname => 'Stock Opname (OPN)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::GoodsReceipt => 'emerald',
            self::GoodsIssue => 'rose',
            self::Transfer => 'sky',
            self::Adjustment => 'amber',
            self::Opname => 'purple',
        };
    }
}
