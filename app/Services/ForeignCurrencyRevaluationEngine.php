<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\LedgerSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForeignCurrencyRevaluationEngine
{
    public function __construct(
        protected NumberRangeEngine $numberRangeEngine,
        protected PostingEngineService $postingEngine
    ) {}

    /**
     * Executes foreign currency revaluation at month-end.
     *
     * @param  string  $currencyCode  e.g. USD
     * @return string|null Generated adjusting journal number
     */
    public function revalue(
        int $companyId,
        int $branchId,
        int $ledgerId,
        string $currencyCode,
        float $newSpotRate,
        int $userId
    ): ?string {
        $now = Carbon::now();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('m');

        // Find all asset/liability accounts with foreign currency postings
        $snapshots = LedgerSnapshot::where([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'ledger_id' => $ledgerId,
            'fiscal_year' => $year,
            'fiscal_month' => $month,
        ])->get();

        $adjustingLines = [];
        $unrealizedGainAccountId = $this->getOrCreateCOA($companyId, $branchId, 'UNREALIZED_GAIN', 'Unrealized Exchange Gain', 'revenue', 'credit');
        $unrealizedLossAccountId = $this->getOrCreateCOA($companyId, $branchId, 'UNREALIZED_LOSS', 'Unrealized Exchange Loss', 'expense', 'debit');

        foreach ($snapshots as $snap) {
            $account = ChartOfAccount::find($snap->account_id);
            if (! in_array($account->account_type, ['asset', 'liability'])) {
                continue;
            }

            // Sum original base amount and foreign amount from posted journal lines
            $originalBaseAmount = 0.00;
            $foreignAmount = 0.00;

            $lines = JournalLine::whereHas('journalEntry', function ($query) use ($companyId, $branchId, $ledgerId) {
                $query->where([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'ledger_id' => $ledgerId,
                    'status' => 'POSTED',
                ]);
            })
                ->where('account_id', $snap->account_id)
                ->where('currency_code', $currencyCode)
                ->get();

            foreach ($lines as $line) {
                $originalBaseAmount += ((float) $line->base_debit_amount - (float) $line->base_credit_amount);
                $foreignAmount += ((float) $line->debit_amount - (float) $line->credit_amount);
            }

            if ($foreignAmount == 0.00) {
                continue;
            }

            // Revalued base amount based on new spot rate
            $revaluedBaseAmount = round($foreignAmount * $newSpotRate, 2);
            $variance = round($revaluedBaseAmount - $originalBaseAmount, 2);

            if ($variance !== 0.00) {
                // If it is an Asset: gain is positive variance, loss is negative
                // If it is a Liability: loss is positive variance, gain is negative
                $isGain = ($account->account_type === 'asset' && $variance > 0) ||
                          ($account->account_type === 'liability' && $variance < 0);

                $adjustingLines[] = [
                    'account_id' => $snap->account_id,
                    'variance' => abs($variance),
                    'is_gain' => $isGain,
                ];
            }
        }

        if (empty($adjustingLines)) {
            return null;
        }

        return DB::transaction(function () use ($companyId, $branchId, $ledgerId, $adjustingLines, $unrealizedGainAccountId, $unrealizedLossAccountId, $userId, $now) {
            $journalNumber = $this->numberRangeEngine->generate($companyId, $branchId, 'JOURNAL');

            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'ledger_id' => $ledgerId,
                'journal_number' => $journalNumber,
                'journal_type' => 'ADJUSTING',
                'transaction_date' => $now,
                'status' => 'DRAFT',
                'created_by' => $userId,
            ]);

            foreach ($adjustingLines as $adj) {
                if ($adj['is_gain']) {
                    // Debit the Asset/Liability, Credit Unrealized Gain
                    $journal->lines()->create([
                        'account_id' => $adj['account_id'],
                        'debit_amount' => $adj['variance'],
                        'credit_amount' => 0.00,
                        'currency_code' => 'IDR',
                        'exchange_rate' => 1.000000,
                        'base_debit_amount' => $adj['variance'],
                        'base_credit_amount' => 0.00,
                    ]);

                    $journal->lines()->create([
                        'account_id' => $unrealizedGainAccountId,
                        'debit_amount' => 0.00,
                        'credit_amount' => $adj['variance'],
                        'currency_code' => 'IDR',
                        'exchange_rate' => 1.000000,
                        'base_debit_amount' => 0.00,
                        'base_credit_amount' => $adj['variance'],
                    ]);
                } else {
                    // Debit Unrealized Loss, Credit the Asset/Liability
                    $journal->lines()->create([
                        'account_id' => $unrealizedLossAccountId,
                        'debit_amount' => $adj['variance'],
                        'credit_amount' => 0.00,
                        'currency_code' => 'IDR',
                        'exchange_rate' => 1.000000,
                        'base_debit_amount' => $adj['variance'],
                        'base_credit_amount' => 0.00,
                    ]);

                    $journal->lines()->create([
                        'account_id' => $adj['account_id'],
                        'debit_amount' => 0.00,
                        'credit_amount' => $adj['variance'],
                        'currency_code' => 'IDR',
                        'exchange_rate' => 1.000000,
                        'base_debit_amount' => 0.00,
                        'base_credit_amount' => $adj['variance'],
                    ]);
                }
            }

            // Post revaluation adjust entry
            $this->postingEngine->post($journal, $userId);

            return $journalNumber;
        });
    }

    protected function getOrCreateCOA(
        int $companyId,
        int $branchId,
        string $code,
        string $name,
        string $type,
        string $normalBalance
    ): int {
        $account = ChartOfAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('account_code', $code)
            ->first();

        if (! $account) {
            $account = ChartOfAccount::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'account_code' => $code,
                'name' => $name,
                'account_type' => $type,
                'normal_balance' => $normalBalance,
                'is_control_account' => false,
                'is_posting_allowed' => true,
            ]);
        }

        return $account->id;
    }
}
