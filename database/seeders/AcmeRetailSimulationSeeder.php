<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Control\Application\Services\TenantModuleCatalogService;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Deja solo el tenant de la instancia (Acme Retail) y asegura catálogo de simulación.
 */
final class AcmeRetailSimulationSeeder extends Seeder
{
    public function run(): void
    {
        $slug = Str::slug((string) config('platform.client_slug', 'acme-retail'));

        $removed = TenantModel::query()->where('slug', '!=', $slug)->delete();
        if ($removed > 0) {
            $this->command?->warn("Eliminados {$removed} tenant(s) distintos de «{$slug}».");
        }

        $tenant = TenantModel::query()->where('slug', $slug)->first();
        if ($tenant === null) {
            $this->command?->error("No existe tenant slug={$slug}. Ejecute InstanceTenantSeeder primero.");

            return;
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $catalog = $settings['modules_catalog'] ?? null;
        if (! is_array($catalog) || ! $this->catalogHasModules($catalog)) {
            $settings['modules_catalog'] = $this->defaultAcmeCatalog();
            $tenant->update(['settings' => $settings]);
            $this->command?->info('Catálogo Acme Retail aplicado en tenant.settings.modules_catalog.');
        }

        $catalogService = app(TenantModuleCatalogService::class);
        if ($catalogService->canApplyToCurrentInstance($tenant)) {
            $catalogService->applyToCurrentInstance($tenant);
            $this->command?->info('modules_config.json actualizado para la instancia.');
        }
    }

    /** @return array<string, mixed> */
    private function defaultAcmeCatalog(): array
    {
        $fixture = base_path('tests/Fixtures/clients/acmepos/modules_config.json');
        if (is_readable($fixture)) {
            try {
                $decoded = json_decode((string) file_get_contents($fixture), true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $decoded['service_contact_message'] = 'Módulos asignados por su proveedor SaaS. Elija cuáles mostrar en el dashboard.';

                    return $decoded;
                }
            } catch (\JsonException) {
                // fallback below
            }
        }

        return [
            'service_contact_message' => 'Módulos asignados por su proveedor SaaS.',
            'middleware'              => [
                'id'          => 'middleware',
                'name'        => 'Middleware bus',
                'description' => 'Bus central Acme Retail.',
                'role'        => 'routing',
            ],
            'producers'   => [
                [
                    'id'                  => 'acme_pos',
                    'name'                => 'Acme POS Terminal',
                    'event_types_emitted' => ['AcmePOS.Sale.Completed'],
                    'channels'            => ['POS'],
                ],
            ],
            'subscribers' => [
                [
                    'id'                   => 'acme_reporting',
                    'name'                 => 'Acme Reporting',
                    'event_types_consumed' => ['AcmePOS.Sale.Completed'],
                ],
            ],
        ];
    }

    /** @param array<string, mixed> $catalog */
    private function catalogHasModules(array $catalog): bool
    {
        $producers = is_array($catalog['producers'] ?? null) ? $catalog['producers'] : [];
        $subscribers = is_array($catalog['subscribers'] ?? null) ? $catalog['subscribers'] : [];

        return $producers !== [] || $subscribers !== [];
    }
}
