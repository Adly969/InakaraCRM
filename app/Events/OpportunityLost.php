<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OpportunityLost
{
    use Dispatchable;

    public function __construct(
        public readonly int $opportunityId,
        public readonly int $lossReasonId,
        public readonly ?string $lossNotes,
        public readonly ?int $companyId = null,
        public readonly ?int $branchId = null
    ) {}
}
