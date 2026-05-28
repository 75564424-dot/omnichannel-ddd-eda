<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ReadModels;

final class TopologyView
{
    public function __construct(
        public readonly array  $producers,
        public readonly array  $bus,
        public readonly array  $consumers,
        public readonly string $generatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'producers'    => $this->producers,
            'bus'          => $this->bus,
            'consumers'    => $this->consumers,
            'generated_at' => $this->generatedAt,
        ];
    }
}
