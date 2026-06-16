<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use App\Shared\Identity\Domain\PlatformRole;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\InstanceDeploymentService;

final class CompanyShowPageService
{
    public function __construct(
        private readonly TenantPresentationService $presentation,
        private readonly GetSystemNodeStatusUseCase $nodeStatus,
        private readonly InstanceDeploymentService $deployment,
        private readonly TenantLifecycleOrchestrator $orchestrator,
    ) {}

    /** @return array<string, mixed> */
    public function buildProps(TenantModel $tenant): array
    {
        $detail = $this->presentation->tenantDetail($tenant->id);
        if ($detail === null) {
            abort(404);
        }

        $lifecycleDetails = $this->orchestrator->lifecycleStatus($tenant);
        $detail = array_merge($detail, [
            'lifecycle'         => $lifecycleDetails['lifecycle'],
            'status'            => $lifecycleDetails['status'],
            'actions_available' => $lifecycleDetails['actions_available'],
        ]);

        $nodes = $this->nodeStatus->execute()->toArray();

        return [
            'tenant' => $detail,
            'deployment' => $this->deployment->presentationForTenant($tenant),
            'health' => [
                'nodes' => $nodes['status_by_node'] ?? [],
                'last_updated' => $nodes['last_updated'] ?? null,
            ],
            'plans' => $this->presentation->availablePlans(),
            'modules' => $this->presentation->availableModuleKeys(),
            'roles' => array_map(
                fn (PlatformRole $role) => ['value' => $role->value, 'label' => $role->label()],
                array_filter(PlatformRole::cases(), fn (PlatformRole $r) => $r->isInstanceOperator()),
            ),
        ];
    }
}
