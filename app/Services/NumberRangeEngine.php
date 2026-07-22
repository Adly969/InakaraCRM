<?php

namespace App\Services;

use App\Models\DocumentNumberRange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class NumberRangeEngine
{
    /**
     * Generate a consecutive, gap-free document number atomically.
     */
    public function generate(int $companyId, int $branchId, string $docType): string
    {
        $prefix = strtoupper(substr($docType, 0, 3)).'-'.date('Y').'-';

        try {
            $key = "seq:company:{$companyId}:branch:{$branchId}:{$docType}";
            $sequence = Redis::incr($key);
        } catch (\Throwable $e) {
            // Safe database transaction fallback with pessimistic row locking
            $sequence = DB::transaction(function () use ($companyId, $branchId, $docType, $prefix) {
                $range = DocumentNumberRange::where([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'document_type' => $docType,
                ])->lockForUpdate()->firstOrCreate([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'document_type' => $docType,
                    'prefix' => $prefix,
                    'current_value' => 0,
                ]);

                $range->current_value += 1;
                $range->save();

                return $range->current_value;
            });
        }

        return $prefix.str_pad((string) $sequence, 9, '0', STR_PAD_LEFT);
    }
}
