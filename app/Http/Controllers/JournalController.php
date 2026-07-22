<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\JournalRevision;
use App\Services\PostingEngineService;
use App\Services\ReversalEngineService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalController extends Controller
{
    public function __construct(
        protected PostingEngineService $postingEngine,
        protected ReversalEngineService $reversalEngine
    ) {}

    /**
     * Create a new draft journal entry.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ledger_id' => 'required|exists:ledgers,id',
            'journal_type' => 'required|string|in:MANUAL,ACCRUAL',
            'transaction_date' => 'required|date',
            'reverse_on_date' => 'nullable|date|after:transaction_date',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit_amount' => 'required|numeric|min:0',
            'lines.*.credit_amount' => 'required|numeric|min:0',
            'lines.*.dimensions' => 'nullable|array',
        ]);

        $user = Auth::user();
        $journalNumber = 'JV-'.date('Y').'-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $journal = DB::transaction(function () use ($request, $user, $journalNumber) {
            $journal = JournalEntry::create([
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
                'ledger_id' => $request->input('ledger_id'),
                'journal_number' => $journalNumber,
                'journal_type' => $request->input('journal_type'),
                'transaction_date' => Carbon::parse($request->input('transaction_date')),
                'reverse_on_date' => $request->input('reverse_on_date') ? Carbon::parse($request->input('reverse_on_date')) : null,
                'status' => 'DRAFT',
                'created_by' => $user->id,
            ]);

            foreach ($request->input('lines') as $lineData) {
                $line = new JournalLine([
                    'account_id' => $lineData['account_id'],
                    'debit_amount' => $lineData['debit_amount'],
                    'credit_amount' => $lineData['credit_amount'],
                    'currency_code' => 'IDR',
                    'exchange_rate' => 1.000000,
                    'base_debit_amount' => $lineData['debit_amount'],
                    'base_credit_amount' => $lineData['credit_amount'],
                ]);
                $journal->lines()->save($line);

                if (! empty($lineData['dimensions'])) {
                    foreach ($lineData['dimensions'] as $dimId => $valId) {
                        $line->dimensionValues()->attach($valId, ['financial_dimension_id' => $dimId]);
                    }
                }
            }

            return $journal;
        });

        return response()->json([
            'message' => 'Journal draft created successfully.',
            'journal_number' => $journal->journal_number,
            'id' => $journal->id,
        ], 201);
    }

    /**
     * Update/revise a draft journal entry, incrementing version.
     */
    public function revision(Request $request, int $id): JsonResponse
    {
        $journal = JournalEntry::findOrFail($id);
        if ($journal->status !== 'DRAFT') {
            return response()->json(['message' => 'Only draft journals can be revised.'], 422);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit_amount' => 'required|numeric|min:0',
            'lines.*.credit_amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($journal, $request, $user) {
            // Save original state as revision log
            $oldLines = $journal->lines->map(fn ($l) => $l->toArray())->toArray();

            JournalRevision::create([
                'journal_entry_id' => $journal->id,
                'version_number' => $journal->current_version,
                'changes' => ['old_lines' => $oldLines],
                'reason' => $request->input('reason'),
                'editor_id' => $user->id,
            ]);

            // Recreate lines
            $journal->lines()->delete();

            foreach ($request->input('lines') as $lineData) {
                $line = new JournalLine([
                    'account_id' => $lineData['account_id'],
                    'debit_amount' => $lineData['debit_amount'],
                    'credit_amount' => $lineData['credit_amount'],
                    'currency_code' => 'IDR',
                    'exchange_rate' => 1.000000,
                    'base_debit_amount' => $lineData['debit_amount'],
                    'base_credit_amount' => $lineData['credit_amount'],
                ]);
                $journal->lines()->save($line);
            }

            $journal->current_version += 1;
            $journal->save();
        });

        return response()->json(['message' => 'Journal revision saved successfully.']);
    }

    /**
     * Post a draft journal entry.
     */
    public function post(int $id): JsonResponse
    {
        $journal = JournalEntry::findOrFail($id);
        $user = Auth::user();

        // Maker-Checker authorization check
        if ($journal->created_by === $user->id) {
            throw ValidationException::withMessages([
                'approval' => ['Maker-Checker violation: The creator of a journal cannot approve or post it.'],
            ]);
        }

        $this->postingEngine->post($journal, $user->id);

        return response()->json(['message' => 'Journal entry posted successfully.']);
    }

    /**
     * Reverse a posted journal entry.
     */
    public function reverse(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $user = Auth::user();

        $reversingNumber = $this->reversalEngine->reverse($id, $user->id, $request->input('reason'));

        return response()->json([
            'message' => 'Journal entry reversed successfully.',
            'reversing_journal_number' => $reversingNumber,
        ]);
    }

    /**
     * Bulk approve and post selected journals.
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:journal_entries,id',
        ]);

        $user = Auth::user();
        $successCount = 0;
        $errors = [];

        foreach ($request->input('ids') as $id) {
            $journal = JournalEntry::find($id);
            if ($journal->created_by === $user->id) {
                $errors[$id] = 'Maker-Checker violation.';

                continue;
            }
            try {
                $this->postingEngine->post($journal, $user->id);
                $successCount++;
            } catch (\Throwable $e) {
                $errors[$id] = $e->getMessage();
            }
        }

        return response()->json([
            'message' => "Successfully posted {$successCount} journals.",
            'errors' => $errors,
        ]);
    }

    /**
     * Bulk reverse selected journals.
     */
    public function bulkReverse(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:journal_entries,id',
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $successCount = 0;
        $errors = [];

        foreach ($request->input('ids') as $id) {
            try {
                $this->reversalEngine->reverse($id, $user->id, $request->input('reason'));
                $successCount++;
            } catch (\Throwable $e) {
                $errors[$id] = $e->getMessage();
            }
        }

        return response()->json([
            'message' => "Successfully reversed {$successCount} journals.",
            'errors' => $errors,
        ]);
    }

    /**
     * Trial Balance Inquiry.
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('m'));

        // Query ledger snapshots for starting balances, plus current period movements
        $trialBalance = DB::table('chart_of_accounts')
            ->leftJoin('ledger_snapshots', function ($join) use ($year, $month, $user) {
                $join->on('chart_of_accounts.id', '=', 'ledger_snapshots.account_id')
                    ->where('ledger_snapshots.fiscal_year', '=', $year)
                    ->where('ledger_snapshots.fiscal_month', '=', $month)
                    ->where('ledger_snapshots.company_id', '=', $user->company_id);
            })
            ->select(
                'chart_of_accounts.id',
                'chart_of_accounts.account_code',
                'chart_of_accounts.name',
                'chart_of_accounts.account_type',
                'chart_of_accounts.normal_balance',
                DB::raw('COALESCE(ledger_snapshots.opening_balance, 0.00) as opening_balance'),
                DB::raw('COALESCE(ledger_snapshots.total_debits, 0.00) as debits'),
                DB::raw('COALESCE(ledger_snapshots.total_credits, 0.00) as credits'),
                DB::raw('COALESCE(ledger_snapshots.closing_balance, 0.00) as closing_balance')
            )
            ->where('chart_of_accounts.company_id', $user->company_id)
            ->orderBy('chart_of_accounts.account_code')
            ->get();

        return response()->json([
            'year' => $year,
            'month' => $month,
            'data' => $trialBalance,
        ]);
    }
}
