<?php

declare(strict_types=1);

namespace App\Control\Application\UseCases\Lifecycle;

use App\Control\Domain\Events\TenantLifecycleSuspended;
use App\Control\Domain\Policies\TenantLifecyclePolicy;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetProcessSupervisorInterface;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetTenantMirrorInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;

final class SuspendTenantServiceUseCase
{
    public function __construct(
        private readonly LocalFleetProcessSupervisorInterface $supervisor,
        private readonly LocalFleetTenantMirrorInterface $mirror,
        private readonly DatabaseManager $db,
        private readonly Dispatcher $events,
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
        $this->db->connection()->transaction(function () use ($tenant, $settings, $deployment, $newLifecycle): void {
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
        $this->events->dispatch(new TenantLifecycleSuspended(
            tenantId: $tenant->id,
            status: 'suspended',
            lifecycle: $newLifecycle,
            timestamp: time()
        ));
    }
}
