<?php

namespace App\Services;

use App\Enums\OpportunityStatus;
use App\Events\OpportunityLost;
use App\Events\OpportunityStageChanged;
use App\Events\OpportunityWon;
use App\Models\CrmPipelineStage;
use App\Models\CrmStageHistory;
use App\Models\Opportunity;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class PipelineService
{
    public function __construct(protected TransitionValidator $transitionValidator) {}

    /**
     * Move an opportunity to a different pipeline stage.
     *
     * @throws DomainException If the opportunity is already closed.
     */
    public function changeStage(Opportunity $opportunity, CrmPipelineStage $toStage, User $user): Opportunity
    {
        if ($opportunity->status->isClosed()) {
            throw new DomainException('Cannot change stage on a closed opportunity.');
        }

        // Validate the transition sequence rules
        $this->transitionValidator->validate($opportunity, $toStage, $user);

        return DB::transaction(function () use ($opportunity, $toStage, $user) {
            $fromStage = $opportunity->stage;

            // Calculate seconds spent in the previous stage
            $lastHistory = CrmStageHistory::where('opportunity_id', $opportunity->id)
                ->latest('created_at')
                ->first();

            $duration = $lastHistory
                ? (int) now()->diffInSeconds($lastHistory->created_at)
                : (int) now()->diffInSeconds($opportunity->created_at);

            CrmStageHistory::create([
                'opportunity_id' => $opportunity->id,
                'from_stage_id' => $fromStage ? $fromStage->id : $toStage->id,
                'to_stage_id' => $toStage->id,
                'changed_by' => $user->id,
                'duration_in_seconds' => $duration,
            ]);

            $opportunity->pipeline_stage_id = $toStage->id;
            $opportunity->updated_by = $user->id;
            $opportunity->save();

            OpportunityStageChanged::dispatch(
                $opportunity->id,
                $fromStage ? $fromStage->id : null,
                $toStage->id,
                $user->id,
                $opportunity->company_id,
                $opportunity->branch_id
            );

            return $opportunity;
        });
    }

    /**
     * Close an opportunity as Won.
     *
     * @throws DomainException If already closed.
     */
    public function win(Opportunity $opportunity, User $user): Opportunity
    {
        if ($opportunity->status->isClosed()) {
            throw new DomainException('Opportunity is already closed.');
        }

        return DB::transaction(function () use ($opportunity, $user) {
            $wonStage = CrmPipelineStage::where('name', 'Won')->first();
            $fromStage = $opportunity->stage;

            $opportunity->status = OpportunityStatus::Won;
            if ($wonStage) {
                $opportunity->pipeline_stage_id = $wonStage->id;
            }
            $opportunity->updated_by = $user->id;
            $opportunity->save();

            if ($wonStage && $fromStage && $wonStage->id !== $fromStage->id) {
                $lastHistory = CrmStageHistory::where('opportunity_id', $opportunity->id)
                    ->latest('created_at')
                    ->first();

                $duration = $lastHistory
                    ? (int) now()->diffInSeconds($lastHistory->created_at)
                    : (int) now()->diffInSeconds($opportunity->created_at);

                CrmStageHistory::create([
                    'opportunity_id' => $opportunity->id,
                    'from_stage_id' => $fromStage->id,
                    'to_stage_id' => $wonStage->id,
                    'changed_by' => $user->id,
                    'duration_in_seconds' => $duration,
                ]);
            }

            OpportunityWon::dispatch(
                $opportunity->id,
                $opportunity->company_id,
                $opportunity->branch_id
            );

            return $opportunity;
        });
    }

    /**
     * Close an opportunity as Lost.
     *
     * @throws DomainException If already closed or missing loss reason.
     */
    public function lose(Opportunity $opportunity, int $lossReasonId, ?string $lossNotes, User $user): Opportunity
    {
        if ($opportunity->status->isClosed()) {
            throw new DomainException('Opportunity is already closed.');
        }

        return DB::transaction(function () use ($opportunity, $lossReasonId, $lossNotes, $user) {
            $lostStage = CrmPipelineStage::where('name', 'Lost')->first();
            $fromStage = $opportunity->stage;

            $opportunity->status = OpportunityStatus::Lost;
            $opportunity->loss_reason_id = $lossReasonId;
            $opportunity->loss_notes = $lossNotes;
            if ($lostStage) {
                $opportunity->pipeline_stage_id = $lostStage->id;
            }
            $opportunity->updated_by = $user->id;
            $opportunity->save();

            if ($lostStage && $fromStage && $lostStage->id !== $fromStage->id) {
                $lastHistory = CrmStageHistory::where('opportunity_id', $opportunity->id)
                    ->latest('created_at')
                    ->first();

                $duration = $lastHistory
                    ? (int) now()->diffInSeconds($lastHistory->created_at)
                    : (int) now()->diffInSeconds($opportunity->created_at);

                CrmStageHistory::create([
                    'opportunity_id' => $opportunity->id,
                    'from_stage_id' => $fromStage->id,
                    'to_stage_id' => $lostStage->id,
                    'changed_by' => $user->id,
                    'duration_in_seconds' => $duration,
                ]);
            }

            OpportunityLost::dispatch(
                $opportunity->id,
                $lossReasonId,
                $lossNotes,
                $opportunity->company_id,
                $opportunity->branch_id
            );

            return $opportunity;
        });
    }
}
