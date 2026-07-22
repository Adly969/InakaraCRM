<?php

namespace Database\Factories;

use App\Enums\OpportunityStatus;
use App\Models\CrmPipelineStage;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => null,
            'customer_id' => Customer::factory(),
            'title' => fake()->sentence(3),
            'pipeline_stage_id' => CrmPipelineStage::first() ?? CrmPipelineStage::factory(),
            'status' => OpportunityStatus::Qualification,
            'deal_value' => fake()->randomFloat(2, 5000000, 50000000),
            'expected_close_date' => fake()->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
            'loss_reason_id' => null,
            'loss_notes' => null,
            'assigned_to' => User::factory(),
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
