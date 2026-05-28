<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Persistence;

use App\Integration\Domain\Repositories\IntegrationRepositoryInterface;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class EloquentIntegrationRepository implements IntegrationRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $tenantContext,
    ) {}

    public function listAll(): array
    {
        return DB::table('integrations')
            ->whereNull('deleted_at')
            ->orderBy('code')
            ->get()
            ->map(fn ($row) => $this->mapRow($row))
            ->all();
    }

    public function findById(string $id): ?array
    {
        $row = DB::table('integrations')->where('id', $id)->whereNull('deleted_at')->first();

        return $row ? $this->mapRow($row) : null;
    }

    public function findActiveByCode(string $code): ?array
    {
        $row = DB::table('integrations')
            ->where('code', $code)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->first();

        return $row ? $this->mapRow($row) : null;
    }

    public function create(array $data): string
    {
        $id = (string) Str::uuid();
        DB::table('integrations')->insert([
            'id'          => $id,
            'tenant_id'   => $this->tenantContext->tenantId(),
            'channel_id'  => $data['channel_id'] ?? null,
            'provider_id' => $data['provider_id'] ?? null,
            'code'        => $data['code'],
            'name'        => $data['name'],
            'direction'   => $data['direction'],
            'status'      => $data['status'] ?? 'active',
            'config'      => isset($data['config']) ? json_encode($data['config'], JSON_THROW_ON_ERROR) : null,
            'version'     => (int) ($data['version'] ?? 1),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return $id;
    }

    public function update(string $id, array $data): void
    {
        if ($this->findById($id) === null) {
            throw new RuntimeException("Integration {$id} not found.");
        }

        $update = ['updated_at' => now()];
        foreach (['name', 'direction', 'status', 'channel_id', 'provider_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        if (array_key_exists('config', $data)) {
            $update['config'] = json_encode($data['config'], JSON_THROW_ON_ERROR);
        }

        DB::table('integrations')->where('id', $id)->update($update);
    }

    public function delete(string $id): void
    {
        DB::table('integrations')->where('id', $id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function mapRow(object $row): array
    {
        return [
            'id'          => (string) $row->id,
            'tenant_id'   => $row->tenant_id,
            'channel_id'  => $row->channel_id,
            'provider_id' => $row->provider_id,
            'code'        => (string) $row->code,
            'name'        => (string) $row->name,
            'direction'   => (string) $row->direction,
            'status'      => (string) $row->status,
            'config'      => json_decode((string) ($row->config ?? 'null'), true),
            'version'     => (int) $row->version,
        ];
    }
}
