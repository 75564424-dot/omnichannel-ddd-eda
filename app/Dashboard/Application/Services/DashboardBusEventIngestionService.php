<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Services;

use App\Dashboard\Domain\Hooks\AfterEventFeedInsertHookInterface;
use App\Dashboard\Domain\ValueObjects\EventImpact;
use App\Dashboard\Domain\ValueObjects\EventOrigin;
use App\Dashboard\Infrastructure\Projectors\EventFeedProjector;
use App\Shared\Logging\StructuredLogContext;
use Illuminate\Support\Facades\Log;

/**
 * Projects validated bus envelopes into the dashboard event feed read model.
 */
final class DashboardBusEventIngestionService
{
    public function __construct(
        private readonly EventFeedProjector $feedProjector,
        private readonly DashboardIngestionGateService $ingestionGate,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function ingest(array $data): void
    {
        $eventId   = $data['event_id'] ?? '';
        $eventType = $data['event'] ?? $data['event_type'] ?? 'Unknown';

        if ($eventId === '' || $eventId === 'unknown') {
            Log::info('Dashboard: bus ingestion skipped (no event_id)', [
                'event_type' => $eventType,
            ]);

            return;
        }

        if (! $this->ingestionGate->allows($eventType, $data)) {
            Log::info('Dashboard: bus ingestion skipped (ingestion gate)', [
                'event_type' => $eventType,
                'event_id'   => $eventId,
            ]);

            return;
        }

        $origin = EventOrigin::fromEventPayload($eventType, $data);
        $impact = EventImpact::fromGenericEnvelope($eventType, $data);

        $newId = $this->feedProjector->project(
            eventId:    $eventId,
            eventType:  $eventType,
            origin:     $origin,
            impact:     $impact,
            occurredAt: $data['occurred_at'] ?? now()->toDateTimeString(),
            rawPayload: $data,
            correlationId: $this->resolveCorrelationId($data),
        );

        if ($newId === null) {
            return;
        }

        foreach (config('dashboard.after_feed_insert_hooks', []) as $hookClass) {
            $hook = app($hookClass);
            if ($hook instanceof AfterEventFeedInsertHookInterface) {
                $hook->onNewFeedRow($eventType, $data);
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveCorrelationId(array $data): ?string
    {
        $candidate = $data['correlation_id'] ?? null;

        if (is_string($candidate) && $candidate !== '') {
            return $candidate;
        }

        $ctx = StructuredLogContext::toArray();

        return $ctx['correlation_id'] ?? null;
    }
}
