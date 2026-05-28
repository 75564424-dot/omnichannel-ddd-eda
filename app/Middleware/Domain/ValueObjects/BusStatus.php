<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

final class BusStatus
{
    public const ACTIVE   = 'ACTIVE';
    public const DEGRADED = 'DEGRADED';
    public const HI_LOAD  = 'HI-LOAD';
    public const STOPPED  = 'STOPPED';

    private readonly string $value;

    public function __construct(string $value)
    {
        $upper = strtoupper(trim($value));
        $this->value = in_array($upper, [self::ACTIVE, self::DEGRADED, self::HI_LOAD, self::STOPPED], true)
            ? $upper
            : self::STOPPED;
    }

    public static function active(): self   { return new self(self::ACTIVE); }
    public static function degraded(): self { return new self(self::DEGRADED); }
    public static function hiLoad(): self   { return new self(self::HI_LOAD); }
    public static function stopped(): self  { return new self(self::STOPPED); }

    /**
     * Evaluates bus health from live metrics.
     * Thresholds are injected from config/eventbus.php by the Application layer
     * to keep the Domain free of framework dependencies.
     */
    public static function evaluate(
        ErrorRate     $errorRate,
        ThroughputEps $eps,
        LatencyMs     $latency,
        int           $deadLetterCount,
        float         $criticalErrorRateThreshold = 10.0,
        int           $highLoadEpsThreshold       = 100,
        int           $criticalLatencyMs          = 2000,
        int           $deadLetterAlertThreshold   = 10,
    ): self {
        if ($errorRate->value() >= $criticalErrorRateThreshold || $deadLetterCount > $deadLetterAlertThreshold) {
            return self::degraded();
        }
        if ($eps->value() > $highLoadEpsThreshold || $latency->value() > $criticalLatencyMs) {
            return self::hiLoad();
        }

        // Idle (no events in the window) is healthy standby — not a stopped bus.
        // STOPPED is reserved for explicit outages (see BusMetricsService / node snapshots).
        return self::active();
    }

    public function isActive(): bool   { return $this->value === self::ACTIVE; }
    public function isHealthy(): bool  { return in_array($this->value, [self::ACTIVE, self::HI_LOAD], true); }
    public function value(): string    { return $this->value; }
    public function __toString(): string { return $this->value; }
}
