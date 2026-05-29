<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Orchestration;


use App\Simulation\Domain\ValueObjects\SimulationMessages;
use App\Control\Infrastructure\Models\SimulationRunModel;

/**
 * Marks active simulation runs as failed (replacement, reset, etc.).
 */
final class SimulationStaleRunReplacer
{
    public function replaceActiveForTenant(string $tenantId, string $message = SimulationMessages::REPLACED_BY_NEW_RUN): int
    {
        return SimulationRunModel::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [SimulationRunModel::STATUS_PENDING, SimulationRunModel::STATUS_RUNNING])
            ->update([
                'status'        => SimulationRunModel::STATUS_FAILED,
                'finished_at'   => now(),
                'error_message' => $message,
            ]);
    }

    public function replaceAllActive(string $message): int
    {
        return SimulationRunModel::query()
            ->whereIn('status', [SimulationRunModel::STATUS_PENDING, SimulationRunModel::STATUS_RUNNING])
            ->update([
                'status'        => SimulationRunModel::STATUS_FAILED,
                'finished_at'   => now(),
                'error_message' => $message,
            ]);
    }
}
