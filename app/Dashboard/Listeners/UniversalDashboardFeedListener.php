<?php

declare(strict_types=1);

namespace App\Dashboard\Listeners;

use App\Dashboard\Domain\Hooks\AfterEventFeedInsertHookInterface;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\EventImpact;
use App\Dashboard\Domain\ValueObjects\EventOrigin;
use App\Dashboard\Infrastructure\Projectors\EventFeedProjector;
use App\Shared\EventBus\PlatformWildcardPayload;
use Illuminate\Support\Facades\Log;

/**
 * Records bus-observable events into the dashboard read-model when envelopes carry an event_id.
 */
final class UniversalDashboardFeedListener
{
    public function __construct(
        private readonly EventFeedProjector $feedProjector,
        private readonly NodeStatusRepositoryInterface $nodeStatusRepository,
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
        $this->projectIfValid($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function projectIfValid(array $data): void
    {
        $eventId   = $data['event_id'] ?? '';
        $eventType = $data['event'] ?? $data['event_type'] ?? 'Unknown';

        if ($eventId === '' || $eventId === 'unknown') {
            Log::info('Dashboard: UniversalDashboardFeedListener skipped (no event_id)', [
                'event_type' => $eventType,
            ]);

            return;
        }

        if (! $this->passesIngestionGate($eventType, $data)) {
            Log::info('Dashboard: UniversalDashboardFeedListener skipped (ingestion gate)', [
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

    private function passesIngestionGate(string $eventType, array $data): bool
    {
        $gates = config('dashboard.ingestion_gates', []);
        $spec  = $gates[$eventType] ?? null;
        if ($spec === null) {
            return true;
        }

        if (($spec['type'] ?? '') !== 'channel_nodes') {
            return true;
        }

        $channelField = (string) ($spec['channel_field'] ?? 'channel');
        $ch           = strtoupper(trim((string) ($data[$channelField] ?? '')));
        $webVal       = strtoupper((string) ($spec['web_value'] ?? 'WEB'));
        $nodeKey      = $ch === $webVal ? (string) ($spec['web_node'] ?? 'web') : (string) ($spec['default_node'] ?? 'default');

        return $this->nodeStatusRepository->middlewareEventsEnabled($nodeKey);
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

        $ctx = \App\Shared\Logging\StructuredLogContext::toArray();

        return $ctx['correlation_id'] ?? null;
    }
}
