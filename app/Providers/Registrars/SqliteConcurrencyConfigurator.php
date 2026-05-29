<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Applies SQLite WAL + busy_timeout for local/dev concurrency (non-fatal in tests).
 */
final class SqliteConcurrencyConfigurator
{
    public function configure(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        try {
            $pdo = DB::connection()->getPdo();
            $pdo->exec('PRAGMA busy_timeout = 10000');
            $pdo->exec('PRAGMA journal_mode = WAL');
        } catch (Throwable) {
            // Non-fatal: some test environments use in-memory sqlite without WAL support.
        }
    }
}
