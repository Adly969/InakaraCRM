<?php

namespace App\Casts;

use App\Domain\ValueObjects\LeadScore;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class LeadScoreCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): LeadScore
    {
        return new LeadScore((int) ($value ?? 0));
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value instanceof LeadScore) {
            return $value->score;
        }

        return (int) $value;
    }
}
