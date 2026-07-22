<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class LeadScore
{
    /**
     * LeadScore constructor.
     */
    public function __construct(public readonly int $score)
    {
        if ($score < 0 || $score > 100) {
            throw new InvalidArgumentException('Lead score must be between 0 and 100.');
        }
    }

    /**
     * Get temperature ranking based on score value.
     */
    public function temperature(): string
    {
        if ($this->score >= 75) {
            return 'hot';
        }
        if ($this->score >= 35) {
            return 'warm';
        }

        return 'cold';
    }

    /**
     * Determine if another LeadScore is identical.
     */
    public function equals(LeadScore $other): bool
    {
        return $this->score === $other->score;
    }
}
