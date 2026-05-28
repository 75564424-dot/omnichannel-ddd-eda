<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;
use App\Dashboard\Infrastructure\Models\NodeStatusSnapshotModel;

final class EloquentNodeStatusRepository implements NodeStatusRepositoryInterface
{
    public function setStatus(string $nodeName, string $status): void
    {
        NodeStatusSnapshotModel::updateOrCreate(
            ['node_code' => $nodeName],
            ['status' => $status, 'updated_at' => now(), 'recorded_at' => now()],
        );
    }

    public function setMiddlewareEventsEnabled(string $nodeName, bool $enabled): void
    {
        $row = NodeStatusSnapshotModel::query()->where('node_code', $nodeName)->first();

        $status = $row?->status;
        if ($status === null || $status === '') {
            $status = $enabled ? 'ONLINE' : 'OFFLINE';
        } elseif ($enabled) {
            $status = 'ONLINE';
        }

        NodeStatusSnapshotModel::updateOrCreate(
            ['node_code' => $nodeName],
            [
                'events_enabled' => $enabled,
                'status'         => $status,
                'updated_at'     => now(),
                'recorded_at'    => now(),
            ],
        );
    }

    public function middlewareEventsEnabled(string $nodeName): bool
    {
        $row = NodeStatusSnapshotModel::query()->where('node_code', $nodeName)->first();

        return $row !== null && (bool) $row->events_enabled;
    }

    public function getAllStatuses(): array
    {
        return NodeStatusSnapshotModel::all()
            ->keyBy('node_code')
            ->map(fn ($m) => [
                'status'                    => $m->status,
                'updated_at'                => $m->updated_at?->toDateTimeString() ?? now()->toDateTimeString(),
                'middleware_events_enabled' => (bool) ($m->events_enabled ?? false),
            ])
            ->all();
    }
}
