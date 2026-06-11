<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetTenantMirrorInterface;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Mirrors control-plane tenant settings and operators into a client silo SQLite DB.
 */
final class LocalFleetTenantMirror implements LocalFleetTenantMirrorInterface
{
    public function __construct(
        private readonly LocalFleetEnvBuilder $envBuilder,
    ) {}

    public function mirror(TenantModel $controlPlaneTenant): void
    {
        $this->mirrorToSilo($controlPlaneTenant, syncOperators: true);
    }

    public function mirrorCatalog(TenantModel $controlPlaneTenant): void
    {
        $this->mirrorToSilo($controlPlaneTenant, syncOperators: false);
    }

    private function mirrorToSilo(TenantModel $controlPlaneTenant, bool $syncOperators): void
    {
        $slug = Str::slug($controlPlaneTenant->slug);
        $dbPath = $this->envBuilder->ensureSqliteFile($slug);

        if (! is_file($dbPath)) {
            throw new \RuntimeException("Client database missing: {$dbPath}");
        }

        $connection = $this->clientConnection($slug, $dbPath);

        try {
            $instanceTenantId = $this->resolveInstanceTenantId($connection, $slug);
            $this->syncTenantSettings($connection, $instanceTenantId, $controlPlaneTenant);
            if ($syncOperators) {
                $this->syncOperators($connection, $instanceTenantId, $controlPlaneTenant);
            }
            $this->writeModulesConfig($slug, $controlPlaneTenant);
        } finally {
            DB::purge('client_silo');
        }
    }

    private function clientConnection(string $slug, string $dbPath): Connection
    {
        Config::set('database.connections.client_silo', [
            'driver'                  => 'sqlite',
            'database'                => $dbPath,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);

        $connection = DB::connection('client_silo');
        $connection->getPdo()->exec('PRAGMA busy_timeout = 5000');

        return $connection;
    }

    private function resolveInstanceTenantId(Connection $connection, string $slug): string
    {
        $row = $connection->table('tenants')->where('slug', $slug)->first(['id']);

        if ($row === null) {
            throw new \RuntimeException("Instance tenant «{$slug}» not found. Run bootstrap first.");
        }

        return (string) $row->id;
    }

    private function syncTenantSettings(Connection $connection, string $instanceTenantId, TenantModel $source): void
    {
        $settings = is_array($source->settings) ? $source->settings : [];
        unset($settings['deployment']);

        $connection->table('tenants')->where('id', $instanceTenantId)->update([
            'name'     => $source->name,
            'status'   => $source->status,
            'settings' => json_encode($settings, JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);
    }

    private function syncOperators(Connection $connection, string $instanceTenantId, TenantModel $source): void
    {
        $operators = User::query()
            ->where('tenant_id', $source->id)
            ->whereIn('platform_role', $this->mirroredRoles())
            ->orderBy('created_at')
            ->get();

        if ($operators->isEmpty()) {
            return;
        }

        $emails = [];

        foreach ($operators as $operator) {
            $email = (string) $operator->getAttribute('email');
            $emails[] = $email;
            $passwordHash = (string) DB::table('users')->where('id', $operator->id)->value('password');

            $existing = $connection->table('users')->where('email', $email)->first();

            $payload = [
                'tenant_id'     => $instanceTenantId,
                'name'          => (string) $operator->getAttribute('name'),
                'email'         => $email,
                'password'      => $passwordHash,
                'platform_role' => (string) $operator->getAttribute('platform_role'),
                'updated_at'    => now(),
            ];

            if ($existing === null) {
                $connection->table('users')->insert(array_merge($payload, [
                    'created_at' => $operator->getAttribute('created_at') ?? now(),
                ]));
            } else {
                $connection->table('users')->where('email', $email)->update($payload);
            }
        }

        $connection->table('users')
            ->where('tenant_id', $instanceTenantId)
            ->whereNotIn('email', $emails)
            ->whereIn('platform_role', $this->mirroredRoles())
            ->delete();

        $connection->table('users')
            ->where('platform_role', 'saas_admin')
            ->delete();
    }

    /** @return list<string> */
    private function mirroredRoles(): array
    {
        return ['platform_admin', 'bus_operator', 'dashboard_viewer'];
    }

    private function writeModulesConfig(string $slug, TenantModel $source): void
    {
        $settings = is_array($source->settings) ? $source->settings : [];
        $catalog = $settings['modules_catalog'] ?? null;

        if (! is_array($catalog) || $catalog === []) {
            return;
        }

        $directory = config_path('modules/instances/'.$slug);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/modules_config.json';
        File::put(
            $path,
            json_encode($catalog, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n",
        );
    }
}
