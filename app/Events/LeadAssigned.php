<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class LeadAssigned
{
    use Dispatchable;

    public function __construct(
        public readonly int $leadId,
        public readonly int $assignedTo,
        public readonly int $assignedBy,
        public readonly ?int $companyId = null,
        public readonly ?int $branchId = null
    ) {}
}
