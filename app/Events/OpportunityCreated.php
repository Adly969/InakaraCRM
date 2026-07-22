<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OpportunityCreated
{
    use Dispatchable;

    public function __construct(
        public readonly int $opportunityId,
        public readonly string $title,
        public readonly int $customerId,
        public readonly float $dealValue,
        public readonly int $assignedTo,
        public readonly ?int $companyId = null,
        public readonly ?int $branchId = null
    ) {}
}
