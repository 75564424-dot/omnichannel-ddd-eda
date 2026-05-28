<?php

declare(strict_types=1);

namespace Tests\Fixtures\Middleware;

/**
 * Listener usado en feature tests para comprobar que el bus Laravel
 * invoca consumidores registrados vía {@see \Illuminate\Support\Facades\Event::listen}.
 */
final class E2ECountedConsumerListener
{
    public static int $invocations = 0;

    public static function reset(): void
    {
        self::$invocations = 0;
    }

    /**
     * Laravel puede invocar el primer argumento como array envuelto (`[$payload]`) según el dispatcher.
     */
    public function handle(mixed $first, mixed $second = null): void
    {
        ++self::$invocations;
    }
}
