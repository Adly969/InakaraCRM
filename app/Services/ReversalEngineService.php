<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\JournalReversal;
use Illuminate\Support\Facades\DB;

class ReversalEngineService
{
    public function __construct(
        protected NumberRangeEngine $numberRangeEngine,
        protected PostingEngineService $postingEngine
    ) {}

    /**
     * Reverses a posted journal entry.
     *
     * @return string Generated reversing journal number
     */
    public function reverse(int $originalJournalId, int $userId, string $reason): string
    {
        $original = JournalEntry::findOrFail($originalJournalId);
        if ($original->status !== 'POSTED') {
            throw new \InvalidArgumentException('Only posted journals can be reversed.');
        }

        return DB::transaction(function () use ($original, $userId, $reason) {
            $reversingNumber = $this->numberRangeEngine->generate($original->company_id, $original->branch_id, 'JOURNAL');

            $reversing = JournalEntry::create([
                'company_id' => $original->company_id,
                'branch_id' => $original->branch_id,
                'ledger_id' => $original->ledger_id,
                'journal_number' => $reversingNumber,
                'journal_type' => 'REVERSING',
                'transaction_date' => now(),
                'status' => 'DRAFT',
                'created_by' => $userId,
            ]);

            foreach ($original->lines as $line) {
                $reversingLine = new JournalLine([
                    'account_id' => $line->account_id,
                    // Swap Debit & Credit columns
                    'debit_amount' => $line->credit_amount,
                    'credit_amount' => $line->debit_amount,
                    'currency_code' => $line->currency_code,
                    'exchange_rate' => $line->exchange_rate,
                    'base_debit_amount' => $line->base_credit_amount,
                    'base_credit_amount' => $line->base_debit_amount,
                ]);

                $reversing->lines()->save($reversingLine);

                // Copy dimensions
                foreach ($line->dimensionValues as $val) {
                    $reversingLine->dimensionValues()->attach($val->id, [
                        'financial_dimension_id' => $val->financial_dimension_id,
                    ]);
                }
            }

            // Post reversing journal
            $this->postingEngine->post($reversing, $userId);

            // Record reversal history
            JournalReversal::create([
                'original_journal_id' => $original->id,
                'reversing_journal_id' => $reversing->id,
                'reversed_by' => $userId,
                'reason' => $reason,
            ]);

            // Set original status to REVERSED
            $original->status = 'REVERSED';
            $original->save();

            return $reversingNumber;
        });
    }

    /**
     * Auto-runs scheduled reversals for the given date.
     *
     * @param  string  $date  e.g. Y-m-d
     * @param  int  $userId  System user ID for queue jobs
     * @return int Count of journals reversed
     */
    public function runScheduledReversals(string $date, int $userId): int
    {
        $journals = JournalEntry::where('status', 'POSTED')
            ->where('reverse_on_date', '<=', $date)
            ->get();

        $count = 0;
        foreach ($journals as $j) {
            $this->reverse($j->id, $userId, "Auto-scheduled reversal on {$date}");
            $count++;
        }

        return $count;
    }
}
