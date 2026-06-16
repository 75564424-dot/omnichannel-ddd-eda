<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Http\Middleware\AuthenticatePlatformApi;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

final class PlatformRateLimitConfigurator
{
    public static function configure(): void
    {
        RateLimiter::for('platform-publish', fn (Request $request) => self::limit(
            $request,
            (int) config('security.rate_limits.publish', 100),
        ));

        RateLimiter::for('platform-sync', fn (Request $request) => self::limit(
            $request,
            (int) config('security.rate_limits.sync_config', 10),
        ));

        RateLimiter::for('platform-stream', fn (Request $request) => self::limit(
            $request,
            (int) config('security.rate_limits.stream', 60),
        ));

        RateLimiter::for('platform-api', fn (Request $request) => self::limit(
            $request,
            (int) config('security.rate_limits.default_api', 120),
        ));
    }

    private static function limit(Request $request, int $perMinute): Limit
    {
        $key = AuthenticatePlatformApi::principal($request)?->actorId ?? $request->ip();

        return Limit::perMinute($perMinute)->by((string) $key);
    }
}
