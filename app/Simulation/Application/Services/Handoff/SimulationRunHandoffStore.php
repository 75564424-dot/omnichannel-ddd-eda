<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Handoff;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Simulation\Application\Services\Handoff\Support\SimulationRunHandoffFileGateway;
use App\Simulation\Application\Services\Handoff\Support\SimulationRunHandoffPayloadMapper;

/**
 * Persists simulation run specs on disk so client workers can start without HTTP to control plane.
 */
final class SimulationRunHandoffStore
{
    public function __construct(
        private readonly SimulationRunHandoffPayloadMapper $payloadMapper,
        private readonly SimulationRunHandoffFileGateway $fileGateway,
    ) {}

    /**
     * @param array<string, mixed> $modulesCatalog
     */
    public function write(
        SimulationRunModel $run,
        TenantModel $tenant,
        array $modulesCatalog,
    ): void {
        $this->fileGateway->write(
            $run->id,
            $this->payloadMapper->buildDispatchPayload($run, $tenant, $modulesCatalog),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $runId): ?array
    {
        return $this->fileGateway->read($runId, useSharedLock: true);
    }

    /**
     * Fast read for control-plane polling (atomic rename writes on the worker side).
     *
     * @return array<string, mixed>|null
     */
    public function readForSync(string $runId): ?array
    {
        return $this->fileGateway->read($runId, useSharedLock: false);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function markTerminal(string $runId, string $status, array $payload): void
    {
        $data = $this->read($runId) ?? ['run_id' => $runId];
        $data = $this->payloadMapper->applyTerminal($data, $status, $payload);

        $this->fileGateway->write($runId, $data);
    }

    public function updateProgress(string $runId, int $current, int $total, string $phase = 'publishing'): void
    {
        $payload = $this->read($runId);
        if ($payload === null) {
            return;
        }

        $this->fileGateway->write(
            $runId,
            $this->payloadMapper->applyProgress($payload, $current, $total, $phase),
        );
    }

    public function requestCancel(string $runId, ?int $userId): void
    {
        $payload = $this->read($runId);
        if ($payload === null) {
            return;
        }

        $payload['cancel_requested'] = true;
        $payload['cancel_requested_at'] = now()->toIso8601String();
        if ($userId !== null) {
            $payload['cancel_requested_by_user_id'] = $userId;
        }

        $this->fileGateway->write($runId, $payload);
    }

    public function isCancelRequested(string $runId): bool
    {
        $payload = $this->readForSync($runId);

        return is_array($payload) && ($payload['cancel_requested'] ?? false) === true;
    }

    public function forget(string $runId): void
    {
        $this->fileGateway->forget($runId);
    }

    public function purgeAll(): int
    {
        return $this->fileGateway->purgeAll();
    }
}
