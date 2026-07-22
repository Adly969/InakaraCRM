<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\LeadConversionService;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LeadWorkflowController extends Controller
{
    public function __construct(
        protected LeadService $leadService,
        protected LeadConversionService $leadConversionService
    ) {}

    /**
     * Mark the lead as Qualified.
     */
    public function qualify(Lead $lead, Request $request): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $this->leadService->changeStatus($lead, LeadStatus::Qualified, null, $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Lead qualified successfully.',
        ]);

        return back();
    }

    /**
     * Show the conversion form.
     */
    public function showConvertForm(Lead $lead): Response
    {
        Gate::authorize('update', $lead);

        if ($lead->status !== LeadStatus::Qualified) {
            abort(400, 'Only qualified leads can be converted.');
        }

        // Find or create customer matching details, or get list of existing customers
        $customers = Customer::select(['id', 'name', 'company_name'])->get();

        return Inertia::render('leads/convert', [
            'lead' => $lead,
            'customers' => $customers,
        ]);
    }

    /**
     * Convert qualified lead to Opportunity.
     */
    public function convert(Lead $lead, Request $request): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'title' => ['required', 'string', 'max:150'],
            'deal_value' => ['required', 'numeric', 'min:0'],
            'expected_close_date' => ['required', 'date'],
        ]);

        $opportunity = $this->leadConversionService->convert($lead, $validated, $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Lead successfully converted to Opportunity: '.$opportunity->title,
        ]);

        return to_route('opportunities.show', $opportunity->id);
    }
}
