<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PaymentNumberGenerator
{
    /**
     * Generate the next sequential reference number.
     * Format: PAY/YYYYMMDD/XXXX
     */
    public function generateNextNumber(): string
    {
        return DB::transaction(function () {
            $prefix = 'PAY/'.now()->format('Ymd').'/';

            $latest = DB::table('payments')
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
