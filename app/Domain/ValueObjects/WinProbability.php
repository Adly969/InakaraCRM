<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class WinProbability
{
    /**
     * WinProbability constructor.
     */
    public function __construct(public readonly int $value)
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException('Win probability must be between 0 and 100.');
        }
    }

    /**
     * Determine if another WinProbability is identical.
     */
    public function equals(WinProbability $other): bool
    {
        return $this->value === $other->value;
    }
}
