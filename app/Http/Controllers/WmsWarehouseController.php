<?php

namespace App\Http\Controllers;

use App\Models\WmsLocation;
use App\Models\WmsTask;
use App\Models\WmsWarehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WmsWarehouseController extends Controller
{
    /**
     * Display a listing of WMS Warehouses and occupancy heatmaps.
     */
    public function index(Request $request): Response
    {
        $warehouses = WmsWarehouse::withCount('locations')
            ->latest()
            ->paginate(10);

        $tasksCount = WmsTask::count();
        $locationsCount = WmsLocation::count();

        return Inertia::render('wms/dashboard', [
            'warehouses' => $warehouses,
            'stats' => [
                'total_tasks' => $tasksCount,
                'total_locations' => $locationsCount,
            ],
        ]);
    }
}
