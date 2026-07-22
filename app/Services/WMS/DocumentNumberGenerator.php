<?php

namespace App\Services\WMS;

use App\Models\DocumentNumberSequence;
use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    /**
     * Generate an atomic, concurrency-safe document sequence number.
     * Format: {PREFIX}-{YEAR}-{SEQUENCE:06} (e.g. GRN-2026-000001)
     */
    public function generate(string $prefix, string $tenantId, ?int $companyId = null): string
    {
        $year = (int) date('Y');

        return DB::transaction(function () use ($prefix, $tenantId, $companyId, $year) {
            $seq = DocumentNumberSequence::where('tenant_id', $tenantId)
                ->where('prefix', $prefix)
                ->where('fiscal_year', $year)
                ->lockForUpdate()
                ->first();

            if (! $seq) {
                $seq = DocumentNumberSequence::create([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'prefix' => $prefix,
                    'fiscal_year' => $year,
                    'current_sequence' => 1,
                ]);
                $next = 1;
            } else {
                $seq->increment('current_sequence');
                $next = $seq->current_sequence;
            }

            return sprintf('%s-%d-%06d', strtoupper($prefix), $year, $next);
        });
    }
}
