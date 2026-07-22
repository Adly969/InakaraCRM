<?php

namespace App\Enums;

enum ProductType: string
{
    case FinishedGoods = 'finished_goods';
    case RawMaterial = 'raw_material';
    case SemiFinished = 'semi_finished';
    case Service = 'service';
    case Bundle = 'bundle';

    public function label(): string
    {
        return match ($this) {
            self::FinishedGoods => 'Finished Goods',
            self::RawMaterial => 'Raw Material',
            self::SemiFinished => 'Semi-Finished Goods',
            self::Service => 'Service Item',
            self::Bundle => 'Product Bundle',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FinishedGoods => 'sky',
            self::RawMaterial => 'amber',
            self::SemiFinished => 'purple',
            self::Service => 'emerald',
            self::Bundle => 'rose',
        };
    }
}
