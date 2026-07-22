<?php

namespace Database\Seeders;

use App\Models\CrmLossReason;
use App\Models\CrmPipelineDefinition;
use App\Models\CrmPipelineStage;
use Illuminate\Database\Seeder;

class CrmPipelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Default Pipeline
        $pipeline = CrmPipelineDefinition::firstOrCreate(
            ['is_default' => true],
            [
                'name' => 'Sales Pipeline',
                'description' => 'Standard B2B CRM Pipeline',
                'is_active' => true,
            ]
        );

        // 2. Create default stages under the pipeline
        $stages = [
            ['name' => 'Qualification', 'probability' => 10.00, 'stage_sequence' => 1],
            ['name' => 'Discovery', 'probability' => 20.00, 'stage_sequence' => 2],
            ['name' => 'Proposal', 'probability' => 50.00, 'stage_sequence' => 3],
            ['name' => 'Negotiation', 'probability' => 80.00, 'stage_sequence' => 4],
            ['name' => 'Verbal Commit', 'probability' => 90.00, 'stage_sequence' => 5],
            ['name' => 'Won', 'probability' => 100.00, 'stage_sequence' => 6],
            ['name' => 'Lost', 'probability' => 0.00, 'stage_sequence' => 7],
        ];

        foreach ($stages as $stage) {
            CrmPipelineStage::firstOrCreate(
                [
                    'pipeline_definition_id' => $pipeline->id,
                    'stage_sequence' => $stage['stage_sequence'],
                ],
                [
                    'name' => $stage['name'],
                    'probability' => $stage['probability'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Create default Loss Reasons
        $lossReasons = [
            'Pricing/Budget',
            'Competitor Advantage',
            'Lost Contact / Unresponsive',
            'Project Cancelled',
            'Feature Gap',
        ];

        foreach ($lossReasons as $reason) {
            CrmLossReason::firstOrCreate(
                ['name' => $reason],
                ['is_active' => true]
            );
        }
    }
}
