<?php

namespace Database\Factories;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
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
            'source' => fake()->randomElement(LeadSource::cases()),
            'status' => LeadStatus::New,
            'disqualification_reason' => null,
            'assigned_to' => null,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }

    /**
     * Indicate that the lead is disqualified.
     */
    public function disqualified(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeadStatus::Disqualified,
            'disqualification_reason' => $reason ?? fake()->sentence(),
        ]);
    }
}
