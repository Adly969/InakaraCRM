<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\CrmPipelineStage;
use App\Models\Opportunity;
use App\Models\User;
use DomainException;

class TransitionValidator
{
    /**
     * Validate the transition from current stage to the target stage.
     *
     * @throws DomainException
     */
    public function validate(Opportunity $opportunity, CrmPipelineStage $toStage, User $user): void
    {
        $currentStage = $opportunity->stage;

        if (! $currentStage) {
            return; // No stage set yet, allow initial stage binding
        }

        if ($currentStage->id === $toStage->id) {
            return; // No change, valid
        }

        // Allow Owners, Admins, and Managers to bypass sequence constraints
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value)) {
            return;
        }

        $currentSeq = $currentStage->stage_sequence;
        $targetSeq = $toStage->stage_sequence;

        // Allow closing (Won/Lost stage) from any stage
        // In the default seeder, Won has sequence 6, Lost has sequence 7.
        // Let's identify won/lost stages by name or sequence.
        $isClosingStage = in_array(strtolower($toStage->name), ['won', 'lost']);
        if ($isClosingStage) {
            return;
        }

        // Enforce sequential pipeline progression (cannot jump ahead more than 1 stage)
        if ($targetSeq > $currentSeq + 1) {
            throw new DomainException(
                "Cannot skip pipeline stages (transitioning from {$currentStage->name} to {$toStage->name} is blocked)."
            );
        }

        // Cannot move backwards more than 1 stage either without manager approval
        if ($targetSeq < $currentSeq - 1) {
            throw new DomainException(
                'Cannot jump backwards multiple stages without manager approval.'
            );
        }
    }
}
