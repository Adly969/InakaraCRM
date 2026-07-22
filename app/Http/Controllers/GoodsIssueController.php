<?php

namespace App\Http\Controllers;

use App\Enums\GoodsIssueStatus;
use App\Enums\SalesOrderStatus;
use App\Http\Requests\StoreGoodsIssueRequest;
use App\Models\GoodsIssue;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Services\GoodsIssueService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GoodsIssueController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected GoodsIssueService $issueService)
    {
        $this->authorizeResource(GoodsIssue::class, 'goods_issue');
    }

    public function index(Request $request): Response
    {
        $query = GoodsIssue::query()->with(['warehouse', 'salesOrder']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reference_no', 'like', "%{$search}%");
        }

        $goodsIssues = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('goods-issues/index', [
            'goodsIssues' => $goodsIssues,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request): Response
    {
        // Confirmed sales orders
        $salesOrders = SalesOrder::where('status', SalesOrderStatus::Confirmed->value)->get(['id', 'reference_no']);
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name']);

        $selectedSo = null;
        if ($request->filled('sales_order_id')) {
            $selectedSo = SalesOrder::with('items')->find($request->input('sales_order_id'));
        }

        return Inertia::render('goods-issues/create', [
            'salesOrders' => $salesOrders,
            'warehouses' => $warehouses,
            'selectedSo' => $selectedSo,
        ]);
    }

    public function store(StoreGoodsIssueRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $referenceNo = 'GI-'.strtoupper(uniqid());

        $gi = DB::transaction(function () use ($data, $referenceNo) {
            $gi = GoodsIssue::create([
                'reference_no' => $referenceNo,
                'sales_order_id' => $data['sales_order_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'issued_date' => $data['issued_date'],
                'notes' => $data['notes'] ?? null,
                'status' => GoodsIssueStatus::Draft,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['items'] as $index => $itemData) {
                $gi->items()->create([
                    'sales_order_item_id' => $itemData['sales_order_item_id'] ?? null,
                    'sku' => $itemData['sku'],
                    'description' => $itemData['description'],
                    'quantity_issued' => $itemData['quantity_issued'],
                    'unit' => $itemData['unit'] ?? 'pcs',
                    'sort_order' => $index,
                ]);
            }

            return $gi;
        });

        return redirect()->route('goods-issues.show', $gi->id)
            ->with('success', 'Goods Issue draft created successfully.');
    }

    public function show(GoodsIssue $goodsIssue): Response
    {
        $goodsIssue->load(['warehouse', 'salesOrder', 'items.salesOrderItem', 'creator', 'updater']);

        return Inertia::render('goods-issues/show', [
            'goodsIssue' => $goodsIssue,
        ]);
    }

    public function issue(Request $request, GoodsIssue $goodsIssue): RedirectResponse
    {
        $this->authorize('approve', $goodsIssue);

        $request->validate([
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        $this->issueService->issue($goodsIssue, $request->input('remark'));

        return back()->with('success', 'Goods Issue posted and inventory items updated successfully.');
    }
}
