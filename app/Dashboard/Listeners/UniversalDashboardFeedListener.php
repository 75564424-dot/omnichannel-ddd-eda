<?php

declare(strict_types=1);

namespace App\Dashboard\Listeners;

use App\Dashboard\Application\Services\DashboardBusEventIngestionService;
use App\Shared\EventBus\PlatformWildcardPayload;

/**
 * Records bus-observable events into the dashboard read-model when envelopes carry an event_id.
 */
final class UniversalDashboardFeedListener
{
    public function __construct(
        private readonly DashboardBusEventIngestionService $ingestion,
    ) {}

    /**
     * @param  string|array<string, mixed>  $first
     * @param  array<int, mixed>|null  $second
     */
    public function handle(mixed $first, mixed $second = null): void
    {
        [$eventName, $data] = PlatformWildcardPayload::parse($first, $second);
        if ($eventName !== null && ! PlatformWildcardPayload::shouldObserveWildcardEvent($eventName)) {
            return;
        }
        if (empty($data['event'] ?? null) && empty($data['event_type'] ?? null) && is_string($eventName)) {
            $data['event']      = $eventName;
            $data['event_type'] = $eventName;
        }

        $this->ingestion->ingest($data);
    }
}
