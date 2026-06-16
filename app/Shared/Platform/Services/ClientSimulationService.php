<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Middleware\Application\Services\EventPublisherService;
use App\Middleware\Application\UseCases\GetEventQueueUseCase;
use App\Middleware\Application\UseCases\SyncConfiguredModulesToRegistryUseCase;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * End-to-end client simulation orchestration (Plan_SimulacionClientes.md).
 */
final class ClientSimulationService
{
    public function __construct(
        private readonly ClientFixtureLoader $fixtures,
        private readonly PlatformCatalogValidator $validator,
        private readonly SyncConfiguredModulesToRegistryUseCase $syncRegistry,
        private readonly EventPublisherService $publisher,
        private readonly GetEventQueueUseCase $eventQueue,
    ) {}

    /**
     * @return array{
     *     slug: string,
     *     validation_errors: list<string>,
     *     sync: array<string, int>|null,
     *     published: int,
     *     event_ids: list<string>,
     *     queue_matches: int
     * }
     */
    /**
     * @return array{total: int, interval_microseconds: int|null}
     */
    public static function resolvePublishPlan(int $events, ?int $eventsPerMinute, ?int $durationMinutes): array
    {
        if ($events < 0) {
            throw new RuntimeException('events must be >= 0');
        }

        $perMinute = $eventsPerMinute !== null && $eventsPerMinute > 0 ? $eventsPerMinute : null;
        $minutes   = $durationMinutes !== null && $durationMinutes > 0 ? $durationMinutes : null;

        $total = $events;
        if ($perMinute !== null) {
            if ($minutes !== null) {
                $total = $perMinute * $minutes;
            } elseif ($total <= 0) {
                $total = $perMinute;
            }
        }

        $intervalMicroseconds = $perMinute !== null
            ? (int) round(60_000_000 / $perMinute)
            : null;

        return [
            'total'                  => max(0, $total),
            'interval_microseconds'  => $intervalMicroseconds,
        ];
    }

    /**
     * @param list<array<string, mixed>>|null $sampleTemplates When set, skips fixture overlay and uses these templates.
     * @param callable(int, int, string, string): void|null $onPublished Current, total, event id, event type
     */
    public function simulate(
        string $slug,
        int $events = 10,
        bool $applyFixture = false,
        bool $skipValidate = false,
        bool $skipSync = false,
        ?int $eventsPerMinute = null,
        ?int $durationMinutes = null,
        ?array $sampleTemplates = null,
        ?callable $onPublished = null,
    ): array {
        $plan = self::resolvePublishPlan($events, $eventsPerMinute, $durationMinutes);
        $events = $plan['total'];
        $intervalMicroseconds = $plan['interval_microseconds'];

        if ($sampleTemplates === null) {
            if (! $this->fixtures->exists($slug)) {
                throw new RuntimeException("Unknown client fixture slug: {$slug}");
            }

            if ($applyFixture) {
                $this->fixtures->applyToFilesystem($slug);
            }

            $this->fixtures->applyToRuntimeConfig($slug);
        }

        $validationErrors = [];
        if (! $skipValidate) {
            $validationErrors = $this->validator->validate();
            if ($validationErrors !== []) {
                return [
                    'slug'              => $slug,
                    'validation_errors' => $validationErrors,
                    'sync'              => null,
                    'published'         => 0,
                    'event_ids'         => [],
                    'queue_matches'     => 0,
                ];
            }
        }

        $syncStats = null;
        if (! $skipSync) {
            $syncStats = $this->syncRegistry->execute();
        }

        $templates = $sampleTemplates ?? $this->fixtures->loadSampleEvents($slug);
        if ($events > 0 && $templates === []) {
            throw new RuntimeException('No sample events defined for simulation.');
        }

        $eventIds = [];
        // When total is fixed (rate × duration), honor the event count — not a wall-clock cap.
        // Per-event drain/sync can exceed duration_minutes; cutting early caused 5/10 style partial runs.
        $hasFixedEventCount = $events > 0 && $eventsPerMinute !== null && $eventsPerMinute > 0
            && $durationMinutes !== null && $durationMinutes > 0;
        $deadlineNs = (! $hasFixedEventCount && $durationMinutes !== null && $durationMinutes > 0)
            ? hrtime(true) + ($durationMinutes * 60 * 1_000_000_000)
            : null;

        if ($events > 0) {
            for ($i = 1; $i <= $events; $i++) {
                if ($deadlineNs !== null && hrtime(true) >= $deadlineNs) {
                    break;
                }

                $template = $templates[($i - 1) % count($templates)];
                $eventId  = Uuid::uuid4()->toString();
                $type     = trim((string) ($template['event_type'] ?? ''));
                if ($type === '') {
                    continue;
                }

                $payload = $this->buildPayload($template, $eventId, $type, $i);
                $this->publisher->publish([
                    'event_id'    => $eventId,
                    'event_type'  => $type,
                    'occurred_at' => now()->toIso8601String(),
                    'origin'      => (string) ($template['origin'] ?? 'ClientSimulation'),
                    'payload'     => $payload,
                ]);
                $eventIds[] = $eventId;

                if ($onPublished !== null) {
                    $onPublished(count($eventIds), $events, $eventId, $type);
                }

                if ($deadlineNs !== null && hrtime(true) >= $deadlineNs) {
                    break;
                }

                if ($intervalMicroseconds !== null && $i < $events) {
                    usleep($intervalMicroseconds);
                }
            }
        }

        $queueMatches = $this->countQueueMatchesForEventIds($eventIds);

        return [
            'slug'                   => $slug,
            'validation_errors'      => [],
            'sync'                   => $syncStats,
            'published'              => count($eventIds),
            'event_ids'              => $eventIds,
            'queue_matches'          => $queueMatches,
            'events_per_minute'      => $eventsPerMinute,
            'duration_minutes'       => $durationMinutes,
            'interval_microseconds'  => $intervalMicroseconds,
        ];
    }

    /**
     * @param array<string, mixed> $template
     * @return array<string, mixed>
     */
    private function buildPayload(array $template, string $eventId, string $type, int $sequence): array
    {
        $payload = [
            'event_id'    => $eventId,
            'event'       => $type,
            'event_type'  => $type,
            'occurred_at' => now()->toIso8601String(),
        ];

        $templatePayload = $template['payload_template'] ?? [];
        if (! is_array($templatePayload)) {
            return $payload;
        }

        foreach ($templatePayload as $key => $value) {
            if (is_string($value)) {
                $payload[(string) $key] = str_replace('{n}', (string) $sequence, $value);

                continue;
            }
            $payload[(string) $key] = $value;
        }

        return $payload;
    }

    /**
     * @param list<string> $eventIds
     */
    public function countQueueMatchesForEventIds(array $eventIds): int
    {
        if ($eventIds === []) {
            return 0;
        }

        $entries = $this->eventQueue->execute(max(50, count($eventIds) * 2));
        $found   = 0;
        foreach ($entries as $entry) {
            $row = $entry->toArray();
            if (in_array($row['event_id'] ?? '', $eventIds, true)) {
                $found++;
            }
        }

        return $found;
    }
}
