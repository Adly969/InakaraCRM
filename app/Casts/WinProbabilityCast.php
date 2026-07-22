<?php

namespace App\Casts;

use App\Domain\ValueObjects\WinProbability;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class WinProbabilityCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): WinProbability
    {
        return new WinProbability((int) $value);
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value instanceof WinProbability) {
            return $value->value;
        }

        return (int) $value;
    }
}
