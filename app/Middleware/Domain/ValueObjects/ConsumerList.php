<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

final class ConsumerList
{
    private readonly array $consumers;

    public function __construct(array $consumers)
    {
        $this->consumers = array_values(array_filter(array_map('trim', $consumers)));
    }

    public static function empty(): self { return new self([]); }
    public static function of(string ...$consumers): self { return new self($consumers); }

    public function toArray(): array { return $this->consumers; }
    public function count(): int { return count($this->consumers); }
    public function isEmpty(): bool { return empty($this->consumers); }
    public function contains(string $consumer): bool { return in_array($consumer, $this->consumers, true); }
}
