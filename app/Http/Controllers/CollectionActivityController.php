<?php

namespace App\Http\Controllers;

use App\Enums\CollectionActivityType;
use App\Models\Payment;
use App\Services\CollectionActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CollectionActivityController extends Controller
{
    public function __construct(
        protected CollectionActivityService $activityService
    ) {}

    /**
     * Store collection activity log.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('viewReceivables', Payment::class);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'activity_type' => ['required', 'string', Rule::in(CollectionActivityType::values())],
            'status' => ['required', 'string', Rule::in(['pending', 'completed', 'broken'])],
            'promise_amount' => ['nullable', 'numeric', 'min:0'],
            'promise_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'next_follow_up_date' => ['nullable', 'date'],
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $this->activityService->logActivity($validated);

        return back()->with('success', 'Collection activity logged successfully.');
    }
}
