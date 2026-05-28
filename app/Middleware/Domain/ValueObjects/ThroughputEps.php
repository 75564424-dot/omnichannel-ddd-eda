<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

use InvalidArgumentException;

final class ThroughputEps
{
    private readonly int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('ThroughputEps cannot be negative.');
        }
        $this->value = $value;
    }

    public static function zero(): self       { return new self(0); }
    public static function of(int $eps): self { return new self($eps); }

    public function value(): int { return $this->value; }
    public function isIdle(): bool    { return $this->value === 0; }
    public function isHighLoad(): bool { return $this->value > 100; }
}
