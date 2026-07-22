<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\PostingRuleVersion;
use Illuminate\Validation\ValidationException;

class PostingRuleValidator
{
    /**
     * Validate a posting rule before publication.
     *
     * @throws ValidationException
     */
    public function validate(PostingRuleVersion $rule): void
    {
        $debitAcc = ChartOfAccount::find($rule->debit_account_id);
        $creditAcc = ChartOfAccount::find($rule->credit_account_id);

        // 1. Account existence
        if (! $debitAcc) {
            throw ValidationException::withMessages(['debit_account_id' => 'Debit account does not exist.']);
        }
        if (! $creditAcc) {
            throw ValidationException::withMessages(['credit_account_id' => 'Credit account does not exist.']);
        }

        // 2. Self posting detection
        if ($rule->debit_account_id === $rule->credit_account_id) {
            throw ValidationException::withMessages(['credit_account_id' => 'Debit and Credit accounts cannot be identical (Self-Posting).']);
        }

        // 3. Posting allowed checks
        if (! $debitAcc->is_posting_allowed) {
            throw ValidationException::withMessages(['debit_account_id' => 'Debit account does not allow posting.']);
        }
        if (! $creditAcc->is_posting_allowed) {
            throw ValidationException::withMessages(['credit_account_id' => 'Credit account does not allow posting.']);
        }

        // 4. Duplicate checks
        $duplicate = PostingRuleVersion::where([
            'company_id' => $rule->company_id,
            'branch_id' => $rule->branch_id,
            'event_type' => $rule->event_type,
            'version' => $rule->version,
            'status' => 'PUBLISHED',
        ])->where('id', '!=', $rule->id)->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['event_type' => 'A published posting rule version already exists for this event and branch context.']);
        }
    }
}
