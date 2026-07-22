<?php

namespace App\Http\Controllers\CRM;

use App\Enums\ActivityOutcome;
use App\Enums\CrmActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\CompleteActivityRequest;
use App\Http\Requests\CRM\StoreActivityRequest;
use App\Http\Requests\CRM\UpdateActivityRequest;
use App\Models\CrmActivity;
use App\Models\User;
use App\Services\CRM\ActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrmActivityController extends Controller
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CrmActivity::class);

        $activities = $this->activityService->getPaginated([
            'activity_type' => $request->query('activity_type'),
            'status' => $request->query('status'),
            'assigned_to' => $request->query('assigned_to'),
            'customer_id' => $request->query('customer_id'),
            'lead_id' => $request->query('lead_id'),
            'opportunity_id' => $request->query('opportunity_id'),
            'search' => $request->query('search'),
        ]);

        return Inertia::render('crm/activities/index', [
            'activities' => $activities,
            'filters' => $request->only(['activity_type', 'status', 'assigned_to', 'customer_id', 'lead_id', 'opportunity_id', 'search']),
            'activityTypes' => collect(CrmActivityType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'icon' => $t->icon(),
            ]),
            'outcomes' => collect(ActivityOutcome::cases())->map(fn ($o) => [
                'value' => $o->value,
                'label' => $o->label(),
                'color' => $o->color(),
            ]),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $this->activityService->create($request->validated(), $request->user());

        return redirect()->back()->with('success', 'Activity logged successfully.');
    }

    public function show(CrmActivity $activity): Response
    {
        $this->authorize('view', $activity);

        $activity->load([
            'customer:id,name',
            'lead:id,first_name,last_name,company_name',
            'opportunity:id,title',
            'assignedTo:id,name',
            'creator:id,name',
            'activityComments.user:id,name',
            'attachments.uploader:id,name',
            'tags',
        ]);

        return Inertia::render('crm/activities/show', [
            'activity' => $activity,
            'outcomes' => collect(ActivityOutcome::cases())->map(fn ($o) => [
                'value' => $o->value,
                'label' => $o->label(),
                'color' => $o->color(),
            ]),
        ]);
    }

    public function update(UpdateActivityRequest $request, CrmActivity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $this->activityService->update($activity, $request->validated(), $request->user());

        return redirect()->back()->with('success', 'Activity updated.');
    }

    public function destroy(CrmActivity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return redirect()->route('crm.activities.index')->with('success', 'Activity deleted.');
    }

    public function complete(CompleteActivityRequest $request, CrmActivity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $outcome = ActivityOutcome::from($request->validated()['outcome']);
        $this->activityService->complete($activity, $outcome, $request->validated()['notes'] ?? null, $request->user());

        return redirect()->back()->with('success', 'Activity marked as completed.');
    }

    public function storeComment(Request $request, CrmActivity $activity): RedirectResponse
    {
        $this->authorize('view', $activity);

        $request->validate(['body' => 'required|string']);

        $this->activityService->addComment($activity, $request->input('body'), $request->user());

        return redirect()->back()->with('success', 'Comment added.');
    }

    public function storeAttachment(Request $request, CrmActivity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $request->validate(['file' => 'required|file|max:25600']);

        $this->activityService->addAttachment($activity, $request->file('file'), $request->user());

        return redirect()->back()->with('success', 'Attachment uploaded.');
    }
}
