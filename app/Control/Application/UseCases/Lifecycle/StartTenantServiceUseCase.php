<?php

declare(strict_types=1);

namespace App\Control\Application\UseCases\Lifecycle;

use App\Control\Domain\Events\TenantLifecycleStarted;
use App\Control\Domain\Policies\TenantLifecyclePolicy;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetProcessSupervisor;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class StartTenantServiceUseCase
{
    public function __construct(
        private readonly LocalFleetProcessSupervisor $supervisor,
        private readonly LocalFleetTenantMirror $mirror,
    ) {}

    public function execute(TenantModel $tenant): void
    {
        $currentLifecycle = TenantLifecyclePolicy::inferLifecycle($tenant);

        if ($currentLifecycle === 'running') {
            // Idempotent start
            return;
        }

        if (! TenantLifecyclePolicy::canStart($tenant->status, $currentLifecycle)) {
            throw new InvalidArgumentException("Cannot start tenant in status '{$tenant->status}' with lifecycle '{$currentLifecycle}'");
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $deployment = $settings['deployment'] ?? null;

        if (! is_array($deployment) || ! isset($deployment['local_instance'])) {
            throw new RuntimeException("Local instance deployment details not found for tenant '{$tenant->slug}'");
        }

        $localInstance = $deployment['local_instance'];
        $port = (int) ($localInstance['port'] ?? 0);
        $envId = (string) ($localInstance['env_id'] ?? '');

        if ($port === 0 || $envId === '') {
            throw new RuntimeException("Invalid port or env ID for local instance of tenant '{$tenant->slug}'");
        }

        // Spawn process
        $success = $this->supervisor->ensureRunning($envId, $port);
        if (! $success) {
            throw new RuntimeException("Failed to spawn or confirm isolated silo process on port {$port}");
        }

        // Database transactional update
        DB::transaction(function () use ($tenant, $settings, $deployment): void {
            $settings['deployment'] = array_merge($deployment, [
                'lifecycle' => 'running',
                'lifecycle_updated_at' => now()->toIso8601String(),
            ]);

            $tenant->update(['settings' => $settings]);
        });

        // Mirror post-transition (synchronous and mandatory)
        $this->mirror->mirror($tenant->fresh());

        // Emit EDA domain event
        event(new TenantLifecycleStarted(
            tenantId: $tenant->id,
            lifecycle: 'running',
            appUrl: $localInstance['app_url'] ?? null,
            timestamp: time()
        ));
    }
}
