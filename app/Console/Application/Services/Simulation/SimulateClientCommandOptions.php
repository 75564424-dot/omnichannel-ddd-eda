<?php

declare(strict_types=1);

namespace App\Console\Application\Services\Simulation;

use App\Shared\Platform\Services\ClientSimulationService;
use Illuminate\Console\Command;

final class SimulateClientCommandOptions
{
    public function __construct(
        public readonly string $slug,
        public readonly int $events,
        public readonly ?int $perMinute,
        public readonly int $durationMinutes,
        public readonly bool $applyFixture,
        public readonly bool $skipSync,
        public readonly bool $skipValidate,
    ) {}

    public static function fromCommand(Command $command): self
    {
        $perMinute = $command->option('per-minute');
        $perMinute = $perMinute !== null && $perMinute !== '' ? max(1, (int) $perMinute) : null;

        return new self(
            slug: strtolower(trim((string) $command->argument('slug'))),
            events: max(0, (int) $command->option('events')),
            perMinute: $perMinute,
            durationMinutes: max(1, (int) $command->option('duration-minutes')),
            applyFixture: (bool) $command->option('apply-fixture'),
            skipSync: (bool) $command->option('skip-sync'),
            skipValidate: (bool) $command->option('skip-validate'),
        );
    }

    /**
     * @return array{total: int, interval_microseconds: int|null}
     */
    public function publishPlan(): array
    {
        return ClientSimulationService::resolvePublishPlan(
            $this->events,
            $this->perMinute,
            $this->durationMinutes,
        );
    }
}
