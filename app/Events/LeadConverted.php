<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class LeadConverted
{
    use Dispatchable;

    public function __construct(
        public readonly int $leadId,
        public readonly int $opportunityId,
        public readonly int $convertedBy,
        public readonly ?int $companyId = null,
        public readonly ?int $branchId = null
    ) {}
}
