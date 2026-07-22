<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class PostingRuleSimulator
{
    public function __construct(
        protected PostingRuleResolver $resolver,
        protected DocumentSplittingEngine $splittingEngine,
        protected DigitalSignatureService $signatureService
    ) {}

    /**
     * Dry-runs a financial event and returns the projected journal layout.
     *
     * @return array Projected journal lines and status details
     */
    public function simulate(string $eventType, array $payload): array
    {
        return DB::transaction(function () use ($eventType, $payload) {
            $companyId = $payload['company_id'];
            $branchId = $payload['branch_id'];
            $amount = (float) $payload['amount'];
            $ledgerId = $payload['ledger_id'] ?? 1;

            // Resolve rule version
            $rule = $this->resolver->resolve($eventType, [
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Create temporary draft Journal Entry (rolled back at transaction end)
            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'ledger_id' => $ledgerId,
                'journal_number' => 'SIM-'.time(),
                'journal_type' => 'AUTOMATIC',
                'transaction_date' => now(),
                'status' => 'DRAFT',
                'created_by' => $payload['user_id'] ?? 1,
            ]);

            $debitLine = new JournalLine([
                'account_id' => $rule->debit_account_id,
                'debit_amount' => $amount,
                'credit_amount' => 0.00,
                'currency_code' => $payload['currency_code'] ?? 'IDR',
                'exchange_rate' => $payload['exchange_rate'] ?? 1.000000,
                'base_debit_amount' => $amount * ($payload['exchange_rate'] ?? 1.00),
                'base_credit_amount' => 0.00,
            ]);
            $journal->lines()->save($debitLine);

            $creditLine = new JournalLine([
                'account_id' => $rule->credit_account_id,
                'debit_amount' => 0.00,
                'credit_amount' => $amount,
                'currency_code' => $payload['currency_code'] ?? 'IDR',
                'exchange_rate' => $payload['exchange_rate'] ?? 1.000000,
                'base_debit_amount' => 0.00,
                'base_credit_amount' => $amount * ($payload['exchange_rate'] ?? 1.00),
            ]);
            $journal->lines()->save($creditLine);

            // Apply document splitting logic
            $suspenseAccountId = $this->getOrCreateSuspenseAccount($companyId, $branchId);
            $this->splittingEngine->split($journal, $suspenseAccountId);

            // Project signature
            $signature = $this->signatureService->sign($journal);

            $projectedLines = [];
            foreach ($journal->lines as $line) {
                $projectedLines[] = [
                    'account_id' => $line->account_id,
                    'account_name' => $line->account->name,
                    'debit_amount' => (float) $line->debit_amount,
                    'credit_amount' => (float) $line->credit_amount,
                    'base_debit_amount' => (float) $line->base_debit_amount,
                    'base_credit_amount' => (float) $line->base_credit_amount,
                    'currency' => $line->currency_code,
                ];
            }

            // Force transaction rollback so nothing is committed to database
            DB::rollBack();

            return [
                'event_type' => $eventType,
                'debit_account_id' => $rule->debit_account_id,
                'credit_account_id' => $rule->credit_account_id,
                'projected_signature' => $signature,
                'lines' => $projectedLines,
            ];
        });
    }

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
