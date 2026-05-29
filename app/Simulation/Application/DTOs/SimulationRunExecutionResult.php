<?php

declare(strict_types=1);

namespace App\Simulation\Application\DTOs;

final readonly class SimulationRunExecutionResult
{
    private function __construct(
        public bool $success,
        public ?string $infoMessage = null,
        public ?string $errorMessage = null,
        public ?string $warningMessage = null,
    ) {}

    public static function completed(string $runId, int $published): self
    {
        return new self(
            success: true,
            infoMessage: "Simulation {$runId} completed: {$published} events published.",
        );
    }

    public static function failed(string $message): self
    {
        return new self(success: false, errorMessage: $message);
    }

    public static function completedWithWarning(string $runId, int $published, string $warning): self
    {
        return new self(
            success: true,
            infoMessage: "Simulation {$runId} completed: {$published} events published.",
            warningMessage: $warning,
        );
    }
}
