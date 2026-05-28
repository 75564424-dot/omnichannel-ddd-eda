<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Domain\ReadModels\EventFeedEntry;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\EventImpact;
use App\Dashboard\Domain\ValueObjects\EventOrigin;
use App\Dashboard\Infrastructure\Models\EventFeedEntryModel;
use DateTimeImmutable;

final class EloquentEventFeedRepository implements EventFeedRepositoryInterface
{
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
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }

    public function getPaginated(int $page, int $limit): array
    {
        $offset = max(0, ($page - 1) * $limit);

        return EventFeedEntryModel::orderByDesc('id')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
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
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }

    public function computeAverageLatencyMs(int $lastN = 100): int
    {
        $entries = EventFeedEntryModel::orderByDesc('id')
            ->limit($lastN)
            ->get(['occurred_at', 'received_at']);

        if ($entries->isEmpty()) {
            return 0;
        }

        $totalMs = $entries->sum(function ($e) {
            $occurred = DateTimeImmutable::createFromInterface($e->occurred_at);
            $received = DateTimeImmutable::createFromInterface($e->received_at);
            return max(0, ($received->getTimestamp() - $occurred->getTimestamp()) * 1000);
        });

        return (int) round($totalMs / $entries->count());
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
        $days  = max(1, min(90, $days));
        $tz    = config('app.timezone');
        $since = now()->subDays($days - 1)->startOfDay();

        $rows = EventFeedEntryModel::query()
            ->where('event_type', $eventType)
            ->where('occurred_at', '>=', $since)
            ->orderBy('occurred_at')
            ->get(['occurred_at', 'raw_payload']);

        $sums = [];
        foreach ($rows as $row) {
            $day     = $row->occurred_at->clone()->timezone($tz)->format('Y-m-d');
            $payload = $row->raw_payload ?? [];
            $value   = $payload;
            foreach ($pathKeys as $key) {
                $value = is_array($value) ? ($value[$key] ?? 0) : 0;
            }
            $sums[$day] = ($sums[$day] ?? 0.0) + (float) $value;
        }

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d        = now()->subDays($i)->timezone($tz)->format('Y-m-d');
            $series[] = [
                'date'  => $d,
                'total' => round($sums[$d] ?? 0.0, 2),
            ];
        }

        return $series;
    }

    public function countEventsByCalendarDay(array $eventTypes, int $days = 14): array
    {
        $days  = max(1, min(90, $days));
        $tz    = config('app.timezone');
        $since = now()->subDays($days - 1)->startOfDay();

        $query = EventFeedEntryModel::query()
            ->where('occurred_at', '>=', $since);

        $normalizedTypes = array_values(array_unique(array_filter(array_map(
            static fn ($t) => trim((string) $t),
            $eventTypes,
        ))));

        if ($normalizedTypes !== []) {
            $query->whereIn('event_type', $normalizedTypes);
        }

        $counts = [];
        foreach ($query->get(['occurred_at']) as $row) {
            $day = $row->occurred_at->clone()->timezone($tz)->format('Y-m-d');
            $counts[$day] = ($counts[$day] ?? 0) + 1;
        }

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d        = now()->subDays($i)->timezone($tz)->format('Y-m-d');
            $series[] = [
                'date'  => $d,
                'total' => (int) ($counts[$d] ?? 0),
            ];
        }

        return $series;
    }

    private function toDomain(EventFeedEntryModel $model): EventFeedEntry
    {
        return new EventFeedEntry(
            id:         (int) $model->id,
            eventId:    $model->event_uuid,
            eventType:  $model->event_type,
            origin:     new EventOrigin($model->origin),
            impact:     new EventImpact($model->impact),
            status:     $model->status,
            occurredAt: DateTimeImmutable::createFromInterface($model->occurred_at),
            receivedAt: DateTimeImmutable::createFromInterface($model->received_at),
            rawPayload: $model->raw_payload ?? [],
        );
    }
}
