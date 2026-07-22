<?php

namespace App\Services;

use App\Models\FinancialDimension;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class DocumentSplittingEngine
{
    /**
     * Splits and balances journal line dimensions.
     */
    public function split(JournalEntry $journal, int $suspenseAccountId): void
    {
        $companyId = $journal->company_id;
        $dimensions = FinancialDimension::where('company_id', $companyId)->get();

        foreach ($dimensions as $dim) {
            $balances = [];

            // Sum debits/credits grouped by dimension value id
            foreach ($journal->lines as $line) {
                $dimVal = $line->dimensionValues()
                    ->wherePivot('financial_dimension_id', $dim->id)
                    ->first();

                if ($dimVal) {
                    $valId = $dimVal->id;
                    $balances[$valId] = ($balances[$valId] ?? 0.00) + ((float) $line->base_debit_amount - (float) $line->base_credit_amount);
                }
            }

            // Create balancing offset lines for imbalanced segments
            foreach ($balances as $valId => $net) {
                $net = round($net, 2);
                if ($net !== 0.00) {
                    $offsetLine = new JournalLine([
                        'account_id' => $suspenseAccountId,
                        'debit_amount' => $net < 0 ? abs($net) : 0.00,
                        'credit_amount' => $net > 0 ? $net : 0.00,
                        'currency_code' => 'IDR',
                        'exchange_rate' => 1.000000,
                        'base_debit_amount' => $net < 0 ? abs($net) : 0.00,
                        'base_credit_amount' => $net > 0 ? $net : 0.00,
                    ]);

                    $journal->lines()->save($offsetLine);
                    $offsetLine->dimensionValues()->attach($valId, ['financial_dimension_id' => $dim->id]);
                }
            }
        }
    }
}
