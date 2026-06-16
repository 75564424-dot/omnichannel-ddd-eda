<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Domain\ReadModels\EventFeedEntry;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Dashboard\Infrastructure\Models\EventFeedEntryModel;

final class EloquentEventFeedRepository implements EventFeedRepositoryInterface
{
    public function __construct(
        private readonly EventFeedEntryMapper $mapper,
        private readonly EventFeedLatencyCalculator $latencyCalculator,
        private readonly EventFeedCalendarSeriesQuery $calendarSeriesQuery,
    ) {}

    public function save(EventFeedEntry $entry): int
    {
        $model = EventFeedEntryModel::create([
            'event_uuid'  => $entry->eventId,
            'event_type'  => $entry->eventType,
            'origin'      => $entry->origin->value(),
            'impact'      => $entry->impact->value(),
            'status'      => $entry->status,
            'occurred_at' => $entry->occurredAt->format('Y-m-d H:i:s'),
            'received_at' => $entry->receivedAt->format('Y-m-d H:i:s'),
            'raw_payload' => $entry->rawPayload,
            'correlation_id' => $entry->correlationId,
        ]);

        return $model->id;
    }

    public function existsByEventId(string $eventId): bool
    {
        return EventFeedEntryModel::where('event_uuid', $eventId)->exists();
    }

    public function getRecent(int $limit = 50): array
    {
        return EventFeedEntryModel::orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m))
            ->all();
    }

    public function getPaginated(int $page, int $limit): array
    {
        $offset = max(0, ($page - 1) * $limit);

        return EventFeedEntryModel::orderByDesc('id')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m))
            ->all();
    }

    public function countAll(): int
    {
        return (int) EventFeedEntryModel::count();
    }

    public function getNewerThan(int $lastId, int $limit = 50): array
    {
        return EventFeedEntryModel::where('id', '>', $lastId)
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m))
            ->all();
    }

    public function computeAverageLatencyMs(int $lastN = 100): int
    {
        return $this->latencyCalculator->averageMs($lastN);
    }

    public function countEventsInLastSeconds(int $seconds = 60): int
    {
        return EventFeedEntryModel::where('received_at', '>=', now()->subSeconds($seconds))->count();
    }

    public function countReceivedSince(\DateTimeInterface $since): int
    {
        return EventFeedEntryModel::where('received_at', '>=', $since)->count();
    }

    public function sumPayloadPathByCalendarDay(string $eventType, array $pathKeys, int $days = 14): array
    {
        return $this->calendarSeriesQuery->sumPayloadPathByCalendarDay($eventType, $pathKeys, $days);
    }

    public function countEventsByCalendarDay(array $eventTypes, int $days = 14): array
    {
        return $this->calendarSeriesQuery->countEventsByCalendarDay($eventTypes, $days);
    }
}
