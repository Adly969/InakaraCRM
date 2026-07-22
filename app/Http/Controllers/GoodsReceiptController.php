<?php

namespace App\Http\Controllers;

use App\Enums\GoodsReceiptStatus;
use App\Enums\ProductionOrderStatus;
use App\Http\Requests\StoreGoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\ProductionOrder;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GoodsReceiptController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected GoodsReceiptService $receiptService)
    {
        $this->authorizeResource(GoodsReceipt::class, 'goods_receipt');
    }

    public function index(Request $request): Response
    {
        $query = GoodsReceipt::query()->with(['warehouse', 'productionOrder']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reference_no', 'like', "%{$search}%");
        }

        $goodsReceipts = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('goods-receipts/index', [
            'goodsReceipts' => $goodsReceipts,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $productionOrders = ProductionOrder::where('status', ProductionOrderStatus::Completed->value)->get(['id', 'reference_no']);
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name']);

        $selectedPo = null;
        if ($request->filled('production_order_id')) {
            $selectedPo = ProductionOrder::with('items.salesOrderItem')->find($request->input('production_order_id'));
        }

        return Inertia::render('goods-receipts/create', [
            'productionOrders' => $productionOrders,
            'warehouses' => $warehouses,
            'selectedPo' => $selectedPo,
        ]);
    }

    public function store(StoreGoodsReceiptRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $referenceNo = 'GR-'.strtoupper(uniqid());

        $gr = DB::transaction(function () use ($data, $referenceNo) {
            $gr = GoodsReceipt::create([
                'reference_no' => $referenceNo,
                'production_order_id' => $data['production_order_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'received_date' => $data['received_date'],
                'notes' => $data['notes'] ?? null,
                'status' => GoodsReceiptStatus::Draft,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['items'] as $index => $itemData) {
                $gr->items()->create([
                    'production_order_item_id' => $itemData['production_order_item_id'] ?? null,
                    'sku' => $itemData['sku'],
                    'description' => $itemData['description'],
                    'quantity_received' => $itemData['quantity_received'],
                    'unit' => $itemData['unit'] ?? 'pcs',
                    'unit_cost' => $itemData['unit_cost'] ?? 0.00,
                    'sort_order' => $index,
                ]);
            }

            return $gr;
        });

        return redirect()->route('goods-receipts.show', $gr->id)
            ->with('success', 'Goods Receipt draft created successfully.');
    }

    public function show(GoodsReceipt $goodsReceipt): Response
    {
        $goodsReceipt->load(['warehouse', 'productionOrder', 'items.productionOrderItem', 'creator', 'updater']);

        return Inertia::render('goods-receipts/show', [
            'goodsReceipt' => $goodsReceipt,
        ]);
    }

    public function receive(Request $request, GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $this->authorize('approve', $goodsReceipt);

        $request->validate([
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        $this->receiptService->receive($goodsReceipt, $request->input('remark'));

        return back()->with('success', 'Goods Receipt posted and inventory items updated successfully.');
    }
}
