<?php

namespace App\Casts;

use App\Domain\ValueObjects\ForecastCategory;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ForecastCategoryCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ForecastCategory
    {
        return new ForecastCategory((string) ($value ?? ForecastCategory::PIPELINE));
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof ForecastCategory) {
            return $value->value;
        }

        return (string) $value;
    }
}
