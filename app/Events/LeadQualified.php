<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class LeadQualified
{
    use Dispatchable;

    public function __construct(
        public readonly int $leadId,
        public readonly int $qualifiedBy,
        public readonly ?int $companyId = null,
        public readonly ?int $branchId = null
    ) {}
}
