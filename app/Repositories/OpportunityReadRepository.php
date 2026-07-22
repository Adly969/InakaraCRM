<?php

namespace App\Repositories;

use App\Models\CrmPipelineStage;
use App\Models\Opportunity;
use Illuminate\Support\Facades\DB;

class OpportunityReadRepository
{
    /**
     * Get opportunities structured for the Kanban board view.
     */
    public function getKanbanData(): array
    {
        // Fetch active stages with count and aggregated value calculations
        $stages = CrmPipelineStage::where('is_active', true)
            ->orderBy('stage_sequence')
            ->get();

        $opportunities = Opportunity::select([
            'id',
            'title',
            'deal_value',
            'pipeline_stage_id',
            'status',
            'assigned_to',
            'customer_id',
            'expected_close_date',
        ])
            ->with([
                'customer:id,name,company_name',
                'assignedTo:id,name',
                'stage:id,name,probability',
            ])
            ->get();

        $stageGroups = [];
        foreach ($stages as $stage) {
            $stageOpps = $opportunities->where('pipeline_stage_id', $stage->id)->values();

            // Expected / Weighted Revenue calculation
            $totalDealValue = (float) $stageOpps->sum('deal_value');
            $probability = $stage->probability ? $stage->probability->value : 0;
            $weightedRevenue = round($totalDealValue * ($probability / 100), 2);

            $stageGroups[] = [
                'stage_id' => $stage->id,
                'stage_name' => $stage->name,
                'probability' => $probability,
                'forecast_category' => $stage->forecast_category,
                'total_deal_value' => $totalDealValue,
                'weighted_revenue' => $weightedRevenue,
                'opportunities_count' => $stageOpps->count(),
                'opportunities' => $stageOpps->toArray(),
            ];
        }

        return $stageGroups;
    }

    /**
     * Get aggregated monthly forecasting statistics.
     */
    public function getForecastReport(): array
    {
        return Opportunity::select('crm_opportunities.status', DB::raw('SUM(deal_value) as total_value'))
            ->groupBy('crm_opportunities.status')
            ->get()
            ->toArray();
    }
}
