<?php

namespace App\Services;

use App\Models\OfficialReceipt;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfficialReceiptService
{
    /**
     * Generate an official receipt for a payment idempotently.
     */
    public function generateReceipt(Payment $payment): OfficialReceipt
    {
        return DB::transaction(function () use ($payment) {
            // Check if already exists
            $existing = OfficialReceipt::where('payment_id', $payment->id)->first();
            if ($existing) {
                return $existing;
            }

            $receiptNo = 'REC/'.now()->format('Ymd').'/'.str_pad($payment->id, 5, '0', STR_PAD_LEFT);

            // Generate mock PDF path
            $pdfPath = 'receipts/'.$receiptNo.'.pdf';

            $receipt = OfficialReceipt::create([
                'payment_id' => $payment->id,
                'receipt_no' => $receiptNo,
                'status' => 'generated',
                'pdf_path' => $pdfPath,
            ]);

            Log::info("Official Receipt generated: {$receiptNo} for Payment ID {$payment->id}");

            return $receipt;
        });
    }

    /**
     * Void the official receipt.
     */
    public function voidReceipt(Payment $payment): void
    {
        $receipt = OfficialReceipt::where('payment_id', $payment->id)->first();
        if ($receipt) {
            $receipt->status = 'voided';
            $receipt->save();
            Log::info("Official Receipt {$receipt->receipt_no} voided.");
        }
    }
}
