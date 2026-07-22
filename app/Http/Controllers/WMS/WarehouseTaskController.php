<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseTask;
use App\Services\WMS\DocumentNumberGenerator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseTaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected DocumentNumberGenerator $numberGenerator) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', WarehouseTask::class);

        $query = WarehouseTask::query()->with(['warehouse', 'operator', 'items.product']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('task_number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();
        $operators = User::all(['id', 'name']);
        $warehouses = Warehouse::all(['id', 'name']);

        return Inertia::render('inventory/tasks/index', [
            'tasks' => $tasks,
            'operators' => $operators,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('assign', WarehouseTask::class);

        $validated = $request->validate([
            'task_type' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'assigned_operator_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string',
            'due_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_target' => 'required|numeric|min:0.0001',
        ]);

        $tenantId = (string) Auth::user()->tenant_id;
        $taskNumber = $this->numberGenerator->generate('TSK', $tenantId);

        $task = WarehouseTask::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'company_id' => Auth::user()->company_id,
            'task_number' => $taskNumber,
            'task_type' => $validated['task_type'],
            'status' => ! empty($validated['assigned_operator_id']) ? 'assigned' : 'draft',
            'priority' => $validated['priority'] ?? 'medium',
            'warehouse_id' => $validated['warehouse_id'],
            'assigned_operator_id' => $validated['assigned_operator_id'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $task->items()->create([
                'product_id' => $item['product_id'],
                'quantity_target' => $item['quantity_target'],
            ]);
        }

        return redirect()->back()->with('success', "Task {$taskNumber} created successfully.");
    }

    public function show(WarehouseTask $task): Response
    {
        $this->authorize('view', $task);

        $task->load(['warehouse', 'operator', 'items.product']);

        return Inertia::render('inventory/tasks/show', [
            'task' => $task,
        ]);
    }

    public function complete(WarehouseTask $task): RedirectResponse
    {
        $this->authorize('execute', $task);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', "Task {$task->task_number} completed.");
    }
}
