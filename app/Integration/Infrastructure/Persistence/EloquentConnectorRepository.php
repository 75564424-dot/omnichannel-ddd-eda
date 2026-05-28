<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Persistence;

use App\Integration\Domain\Repositories\ConnectorRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class EloquentConnectorRepository implements ConnectorRepositoryInterface
{
    public function findById(string $connectorId): ?array
    {
        $row = DB::table('connectors')->where('id', $connectorId)->first();

        return $row ? $this->mapRow($row) : null;
    }

    public function listForIntegration(string $integrationId): array
    {
        return DB::table('connectors')
            ->where('integration_id', $integrationId)
            ->orderBy('connector_type')
            ->get()
            ->map(fn ($row) => $this->mapRow($row))
            ->all();
    }

    private function mapRow(object $row): array
    {
        return [
            'id'                   => (string) $row->id,
            'integration_id'       => (string) $row->integration_id,
            'connector_type'       => (string) $row->connector_type,
            'endpoint'             => $row->endpoint,
            'config'               => json_decode((string) ($row->config ?? 'null'), true),
            'health_status'        => (string) $row->health_status,
            'last_health_check_at' => $row->last_health_check_at,
        ];
    }
}
