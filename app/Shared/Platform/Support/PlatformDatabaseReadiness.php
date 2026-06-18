<?php

declare(strict_types=1);

namespace App\Shared\Platform\Support;

/**
 * Guards boot-time schema checks when SQLite file is not created yet (fresh git clone).
 */
final class PlatformDatabaseReadiness
{
    public static function canQuerySchema(): bool
    {
        if (config('database.default') !== 'sqlite') {
            return true;
        }

        $path = config('database.connections.sqlite.database');

        if (! is_string($path) || $path === '' || $path === ':memory:') {
            return false;
        }

        return is_file($path);
    }
}
