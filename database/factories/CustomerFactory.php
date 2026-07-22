<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
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
            'name' => fake()->name(),
            'company_name' => fake()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'type' => fake()->randomElement(['individual', 'organization']),
            'status' => CustomerStatus::Active,
            'notes' => fake()->sentence(),
            'assigned_to' => null,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }

    /**
     * Indicate that the customer is blacklisted.
     */
    public function blacklisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatus::Blacklisted,
        ]);
    }
}
