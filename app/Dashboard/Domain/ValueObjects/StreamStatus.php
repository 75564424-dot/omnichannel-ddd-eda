<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\ValueObjects;

final class StreamStatus
{
    public const ACTIVE   = 'ACTIVE';
    public const DEGRADED = 'DEGRADED';
    public const STOPPED  = 'STOPPED';

    private readonly string $value;

    public function __construct(string $value)
    {
        $upper = strtoupper(trim($value));
        $this->value = in_array($upper, [self::ACTIVE, self::DEGRADED, self::STOPPED], true)
            ? $upper
            : self::STOPPED;
    }

    public static function active(): self   { return new self(self::ACTIVE); }
    public static function degraded(): self { return new self(self::DEGRADED); }
    public static function stopped(): self  { return new self(self::STOPPED); }

    public static function fromMetrics(int $eventsLastMinute, int $queueSize): self
    {
        if ($eventsLastMinute === 0 && $queueSize === 0) {
            return self::stopped();
        }
        if ($queueSize > 500 || $eventsLastMinute > 1000) {
            return self::degraded();
        }
        return self::active();
    }

    public function value(): string { return $this->value; }
    public function isActive(): bool { return $this->value === self::ACTIVE; }
    public function __toString(): string { return $this->value; }
}
