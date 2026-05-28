<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Readiness probe for load balancers and orchestrators (Plan_Cloud).
 * Verifies dependencies required for this instance to serve traffic.
 */
final class ReadinessController
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis'    => $this->checkRedis(),
        ];

        $ready = $checks['database'] === 'ok'
            && ($checks['redis'] === 'ok' || $checks['redis'] === 'skipped');

        return response()->json([
            'status' => $ready ? 'ready' : 'not_ready',
            'checks' => $checks,
        ], $ready ? 200 : 503);
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
