<?php

namespace App\Enums;

enum ProductionOrderStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case InProduction = 'in_production';
    case QualityControl = 'quality_control';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::InProduction => 'In Production',
            self::QualityControl => 'Quality Control',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Get all allowed transitions for status.
     *
     * @return array<string, array<string>>
     */
    public static function transitions(): array
    {
        return [
            self::Draft->value => [self::Scheduled->value, self::Cancelled->value],
            self::Scheduled->value => [self::InProduction->value, self::Cancelled->value],
            self::InProduction->value => [self::QualityControl->value, self::Cancelled->value],
            self::QualityControl->value => [self::Completed->value, self::InProduction->value, self::Cancelled->value],
            self::Completed->value => [],
            self::Cancelled->value => [],
        ];
    }

    /**
     * Check if transition is allowed.
     */
    public function canTransitionTo(self $to): bool
    {
        $allowed = self::transitions()[$this->value] ?? [];

        return in_array($to->value, $allowed, true);
    }
}
