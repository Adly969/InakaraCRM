<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class IdempotencyService
{
    /**
     * Executes the closure with idempotency key checking.
     * Caches successful responses to avoid duplicate execution.
     */
    public function handle(string $key, Closure $callback)
    {
        $cacheKey = "idempotency_key_{$key}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Lock to prevent concurrent requests with the exact same key
        $lock = Cache::lock("lock_{$cacheKey}", 10);

        return $lock->block(5, function () use ($cacheKey, $callback) {
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = $callback();

            // Cache the result for 24 hours
            Cache::put($cacheKey, $response, now()->addDay());

            return $response;
        });
    }
}
