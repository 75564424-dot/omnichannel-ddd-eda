<?php

declare(strict_types=1);

namespace App\Dashboard\Application\DTOs;

/**
 * KPI strip — purely config-driven (see config/dashboard_config.json counter_cards).
 */
final class GlobalMetricsDTO
{
    /**
     * @param list<array{id: string, name: string, value: int|float, suffix: string}> $counters
     */
    public function __construct(
        public readonly array $counters,
        public readonly string $lastUpdated,
    ) {}

    public function toArray(): array
    {
        return [
            'counters'     => $this->counters,
            'last_updated' => $this->lastUpdated,
        ];
    }
}
