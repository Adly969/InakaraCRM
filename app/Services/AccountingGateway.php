<?php

namespace App\Services;

use App\DTO\GatewayReceipt;
use App\Models\ChartOfAccount;
use App\Models\FinancialEvent;
use App\Models\IdempotencyKey;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountingGateway implements AccountingGatewayInterface
{
    public function __construct(
        protected PostingRuleResolver $ruleResolver,
        protected EventSchemaValidator $schemaValidator,
        protected ValidationPipeline $validationPipeline,
        protected NumberRangeEngine $numberRangeEngine,
        protected PostingEngineService $postingEngine,
        protected DocumentSplittingEngine $splittingEngine
    ) {}

    /**
     * Ingests a business event, translates it, validates rules, and posts it.
     *
     * @param  string  $eventType  e.g. SalesInvoiceApproved
     * @param  array  $payload  Operational data from the emitting module
     * @param  string  $idempotencyKey  Unique key to prevent duplicate posting
     * @return GatewayReceipt Response containing journal details
     *
     * @throws ValidationException
     */
    public function postTransaction(string $eventType, array $payload): string
    {
        // Fallback for backwards compatibility with Sprint 12 tests
        $receipt = $this->postEvent($eventType, $payload, $payload['idempotency_key'] ?? (string) Str::uuid());

        return $receipt->journalNumber;
    }

    /**
     * Processes event transaction ingestion securely.
     */
    public function postEvent(string $eventType, array $payload, string $idempotencyKey): GatewayReceipt
    {
        $hashKey = hash('sha256', $idempotencyKey);

        return DB::transaction(function () use ($eventType, $payload, $idempotencyKey, $hashKey) {
            // 1. Idempotency manager deduplication check
            $exists = IdempotencyKey::where('key_hash', $hashKey)->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    'idempotency' => ['Duplicate transaction payload detected (Idempotency Violation).'],
                ]);
            }

            IdempotencyKey::create([
                'key_hash' => $hashKey,
                'expiry_time' => now()->addDays(3),
            ]);

            $companyId = $payload['company_id'];
            $branchId = $payload['branch_id'];
            $amount = (float) $payload['amount'];
            $userId = $payload['user_id'];
            $ledgerId = $payload['ledger_id'] ?? 1;
            $date = Carbon::parse($payload['transaction_date'] ?? now());

            // 2. Event Schema validation
            $this->schemaValidator->validate($eventType, $payload['schema_version'] ?? 1, $payload);

            // 3. Log Inbound Event
            $eventUuid = (string) Str::uuid();
            $financialEvent = FinancialEvent::create([
                'event_uuid' => $eventUuid,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'event_type' => $eventType,
                'source_module' => $payload['source_module'] ?? 'UNKNOWN',
                'payload' => $payload,
                'status' => 'RECEIVED',
                'idempotency_key' => $idempotencyKey,
                'correlation_id' => $payload['correlation_id'] ?? (string) Str::uuid(),
            ]);

            // 4. Resolve Posting Rule Mapping version dynamically
            $rule = $this->ruleResolver->resolve($eventType, [
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // 5. Build Journal Draft
            $journalNumber = $this->numberRangeEngine->generate($companyId, $branchId, 'JOURNAL');
            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'ledger_id' => $ledgerId,
                'journal_number' => $journalNumber,
                'journal_type' => 'AUTOMATIC',
                'transaction_date' => $date,
                'status' => 'DRAFT',
                'created_by' => $userId,
            ]);

            // Debit Line
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

            // Credit Line
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

            // Attach Dimensions if present in the payload
            if (! empty($payload['dimensions'])) {
                foreach ($payload['dimensions'] as $dimId => $dimValId) {
                    $debitLine->dimensionValues()->attach($dimValId, ['financial_dimension_id' => $dimId]);
                    $creditLine->dimensionValues()->attach($dimValId, ['financial_dimension_id' => $dimId]);
                }
            }

            // 6. Balance Dimensions (Document Splitting Engine)
            $suspenseAccountId = $this->getOrCreateSuspenseAccount($companyId, $branchId);
            $this->splittingEngine->split($journal, $suspenseAccountId);

            // 7. Validate Period / Validation Pipeline
            $this->validationPipeline->validate($companyId, $branchId, $ledgerId, (int) $date->format('Y'), (int) $date->format('m'));

            // 8. Commit & Post via PostingEngine
            $this->postingEngine->post($journal, $userId);

            $financialEvent->update(['status' => 'POSTED']);

            return new GatewayReceipt(
                eventUuid: $eventUuid,
                journalNumber: $journalNumber,
                status: 'POSTED',
                signature: $journal->posting_hash ?? ''
            );
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
