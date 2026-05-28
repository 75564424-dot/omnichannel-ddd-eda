<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Entities\QueueEntry;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Middleware\Domain\ValueObjects\ConsumerList;
use App\Middleware\Domain\ValueObjects\EventStatus;
use App\Middleware\Infrastructure\Models\QueueEntryModel;
use App\Shared\Persistence\MessageQueueStatusMapper;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use DateTimeImmutable;

final class EloquentQueueEntryRepository implements QueueEntryRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceTenant,
    ) {}

    public function save(QueueEntry $entry): int
    {
        if ($entry->id() > 0) {
            QueueEntryModel::where('id', $entry->id())->update([
                'status'             => MessageQueueStatusMapper::toDb($entry->status()->value()),
                'dispatched_at'      => $entry->dispatchedAt()?->format('Y-m-d H:i:s'),
                'processing_time_ms' => $entry->processingTimeMs(),
                'attempt_count'      => $entry->attemptCount(),
            ]);
            return $entry->id();
        }

        $model = QueueEntryModel::create([
            'tenant_id'            => $this->instanceTenant->tenantId(),
            'event_uuid'           => $entry->eventId(),
            'message_type'         => $entry->eventType(),
            'origin'               => $entry->origin(),
            'target_consumers'     => $entry->consumers()->toArray(),
            'payload'              => $entry->payload(),
            'status'               => MessageQueueStatusMapper::toDb($entry->status()->value()),
            'published_at'         => $entry->publishedAt()->format('Y-m-d H:i:s'),
            'dispatched_at'        => $entry->dispatchedAt()?->format('Y-m-d H:i:s'),
            'processing_time_ms'   => $entry->processingTimeMs(),
            'attempt_count'        => $entry->attemptCount(),
            'correlation_id'       => $entry->correlationId(),
            'channel_id'           => $entry->channelId(),
            'integration_id'       => $entry->integrationId(),
        ]);

        return $model->id;
    }

    public function findByEventId(string $eventId): ?QueueEntry
    {
        $model = QueueEntryModel::where('event_uuid', $eventId)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function existsByEventId(string $eventId): bool
    {
        return QueueEntryModel::where('event_uuid', $eventId)->exists();
    }

    public function getRecent(int $limit = 50): array
    {
        return QueueEntryModel::orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->all();
    }

    public function getPaginated(int $page, int $limit): array
    {
        $offset = max(0, ($page - 1) * $limit);

        return QueueEntryModel::orderByDesc('published_at')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }

    public function countAll(): int
    {
        return (int) QueueEntryModel::count();
    }

    public function countByStatus(string $status, int $lastSeconds = 60): int
    {
        return QueueEntryModel::where('status', MessageQueueStatusMapper::toDb($status))
            ->where('published_at', '>=', now()->subSeconds($lastSeconds))
            ->count();
    }

    public function computeAverageProcessingTimeMs(int $lastN = 100): int
    {
        $avg = QueueEntryModel::whereNotNull('processing_time_ms')
            ->orderByDesc('published_at')
            ->limit($lastN)
            ->avg('processing_time_ms');

        return (int) ($avg ?? 0);
    }

    public function countTotal(int $lastSeconds = 60): int
    {
        return QueueEntryModel::where('published_at', '>=', now()->subSeconds($lastSeconds))
            ->count();
    }

    public function markDeadLettered(string $eventId): void
    {
        QueueEntryModel::where('event_uuid', $eventId)->update([
            'status' => 'dead_lettered',
        ]);
    }

    public function resetForRequeue(string $eventId): void
    {
        QueueEntryModel::where('event_uuid', $eventId)->update([
            'status'        => 'pending',
            'attempt_count' => 0,
        ]);
    }

    private function toDomain(QueueEntryModel $model): QueueEntry
    {
        return QueueEntry::reconstitute(
            id:               $model->id,
            eventId:          $model->event_uuid,
            eventType:        $model->message_type,
            origin:           $model->origin,
            consumers:        new ConsumerList($model->target_consumers ?? []),
            payload:          $model->payload ?? [],
            status:           new EventStatus(MessageQueueStatusMapper::fromDb($model->status)),
            publishedAt:      DateTimeImmutable::createFromInterface($model->published_at),
            dispatchedAt:     $model->dispatched_at
                                ? DateTimeImmutable::createFromInterface($model->dispatched_at)
                                : null,
            processingTimeMs: $model->processing_time_ms,
            attemptCount:     (int) ($model->attempt_count ?? 0),
            correlationId:    $model->correlation_id,
            channelId:        $model->channel_id,
            integrationId:    $model->integration_id,
        );
    }
}
