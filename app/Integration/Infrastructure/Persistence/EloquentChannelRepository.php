<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Persistence;

use App\Integration\Domain\Repositories\ChannelRepositoryInterface;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class EloquentChannelRepository implements ChannelRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $tenantContext,
    ) {}

    public function listAll(): array
    {
        return DB::table('channels')
            ->whereNull('deleted_at')
            ->orderBy('code')
            ->get()
            ->map(fn ($row) => $this->mapRow($row))
            ->all();
    }

    public function findById(string $id): ?array
    {
        $row = DB::table('channels')->where('id', $id)->whereNull('deleted_at')->first();

        return $row ? $this->mapRow($row) : null;
    }

    public function create(array $data): string
    {
        $id = (string) Str::uuid();
        DB::table('channels')->insert([
            'id'           => $id,
            'tenant_id'    => $this->tenantContext->tenantId(),
            'code'         => $data['code'],
            'name'         => $data['name'],
            'channel_type' => $data['channel_type'],
            'status'       => $data['status'] ?? 'active',
            'metadata'     => isset($data['metadata']) ? json_encode($data['metadata'], JSON_THROW_ON_ERROR) : null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return $id;
    }

    public function update(string $id, array $data): void
    {
        if ($this->findById($id) === null) {
            throw new RuntimeException("Channel {$id} not found.");
        }

        $update = ['updated_at' => now()];
        foreach (['name', 'channel_type', 'status'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        if (array_key_exists('metadata', $data)) {
            $update['metadata'] = json_encode($data['metadata'], JSON_THROW_ON_ERROR);
        }

        DB::table('channels')->where('id', $id)->update($update);
    }

    public function delete(string $id): void
    {
        DB::table('channels')->where('id', $id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function mapRow(object $row): array
    {
        return [
            'id'           => (string) $row->id,
            'tenant_id'    => $row->tenant_id,
            'code'         => (string) $row->code,
            'name'         => (string) $row->name,
            'channel_type' => (string) $row->channel_type,
            'status'       => (string) $row->status,
            'metadata'     => json_decode((string) ($row->metadata ?? 'null'), true),
        ];
    }
}
