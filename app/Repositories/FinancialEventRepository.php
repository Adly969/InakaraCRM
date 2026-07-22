<?php

namespace App\Repositories;

use App\DTO\BusinessEventPayload;
use App\Models\FinancialEvent;

class FinancialEventRepository implements FinancialEventRepositoryInterface
{
    public function logEvent(BusinessEventPayload $dto, string $idempotencyKey): FinancialEvent
    {
        return FinancialEvent::create([
            'event_uuid' => $dto->eventUuid,
            'company_id' => $dto->companyId,
            'branch_id' => $dto->branchId,
            'event_type' => $dto->eventType,
            'source_module' => $dto->sourceModule,
            'payload' => $dto->payload,
            'status' => 'RECEIVED',
            'idempotency_key' => $idempotencyKey,
            'correlation_id' => $dto->correlationId,
        ]);
    }

    public function findByUuid(string $uuid): ?FinancialEvent
    {
        return FinancialEvent::where('event_uuid', $uuid)->first();
    }

    public function updateStatus(int $id, string $status): void
    {
        FinancialEvent::where('id', $id)->update(['status' => $status]);
    }
}
