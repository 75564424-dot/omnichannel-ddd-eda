<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class InstanceReadinessProbe
{
    /** @return array{status: string, checks: array<string, string>} */
    public function probe(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis'    => $this->checkRedis(),
        ];

        $ready = $checks['database'] === 'ok'
            && ($checks['redis'] === 'ok' || $checks['redis'] === 'skipped');

        return [
            'status' => $ready ? 'ready' : 'not_ready',
            'checks' => $checks,
        ];
    }

    public function httpStatusCode(): int
    {
        return $this->probe()['status'] === 'ready' ? 200 : 503;
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();

            return 'ok';
        } catch (Throwable) {
            return 'fail';
        }
    }

    private function checkRedis(): string
    {
        if (! $this->redisRequired()) {
            return 'skipped';
        }

        try {
            $pong = Redis::connection()->ping();
            if ($pong === true || $pong === 'PONG' || $pong === '+PONG') {
                return 'ok';
            }

            return 'fail';
        } catch (Throwable) {
            return 'fail';
        }
    }

    private function redisRequired(): bool
    {
        $drivers = [
            (string) config('cache.default'),
            (string) config('queue.default'),
            (string) config('session.driver'),
        ];

        return in_array('redis', $drivers, true);
    }
}
