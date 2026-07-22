<?php

namespace App\Services;

use App\Models\PostingRule;
use App\Models\PostingRuleVersion;
use Illuminate\Support\Facades\Cache;

class PostingRuleResolver
{
    /**
     * Resolves the debit and credit account mappings dynamically.
     * Uses Redis caching with auto-invalidation on rule updates.
     *
     * @param  string  $eventType  e.g. SalesInvoiceApproved
     * @param  array  $context  Matching context parameters
     *
     * @throws \RuntimeException
     */
    public function resolve(string $eventType, array $context): PostingRuleVersion
    {
        $companyId = $context['company_id'];
        $branchId = $context['branch_id'] ?? null;
        $now = now();

        $cacheKey = "posting_rules:{$companyId}:{$branchId}:{$eventType}";

        // Wrap dynamic lookup in Redis Cache with 1 hour TTL
        return Cache::remember($cacheKey, 3600, function () use ($companyId, $branchId, $eventType, $now) {
            $query = PostingRuleVersion::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('event_type', $eventType)
                ->where('status', 'PUBLISHED')
                ->where('effective_from', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('effective_until')
                        ->orWhere('effective_until', '>=', $now);
                });

            if ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->orWhere('branch_id', 0);
                });
            }

            // Order by priority descending to find the highest-priority override
            $rules = $query->orderBy('priority', 'desc')->get();

            if ($rules->isEmpty()) {
                $fallback = PostingRule::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where('event_type', $eventType)
                    ->first();

                if ($fallback) {
                    return new PostingRuleVersion([
                        'debit_account_id' => $fallback->debit_account_id,
                        'credit_account_id' => $fallback->credit_account_id,
                        'priority' => 0,
                    ]);
                }

                throw new \RuntimeException("No active posting rules configured for event: {$eventType}");
            }

            // Conflict detection: if top 2 rules have the identical priority, throw exception
            if ($rules->count() > 1 && $rules[0]->priority === $rules[1]->priority) {
                throw new \RuntimeException("Ambiguous rule priority conflict detected for event: {$eventType}");
            }

            return $rules->first();
        });
    }

    /**
     * Clear rule cache.
     */
    public function invalidateCache(int $companyId, ?int $branchId, string $eventType): void
    {
        Cache::forget("posting_rules:{$companyId}:{$branchId}:{$eventType}");
    }
}
