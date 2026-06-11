<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;

final class LocalFleetLocalInstanceDescriptor
{
    /**
     * @param array<string, mixed> $instanceRow
     *
     * @return array<string, mixed>
     */
    public function describe(array $instanceRow, string $envPath, TenantModel $tenant): array
    {
        return [
            'app_url'  => 'http://127.0.0.1:'.(int) $instanceRow['port'],
            'port'     => (int) $instanceRow['port'],
            'env_file' => basename($envPath),
            'env_id'   => (string) $instanceRow['id'],
            'db_path'  => 'database/instances/'.Str::slug($tenant->slug).'.sqlite',
        ];
    }
}
