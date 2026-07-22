<?php

namespace App\ValueObjects;

class DimensionSet
{
    public function __construct(
        public array $dimensions = []
    ) {}

    public function get(string $key): ?int
    {
        return $this->dimensions[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->dimensions);
    }

    public function toArray(): array
    {
        return $this->dimensions;
    }
}
