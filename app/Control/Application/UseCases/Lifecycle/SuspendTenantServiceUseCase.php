<?php

declare(strict_types=1);

namespace App\Control\Application\UseCases\Lifecycle;

use App\Control\Domain\Events\TenantLifecycleSuspended;
use App\Control\Domain\Policies\TenantLifecyclePolicy;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetProcessSupervisor;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class SuspendTenantServiceUseCase
{
    public function __construct(
        private readonly LocalFleetProcessSupervisor $supervisor,
        private readonly LocalFleetTenantMirror $mirror,
    ) {}

    public function execute(TenantModel $tenant): void
    {
        $currentLifecycle = TenantLifecyclePolicy::inferLifecycle($tenant);

        if (! TenantLifecyclePolicy::canSuspend($tenant->status, $currentLifecycle)) {
            throw new InvalidArgumentException("Cannot suspend tenant in status '{$tenant->status}'");
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $deployment = $settings['deployment'] ?? [];
        $localInstance = $deployment['local_instance'] ?? null;

        $shouldStop = (bool) config('platform.local_fleet.stop_on_suspend', false);
        $newLifecycle = $currentLifecycle;

        if ($shouldStop && is_array($localInstance)) {
            $port = (int) ($localInstance['port'] ?? 0);
            $envId = (string) ($localInstance['env_id'] ?? '');
            if ($port > 0 && $envId !== '') {
                $this->supervisor->stop($envId, $port);
                $newLifecycle = 'stopped';
            }
        }

        // Database transactional update
        DB::transaction(function () use ($tenant, $settings, $deployment, $newLifecycle): void {
            $settings['deployment'] = array_merge($deployment, [
                'lifecycle' => $newLifecycle,
                'lifecycle_updated_at' => now()->toIso8601String(),
            ]);

            $tenant->update([
                'status' => 'suspended',
                'settings' => $settings,
            ]);
        });

        // Mirror post-transition (synchronous and mandatory)
        $this->mirror->mirror($tenant->fresh());

        // Emit EDA domain event
        event(new TenantLifecycleSuspended(
            tenantId: $tenant->id,
            status: 'suspended',
            lifecycle: $newLifecycle,
            timestamp: time()
        ));
    }
}
