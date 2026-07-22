<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OpportunityStageChanged
{
    use Dispatchable;

    public function __construct(
        public readonly int $opportunityId,
        public readonly ?int $fromStageId,
        public readonly int $toStageId,
        public readonly int $changedBy,
        public readonly ?int $companyId = null,
        public readonly ?int $branchId = null
    ) {}
}
