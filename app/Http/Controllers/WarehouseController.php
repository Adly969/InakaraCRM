<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreWarehouseRequest;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected WarehouseService $warehouseService)
    {
        $this->authorizeResource(Warehouse::class, 'warehouse');
    }

    public function index(Request $request): Response
    {
        $query = Warehouse::query()->with('manager');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('warehouses/index', [
            'warehouses' => $warehouses,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        $managers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', [UserRole::Owner->value, UserRole::Manager->value, UserRole::Gudang->value]);
        })->get(['id', 'name']);

        return Inertia::render('warehouses/create', [
            'managers' => $managers,
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $this->warehouseService->create($data);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function edit(Warehouse $warehouse): Response
    {
        $managers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', [UserRole::Owner->value, UserRole::Manager->value, UserRole::Gudang->value]);
        })->get(['id', 'name']);

        return Inertia::render('warehouses/edit', [
            'warehouse' => $warehouse,
            'managers' => $managers,
        ]);
    }

    public function update(StoreWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $this->warehouseService->update($warehouse, $data);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->deleted_by = Auth::id();
        $warehouse->save();
        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
