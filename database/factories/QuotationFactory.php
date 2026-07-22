<?php

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_no' => null,
            'lead_id' => null,
            'customer_id' => null,
            'subject' => fake()->sentence(4),
            'revision' => 1,
            'status' => QuotationStatus::Draft,
            'valid_until' => Carbon::now()->addDays(14)->toDateString(),
            'notes' => fake()->paragraph(),
            'currency' => 'IDR',
            'tax_rate' => 11.00,
            'subtotal' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 0.00,
            'assigned_to' => null,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
