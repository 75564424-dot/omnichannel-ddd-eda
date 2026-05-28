<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Repositories\OutboxRepositoryInterface;
use App\Middleware\Infrastructure\Models\OutboxMessageModel;
use Illuminate\Support\Facades\DB;

final class EloquentOutboxRepository implements OutboxRepositoryInterface
{
    public function enqueue(string $eventId, string $eventType, string $origin, array $payload): int
    {
        $model = OutboxMessageModel::create([
            'event_uuid'  => $eventId,
            'event_type'  => $eventType,
            'origin'      => $origin,
            'payload'     => $payload,
            'status'      => 'pending',
            'created_at'  => now(),
        ]);

        return (int) $model->id;
    }

    public function claimPending(int $limit = 50): array
    {
        return OutboxMessageModel::where('status', 'pending')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (OutboxMessageModel $m) => [
                'id'         => (int) $m->id,
                'event_uuid' => $m->event_uuid,
                'event_type' => $m->event_type,
                'origin'     => $m->origin,
                'payload'    => $m->payload ?? [],
            ])
            ->all();
    }

    public function markPublished(int $id): void
    {
        OutboxMessageModel::where('id', $id)->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);
    }

    public function markFailed(int $id): void
    {
        DB::table('outbox_messages')->where('id', $id)->update([
            'status'        => 'failed',
            'attempt_count' => DB::raw('attempt_count + 1'),
        ]);
    }
}
