<?php

namespace Database\Factories;

use App\Models\QuotationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 10);
        $unitPrice = fake()->randomFloat(2, 10000, 100000);

        return [
            'quotation_id' => null,
            'description' => fake()->words(3, true),
            'quantity' => $quantity,
            'unit' => 'pcs',
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'sort_order' => 0,
        ];
    }
}
