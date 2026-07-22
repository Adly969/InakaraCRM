<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JournalPosted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $journalId,
        public int $postedBy,
        public int $companyId,
        public string $postedAt,
        public string $signature
    ) {}
}
