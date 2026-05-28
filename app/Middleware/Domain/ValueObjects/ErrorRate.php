<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

use InvalidArgumentException;

final class ErrorRate
{
    private readonly float $value;

    public function __construct(float $value)
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException("ErrorRate must be between 0 and 100, got {$value}.");
        }
        $this->value = round($value, 2);
    }

    public static function zero(): self { return new self(0.0); }

    public static function compute(int $failed, int $total): self
    {
        if ($total === 0) return self::zero();
        return new self(($failed / $total) * 100);
    }

    public function value(): float { return $this->value; }
    public function isHealthy(): bool   { return $this->value < 1.0; }
    public function isDegraded(): bool  { return $this->value >= 1.0 && $this->value < 10.0; }
    public function isCritical(): bool  { return $this->value >= 10.0; }
}
