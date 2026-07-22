<?php

namespace App\Http\Controllers\CRM;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreTaskRequest;
use App\Models\CrmTask;
use App\Models\CrmTaskChecklist;
use App\Models\User;
use App\Services\CRM\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrmTaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CrmTask::class);

        $tasks = $this->taskService->getPaginated([
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
            'assigned_to' => $request->query('assigned_to'),
            'customer_id' => $request->query('customer_id'),
            'lead_id' => $request->query('lead_id'),
            'opportunity_id' => $request->query('opportunity_id'),
            'search' => $request->query('search'),
        ]);

        return Inertia::render('crm/tasks/index', [
            'tasks' => $tasks,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'customer_id', 'lead_id', 'opportunity_id', 'search']),
            'statuses' => collect(TaskStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
            'priorities' => collect(TaskPriority::cases())->map(fn ($p) => [
                'value' => $p->value,
                'label' => $p->label(),
                'color' => $p->color(),
            ]),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $this->taskService->create($request->validated(), $request->user());

        return redirect()->back()->with('success', 'Task created successfully.');
    }

    public function show(CrmTask $task): Response
    {
        $this->authorize('view', $task);

        $task->load([
            'assignedTo:id,name',
            'creator:id,name',
            'customer:id,name',
            'lead:id,first_name,last_name',
            'opportunity:id,title',
            'checklists.completedBy:id,name',
            'comments.author:id,name',
            'subtasks.assignedTo:id,name',
        ]);

        return Inertia::render('crm/tasks/show', [
            'task' => $task,
            'statuses' => collect(TaskStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'priorities' => collect(TaskPriority::cases())->map(fn ($p) => ['value' => $p->value, 'label' => $p->label()]),
        ]);
    }

    public function toggleStatus(Request $request, CrmTask $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $request->validate(['status' => 'required|string']);
        $newStatus = TaskStatus::from($request->input('status'));

        $this->taskService->toggleStatus($task, $newStatus, $request->user());

        return redirect()->back()->with('success', 'Task status updated.');
    }

    public function toggleChecklist(Request $request, CrmTaskChecklist $checklist): RedirectResponse
    {
        $task = $checklist->task;
        $this->authorize('update', $task);

        $this->taskService->toggleChecklist($checklist, $request->user());

        return redirect()->back()->with('success', 'Checklist item updated.');
    }

    public function destroy(CrmTask $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('crm.tasks.index')->with('success', 'Task deleted.');
    }
}
