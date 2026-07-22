<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\Shipment;
use Illuminate\Support\Carbon;

class DeliveryNumberGenerator
{
    /**
     * Generate a unique reference number for a Delivery Order.
     * Format: DO-YYYYMMDD-XXXX (where XXXX is sequential daily number)
     */
    public function generateDoNumber(): string
    {
        $today = Carbon::today()->format('Ymd');
        $prefix = "DO-{$today}-";

        $lastDo = DeliveryOrder::where('reference_no', 'like', "{$prefix}%")
            ->orderBy('reference_no', 'desc')
            ->first();

        $sequence = 1;
        if ($lastDo) {
            $parts = explode('-', $lastDo->reference_no);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        }

        $paddedSequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$paddedSequence}";
    }

    /**
     * Generate a unique reference number for a Shipment.
     * Format: SHP-YYYYMMDD-XXXX
     */
    public function generateShipmentNumber(): string
    {
        $today = Carbon::today()->format('Ymd');
        $prefix = "SHP-{$today}-";

        $lastShp = Shipment::where('reference_no', 'like', "{$prefix}%")
            ->orderBy('reference_no', 'desc')
            ->first();

        $sequence = 1;
        if ($lastShp) {
            $parts = explode('-', $lastShp->reference_no);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        }

        $paddedSequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$paddedSequence}";
    }
}
