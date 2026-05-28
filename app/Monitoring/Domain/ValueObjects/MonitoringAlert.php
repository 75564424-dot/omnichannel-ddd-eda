<?php

declare(strict_types=1);

namespace App\Monitoring\Domain\ValueObjects;

/**
 * Fired monitoring alert (read-only value object).
 */
final class MonitoringAlert
{
    public function __construct(
        public readonly string $name,
        public readonly AlertSeverity $severity,
        public readonly string $message,
        public readonly float|int $currentValue,
        public readonly float|int $threshold,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'severity'      => $this->severity->value,
            'message'       => $this->message,
            'current_value' => $this->currentValue,
            'threshold'     => $this->threshold,
        ];
    }
}
