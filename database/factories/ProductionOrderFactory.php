<?php

namespace Database\Factories;

use App\Enums\ProductionOrderStatus;
use App\Enums\ProductionPriority;
use App\Models\Customer;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionOrderFactory extends Factory
{
    protected $model = ProductionOrder::class;

    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'customer_id' => Customer::factory(),
            'subject' => $this->faker->sentence(),
            'status' => ProductionOrderStatus::Draft,
            'priority' => ProductionPriority::Normal,
            'target_completion_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 1000000,
            'tax_amount' => 110000,
            'total_amount' => 1110000,
            'created_by' => User::factory(),
        ];
    }
}
