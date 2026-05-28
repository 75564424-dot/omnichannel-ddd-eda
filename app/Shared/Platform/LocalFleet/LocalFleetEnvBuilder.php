<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use Illuminate\Support\Str;

final class LocalFleetEnvBuilder
{
    /**
     * @param array<string, mixed> $instance
     */
    public function build(array $instance, string $appKey): string
    {
        $slug = Str::slug((string) ($instance['slug'] ?? 'client'));
        $label = (string) ($instance['label'] ?? $slug);
        $port = (int) ($instance['port'] ?? 8001);
        $appUrl = 'http://127.0.0.1:'.$port;
        $displayName = $label;
        $dbAbsolute = str_replace('\\', '/', base_path('database/instances/'.$slug.'.sqlite'));
        $sessionCookie = 'platform_session_'.str_replace('-', '_', $slug);
        $envId = (string) ($instance['id'] ?? 'client-'.$slug);
        $adminEmail = (string) ($instance['adminEmail'] ?? 'admin@'.$slug.'-local');
        $adminPassword = (string) ($instance['adminPassword'] ?? 'change-me-local');
        $adminName = (string) ($instance['adminName'] ?? 'Admin '.$label);

        return <<<ENV
# Local client silo — slug={$slug} (auto-provisioned)
APP_NAME="{$displayName}"
APP_ENV={$envId}
APP_KEY={$appKey}
APP_DEBUG=true
APP_URL={$appUrl}
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_LEVEL=debug

PLATFORM_CLIENT_SLUG={$slug}
PLATFORM_CLIENT_NAME="{$label}"
PLATFORM_DEPLOYMENT_MODE=instance_per_client
PLATFORM_CONTROL_PLANE=false
PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false
PLATFORM_SEED_INSTANCE_TENANT=true
PLATFORM_SIMULATION_ENABLED=false

DB_CONNECTION=sqlite
DB_DATABASE={$dbAbsolute}

QUEUE_CONNECTION=sync
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_COOKIE={$sessionCookie}

MODULES_CONFIG_PATH=config/modules/instances/{$slug}/modules_config.json

PLATFORM_API_AUTH_ENABLED=true
PLATFORM_WEB_AUTH_ENABLED=true
CORS_ALLOWED_ORIGINS={$appUrl},http://localhost:{$port}
SANCTUM_STATEFUL_DOMAINS=127.0.0.1:{$port},localhost:{$port}

PLATFORM_OBSERVABILITY_SERVICE_NAME={$slug}

PLATFORM_SEED_SAAS_OPERATOR=false
PLATFORM_SEED_ADMIN_OPERATOR=true
PLATFORM_ADMIN_NAME="{$adminName}"
PLATFORM_ADMIN_EMAIL={$adminEmail}
PLATFORM_ADMIN_PASSWORD={$adminPassword}
PLATFORM_ADMIN_ROLE=platform_admin

ENV;
    }

    public function envFileName(string $slug): string
    {
        return '.env.client-'.Str::slug($slug);
    }

    public function instanceEnvId(string $slug): string
    {
        return 'client-'.Str::slug($slug);
    }

    public function ensureSqliteFile(string $slug): string
    {
        $directory = base_path('database/instances');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.DIRECTORY_SEPARATOR.Str::slug($slug).'.sqlite';
        if (! is_file($path)) {
            touch($path);
        }

        return $path;
    }
}
