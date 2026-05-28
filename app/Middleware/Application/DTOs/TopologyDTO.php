<?php

declare(strict_types=1);

namespace App\Middleware\Application\DTOs;

use App\Middleware\Domain\ReadModels\TopologyView;

final class TopologyDTO
{
    /**
     * @param array{
     *     producers: list<array<string, mixed>>,
     *     consumers: list<array<string, mixed>>,
     *     connections: list<array{from: string, to: string, event_type: string}>
     * } $observed
     */
    public function __construct(
        public readonly array  $producers,
        public readonly array  $bus,
        public readonly array  $consumers,
        public readonly string $generatedAt,
        public readonly array  $observed = [
            'producers'   => [],
            'consumers'   => [],
            'connections' => [],
        ],
    ) {}

    /**
     * @param array<string, mixed>|null $observedTopology
     */
    public static function fromView(TopologyView $view, ?array $observedTopology = null): self
    {
        $observed = $observedTopology ?? [
            'producers'   => [],
            'consumers'   => [],
            'connections' => [],
        ];

        return new self(
            producers:   $view->producers,
            bus:         $view->bus,
            consumers:   $view->consumers,
            generatedAt: $view->generatedAt,
            observed:    $observed,
        );
    }

    public function toArray(): array
    {
        return [
            'producers'    => $this->producers,
            'bus'          => $this->bus,
            'consumers'    => $this->consumers,
            'generated_at' => $this->generatedAt,
            'observed'     => $this->observed,
        ];
    }
}
