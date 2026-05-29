<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Modules;

use App\Dashboard\Application\Contracts\ModulesCatalogDataProviderInterface;

/**
 * Lee exclusivamente config/modules.php (respaldado por JSON en config/modules/).
 * En runtime de instancia cliente se usa TenantAwareModulesCatalogDataProvider (ver DashboardServiceProvider).
 */
final class ConfigModulesCatalogDataProvider implements ModulesCatalogDataProviderInterface
{
    public function __construct(
        private readonly ModulesCatalogNormalizer $normalizer,
    ) {}

    public function getPresentationCatalog(): array
    {
        $catalog = config('modules.catalog', []);
        if (! is_array($catalog)) {
            $catalog = [];
        }
        $msg = (string) config('modules.service_contact_message', '');

        return $this->normalizeCatalogArray($catalog, $msg !== '' ? $msg : null);
    }

    /**
     * @param array<string, mixed> $catalog
     *
     * @return array<string, mixed>
     */
    public function normalizeCatalogArray(array $catalog, ?string $serviceContactMessage = null): array
    {
        return $this->normalizer->normalizeCatalogArray($catalog, $serviceContactMessage);
    }
}
