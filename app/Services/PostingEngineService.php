<?php

namespace App\Services;

use App\Events\JournalPosted;
use App\Events\JournalPostedWithSuspense;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\LedgerSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostingEngineService
{
    public function __construct(
        protected DocumentSplittingEngine $splittingEngine,
        protected DigitalSignatureService $signatureService
    ) {}

    /**
     * Posts a journal entry to the general ledger.
     *
     * @throws ValidationException
     */
    public function post(JournalEntry $journal, int $userId): void
    {
        if ($journal->status === 'POSTED') {
            throw new \InvalidArgumentException('Journal entry is already posted.');
        }

        $companyId = $journal->company_id;
        $branchId = $journal->branch_id;
        $suspenseAccountId = $this->getOrCreateSuspenseAccount($companyId, $branchId);

        // 1. Control Accounts Protection
        if ($journal->journal_type === 'MANUAL') {
            foreach ($journal->lines as $line) {
                if ($line->account->is_control_account) {
                    throw ValidationException::withMessages([
                        'journal_lines' => ["Manual posting to Control Account '{$line->account->name}' is forbidden."],
                    ]);
                }
            }
        }

        // 2. Double Entry Validation & Minor Rounding Adjustments
        $totalDebits = round($journal->lines->sum('base_debit_amount'), 2);
        $totalCredits = round($journal->lines->sum('base_credit_amount'), 2);
        $delta = round($totalDebits - $totalCredits, 2);
        $postedWithSuspense = false;

        if ($delta !== 0.00) {
            if (abs($delta) <= 0.05) {
                // Auto-route delta to suspense account
                $suspenseLine = new JournalLine([
                    'account_id' => $suspenseAccountId,
                    'debit_amount' => $delta < 0 ? abs($delta) : 0.00,
                    'credit_amount' => $delta > 0 ? $delta : 0.00,
                    'currency_code' => 'IDR',
                    'exchange_rate' => 1.000000,
                    'base_debit_amount' => $delta < 0 ? abs($delta) : 0.00,
                    'base_credit_amount' => $delta > 0 ? $delta : 0.00,
                ]);
                $journal->lines()->save($suspenseLine);
                $postedWithSuspense = true;
            } else {
                throw ValidationException::withMessages([
                    'journal_lines' => ["Debits and Credits must balance. Mismatch: {$delta} IDR"],
                ]);
            }
        }

        // 3. Document Splitting Engine (balances dimensions segment by segment)
        $this->splittingEngine->split($journal, $suspenseAccountId);

        // Parse date details for monthly ledger snapshots
        $date = $journal->transaction_date;
        $year = (int) $date->format('Y');
        $month = (int) $date->format('m');

        // Group lines by account to prevent lock contention
        $groupedLines = [];
        foreach ($journal->lines as $line) {
            $groupedLines[$line->account_id][] = $line;
        }

        DB::transaction(function () use ($journal, $groupedLines, $companyId, $branchId, $year, $month, $userId) {
            // 4. Grouped Row-Level Pessimistic Locking
            foreach ($groupedLines as $accountId => $lines) {
                $snapshot = LedgerSnapshot::lockForUpdate()->firstOrCreate([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'ledger_id' => $journal->ledger_id,
                    'account_id' => $accountId,
                    'fiscal_year' => $year,
                    'fiscal_month' => $month,
                ], [
                    'opening_balance' => 0.00,
                    'total_debits' => 0.00,
                    'total_credits' => 0.00,
                    'closing_balance' => 0.00,
                    'is_frozen' => false,
                ]);

                if ($snapshot->is_frozen) {
                    throw new \RuntimeException('Cannot post to a frozen fiscal snapshot.');
                }

                $debitSum = 0.00;
                $creditSum = 0.00;
                foreach ($lines as $line) {
                    $debitSum += (float) $line->base_debit_amount;
                    $creditSum += (float) $line->base_credit_amount;
                }

                $snapshot->total_debits += $debitSum;
                $snapshot->total_credits += $creditSum;

                // Enforce normal balance formula
                $account = ChartOfAccount::find($accountId);
                if ($account->normal_balance === 'debit') {
                    $snapshot->closing_balance = $snapshot->opening_balance + $snapshot->total_debits - $snapshot->total_credits;
                } else {
                    $snapshot->closing_balance = $snapshot->opening_balance - $snapshot->total_debits + $snapshot->total_credits;
                }

                $snapshot->save();
            }

            // 5. Generate HMAC-SHA256 digital signature
            $signature = $this->signatureService->sign($journal);
            $journal->posting_hash = $signature;
            $journal->status = 'POSTED';
            $journal->posted_by = $userId;
            $journal->posted_at = now();
            $journal->save();
        });

        // 6. Redis cache invalidation for Trial Balance and Reports
        Cache::forget("trial_balance_{$companyId}_{$year}_{$month}");

        // 7. Dispatch Domain Events
        event(new JournalPosted(
            $journal->id,
            $userId,
            $companyId,
            now()->toIso8601String(),
            $journal->posting_hash
        ));

        if ($postedWithSuspense) {
            event(new JournalPostedWithSuspense(
                $journal->id,
                $delta,
                $companyId
            ));
        }
    }

    /**
     * Resolves the default suspense account.
     */
    protected function getOrCreateSuspenseAccount(int $companyId, int $branchId): int
    {
        $account = ChartOfAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('account_code', 'SUSPENSE')
            ->first();

        if (! $account) {
            $account = ChartOfAccount::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'account_code' => 'SUSPENSE',
                'name' => 'Suspense Account',
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'is_control_account' => false,
                'is_posting_allowed' => true,
            ]);
        }

        return $account->id;
    }
}
