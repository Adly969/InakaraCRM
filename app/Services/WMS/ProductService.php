<?php

namespace App\Services\WMS;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductService
{
    public function createProduct(array $data, string $tenantId, int $userId): Product
    {
        $sku = ! empty($data['sku']) ? strtoupper($data['sku']) : $this->generateSku($data['name']);

        return Product::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'company_id' => $data['company_id'] ?? null,
            'sku' => $sku,
            'barcode' => $data['barcode'] ?? null,
            'name' => $data['name'],
            'product_type' => $data['product_type'] ?? 'finished_goods',
            'category_id' => $data['category_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'primary_uom_id' => $data['primary_uom_id'] ?? null,
            'safety_stock' => $data['safety_stock'] ?? 0,
            'reorder_point' => $data['reorder_point'] ?? 0,
            'lead_time_days' => $data['lead_time_days'] ?? 0,
            'abc_classification' => $data['abc_classification'] ?? 'C',
            'is_batch_tracked' => $data['is_batch_tracked'] ?? false,
            'is_serial_tracked' => $data['is_serial_tracked'] ?? false,
            'created_by' => $userId,
        ]);
    }

    public function generateSku(string $name): string
    {
        $words = explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $name));
        $prefix = '';
        foreach ($words as $word) {
            if (! empty($word)) {
                $prefix .= strtoupper(substr($word, 0, 3));
            }
        }
        $prefix = substr($prefix, 0, 9);

        return sprintf('%s-%04d', $prefix ?: 'PRD', rand(1000, 9999));
    }
}
