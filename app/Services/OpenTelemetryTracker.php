<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class OpenTelemetryTracker
{
    /**
     * Start an OpenTelemetry-compatible tracing span.
     *
     * @return array Span details
     */
    public function startSpan(string $spanName, string $correlationId): array
    {
        Log::info("[OTEL-SPAN-START] Correlation ID: {$correlationId} - Span: {$spanName}");

        return [
            'name' => $spanName,
            'correlation_id' => $correlationId,
            'start_time' => microtime(true),
        ];
    }

    /**
     * End the span and calculate duration.
     */
    public function endSpan(array $span): void
    {
        $duration = (microtime(true) - $span['start_time']) * 1000;
        Log::info(sprintf(
            '[OTEL-SPAN-END] Correlation ID: %s - Span: %s - Duration: %.2fms',
            $span['correlation_id'],
            $span['name'],
            $duration
        ));
    }
}
