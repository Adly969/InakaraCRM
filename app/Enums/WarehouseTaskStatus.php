<?php

namespace App\Enums;

enum WarehouseTaskStatus: string
{
    case Draft = 'draft';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Assigned => 'amber',
            self::InProgress => 'sky',
            self::Completed => 'emerald',
            self::Cancelled => 'rose',
        };
    }
}
