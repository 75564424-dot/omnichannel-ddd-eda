<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Modules;

use App\Control\Application\Services\ClientDashboardModulesService;
use App\Dashboard\Application\Contracts\ModulesCatalogDataProviderInterface;

final class TenantAwareModulesCatalogDataProvider implements ModulesCatalogDataProviderInterface
{
    public function __construct(
        private readonly ClientDashboardModulesService $clientModules,
    ) {}

    public function getPresentationCatalog(): array
    {
        return $this->clientModules->presentationCatalog();
    }
}
