<?php

namespace App\DTO;

use Illuminate\Support\Str;

class BusinessEventPayload
{
    public function __construct(
        public string $eventUuid,
        public int $companyId,
        public int $branchId,
        public string $eventType,
        public string $sourceModule,
        public array $payload,
        public string $correlationId
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventUuid: $data['event_uuid'] ?? (string) Str::uuid(),
            companyId: (int) $data['company_id'],
            branchId: (int) $data['branch_id'],
            eventType: $data['event_type'],
            sourceModule: $data['source_module'],
            payload: $data['payload'] ?? [],
            correlationId: $data['correlation_id'] ?? (string) Str::uuid()
        );
    }

    public function toArray(): array
    {
        return [
            'event_uuid' => $this->eventUuid,
            'company_id' => $this->companyId,
            'branch_id' => $this->branchId,
            'event_type' => $this->eventType,
            'source_module' => $this->sourceModule,
            'payload' => $this->payload,
            'correlation_id' => $this->correlationId,
        ];
    }
}
