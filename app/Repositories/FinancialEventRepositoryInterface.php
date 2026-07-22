<?php

namespace App\Repositories;

use App\DTO\BusinessEventPayload;
use App\Models\FinancialEvent;

interface FinancialEventRepositoryInterface
{
    public function logEvent(BusinessEventPayload $dto, string $idempotencyKey): FinancialEvent;

    public function findByUuid(string $uuid): ?FinancialEvent;

    public function updateStatus(int $id, string $status): void;
}
