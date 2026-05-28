<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Repositories\WorkflowRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class EloquentWorkflowRepository implements WorkflowRepositoryInterface
{
    public function findActiveByTriggerEventType(string $eventType): array
    {
        if (! Schema::hasTable('workflows')) {
            return [];
        }

        return DB::table('workflows')
            ->where('trigger_event_type', $eventType)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'config'])
            ->map(fn ($row) => [
                'id'     => (string) $row->id,
                'code'   => (string) $row->code,
                'name'   => (string) $row->name,
                'config' => json_decode((string) ($row->config ?? 'null'), true),
            ])
            ->all();
    }
}
