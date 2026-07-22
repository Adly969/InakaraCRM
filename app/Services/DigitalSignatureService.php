<?php

namespace App\Services;

use App\Models\JournalEntry;

class DigitalSignatureService
{
    /**
     * Generate an HMAC-SHA256 signature for a journal entry.
     */
    public function sign(JournalEntry $journal): string
    {
        $linesPayload = '';
        foreach ($journal->lines as $line) {
            $linesPayload .= "{$line->account_id}:{$line->base_debit_amount}:{$line->base_credit_amount}|";
        }

        $payload = implode('|', [
            $journal->journal_number,
            $journal->company_id,
            $journal->branch_id,
            $journal->transaction_date->format('Y-m-d'),
            $linesPayload,
        ]);

        return hash_hmac('sha256', $payload, config('app.key'));
    }

    /**
     * Verify the HMAC signature of a journal entry.
     */
    public function verify(JournalEntry $journal): bool
    {
        if (empty($journal->posting_hash)) {
            return false;
        }

        return hash_equals($journal->posting_hash, $this->sign($journal));
    }
}
