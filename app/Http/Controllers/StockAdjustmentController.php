<?php

namespace App\Http\Controllers;

use App\Enums\StockAdjustmentType;
use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\InventoryItem;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Services\StockAdjustmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected StockAdjustmentService $adjustmentService)
    {
        $this->authorizeResource(StockAdjustment::class, 'stock_adjustment');
    }

    public function index(Request $request): Response
    {
        $query = StockAdjustment::query()->with(['warehouse']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reference_no', 'like', "%{$search}%");
        }

        $stockAdjustments = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('stock-adjustments/index', [
            'stockAdjustments' => $stockAdjustments,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name']);

        $selectedWarehouseItems = [];
        if ($request->filled('warehouse_id')) {
            $selectedWarehouseItems = InventoryItem::where('warehouse_id', $request->input('warehouse_id'))->get(['id', 'sku', 'name', 'unit']);
        }

        $types = array_map(fn ($t) => ['value' => $t->value, 'label' => $t->label()], StockAdjustmentType::cases());

        return Inertia::render('stock-adjustments/create', [
            'warehouses' => $warehouses,
            'inventoryItems' => $selectedWarehouseItems,
            'adjustmentTypes' => $types,
        ]);
    }

    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $adj = $this->adjustmentService->create($data);

        return redirect()->route('stock-adjustments.show', $adj->id)
            ->with('success', 'Stock Adjustment draft created successfully.');
    }

    public function show(StockAdjustment $stockAdjustment): Response
    {
        $stockAdjustment->load(['warehouse', 'items.inventoryItem', 'creator', 'updater', 'approver']);

        return Inertia::render('stock-adjustments/show', [
            'stockAdjustment' => $stockAdjustment,
        ]);
    }

    public function approve(Request $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        $this->authorize('approve', $stockAdjustment);

        $request->validate([
            'approval_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->adjustmentService->approve($stockAdjustment, $request->input('approval_note'));

        return back()->with('success', 'Stock Adjustment approved and posted successfully.');
    }

    public function reject(StockAdjustment $stockAdjustment): RedirectResponse
    {
        $this->authorize('approve', $stockAdjustment);

        $this->adjustmentService->reject($stockAdjustment);

        return back()->with('success', 'Stock Adjustment rejected.');
    }
}
