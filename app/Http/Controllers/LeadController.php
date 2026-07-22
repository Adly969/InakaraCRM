<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected LeadService $leadService) {}

    /**
     * Display a listing of the leads.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Lead::class);

        $query = Lead::query();

        // Row-level scope: Sales representatives can only view leads assigned to them.
        // Owners, Admins, and Managers can view all leads.
        $user = $request->user();
        if (! ($user->hasRole(UserRole::Owner->value) ||
               $user->hasRole(UserRole::Admin->value) ||
               $user->hasRole(UserRole::Manager->value))) {
            $query->where('assigned_to', $user->id);
        }

        $leads = $query->with('assignedTo')->latest()->paginate(10);

        return Inertia::render('leads/index', [
            'leads' => $leads,
        ]);
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create(): Response
    {
        Gate::authorize('create', Lead::class);

        $users = User::select(['id', 'name'])->get();

        return Inertia::render('leads/create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        Gate::authorize('create', Lead::class);

        $this->leadService->create($request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Lead created successfully.',
        ]);

        return to_route('leads.index');
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead): Response
    {
        Gate::authorize('view', $lead);

        return Inertia::render('leads/show', [
            'lead' => $lead->load(['assignedTo', 'creator', 'updater']),
        ]);
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead): Response
    {
        Gate::authorize('update', $lead);

        $users = User::select(['id', 'name'])->get();

        return Inertia::render('leads/edit', [
            'lead' => $lead,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Lead $lead, UpdateLeadRequest $request): RedirectResponse
    {
        Gate::authorize('update', $lead);

        // Reopening check
        if ($lead->status === LeadStatus::Disqualified &&
            $request->enum('status', LeadStatus::class) !== LeadStatus::Disqualified) {
            Gate::authorize('reopen', $lead);
        }

        $this->leadService->update($lead, $request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Lead updated successfully.',
        ]);

        return to_route('leads.index');
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead): RedirectResponse
    {
        Gate::authorize('delete', $lead);

        $lead->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Lead deleted successfully.',
        ]);

        return to_route('leads.index');
    }

    /**
     * Change the status of the specified lead.
     */
    public function changeStatus(Lead $lead, Request $request): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $request->validate([
            'status' => ['required', new Enum(LeadStatus::class)],
            'disqualification_reason' => [
                'required_if:status,'.LeadStatus::Disqualified->value,
                'nullable',
                'string',
            ],
        ]);

        // Reopening check
        $newStatus = $request->enum('status', LeadStatus::class);
        if ($lead->status === LeadStatus::Disqualified && $newStatus !== LeadStatus::Disqualified) {
            Gate::authorize('reopen', $lead);
        }

        $this->leadService->changeStatus(
            $lead,
            $newStatus,
            $request->input('disqualification_reason'),
            $request->user()
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Lead status updated successfully.',
        ]);

        return back();
    }
}
