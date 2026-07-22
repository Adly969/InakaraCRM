<?php

namespace App\Enums;

enum WarehouseTaskType: string
{
    case PutAway = 'put_away';
    case Picking = 'picking';
    case Packing = 'packing';
    case Shipping = 'shipping';
    case Receiving = 'receiving';
    case InternalTransfer = 'internal_transfer';
    case CycleCount = 'cycle_count';

    public function label(): string
    {
        return match ($this) {
            self::PutAway => 'Put Away Task',
            self::Picking => 'Picking Task',
            self::Packing => 'Packing Task',
            self::Shipping => 'Shipping Task',
            self::Receiving => 'Receiving Task',
            self::InternalTransfer => 'Internal Transfer Task',
            self::CycleCount => 'Cycle Count Task',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PutAway => 'sky',
            self::Picking => 'emerald',
            self::Packing => 'amber',
            self::Shipping => 'purple',
            self::Receiving => 'blue',
            self::InternalTransfer => 'indigo',
            self::CycleCount => 'rose',
        };
    }
}
