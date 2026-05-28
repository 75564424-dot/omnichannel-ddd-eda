<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

use InvalidArgumentException;

final class LatencyMs
{
    private readonly int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('LatencyMs cannot be negative.');
        }
        $this->value = $value;
    }

    public static function zero(): self      { return new self(0); }
    public static function of(int $ms): self { return new self($ms); }

    public function value(): int { return $this->value; }
    public function isAcceptable(): bool { return $this->value <= 500; }
    public function isDegraded(): bool   { return $this->value > 500 && $this->value <= 2000; }
    public function isCritical(): bool   { return $this->value > 2000; }
}
