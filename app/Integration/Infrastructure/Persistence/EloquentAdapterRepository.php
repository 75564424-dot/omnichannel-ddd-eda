<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Persistence;

use App\Integration\Domain\Repositories\AdapterRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class EloquentAdapterRepository implements AdapterRepositoryInterface
{
    public function listEnabledForIntegration(string $integrationId): array
    {
        return DB::table('adapters')
            ->where('integration_id', $integrationId)
            ->where('is_enabled', true)
            ->orderBy('priority')
            ->get(['id', 'adapter_type', 'config', 'priority'])
            ->map(fn ($row) => [
                'id'           => (string) $row->id,
                'adapter_type' => (string) $row->adapter_type,
                'config'       => json_decode((string) ($row->config ?? 'null'), true),
                'priority'     => (int) $row->priority,
            ])
            ->all();
    }
}
