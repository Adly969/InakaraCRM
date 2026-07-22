<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\InventoryBalance;
use App\Models\Warehouse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryBalanceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryBalance::class);

        $query = InventoryBalance::query()->with(['warehouse', 'bin', 'product.primaryUom']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        $balances = $query->latest()->paginate(15)->withQueryString();
        $warehouses = Warehouse::all(['id', 'code', 'name']);

        return Inertia::render('inventory/index', [
            'balances' => $balances,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'warehouse_id']),
        ]);
    }
}
