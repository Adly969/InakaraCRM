<?php

namespace Database\Factories;

use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionOrderItemFactory extends Factory
{
    protected $model = ProductionOrderItem::class;

    public function definition(): array
    {
        return [
            'production_order_id' => ProductionOrder::factory(),
            'description' => $this->faker->words(3, true),
            'quantity' => 2,
            'unit' => 'pcs',
            'unit_price' => 500000,
            'total_price' => 1000000,
            'sort_order' => 0,
        ];
    }
}
