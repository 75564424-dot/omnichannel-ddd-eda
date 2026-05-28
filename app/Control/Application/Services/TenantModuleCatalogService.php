<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Facades\File;
use RuntimeException;

final class TenantModuleCatalogService
{
    /** @return array<string, mixed> */
    public function presentationForTenant(TenantModel $tenant): array
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $limits = is_array($settings['module_limits'] ?? null)
            ? $settings['module_limits']
            : config('module_blueprint.default_limits', []);

        $catalog = $this->getCatalog($tenant);

        return [
            'tenant' => [
                'id'   => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            'limits' => [
                'producers_max'   => (int) ($limits['producers_max'] ?? 4),
                'subscribers_max' => (int) ($limits['subscribers_max'] ?? 2),
            ],
            'catalog'                  => $catalog,
            'counts'                   => [
                'producers'   => count($catalog['producers'] ?? []),
                'subscribers' => count($catalog['subscribers'] ?? []),
            ],
            'can_apply_to_instance'    => $this->canApplyToCurrentInstance($tenant),
            'instance_slug'            => (string) config('platform.client_slug', ''),
            'manual'                   => config('control_module_manual', []),
        ];
    }

    /** @return array<string, mixed> */
    public function getCatalog(TenantModel $tenant): array
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $stored = $settings['modules_catalog'] ?? null;

        if (is_array($stored) && $stored !== []) {
            return $this->normalizeCatalog($stored, $tenant->name);
        }

        return $this->defaultCatalogForTenant($tenant);
    }

    /**
     * @param array<string, mixed> $catalog
     * @param array<string, int>   $limits
     */
    public function saveCatalog(TenantModel $tenant, array $catalog, array $limits): void
    {
        $normalized = $this->normalizeCatalog($catalog, $tenant->name);
        $producersMax = max(0, (int) ($limits['producers_max'] ?? 4));
        $subscribersMax = max(0, (int) ($limits['subscribers_max'] ?? 2));

        if (count($normalized['producers']) > $producersMax) {
            throw new RuntimeException("Máximo {$producersMax} productores permitidos para este tenant.");
        }
        if (count($normalized['subscribers']) > $subscribersMax) {
            throw new RuntimeException("Máximo {$subscribersMax} suscriptores permitidos para este tenant.");
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['modules_catalog'] = $normalized;
        $settings['module_limits'] = [
            'producers_max'   => $producersMax,
            'subscribers_max' => $subscribersMax,
        ];
        $settings['modules_catalog_updated_at'] = now()->toIso8601String();

        $tenant->update(['settings' => $settings]);
    }

    public function canApplyToCurrentInstance(TenantModel $tenant): bool
    {
        $instanceSlug = strtolower(trim((string) config('platform.client_slug', '')));

        return $instanceSlug !== '' && $instanceSlug === strtolower($tenant->slug);
    }

    public function applyToCurrentInstance(TenantModel $tenant): void
    {
        if (! $this->canApplyToCurrentInstance($tenant)) {
            throw new RuntimeException('Este tenant no corresponde a la instancia desplegada en este servidor.');
        }

        $path = config_path('modules/modules_config.json');
        $catalog = $this->getCatalog($tenant);

        File::put(
            $path,
            json_encode($catalog, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n",
        );
    }

    /** @return array<string, mixed> */
    private function defaultCatalogForTenant(TenantModel $tenant): array
    {
        $defaults = config('module_blueprint.default_catalog', []);
        if (! is_array($defaults)) {
            $defaults = [];
        }

        $catalog = $defaults;
        if (is_array($catalog['middleware'] ?? null)) {
            $catalog['middleware']['description'] = 'Catálogo para '.$tenant->name;
        }
        $catalog['service_contact_message'] = 'Catálogo de '.$tenant->name.' — gestionado desde control SaaS.';

        return $this->normalizeCatalog($catalog, $tenant->name);
    }

    /**
     * @param array<string, mixed> $raw
     *
     * @return array<string, mixed>
     */
    private function normalizeCatalog(array $raw, string $tenantName): array
    {
        $defaults = config('module_blueprint.default_catalog', []);
        $middleware = is_array($raw['middleware'] ?? null)
            ? array_merge(is_array($defaults['middleware'] ?? null) ? $defaults['middleware'] : [], $raw['middleware'])
            : (is_array($defaults['middleware'] ?? null) ? $defaults['middleware'] : []);

        return [
            'service_contact_message' => trim((string) ($raw['service_contact_message'] ?? ''))
                ?: 'Catálogo de '.$tenantName,
            'middleware'              => [
                'id'          => trim((string) ($middleware['id'] ?? 'middleware')) ?: 'middleware',
                'name'        => trim((string) ($middleware['name'] ?? 'Middleware bus')) ?: 'Middleware bus',
                'description' => trim((string) ($middleware['description'] ?? '')),
                'role'        => trim((string) ($middleware['role'] ?? 'routing')) ?: 'routing',
            ],
            'producers'   => $this->normalizeProducers($raw['producers'] ?? []),
            'subscribers' => $this->normalizeSubscribers($raw['subscribers'] ?? []),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function normalizeProducers(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $out[] = [
                'id'                  => $id,
                'name'                => $name,
                'event_types_emitted' => $this->stringList($row['event_types_emitted'] ?? []),
                'channels'            => $this->stringList($row['channels'] ?? []),
            ];
        }

        return $out;
    }

    /** @return list<array<string, mixed>> */
    private function normalizeSubscribers(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $out[] = [
                'id'                   => $id,
                'name'                 => $name,
                'event_types_consumed' => $this->stringList($row['event_types_consumed'] ?? []),
            ];
        }

        return $out;
    }

    /** @return list<string> */
    private function stringList(mixed $raw): array
    {
        if (is_string($raw)) {
            $parts = preg_split('/[\s,;]+/', $raw) ?: [];

            return array_values(array_unique(array_filter(array_map('trim', $parts))));
        }

        if (! is_array($raw)) {
            return [];
        }

        $list = [];
        foreach ($raw as $item) {
            $s = trim((string) $item);
            if ($s !== '') {
                $list[] = $s;
            }
        }

        return array_values(array_unique($list));
    }
}
