<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

/**
 * Reports pending job depth across configured queues (Plan_Monitoreo).
 */
final class QueueDepthChecker
{
    public function totalPending(): int
    {
        $driver = (string) config('queue.default');
        $names  = config('platform_monitoring.queues.names', []);

        if ($names === []) {
            return 0;
        }

        return match ($driver) {
            'redis'    => $this->redisDepth($names),
            'database' => $this->databaseDepth($names),
            default    => 0,
        };
    }

    /** @param list<string> $names */
    private function redisDepth(array $names): int
    {
        try {
            $total = 0;
            foreach ($names as $name) {
                $total += (int) Redis::connection()->llen('queues:'.$name);
                $total += (int) Redis::connection()->zcard('queues:'.$name.':delayed');
                $total += (int) Redis::connection()->zcard('queues:'.$name.':reserved');
            }

            return $total;
        } catch (\Throwable) {
            return 0;
        }
    }

    /** @param list<string> $names */
    private function databaseDepth(array $names): int
    {
        if (! Schema::hasTable('jobs')) {
            return 0;
        }

        try {
            return (int) DB::table('jobs')->whereIn('queue', $names)->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
