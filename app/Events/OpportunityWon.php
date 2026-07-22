<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OpportunityWon
{
    use Dispatchable;

    public function __construct(
        public readonly int $opportunityId,
        public readonly ?int $companyId = null,
        public readonly ?int $branchId = null
    ) {}
}
