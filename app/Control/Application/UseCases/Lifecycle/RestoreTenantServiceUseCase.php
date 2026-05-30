<?php

declare(strict_types=1);

namespace App\Control\Application\UseCases\Lifecycle;

use App\Control\Domain\Events\TenantLifecycleRestored;
use App\Control\Domain\Policies\TenantLifecyclePolicy;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetProcessSupervisor;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class RestoreTenantServiceUseCase
{
    public function __construct(
        private readonly LocalFleetProcessSupervisor $supervisor,
        private readonly LocalFleetTenantMirror $mirror,
    ) {}

    public function execute(TenantModel $tenant): void
    {
        $currentLifecycle = TenantLifecyclePolicy::inferLifecycle($tenant);

        if (! TenantLifecyclePolicy::canRestore($tenant->status, $currentLifecycle)) {
            throw new InvalidArgumentException("Cannot restore tenant in status '{$tenant->status}'");
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $deployment = $settings['deployment'] ?? [];
        $localInstance = $deployment['local_instance'] ?? null;

        $newLifecycle = $currentLifecycle;

        if (is_array($localInstance)) {
            $port = (int) ($localInstance['port'] ?? 0);
            $envId = (string) ($localInstance['env_id'] ?? '');
            if ($port > 0 && $envId !== '') {
                // Ensure process is running
                $success = $this->supervisor->ensureRunning($envId, $port);
                if (! $success) {
                    throw new RuntimeException("Failed to confirm isolated silo process on port {$port} during restore.");
                }
                $newLifecycle = 'running';
            }
        }

        // Database transactional update
        DB::transaction(function () use ($tenant, $settings, $deployment, $newLifecycle): void {
            $settings['deployment'] = array_merge($deployment, [
                'lifecycle' => $newLifecycle,
                'lifecycle_updated_at' => now()->toIso8601String(),
            ]);

            $tenant->update([
                'status' => 'active',
                'settings' => $settings,
            ]);
        });

        // Mirror post-transition (synchronous and mandatory)
        $this->mirror->mirror($tenant->fresh());

        // Emit EDA domain event
        event(new TenantLifecycleRestored(
            tenantId: $tenant->id,
            status: 'active',
            lifecycle: $newLifecycle,
            timestamp: time()
        ));
    }
}
