<?php

namespace App\Services;

use App\Models\CollectionActivity;
use Illuminate\Support\Facades\Auth;

class CollectionActivityService
{
    /**
     * Create collection activity follow-up.
     *
     * @param  array<string, mixed>  $data
     */
    public function logActivity(array $data): CollectionActivity
    {
        return CollectionActivity::create([
            'customer_id' => $data['customer_id'],
            'invoice_id' => $data['invoice_id'] ?? null,
            'activity_type' => $data['activity_type'],
            'status' => $data['status'] ?? 'pending',
            'promise_amount' => $data['promise_amount'] ?? null,
            'promise_date' => $data['promise_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? Auth::id() ?? 1,
            'created_by' => Auth::id() ?? 1,
        ]);
    }
}
