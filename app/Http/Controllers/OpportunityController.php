<?php

namespace App\Http\Controllers;

use App\Enums\OpportunityStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Models\CrmLossReason;
use App\Models\CrmPipelineStage;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Repositories\OpportunityReadRepository;
use App\Services\IdempotencyService;
use App\Services\PipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    public function __construct(protected PipelineService $pipelineService) {}

    /**
     * Display a listing of opportunities.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Opportunity::class);

        $query = Opportunity::query()->with(['customer', 'stage', 'assignedTo']);

        // Filter by Sales Rep if not owner/admin/manager
        $user = $request->user();
        if (! ($user->hasRole(UserRole::Owner->value) ||
               $user->hasRole(UserRole::Admin->value) ||
               $user->hasRole(UserRole::Manager->value))) {
            $query->where('assigned_to', $user->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('stage_id')) {
            $query->where('pipeline_stage_id', $request->input('stage_id'));
        }

        $opportunities = $query->latest()->paginate(15)->withQueryString();
        $stages = CrmPipelineStage::where('is_active', true)->orderBy('stage_sequence')->get();

        $readRepo = new OpportunityReadRepository;
        $kanbanData = $readRepo->getKanbanData();

        return Inertia::render('opportunities/index', [
            'opportunities' => $opportunities,
            'stages' => $stages,
            'kanbanData' => $kanbanData,
            'filters' => $request->only(['status', 'stage_id']),
        ]);
    }

    /**
     * Show the form for creating a new opportunity.
     */
    public function create(): Response
    {
        Gate::authorize('create', Opportunity::class);

        $customers = Customer::select(['id', 'name', 'company_name'])->get();
        $stages = CrmPipelineStage::where('is_active', true)->orderBy('stage_sequence')->get();
        $users = User::select(['id', 'name'])->get();

        return Inertia::render('opportunities/create', [
            'customers' => $customers,
            'stages' => $stages,
            'users' => $users,
        ]);
    }

    public function store(StoreOpportunityRequest $request): RedirectResponse
    {
        Gate::authorize('create', Opportunity::class);

        $idempotencyKey = $request->header('X-Idempotency-Key');
        if ($idempotencyKey) {
            $idempotencyService = new IdempotencyService;

            return $idempotencyService->handle($idempotencyKey, function () use ($request) {
                return $this->createOpportunity($request);
            });
        }

        return $this->createOpportunity($request);
    }

    protected function createOpportunity(StoreOpportunityRequest $request): RedirectResponse
    {
        $opportunity = new Opportunity;
        $opportunity->fill($request->validated());
        $opportunity->pipeline_stage_id = (int) $request->input('pipeline_stage_id');
        $opportunity->status = OpportunityStatus::Qualification;
        $opportunity->created_by = $request->user()->id;
        $opportunity->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunity created successfully.',
        ]);

        return to_route('opportunities.show', $opportunity->id);
    }

    /**
     * Display the specified opportunity.
     */
    public function show(Opportunity $opportunity): Response
    {
        Gate::authorize('view', $opportunity);

        $opportunity->load([
            'lead',
            'customer',
            'stage.pipelineDefinition',
            'lossReason',
            'assignedTo',
            'creator',
            'competitors',
            'stageHistories.fromStage',
            'stageHistories.toStage',
            'stageHistories.changedBy',
            'activities.creator',
        ]);

        $stages = CrmPipelineStage::where('is_active', true)->orderBy('stage_sequence')->get();
        $lossReasons = CrmLossReason::where('is_active', true)->get();

        return Inertia::render('opportunities/show', [
            'opportunity' => $opportunity,
            'stages' => $stages,
            'lossReasons' => $lossReasons,
        ]);
    }

    /**
     * Show the form for editing the specified opportunity.
     */
    public function edit(Opportunity $opportunity): Response
    {
        Gate::authorize('update', $opportunity);

        $customers = Customer::select(['id', 'name', 'company_name'])->get();
        $stages = CrmPipelineStage::where('is_active', true)->orderBy('stage_sequence')->get();
        $users = User::select(['id', 'name'])->get();
        $lossReasons = CrmLossReason::where('is_active', true)->get();

        return Inertia::render('opportunities/edit', [
            'opportunity' => $opportunity,
            'customers' => $customers,
            'stages' => $stages,
            'users' => $users,
            'lossReasons' => $lossReasons,
        ]);
    }

    /**
     * Update the specified opportunity in storage.
     */
    public function update(Opportunity $opportunity, UpdateOpportunityRequest $request): RedirectResponse
    {
        Gate::authorize('update', $opportunity);

        $opportunity->fill($request->validated());
        $opportunity->updated_by = $request->user()->id;
        $opportunity->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunity updated successfully.',
        ]);

        return to_route('opportunities.show', $opportunity->id);
    }

    /**
     * Remove the specified opportunity from storage.
     */
    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        Gate::authorize('delete', $opportunity);

        $opportunity->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunity deleted successfully.',
        ]);

        return to_route('opportunities.index');
    }

    /**
     * Transition the opportunity to a new pipeline stage.
     */
    public function changeStage(Opportunity $opportunity, Request $request): RedirectResponse
    {
        Gate::authorize('update', $opportunity);

        $request->validate([
            'pipeline_stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
        ]);

        $stage = CrmPipelineStage::findOrFail($request->input('pipeline_stage_id'));
        if (! $stage instanceof CrmPipelineStage) {
            abort(400);
        }

        $this->pipelineService->changeStage($opportunity, $stage, $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunity stage updated successfully.',
        ]);

        return back();
    }

    /**
     * Close the opportunity as Won.
     */
    public function win(Opportunity $opportunity, Request $request): RedirectResponse
    {
        Gate::authorize('update', $opportunity);

        $this->pipelineService->win($opportunity, $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunity closed as WON! Congratulations!',
        ]);

        return back();
    }

    /**
     * Close the opportunity as Lost.
     */
    public function lose(Opportunity $opportunity, Request $request): RedirectResponse
    {
        Gate::authorize('update', $opportunity);

        $request->validate([
            'loss_reason_id' => ['required', 'exists:crm_loss_reasons,id'],
            'loss_notes' => ['nullable', 'string'],
        ]);

        $this->pipelineService->lose(
            $opportunity,
            (int) $request->input('loss_reason_id'),
            $request->input('loss_notes'),
            $request->user()
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunity closed as LOST.',
        ]);

        return back();
    }

    /**
     * Bulk assign opportunities.
     */
    public function bulkAssign(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'exists:crm_opportunities,id'],
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        Opportunity::whereIn('id', $request->input('ids'))->update([
            'assigned_to' => $request->input('assigned_to'),
            'updated_by' => $request->user()->id,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunities reassigned successfully.',
        ]);

        return back();
    }

    /**
     * Bulk transition stages.
     */
    public function bulkStage(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'exists:crm_opportunities,id'],
            'pipeline_stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
        ]);

        $stage = CrmPipelineStage::findOrFail($request->input('pipeline_stage_id'));
        if (! $stage instanceof CrmPipelineStage) {
            abort(400);
        }

        foreach (Opportunity::whereIn('id', $request->input('ids'))->get() as $opp) {
            $this->pipelineService->changeStage($opp, $stage, $request->user());
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunities stage updated successfully.',
        ]);

        return back();
    }

    /**
     * Bulk delete opportunities.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'exists:crm_opportunities,id'],
        ]);

        Opportunity::whereIn('id', $request->input('ids'))->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opportunities deleted successfully.',
        ]);

        return back();
    }
}
