<?php

namespace App\Services;

use App\Models\LedgerSnapshot;
use Illuminate\Validation\ValidationException;

class ValidationPipeline
{
    /**
     * Enforce validation checks on the target journal draft before posting.
     *
     * @throws ValidationException
     */
    public function validate(int $companyId, int $branchId, int $ledgerId, int $year, int $month): void
    {
        // 1. Check if period is frozen
        $snapshot = LedgerSnapshot::withoutGlobalScopes()
            ->where([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'ledger_id' => $ledgerId,
                'fiscal_year' => $year,
                'fiscal_month' => $month,
            ])->first();

        if ($snapshot && $snapshot->is_frozen) {
            throw ValidationException::withMessages([
                'fiscal_period' => ["Target fiscal period {$year}-{$month} is closed/frozen."],
            ]);
        }
    }
}
