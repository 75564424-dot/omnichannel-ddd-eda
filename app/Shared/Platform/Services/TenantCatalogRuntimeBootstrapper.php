<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Dashboard\Application\Services\ConfiguredModuleNodeRegistrar;
use Illuminate\Support\Facades\Schema;

/**
 * Overlays tenant modules.catalog onto runtime eventbus routing on client silo boot.
 */
final class TenantCatalogRuntimeBootstrapper
{
    public function __construct(
        private readonly TenantCatalogRuntimeConfigurator $configurator,
        private readonly ConfiguredModuleNodeRegistrar $moduleNodeRegistrar,
    ) {}

    public function bootstrapIfConfigured(): void
    {
        if (config('platform.control_plane', false) || ! Schema::hasTable('tenants')) {
            return;
        }

        $catalog = config('modules.catalog', []);
        if (! is_array($catalog)) {
            return;
        }

        $producers = is_array($catalog['producers'] ?? null) ? $catalog['producers'] : [];
        $subscribers = is_array($catalog['subscribers'] ?? null) ? $catalog['subscribers'] : [];

        if ($producers === [] && $subscribers === []) {
            return;
        }

        $this->moduleNodeRegistrar->registerFromCatalog($catalog);
        $this->configurator->apply($catalog);
    }
}
