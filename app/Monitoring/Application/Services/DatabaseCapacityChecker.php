<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services;

use Illuminate\Support\Facades\DB;

/**
 * Estimates database storage usage vs configured limit (Plan_Monitoreo DiskSpace alert).
 */
final class DatabaseCapacityChecker
{
    public function usagePercent(): float
    {
        $maxMb = max(1, (int) config('platform_monitoring.database.max_size_mb', 10240));
        $usedMb = $this->usedMegabytes();

        return min(100.0, round(($usedMb / $maxMb) * 100, 2));
    }

    public function usedMegabytes(): float
    {
        $driver = (string) config('database.default');
        $connection = config('database.connections.'.$driver);

        if (! is_array($connection)) {
            return 0.0;
        }

        return match ($connection['driver'] ?? '') {
            'mysql'  => $this->mysqlUsedMb((string) ($connection['database'] ?? '')),
            'sqlite' => $this->sqliteUsedMb((string) ($connection['database'] ?? '')),
            default  => 0.0,
        };
    }

    private function mysqlUsedMb(string $database): float
    {
        if ($database === '') {
            return 0.0;
        }

        try {
            $bytes = DB::selectOne(
                'SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ?',
                [$database],
            );
        } catch (\Throwable) {
            return 0.0;
        }

        return round(((float) ($bytes->size ?? 0)) / 1024 / 1024, 2);
    }

    private function sqliteUsedMb(string $path): float
    {
        if ($path === '' || $path === ':memory:' || ! is_file($path)) {
            return 0.0;
        }

        return round(filesize($path) / 1024 / 1024, 2);
    }
}
