<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderItemFactory extends Factory
{
    protected $model = SalesOrderItem::class;

    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'description' => $this->faker->words(3, true),
            'quantity' => 2,
            'unit' => 'pcs',
            'unit_price' => 500000,
            'total_price' => 1000000,
            'sort_order' => 0,
        ];
    }
}
