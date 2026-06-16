<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Runtime;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Short-lived flag so client UIs can show middleware flow during simulations.
 *
 * Uses a shared JSON file so detached workers and the web server see the same pulse
 * without contending on the SQLite cache store.
 */
final class SimulationPulseService
{
    private const CACHE_KEY = 'middleware.simulation_pulse';

    private const FILE_NAME = 'simulation-pulse.json';

    /** After this many seconds without a tick, the pulse is treated as inactive (avoids stuck UI loops). */
    private const STALE_SECONDS = 25;

    public function __construct(
        private readonly CacheRepository $cache,
    ) {}

    public function tick(string $phase, ?string $eventType = null): void
    {
        $previous = $this->readPayload();
        $sequence = (int) ($previous['sequence'] ?? 0) + 1;

        $payload = [
            'active'          => true,
            'phase'           => $phase,
            'last_event_type' => $eventType,
            'sequence'        => $sequence,
            'tick_at'         => now()->toIso8601String(),
        ];

        $this->cache->put(self::CACHE_KEY, $payload, now()->addMinutes(6));
        $this->writeFile($payload);
    }

    public function clear(): void
    {
        $this->cache->forget(self::CACHE_KEY);
        $path = $this->filePath();
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $stored = $this->readPayload();

        if (! is_array($stored) || ($stored['active'] ?? false) !== true) {
            return ['active' => false];
        }

        $tickAt = $stored['tick_at'] ?? null;
        if (is_string($tickAt) && $tickAt !== '') {
            try {
                $ageSeconds = now()->diffInSeconds(\Carbon\Carbon::parse($tickAt), absolute: true);
                if ($ageSeconds > self::STALE_SECONDS) {
                    $this->clear();

                    return ['active' => false, 'stale' => true];
                }
            } catch (\Throwable) {
                $this->clear();

                return ['active' => false];
            }
        }

        return $stored;
    }

    /** @return array<string, mixed>|null */
    private function readPayload(): ?array
    {
        $fromFile = $this->readFile();
        if (is_array($fromFile)) {
            return $fromFile;
        }

        $cached = $this->cache->get(self::CACHE_KEY);

        return is_array($cached) ? $cached : null;
    }

    private function filePath(): string
    {
        $dir = storage_path('app');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir.DIRECTORY_SEPARATOR.self::FILE_NAME;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function writeFile(array $payload): void
    {
        $path = $this->filePath();
        $tmp  = $path.'.tmp';
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        file_put_contents($tmp, $json, LOCK_EX);
        rename($tmp, $path);
    }

    /** @return array<string, mixed>|null */
    private function readFile(): ?array
    {
        $path = $this->filePath();
        if (! is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false || $contents === '') {
            return null;
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : null;
    }
}
