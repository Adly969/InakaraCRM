<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class ForecastCategory
{
    public const PIPELINE = 'pipeline';

    public const BEST_CASE = 'best_case';

    public const COMMIT = 'commit';

    public const CLOSED = 'closed';

    public const OMITTED = 'omitted';

    public const VALID_CATEGORIES = [
        self::PIPELINE,
        self::BEST_CASE,
        self::COMMIT,
        self::CLOSED,
        self::OMITTED,
    ];

    /**
     * ForecastCategory constructor.
     */
    public function __construct(public readonly string $value)
    {
        $normalized = strtolower($value);
        if (! in_array($normalized, self::VALID_CATEGORIES)) {
            throw new InvalidArgumentException('Invalid forecast category: '.$value);
        }
    }

    /**
     * Determine if another ForecastCategory is identical.
     */
    public function equals(ForecastCategory $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }
}
