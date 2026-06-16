<?php

declare(strict_types=1);

namespace App\Quality\Application\Services;

/**
 * Typed view of platform_quality config (Plan_Calidad).
 */
final class QualitySettings
{
    public static function fromConfig(): self
    {
        $config = config('platform_quality', []);

        return new self(is_array($config) ? $config : []);
    }

    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config,
    ) {}

    public function coverageMinPercent(): int
    {
        $coverage = $this->config['coverage'] ?? [];

        return (int) (is_array($coverage) ? ($coverage['application_min_percent'] ?? 70) : 70);
    }

    public function cloverPath(): string
    {
        $coverage = $this->config['coverage'] ?? [];

        return (string) (is_array($coverage) ? ($coverage['clover_path'] ?? 'build/coverage/clover.xml') : 'build/coverage/clover.xml');
    }

    /** @return list<string> */
    public function applicationCoveragePrefixes(): array
    {
        return [
            'App\\Middleware\\Application\\',
            'App\\Dashboard\\Application\\',
            'App\\Integration\\Application\\',
            'App\\Monitoring\\Application\\',
            'App\\Observability\\Application\\',
            'App\\Shared\\Platform\\',
        ];
    }

    public function loadTestTargetEps(): int
    {
        $load = $this->config['load_test'] ?? [];

        return (int) (is_array($load) ? ($load['target_eps'] ?? 100) : 100);
    }

    public function loadTestEventType(): string
    {
        $load = $this->config['load_test'] ?? [];

        return (string) (is_array($load) ? ($load['event_type'] ?? 'Platform.Quality.LoadTest') : 'Platform.Quality.LoadTest');
    }
}
