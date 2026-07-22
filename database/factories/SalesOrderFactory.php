<?php

namespace Database\Factories;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'reference_no' => 'SO-'.$this->faker->unique()->numberBetween(100000, 999999),
            'customer_id' => Customer::factory(),
            'subject' => $this->faker->sentence(),
            'status' => SalesOrderStatus::Draft->value,
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 1000000,
            'tax_amount' => 110000,
            'total_amount' => 1110000,
            'created_by' => User::factory(),
        ];
    }
}
