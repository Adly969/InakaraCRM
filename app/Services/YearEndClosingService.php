<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\LedgerSnapshot;
use Illuminate\Support\Facades\DB;

class YearEndClosingService
{
    public function __construct(
        protected NumberRangeEngine $numberRangeEngine,
        protected PostingEngineService $postingEngine
    ) {}

    /**
     * Closes the active fiscal year by zeroing out temporary accounts and rolling balance forwards.
     *
     * @return string Generated year-end closing journal number
     */
    public function closeYear(int $companyId, int $branchId, int $ledgerId, int $year, int $userId): string
    {
        $retainedEarningsId = $this->getOrCreateRetainedEarningsAccount($companyId, $branchId);

        // Fetch balances of all Revenue & Expense accounts for the target year
        $snapshots = LedgerSnapshot::where([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'ledger_id' => $ledgerId,
            'fiscal_year' => $year,
        ])->get();

        $closingLines = [];
        $netIncome = 0.00;

        foreach ($snapshots as $snap) {
            $account = ChartOfAccount::find($snap->account_id);
            if (! in_array($account->account_type, ['revenue', 'expense', 'cogs', 'other_income', 'other_expense'])) {
                continue;
            }

            $balance = (float) $snap->closing_balance;
            if ($balance == 0.00) {
                continue;
            }

            // Zero-out calculation:
            // Revenue (normally Credit): we Debit it to make it 0.
            // Expense (normally Debit): we Credit it to make it 0.
            if ($account->normal_balance === 'credit') {
                $closingLines[] = [
                    'account_id' => $snap->account_id,
                    'debit_amount' => $balance,
                    'credit_amount' => 0.00,
                ];
                $netIncome += $balance;
            } else {
                $closingLines[] = [
                    'account_id' => $snap->account_id,
                    'debit_amount' => 0.00,
                    'credit_amount' => $balance,
                ];
                $netIncome -= $balance;
            }
        }

        if (empty($closingLines)) {
            throw new \RuntimeException("No active revenues or expenses to close for year {$year}.");
        }

        // Retained Earnings offset entry
        if ($netIncome !== 0.00) {
            $closingLines[] = [
                'account_id' => $retainedEarningsId,
                // If net income is positive (profit), credit Retained Earnings
                // If net income is negative (loss), debit Retained Earnings
                'debit_amount' => $netIncome < 0 ? abs($netIncome) : 0.00,
                'credit_amount' => $netIncome > 0 ? $netIncome : 0.00,
            ];
        }

        return DB::transaction(function () use ($companyId, $branchId, $ledgerId, $closingLines, $userId) {
            $journalNumber = $this->numberRangeEngine->generate($companyId, $branchId, 'JOURNAL');

            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'ledger_id' => $ledgerId,
                'journal_number' => $journalNumber,
                'journal_type' => 'CLOSING',
                'transaction_date' => now(),
                'status' => 'DRAFT',
                'created_by' => $userId,
            ]);

            foreach ($closingLines as $line) {
                $journal->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'currency_code' => 'IDR',
                    'exchange_rate' => 1.000000,
                    'base_debit_amount' => $line['debit_amount'],
                    'base_credit_amount' => $line['credit_amount'],
                ]);
            }

            // Post closing journal
            $this->postingEngine->post($journal, $userId);

            return $journalNumber;
        });
    }

    protected function getOrCreateRetainedEarningsAccount(int $companyId, int $branchId): int
    {
        $account = ChartOfAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('account_code', 'RETAINED_EARNINGS')
            ->first();

        if (! $account) {
            $account = ChartOfAccount::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'account_code' => 'RETAINED_EARNINGS',
                'name' => 'Retained Earnings',
                'account_type' => 'equity',
                'normal_balance' => 'credit',
                'is_control_account' => false,
                'is_posting_allowed' => true,
            ]);
        }

        return $account->id;
    }
}
