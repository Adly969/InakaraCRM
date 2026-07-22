<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Warehouse;
use App\Services\InventoryProjectionService;
use App\Services\InventoryQueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected InventoryProjectionService $projectionService,
        protected InventoryQueryService $queryService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryItem::class);

        $query = InventoryItem::query()->with(['warehouse']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name']);

        return Inertia::render('inventory/index', [
            'items' => $items,
            'warehouses' => $warehouses,
            'totalValue' => $this->queryService->getTotalInventoryValue(),
            'lowStockCount' => $this->queryService->getLowStockItems(5)->count(),
            'filters' => $request->only(['search', 'warehouse_id']),
        ]);
    }

    public function show(InventoryItem $item): Response
    {
        $this->authorize('view', $item);

        $item->load(['warehouse']);

        $transactions = $item->transactions()
            ->with(['creator'])
            ->latest()
            ->paginate(15);

        return Inertia::render('inventory/show', [
            'item' => $item,
            'transactions' => $transactions,
        ]);
    }

    public function rebuild(InventoryItem $item): RedirectResponse
    {
        $this->authorize('approveAdjustment', InventoryItem::class);

        $this->projectionService->rebuildProjectionFromLedger($item);

        return back()->with('success', 'Inventory projections rebuilt successfully from ledger.');
    }
}
