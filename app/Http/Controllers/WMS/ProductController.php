<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Services\WMS\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected ProductService $productService)
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request): Response
    {
        $query = Product::query()->with(['category', 'brand', 'primaryUom']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = ProductCategory::all(['id', 'name']);
        $brands = ProductBrand::all(['id', 'name']);

        return Inertia::render('master/products/index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('master/products/create', [
            'categories' => ProductCategory::all(['id', 'name']),
            'brands' => ProductBrand::all(['id', 'name']),
            'units' => Unit::all(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'sku' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:100',
            'product_type' => 'required|string',
            'category_id' => 'nullable|exists:product_categories,id',
            'brand_id' => 'nullable|exists:product_brands,id',
            'primary_uom_id' => 'nullable|exists:units,id',
            'safety_stock' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'abc_classification' => 'nullable|string|max:1',
            'is_batch_tracked' => 'nullable|boolean',
            'is_serial_tracked' => 'nullable|boolean',
        ]);

        $tenantId = (string) Auth::user()->tenant_id;
        $userId = Auth::id();

        $this->productService->createProduct($validated, $tenantId, $userId);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): Response
    {
        $product->load(['category', 'brand', 'primaryUom', 'variants', 'digitalAssets', 'attachments', 'balances.warehouse']);

        return Inertia::render('master/products/show', [
            'product' => $product,
        ]);
    }
}
