<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    /**
     * Generate the next atomic invoice sequence number.
     */
    public function generateNextNumber(): string
    {
        return DB::transaction(function () {
            $prefix = 'INV/'.now()->format('Ymd').'/';

            $latest = DB::table('invoices')
                ->where('reference_no', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('reference_no', 'desc')
                ->first();

            $seq = 1;
            if ($latest) {
                $parts = explode('/', $latest->reference_no);
                $seq = intval(end($parts)) + 1;
            }

            return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
