<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JournalPostedWithSuspense
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $journalId,
        public float $suspenseAmount,
        public int $companyId
    ) {}
}
