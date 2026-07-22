<?php

namespace App\DTO;

class GatewayReceipt
{
    public function __construct(
        public string $eventUuid,
        public string $journalNumber,
        public string $status,
        public string $signature
    ) {}
}
